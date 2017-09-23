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

class Clamd extends Model
{
	public $Name= 'clamd';
	public $User= '_clamav';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/clamd.conf';
	public $LogFile= '/var/log/clamd.log';
	
	public $VersionCmd= '/usr/local/sbin/clamd -V';
	
	public $PidFile= '/var/run/clamav/clamd.pid';
	
	function __construct()
	{
		global $TmpFile;
		
		parent::__construct();
		
		$this->StartCmd= "/usr/local/sbin/clamd -c /etc/clamd.conf > $TmpFile 2>&1 &";
	}
		
	/// @todo clamd and freshclam log lines contain the number of virus defs.
	function ParseLogLine($logline, &$cols)
	{
		//Thu Sep 24 14:20:02 2009 -> SelfCheck: Database status OK.
		$re_datetime= '\w+\s+(\w+\s+\d+)\s+(\d+:\d+:\d+)\s+\d+';
		$re_rest= '(.*)';

		$re= "/^$re_datetime\s+->\s+$re_rest$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			$cols['Log']= $match[3];
			return TRUE;
		}
		else if ($this->ParseSyslogLine($logline, $cols)) {
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			return TRUE;
		}
		return FALSE;
	}

	function formatDateHourRegexp($month, $day, $hour, $minute)
	{
		return $this->formatDateHourRegexpWeekDays($month, $day, $hour, $minute);
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
