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

class Smtpgated extends Model
{
	public $Name= 'smtp-gated';
	public $User= '_smtp-ga\w*';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/smtp-gated.conf';
	public $LogFile= '/var/log/smtp-gated.log';
	
	public $VersionCmd= '/usr/local/sbin/smtp-gated -v';
	
	public $PidFile= '/var/run/smtp-gated/smtp-gated.pid';
	
	function __construct()
	{
		global $TmpFile;
		
		parent::__construct();
		
		$this->StartCmd= "/usr/local/sbin/smtp-gated /etc/smtp-gated.conf > $TmpFile 2>&1 &";
	}

	function GetVersion()
	{
		$version= explode("\n", $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -4'));
		return Output($version[1]."\n".$version[3]);
	}
					
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_scanner= '(SCAN|SPAM)';
			$re_result= '(\S+)';
			$re_nonempty= '(\S+)';
			$re_num= '(\d+)';
			$re_srcip= "($Re_Ip)";
			$re_result= '(.*|)';
		
			//SCAN:CLEAN size=1639, time=0, src=192.168.1.1, ident=
			//SPAM:CLEAN size=337, time=0, src=192.168.1.1, ident=, score=2.900000
			//SCAN:VIRUS size=1065, time=0, src=192.168.1.1, ident=, virus=Eicar-Test-Signature
			$re= "/$re_scanner:$re_result\s+size=$re_num,\s+time=$re_num,\s+src=$re_srcip,\s+ident=.*$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Scanner']= $match[1];
				$cols['Result']= $match[2];
				$cols['Bytes']= $match[3];
				$cols['ScanSrcIP']= $match[5];
			}
		
			$re= "/$re_scanner:$re_result\s+size=$re_num,\s+src=$re_srcip,\s+ident=.*$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Scanner']= $match[1];
				$cols['Result']= $match[2];
				$cols['Bytes']= $match[3];
				$cols['ScanSrcIP']= $match[4];
			}
		
			//CLOSE by=server, rcv=442/286, trns=1, rcpts=1, auth=0, time=140183437574146, src=192.168.1.1, ident=
			$re= "/CLOSE\s+by=$re_nonempty,\s+rcv=$re_num\/$re_num,\s+trns=$re_num,\s+rcpts=$re_num,\s+auth=$re_num,\s+time=$re_num,\s+src=$re_srcip,\s+ident=.*$/";
			if (preg_match($re, $logline, $match)) {
				$cols['ClosedBy']= $match[1];
				$cols['Xmted']= $match[2];
				$cols['Rcved']= $match[3];
				$cols['Trns']= $match[4];
				$cols['Rcpts']= $match[5];
				$cols['Seconds']= $match[7];
				$cols['SrcIP']= $match[8];
			}
		
			//SESSION TAKEOVER: src=192.168.1.1, ident=, trns=1, reason=Malware found (Eicar-Test-Signature)
			$re= "/SESSION\s+TAKEOVER:\s+src=$re_srcip,\s+\S+,\s+\S+,\s+reason=$re_result$/";
			if (preg_match($re, $logline, $match)) {
				$cols['SrcIP']= $match[1];
				$cols['STReason']= $match[2];
				$cols['ClosedBy']= 'proxy';
			}
		
			//LOCK:LOCKED src=192.168.1.1, ident=-
			$re= "/LOCK:LOCKED\s+src=$re_srcip,.*$/";
			if (preg_match($re, $logline, $match)) {
				$cols['SrcIP']= $match[1];
				$cols['LockedIP']= $cols['SrcIP'];
				$cols['STReason']= 'Locked';
				$cols['ClosedBy']= 'proxy';
			}

			$re_result= '(RCPT\s+TO:|rejected)';
			$re_sender= '(<\S*>|)';
			$re_recipient= '(<\S*>|)';
			$re_retcode= '(\[\d+\]|\d+)';
			//MAIL FROM <sonertari@gmail.com> RCPT TO: 250<sonertari@gmail.com>
			$re= "/MAIL\s+FROM\s+$re_sender\s+$re_result\s+$re_retcode$re_recipient$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Sender']= trim($match[1], '<>');
				$cols['RResult']= $match[2].' '.$match[3];
				$cols['Recipient']= trim($match[4], '<>');
			}

			$re= "/^$re_result\s+$re_retcode$re_recipient.*$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['RResult']= $match[1].' '.$match[2];
				$cols['Recipient']= trim($match[3], '<>');
			}
			return TRUE;
		}
		return FALSE;
	}
}

$ModelConfig = array(
    'proxy_name' => array(
		),
    'lock_on' => array(
		),
    'lock_duration' => array(
        'type' => UINT,
		),
    'abuse' => array(
		),
    'priority' => array(
        'type' => UINT,
		),
    'max_connections' => array(
        'type' => UINT,
		),
    'max_per_host' => array(
        'type' => UINT,
		),
    'max_load' => array(
        'type' => UINT,
		),
    'scan_max_size' => array(
        'type' => UINT,
		),
    'spam_max_size' => array(
        'type' => UINT,
		),
    'spam_max_load' => array(
        'type' => UINT,
		),
    'spam_threshold' => array(
        'type' => UINT,
		),
    'ignore_errors' => array(
        'type' => UINT,
		),
    'spool_leave_on' => array(
		),
    'log_helo' => array(
		),
    'log_mail_from' => array(
		),
    'log_rcpt_to' => array(
		),
    'log_level' => array(
		),
    'nat_header_type' => array(
		),
    'locale' => array(
		),
	);
?>
