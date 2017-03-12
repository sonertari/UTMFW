<?php
/*
 * Copyright (C) 2004-2017 Soner Tari
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

class Dhcpd extends Model
{
	public $Name= 'dhcpd';
	public $User= '_dhcp';
	
	/// IPs distributed by DHCP server
	private $leasesFile= '/var/db/dhcpd.leases';
	
	public $ConfFile= '/etc/dhcpd.conf';
	
	public $LogFile= '/var/log/dhcpd.log';
						
	function __construct()
	{
		parent::__construct();
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetIfs'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get DHCP interfaces'),
					),

				'GetOption'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Get DHCP option'),
					),

				'GetRange'	=>	array(
					'argv'	=> array(),
					'desc'	=> _('Get DHCP IP range'),
					),

				'AddIf'	=>	array(
					'argv'	=> array(NAME),
					'desc'	=> _('Add DHCP interface'),
					),

				'DelIf'	=>	array(
					'argv'	=> array(NAME),
					'desc'	=> _('Delete DHCP interface'),
					),

				'GetArpTable'	=>	array(
					'argv'	=> array(),
					'desc'	=> _('Get arp table'),
					),

				'GetLeases'	=>	array(
					'argv'	=> array(),
					'desc'	=> _('Get dhcp leases'),
					),

				'SetOptions'=>	array(
					'argv'	=> array(IPADR, IPADR, IPADR, IPADR, IPADR, IPADR),
					'desc'	=> _('Set configuration'),
					),

				'SetDhcpdConf'	=>	array(
					'argv'	=> array(IPADR, IPADR, IPADR, IPADR, IPADR, IPADR),
					'desc'	=> _('Set dhcpd conf'),
					),
				)
			);
	}
	
	/**
	 * Starts dhcpd with the list of interfaces obtained from rc.conf.local file.
	 *
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Start()
	{
		if (($ifs= $this->_getIfs()) !== FALSE) {
			$ifs= explode("\n", $ifs);
			$ifs= implode(' ', $ifs);
			return $this->RunShellCommand("/usr/sbin/dhcpd $ifs");
		}
		return FALSE;
	}
	
	/**
	 * Gets the list of dhcpd interfaces from rc.conf.local file.
	 *
	 * @return string List of interfaces.
	 */
	function GetIfs()
	{
		return Output($this->_getIfs());
	}

	function _getIfs()
	{
		$ifs= $this->SearchFile($this->rcConfLocal, '/^\h*#*\h*dhcpd_flags\h*=\h*("[^#"]*"|)(\h*|\h*#.*)$/m', 1, '"');
		return implode ("\n", preg_split('/\h+/', $ifs));
	}

	/**
	 * Adds a dhcpd interface to listen.
	 *
	 * @param string $if Interface to add.
	 */
	function AddIf($if)
	{
		// Clean up any duplicates first
		$this->DelIf($if);
		if (($ifs= $this->_getIfs()) !== FALSE) {
			$ifs= explode("\n", $ifs);
			$ifs= trim(implode(' ', $ifs)." $if");
			return $this->SetIfs($ifs);
		}
		return FALSE;
	}

	/**
	 * Deletes a dhcpd interface to listen.
	 *
	 * @param string $if Interface to delete.
	 */
	function DelIf($if)
	{
		if (($ifs= $this->_getIfs()) !== FALSE) {
			$ifs= explode("\n", $ifs);

			// Don't use: unset($ifs[array_search($if, $ifs)]), check strictly
			if (($key= array_search($if, $ifs)) !== FALSE) {
				unset($ifs[$key]);
				$ifs= implode(' ', $ifs);
				return $this->SetIfs($ifs);
			}
		}
		return FALSE;
	}

	/**
	 * Sets dhcpd interfaces.
	 *
	 * @param string $ifs List of ifs separated by spaces.
	 */
	function SetIfs($ifs)
	{
		return $this->ReplaceRegexp($this->rcConfLocal, '/^(\h*#*\h*dhcpd_flags\h*=\h*)("[^#"]*"|)(\h*|\h*#.*)$/m', '${1}"'.$ifs.'"${3}');
	}

	/**
	 * Reads the value of the given dhcpd option.
	 *
	 * DHCP server options are usually IPs. Range is read by GetRange().
	 *
	 * @param string $option Option name to get the value of.
	 * @return string Option value.
	 */
	function GetOption($option)
	{
		return Output($this->SearchFile($this->ConfFile, "/^\h*option\h*$option\h*([^#;]*)\h*\;\h*$/m"));
	}

	/**
	 * Reads dhcpd range option.
	 *
	 * @return string IP range.
	 */
	function GetRange()
	{
		global $Re_Ip;
		
		$re= "/^\h*range\h*(($Re_Ip)\h*($Re_Ip))\h*\;\h*$/m";
		if (($output= $this->SearchFile($this->ConfFile, $re)) !== FALSE) {
			return Output(preg_replace("/\s+/", "\n", $output));
		}
		return FALSE;
	}

	/**
	 * Sets dhcpd configuration.
	 *
	 * @param string $dns DNS server.
	 * @param string $router Gateway.
	 * @param string $mask Netmask.
	 * @param string $bc Broadcast address.
	 * @param string $lr Lower IP range.
	 * @param string $ur Upper IP range.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetOptions($dns, $router, $mask, $bc, $lr, $ur)
	{
		$retval=  $this->SetOption('domain-name-servers', $dns);
		$retval&= $this->SetOption('routers', $router);
		$retval&= $this->SetOption('subnet-mask', $mask);
		$retval&= $this->SetOption('broadcast-address', $bc);
		$retval&= $this->SetRange($lr, $ur);
		return $retval;
	}

	/**
	 * Sets the value of the given dhcpd option.
	 *
	 * @param string $option Option name to set the value of.
	 * @param string $value Option value to set.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetOption($option, $value)
	{
		global $Re_Ip;
		
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*option\h*$option\b\h*)($Re_Ip)(\h*\;\h*)$/m", '${1}'.$value.'${3}');
	}

	/**
	 * Sets dhcpd range option.
	 *
	 * @param string $lower Lower limit of IP range.
	 * @param string $upper Upper limit of IP range.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetRange($lower, $upper)
	{
		global $Re_Ip;
		
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*range\h*)($Re_Ip)(\h+)($Re_Ip)(\h*\;\h*)$/m", '${1}'.$lower.'${3}'.$upper.'${5}');
	}

	/**
	 * Sets dhcpd configuration.
	 *
	 * @param string $lanip IP address.
	 * @param string $lanmask Address netmask.
	 * @param string $lannet Subnet.
	 * @param string $lanbc Broadcast address.
	 * @param string $lanmin Lower limit of IP range.
	 * @param string $lanmax Upper limit of IP range.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetDhcpdConf($lanip, $lanmask, $lannet, $lanbc, $lanmin, $lanmax)
	{
		global $Re_Ip;
	
		$retval=  $this->ReplaceRegexp($this->ConfFile, "/^(\h*subnet\h+)($Re_Ip)(\h+netmask\h+$Re_Ip\h*\{.*)/m", '${1}'.$lannet.'${3}');
		$retval&= $this->ReplaceRegexp($this->ConfFile, "/^(\h*subnet\h+$Re_Ip\h+netmask\h+)($Re_Ip)(\h*\{.*)/m", '${1}'.$lanmask.'${3}');
		$retval&= $this->SetOptions($lanip, $lanip, $lanmask, $lanbc, $lanmin, $lanmax);
		return $retval;
	}

	/**
	 * Gets arp table.
	 * 
	 * Gets and parses the output of the arp command.
	 * 
	 * @return array Arp table.
	 */
	function GetArpTable()
	{
		global $preIP, $preMac, $preIfName;

		$lines= $this->RunShellCommand('/usr/sbin/arp -an');

		//Host                                 Ethernet Address   Netif Expire     Flags
		//192.168.0.1                          00:0c:29:a2:8c:56    em1 permanent  l
		//192.168.0.3                          00:0c:29:d3:9b:50    em1 14m40s
		$re_arp= "/^($preIP)\s+($preMac)\s+($preIfName)\s+(\S+).*$/m";

		$logs= array();
		if (preg_match_all($re_arp, $lines, $match, PREG_SET_ORDER)) {
			foreach ($match as $fields) {
				$cols['IP']= $fields[1];
				$cols['MAC']= $fields[6];
				$cols['Interface']= $fields[7];
				$cols['Expire']= $fields[8];
				$logs[]= $cols;
			}
		}
		return Output(json_encode($logs));
	}

	/**
	 * Gets dhcpd leases.
	 * 
	 * Gets and parses the leases file.
	 * 
	 * @return array IP leases.
	 */
	function GetLeases()
	{
		global $Re_Ip;

		// lease 192.168.0.3 {
		// 	starts 3 2016/07/13 00:15:18 UTC;
		// 	ends 3 2016/07/13 12:15:18 UTC;
		// 	hardware ethernet 00:0c:29:d3:9b:50;
		// 	uid 01:00:0c:29:d3:9b:50;
		// 	client-hostname "obsd59";
		// }
		// lease 192.168.0.2 {
		// 	starts 2 2016/07/12 23:09:32 UTC;
		// 	ends 2 2016/07/12 23:11:32 UTC;
		// 	hardware ethernet 00:50:56:c0:00:01;
		// 	uid 01:00:50:56:c0:00:01;
		// 	client-hostname "Soner";
		// }
		
		$re_starts= '\s*starts\s+(\d+)\s+(\d+\/\d+\/\d+)\s+(\d+:\d+:\d+\s+\w*)\s*';
		$re_ends= '\s*ends\s+(\d+)\s+(\d+\/\d+\/\d+)\s+(\d+:\d+:\d+\s+\w*)\s*';
		$re_mac= '\s*hardware\s+\w+\s+(\w+:\w+:\w+:\w+:\w+:\w+)\s*';
		$re_uid= '\s*uid\s+(.+)\s*';
		$re_host= '\s*(client-hostname|hostname)\s+"(.+)"\s*';
		$re_abandoned= '(\s*(abandoned);\s*|)';
		
		$re_lease= "/\s*lease\s+($Re_Ip)\s*\{$re_starts;$re_ends;$re_mac;($re_uid;|)$re_host;$re_abandoned\s*\}\s*/m";

		$lines= $this->GetFile($this->leasesFile);
		
		$logs= array();
		if (preg_match_all($re_lease, $lines, $match, PREG_SET_ORDER)) {
			foreach ($match as $fields) {
				$cols['IP']= $fields[1];
				$cols['Start']= "$fields[3] $fields[4]";
				$cols['End']= "$fields[6] $fields[7]";
				$cols['MAC']= $fields[8];
				$cols['Host']= "$fields[12]";
				$cols['Status']= $fields[14];
				$logs[]= $cols;
			}
		}
		return Output(json_encode($logs));
	}
}
?>
