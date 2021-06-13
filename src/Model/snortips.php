<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once($MODEL_PATH.'/model.php');

class Snortips extends Model
{
	public $Name= 'snortips';
	public $User= 'root';

	private $sigMsgFile= '/var/tmp/snortips.sigmsg';

	public $NVPS= '\h';
	public $ConfFile= '/etc/snort/snortips.conf';
	public $LogFile= '/var/log/snortips.log';
	
	public $VersionCmd= 'unset LC_ALL; unset LANG; /usr/local/sbin/snortips -V';
	
	public $PidFile= '/var/run/snortips.pid';
	
	function __construct()
	{
		parent::__construct();
		
		$this->StartCmd= 'unset LC_ALL; unset LANG; /usr/local/sbin/snortips';

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'AddIPToList'	=> array(
					'argv'	=> array(NAME, IPADR|IPRANGE),
					'desc'	=> _('Add to IP list'),
					),

				'DelIPFromList'	=> array(
					'argv'	=> array(NAME, SERIALARRAY),
					'desc'	=> _('Delete IP from list'),
					),

				'UnblockAll'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Unblock all'),
					),

				'UnblockIPs'	=> array(
					'argv'	=> array(SERIALARRAY),
					'desc'	=> _('Unblock IPs'),
					),

				'BlockIP'	=> array(
					'argv'	=> array(IPADR|IPRANGE, NUM|EMPTYSTR),
					'desc'	=> _('Block IPs'),
					),

