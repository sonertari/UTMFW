<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
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
		parent::__construct();
		
		$this->StartCmd= '/usr/local/sbin/smtp-gated /etc/smtp-gated.conf';
	}

	function GetVersion()
	{
		$version= explode("\n", $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -4'));
		return Output($version[1]."\n".$version[3]);
	}
					
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip, $Re_User;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_scanner= '(SCAN|SPAM)';
			$re_result= '(\S+)';
			$re_nonempty= '(\S+)';
			$re_num= '(\d+)';
			$re_srcip= "($Re_Ip)";
			$re_user= "($Re_User|)";

			//NEW (1/0) on=127.0.0.1:9199, src=127.0.0.1:6834, ident=, dst=127.0.0.1:9199, smtp, user=, id=1553754443.17096
			//NEW (1/0) on=127.0.0.1:9199, src=192.168.3.24:33262, ident=, dst=74.125.206.108:465, smtps, user=soner, id=1501826099.14746
			$re= "/NEW\s+.*,\s+src=$re_srcip:\d+,.*\s+dst=$re_srcip:\d+,\s+(smtps|smtp),\s+user=$re_user,\s+.*$/";
			if (preg_match($re, $logline, $match)) {
				$cols['NewSrcIP']= $match[1];
				$cols['NewDstIP']= $match[2];
				$cols['Proto']= $match[3];
				$cols['NewUser']= $match[4] != '' ? $match[4] : _('Unknown');
				return TRUE;
			}
			
			//SCAN:CLEAN size=1639, time=0, src=192.168.1.1, ident=, user=soner
			//SPAM:CLEAN size=337, time=0, src=192.168.1.1, ident=, score=2.900000
			//SCAN:VIRUS size=1065, time=0, src=192.168.1.1, ident=, virus=Eicar-Test-Signature
			$re= "/$re_scanner:$re_result\s+size=$re_num,\s+time=$re_num,\s+src=$re_srcip,\s+ident=.*,\s+user=$re_user$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Scanner']= $match[1];
				$cols['Result']= $match[2];
				$cols['Bytes']= $match[3];
				$cols['ScanSrcIP']= $match[5];
				$cols['User']= $match[6] != '' ? $match[6] : _('Unknown');
				return TRUE;
			}
		
			$re= "/$re_scanner:$re_result\s+size=$re_num,\s+src=$re_srcip,\s+ident=.*,\s+user=$re_user$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Scanner']= $match[1];
				$cols['Result']= $match[2];
				$cols['Bytes']= $match[3];
				$cols['ScanSrcIP']= $match[4];
				$cols['User']= $match[5] != '' ? $match[5] : _('Unknown');
				return TRUE;
			}
		
			//CLOSE by=server, rcv=442/286, trns=1, rcpts=1, auth=0, time=140183437574146, src=192.168.1.1, ident=, user=soner
			$re= "/CLOSE\s+by=$re_nonempty,\s+rcv=$re_num\/$re_num,\s+trns=$re_num,\s+rcpts=$re_num,\s+auth=$re_num,\s+time=$re_num,\s+src=$re_srcip,\s+ident=.*,\s+user=$re_user$/";
			if (preg_match($re, $logline, $match)) {
				$cols['ClosedBy']= $match[1];
				$cols['Xmted']= $match[2];
				$cols['Rcved']= $match[3];
				$cols['Trns']= $match[4];
				$cols['Rcpts']= $match[5];
				$cols['Seconds']= $match[7];
				$cols['SrcIP']= $match[8];
				$cols['User']= $match[9] != '' ? $match[9] : _('Unknown');
				return TRUE;
			}
		
			$re_result= '(.*|)';
			//SESSION TAKEOVER: src=192.168.1.1, ident=, trns=1, reason=Malware found (Eicar-Test-Signature), user=soner
			$re= "/SESSION\s+TAKEOVER:\s+src=$re_srcip,\s+\S+,\s+\S+,\s+reason=$re_result,\s+user=$re_user$/";
			if (preg_match($re, $logline, $match)) {
				$cols['SrcIP']= $match[1];
				$cols['STReason']= $match[2];
				$cols['ClosedBy']= 'proxy';
				$cols['User']= $match[3] != '' ? $match[3] : _('Unknown');
				return TRUE;
			}
		
			//LOCK:LOCKED src=192.168.1.1, ident=-, user=soner
			$re= "/LOCK:LOCKED\s+src=$re_srcip,.*,\s+user=$re_user$/";
			if (preg_match($re, $logline, $match)) {
				$cols['SrcIP']= $match[1];
				$cols['LockedIP']= $cols['SrcIP'];
				$cols['STReason']= 'Locked';
				$cols['ClosedBy']= 'proxy';
				$cols['User']= $match[2] != '' ? $match[2] : _('Unknown');
				return TRUE;
			}

			$re_result= '(RCPT\s+TO:|rejected)';
			$re_sender= '(<\S*>|)';
			$re_recipient= '(<\S*>|)';
			$re_retcode= '(\[\d+\]|\d+)';
			//MAIL FROM <sonertari@gmail.com> RCPT TO: 250<sonertari@gmail.com>, user=soner
			$re= "/MAIL\s+FROM\s+$re_sender\s+$re_result\s+$re_retcode$re_recipient,\s+user=$re_user$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Sender']= trim($match[1], '<>');
				$cols['RResult']= $match[2].' '.$match[3];
				$cols['Recipient']= trim($match[4], '<>');
				$cols['User']= $match[5] != '' ? $match[5] : _('Unknown');
				return TRUE;
			}

			$re= "/^$re_result\s+$re_retcode$re_recipient.*,\s+user=$re_user$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['RResult']= $match[1].' '.$match[2];
				$cols['Recipient']= trim($match[3], '<>');
				$cols['User']= $match[4] != '' ? $match[4] : _('Unknown');
				return TRUE;
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
