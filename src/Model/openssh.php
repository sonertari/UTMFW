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

class Openssh extends Model
{
	public $Name= 'openssh';
	public $User= 'root';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/ssh/sshd_config';
	public $LogFile= '/var/log/authlog';
	
	public $VersionCmd= '/usr/bin/ssh -V 2>&1';
	
	public $PidFile= '/var/run/sshd.pid';
	
	function __construct()
	{
		parent::__construct();
		
		$this->Proc= 'sshd';
		$this->StartCmd= '/usr/sbin/sshd';
	}
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_user= '((invalid user\s+|)(\S+))';
			$re_clientip= "($Re_Ip)";
			$re_num= '(\d+)';
			$re_type= '(.*)';

			// Failed password for invalid user soner from 81.215.105.114 port 27836 ssh2
			// Failed password for root from 81.215.105.114 port 29782 ssh2
			// Failed none for invalid user soner from 81.215.105.114 port 40401 ssh2
			$re= "/Failed\s+(.*)\s+for\s+$re_user\s+from\s+$re_clientip\s+port\s+$re_num\s+$re_type$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Accepted']= FALSE;
				$cols['Reason']= $match[1];
				$cols['User']= $match[4];
				$cols['IP']= $match[5];
				$cols['Type']= $match[7];
			}
			else {
				// Accepted password for root from 81.215.105.114 port 47179 ssh2
				// Accepted publickey for root from 81.215.105.114 port 58402 ssh2
				// Accepted publickey for root from 81.215.105.114 port 58402 ssh2: RSA SHA256:<key>
				$re= "/Accepted\s+(.*)\s+for\s+$re_user\s+from\s+$re_clientip\s+port\s+$re_num\s+$re_type$/";
				if (preg_match($re, $logline, $match)) {
					$cols['Accepted']= TRUE;
					$cols['User']= $match[4];
					$cols['IP']= $match[5];
					$cols['Type']= $match[7];
				}
			}
			return TRUE;
		}
		return FALSE;
	}

	function _getModuleStatus($generate_info= FALSE, $start= 0)
	{
		$status= parent::_getModuleStatus($generate_info, $start);

		if ($generate_info) {
			$logs= $this->GetLastLogs('( Accepted | Failed )', $start);
			$accepted= 0;
			$failed= 0;
			foreach ($logs as $l) {
				if ($l['Accepted']) {
					$accepted++;
				} else {
					$failed++;
				}
			}
			$status['info']['accepted']= $accepted;
			$status['info']['failed']= $failed;
		}
		return $status;
	}

	function _getFileLineCount($file, $re= '', $needle= '', $month='', $day='', $hour='', $minute='')
	{
		if (!$this->ValidateFile($file)) {
			return FALSE;
		}

		$cmd= "/usr/bin/grep -a ' sshd\[' $file";

		if ($month != '' || $day != '' || $hour != '' || $minute != '') {
			$cmd.= ' | /usr/bin/grep -a -E "' . $this->formatDateHourRegexp($month, $day, $hour, $minute) . '"';
		}

		if ($needle != '') {
			$needle= escapeshellarg($needle);
			$cmd.= " | /usr/bin/grep -a -E $needle";
		}

		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}

		$cmd.= ' | /usr/bin/wc -l';
		
		// OpenBSD wc returns with leading blanks
		return trim($this->RunShellCommand($cmd));
	}

	function GetLogs($file, $end, $count, $re= '', $needle= '', $month='', $day='', $hour='', $minute='')
	{
		if (!$this->ValidateFile($file)) {
			return FALSE;
		}

		$cmd= "/usr/bin/grep -a ' sshd\[' $file";

		if ($month != '' || $day != '' || $hour != '' || $minute != '') {
			$cmd.= ' | /usr/bin/grep -a -E "' . $this->formatDateHourRegexp($month, $day, $hour, $minute) . '"';
		}

		if ($needle != '') {
			$needle= escapeshellarg($needle);
			$cmd.= " | /usr/bin/grep -a -E $needle";
		}

		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}

		$cmd.= " | /usr/bin/head -$end | /usr/bin/tail -$count";
		
		$lines= explode("\n", $this->RunShellCommand($cmd));
		
		$logs= array();
		foreach ($lines as $line) {
			unset($cols);
			if ($this->ParseLogLine($line, $cols)) {
				$logs[]= $cols;
			}
		}
		return Output(json_encode($logs));
	}
	
	function _getLiveLogs($file, $count, $re= '', $needle= '')
	{
		if (!$this->ValidateFile($file)) {
			return FALSE;
		}

		$cmd= "/usr/bin/grep -a ' sshd\[' $file";
		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}
		$cmd.= " | /usr/bin/tail -$count";

		$lines= explode("\n", $this->RunShellCommand($cmd));
		
		$logs= array();
		foreach ($lines as $line) {
			if ($this->ParseLogLine($line, $cols)) {
				$logs[]= $cols;
			}
		}
		return $logs;
	}
}

$ModelConfig = array(
    'Port' => array(
        'type' => PORT,
		),
    'Protocol' => array(
        'type' => UINT,
		),
    'AddressFamily' => array(
		),
    'ListenAddress' => array(
        'type' => IP,
		),
    'ServerKeyBits' => array(
        'type' => UINT,
		),
    'SyslogFacility' => array(
		),
    'LogLevel' => array(
		),
    'LoginGraceTime' => array(
		),
    'PermitRootLogin' => array(
        'type' => STR_yes_no,
		),
    'MaxAuthTries' => array(
        'type' => UINT,
		),
    'PermitEmptyPasswords' => array(
        'type' => STR_yes_no,
		),
    'PrintMotd' => array(
        'type' => STR_yes_no,
		),
    'PrintLastLog' => array(
        'type' => STR_yes_no,
		),
    'TCPKeepAlive' => array(
        'type' => STR_yes_no,
		),
    'UseDNS' => array(
        'type' => STR_yes_no,
		),
    'PidFile' => array(
		),
    'MaxStartups' => array(
		),
    'Banner' => array(
		),
    'Subsystem\s+sftp' => array(
		),
	);
?>