				'GetInfo'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get IPS info'),
					),

				'GetListIPs'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Get listed ips'),
					),

				'GetKeywords'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get alert keywords'),
					),

				'AddKeyword'	=> array(
					/// @todo Is there any pattern or size for keywords?
					'argv'	=> array(STR),
					'desc'	=> _('Add alert keyword'),
					),

				'DelKeyword'	=> array(
					'argv'	=> array(STR),
					'desc'	=> _('Delete alert keywords'),
					),
				)
			);
	}

	function Stop()
	{
		return $this->Kill();
	}
	
	/**
	 * Searches for an IP or !IP.
	 *
	 * @param string $ip Search string.
	 * @return mixed Matched string or FALSE on failure.
	 */
	function GetIP($ip)
	{
		$ip= Escape($ip, '/.');
		return $this->SearchFile($this->ConfFile, "/^\h*($ip)\b\h*$/m");
	}

	function GetInfo()
	{
		global $Re_Ip, $Re_Net;

		// Clear the file if this function spawns too fast (do not read the same contents again)
		$this->PutFile('/var/db/snortips', '');
		
		if ($this->IsRunning()) {
			$this->RunShellCommand('/bin/kill -INFO $(/bin/cat /var/run/snortips.pid)');
			
			/// @todo Use communication over shared memory (e.g. pipes) with SnortIPS instead
			$eof= FALSE;
			$count= 0;
			while ($count++ < self::PROC_STAT_TIMEOUT) {
				if ($items= $this->GetFile('/var/db/snortips')) {
					// Check for EOF
					if (preg_match("/^\.$/m", $items)) {
						$eof= TRUE;
						break;
					}
				}
				exec('/bin/sleep ' . self::PROC_STAT_SLEEP_TIME);
			}
			
			if ($eof) {
				$info= array(
					'Whitelisted' => array(),
					'Blocked' => array(),
					'Blacklisted' => array(),
					);

				$items= explode("\n", $items);
				foreach ($items as $line) {
					if (preg_match("/^($Re_Net|$Re_Ip) \((\d+)\)$/", $line, $match)) {
						$host= $match[1];
						$expires= $match[2];
						$info['Blocked'][$host]= $expires.' ('. floor($expires / 60) .' '._('min').' '. $expires % 60 .' '._('sec').')';
					}
					else if (preg_match("/^!($Re_Net|$Re_Ip)/", $line, $match)) {
						$info['Whitelisted'][]= $match[1];
					}
					else if (preg_match("/^($Re_Net|$Re_Ip)/", $line)) {
						$info['Blacklisted'][]= $line;
					}
				}
				return Output(json_encode($info));
			}
		}
		return FALSE;
	}

	/**
	 * Deletes a list of IPs from a list.
	 *
	 * Does not allow system IPs to be deleted.
	 *
	 * @param string $list List name
	 * @param array $ips List of IPs to delete
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DelIPFromList($list, $ips)
	{
		$systemips[]= '127.0.0.1';
		if ($list == 'whitelist') {
			$ifs= explode("\n", $this->_getPhyIfs());
			foreach ($ifs as $if) {
				if ($ifip= $this->getIpAddr($if)) {
					$systemips[]= $ifip;
				}
			}
		}

		$ips= json_decode($ips, TRUE);
		$retval= TRUE;
		foreach ($ips as $ip) {
			if (!in_array($ip, $systemips)) {
				$method= $list == 'whitelist' ? 'DelAllowedIp' : 'DelRestrictedIp';
				$retval&= $this->$method($ip);
			}
			else {
				Error(_('You cannot delete system IP address').": $ip");
				$retval= FALSE;
			}
		}
		return $retval;
	}

	/**
	 * Extracts IP address assigned to an interface.
	 *
	 * @param string $if Interface name.
	 * @return mixed IP of the interface or FALSE.
	 */
	function getIpAddr($if)
	{
		global $Re_Ip;

		if (file_exists("/etc/hostname.$if")) {
			return $this->SearchFile("/etc/hostname.$if", "/^\h*inet\h*($Re_Ip)\h*$Re_Ip\b.*$/m");
		}
		return FALSE;
	}

	/**
	 * Adds an IP to snortips white or black list.
	 *
	 * @param string $list List name
	 * @param string $ip IP or net
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function AddIPToList($list, $ip)
	{
		global $preIP, $preNet;

		/// Check the IP in the complement list
		$compip= $list == 'whitelist' ? $ip : "!$ip";
		
		$output= $this->GetIP($compip);
		if ($output !== $compip) {
			$output= $this->RunShellCommand("/sbin/pfctl -nv -t snortips -T add $ip 2>&1");
			$output= explode("\n", $output);
			// 0/1 addresses added (dummy).
			if (preg_match('/^(\d+)\/\d+ addresses added \(dummy\)\.$/', $output[0], $match)) {
				if ($match[1] > 0) {
					if (preg_match("/^A\s+($preIP|$preNet)$/", $output[1], $match)) {
						$actualadded= $match[1];
						// 192.168.1.1/32 and 192.168.1.1 are identical, but we miss it above
						$compip= $list == 'whitelist' ? $actualadded : "!$actualadded";
						$output= $this->GetIP($compip);
						if ($output !== $compip) {
							$method= $list == 'whitelist' ? 'AddAllowedIp' : 'AddRestrictedIp';
							$retval= $this->$method($actualadded);
							if ($actualadded == $ip) {
								return $retval;
							}
							Error(_('IP or network address fixed').": $ip -> $actualadded");
						}
						else {
							Error(_('White and black list entries should not be identical').": $actualadded");
						}
					}
				}
				else {
					Error(_('Cannot add').": $ip\n$output[0]");
				}
			}
			else {
				Error(_('Pfctl output does not match').": $ip\n$output[0]");
			}
		}
		else {
			Error(_('White and black list entries should not be identical').": $ip");
		}
		return FALSE;
	}

	/**
	 * Unblock all
	 */
	function UnblockAll()
	{
		return $this->RunShellCommand('/bin/kill -USR2 $(/bin/cat /var/run/snortips.pid)');
	}

	/**
	 * Unblock IPs
	 */
	function UnblockIPs($ips)
	{
		$ips= json_decode($ips, TRUE);
		$contents= array();
		foreach ($ips as $ip) {
			$contents[]= "U $ip\n";
		}
		// file_put_contents() accepts array as data
		file_put_contents($this->sigMsgFile, $contents, LOCK_EX);
		return $this->RunShellCommand('/bin/kill -USR1 $(/bin/cat /var/run/snortips.pid)');
	}

	/**
	 * Unblock IPs
	 */
	function BlockIP($ip, $time= '')
	{
		file_put_contents($this->sigMsgFile, rtrim("B $ip $time"), LOCK_EX);
		return $this->RunShellCommand('/bin/kill -USR1 $(/bin/cat /var/run/snortips.pid)');
	}

	/**
	 * Provides a list of IPs.
	 */
	function GetListIPs($list)
	{
		global $Re_Ip, $Re_Net;
		
		return Output($this->SearchFileAll($this->LISTS[$list], "/^\h*($Re_Ip|$Re_Net)\h*$/m"));
	}

	/**
	 * Get keywords.
	 */
	function GetKeywords()
	{
		return Output($this->SearchFileAll($this->ConfFile, "/^\h*Keyword\h+\"(.*)\"\h*$/m"));
	}
	
	/**
	 * Add a keyword.
	 */
	function AddKeyword($keyword)
	{
		$this->DelKeyword($keyword);
		return $this->AppendToFile($this->ConfFile, "Keyword \"$keyword\"");
	}

	/**
	 * Delete a keyword.
	 */
	function DelKeyword($keyword)
	{
		$keyword= Escape($keyword, '/');
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*Keyword\h+\"$keyword\"\s*)/m", '');
	}

	/**
	 * Parses snortips IPS logs.
	 *
	 * @param string $logline Log line to parse.
	 * @param array $cols Parser output, parsed fields.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip, $Re_Net;

		if ($this->ParseSyslogLine($logline, $cols)) {
			// Unblocking host 10.0.1.13
			$re= "/^Unblocking host ($Re_Ip|$Re_Net)$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['Unblocking']= $match[1];
			}
			else {
				// Blocking host $host as blacklisted
				$re= "/^Blocking host ($Re_Ip|$Re_Net) as blacklisted$/";
				if (preg_match($re, $cols['Log'], $match)) {
					$cols['Blocked']= $match[1];
				}
				else {
					// Host $host is already blocked; blocking as blacklisted
					$re= "/^Host ($Re_Ip|$Re_Net) is already blocked; blocking as blacklisted$/";
					if (preg_match($re, $cols['Log'], $match)) {
						$cols['Blocked']= $match[1];
					}
					else {
						// Blocking host 11.11.11.12 for 3600 ticks
						$re= "/^Blocking host ($Re_Ip|$Re_Net) for (\d+) ticks$/";
						if (preg_match($re, $cols['Log'], $match)) {
							$cols['Blocked']= $match[1];
							$cols['BlockedTime']= $match[2];
						}
						else {
							// Host 11.11.11.11 is already blocked; extending amnesty to 7200 ticks
							$re= "/^Host ($Re_Ip|$Re_Net) is already blocked; extending block duration by (\d+) ticks$/";
							if (preg_match($re, $cols['Log'], $match)) {
								$cols['Extended']= $match[1];
								$cols['ExtendedTime']= $match[2];
							}
							else {
								// Soft init requested, unblocking and zeroing all
								$re= "/^Soft init requested, unblocking and zeroing all$/";
								if (preg_match($re, $cols['Log'], $match)) {
									$cols['Softinit']= $cols['Date'].' - '.substr($cols['Time'], 0, 2)._('h');
								}
							}
						}
					}
				}
			}
			return TRUE;
		}
		return FALSE;
	}

	function GetDateRegexp($date)
	{
		global $MonthNames;

		if ($date['Month'] == '') {
			$re= '.*';
		}
		else {
			$re= $MonthNames[$date['Month']].'\s+';
			if ($date['Day'] == '') {
				$re.= '.*';
			}
			else {
				$re.= sprintf('%02d', $date['Day']);
			}
		}
		return $re;
	}

	function formatDateHourRegexp($month, $day, $hour, $minute)
	{
		return $this->formatDateHourRegexpDayLeadingZero($month, $day, $hour, $minute);
	}
}

$ModelConfig = array(
    'Priority' => array(
        'type' => UINT,
		),
    'AndPrioKey' => array(
        'type' => STR_yes_no,
		),
    'BlockDuration' => array(
        'type' => UINT,
		),
    'MaxBlockDuration' => array(
        'type' => UINT,
		),
	);
?>
