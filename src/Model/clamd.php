<?php
/*
 * Copyright (C) 2004-2022 Soner Tari
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

class Clamd extends Model
{
	public $Name= 'clamd';
	public $User= '_clamav';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/clamd.conf';
	public $LogFile= '/var/log/clamd.log';
	
	public $VersionCmd= '/usr/local/sbin/clamd -V';
	
	public $PidFile= UTMFWDIR.'/run/clamav/clamd.pid';
	
	function __construct()
	{
		parent::__construct();
		
		$this->StartCmd= '/usr/local/sbin/clamd -c /etc/clamd.conf';
	}

	function ParseLogLine($logline, &$cols)
	{
		if ($this->ParseSyslogLine($logline, $cols)) {
			//Oct 31 04:10:24 utmfw62 clamd[53433]: INFO: /tmp/e2guardian/tfmkNCU8: Eicar-Test-Signature FOUND
			if (preg_match('/^\S+:\s+(.*)\s+FOUND$/', $cols['Log'], $match)) {
				$cols['Scan']= 'FOUND';
				$cols['Virus']= $match[1];
			}
			//Oct 28 23:22:12 utmfw62 clamd[93993]: INFO: /tmp/e2guardian/tfEuAxaG: OK
			//Oct 31 04:26:07 utmfw62 clamd[53433]: INFO: stream(127.0.0.1@1295): OK
			//Oct 31 04:28:07 utmfw62 clamd[53433]: INFO: /var/spool/smtp-gated/msg/1509413284.88659: OK
			else if (preg_match('/^\S+:\s+OK$/', $cols['Log'], $match)) {
				$cols['Scan']= 'OK';
			}
			return TRUE;
		}
		return FALSE;
	}
}

$ModelConfig = array(
    'SelfCheck' => array(
        'type' => UINT,
		),
    'LeaveTemporaryFiles' => array(
        'type' => STR_yes_no,
		),
    'LogClean' => array(
        'type' => STR_yes_no,
		),
    'ScanMail' => array(
        'type' => STR_yes_no,
		),
    'ScanPE' => array(
        'type' => STR_yes_no,
		),
    'DetectBrokenExecutables' => array(
        'type' => STR_yes_no,
		),
    'ScanHTML' => array(
        'type' => STR_yes_no,
		),
    'ScanArchive' => array(
        'type' => STR_yes_no,
		),
    'ScanRAR' => array(
        'type' => STR_yes_no,
		),
    'ScanOLE2' => array(
        'type' => STR_yes_no,
		),
    'MailFollowURLs' => array(
        'type' => STR_yes_no,
		),
    'MaxDirectoryRecursion' => array(
        'type' => UINT,
		),
    'FollowDirectorySymlinks' => array(
        'type' => STR_yes_no,
		),
    'FollowFileSymlinks' => array(
        'type' => STR_yes_no,
		),
    'ArchiveMaxFileSize' => array(
		),
    'ArchiveMaxRecursion' => array(
        'type' => UINT,
		),
    'ArchiveMaxFiles' => array(
        'type' => UINT,
		),
    'ArchiveMaxCompressionRatio' => array(
        'type' => UINT,
		),
    'ArchiveLimitMemoryUsage' => array(
        'type' => STR_yes_no,
		),
    'ArchiveBlockEncrypted' => array(
        'type' => STR_yes_no,
		),
    'ArchiveBlockMax' => array(
        'type' => STR_yes_no,
		),
    'MaxThreads' => array(
        'type' => UINT,
		),
    'LogVerbose' => array(
        'type' => STR_yes_no,
		),
    'Debug' => array(
        'type' => STR_yes_no,
		),
	);
?>
