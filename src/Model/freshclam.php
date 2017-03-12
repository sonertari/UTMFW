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

/** @file
 * ClamAV virus db updater.
 */

require_once($MODEL_PATH.'/model.php');

class Freshclam extends Model
{
	public $Name= 'freshclam';
	public $User= '_clamav';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/freshclam.conf';
	public $LogFile= '/var/log/freshclam.log';
	
	public $PidFile= '/var/run/clamav/freshclam.pid';
	
	function __construct()
	{
		global $TmpFile;
		
		parent::__construct();
		
		$this->StartCmd= "/usr/local/bin/freshclam -d > $TmpFile 2>&1 &";
	
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetMirrors'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get database mirrors'),
					),
				
				'AddMirror'=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Add database mirror'),
					),
				
				'DelMirror'=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Delete database mirror'),
					),
				)
			);
	}
	
	/// @todo clamd and freshclam log lines contain number of virus defs.
	function ParseLogLine($logline, &$cols)
	{
		// Mon Oct 26 05:23:56 2009 -> ClamAV update process started at Mon Oct 26 05:23:56 2009
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
		else {
			$cols['Log']= $logline;
			return TRUE;
		}
		return FALSE;		
	}

	function GetMirrors()
	{
		$mirrors= $this->SearchFileAll($this->ConfFile, "/^\h*DatabaseMirror\h*([\w.]+)\b.*\h*$/m");
		// Do not list the main server
		return Output(preg_replace('/^(\s*database\.clamav\.net\s*)/m', '', $mirrors));
	}
	
	function AddMirror($mirror)
	{
		$this->DelMirror($mirror);
		return $this->ReplaceRegexp($this->ConfFile, "/(\h*DatabaseMirror\h+database\.clamav\.net.*)/m", "DatabaseMirror $mirror\n".'${1}');
	}

	function DelMirror($mirror)
	{
		// Do not delete the main server
		if ($mirror !== 'database.clamav.net') {
			$mirror= Escape($mirror, '.');
			return $this->ReplaceRegexp($this->ConfFile, "/^(\h*DatabaseMirror\h+$mirror\b.*(\s|))/m", '');
		}
		Error(_("Won't delete database.clamav.net entry."));
		return FALSE;
	}
}

$ModelConfig = array(
    'Checks' => array(
        'type' => UINT,
		),
    'MaxAttempts' => array(
        'type' => UINT,
		),
    'SafeBrowsing' => array(
        'type' => STR_yes_no,
		),
    'LogVerbose' => array(
        'type' => STR_yes_no,
		),
    'DNSDatabaseInfo' => array(
		),
    'HTTPProxyServer' => array(
		),
    'HTTPProxyPort' => array(
        'type' => PORT,
		),
    'HTTPProxyUsername' => array(
		),
    'HTTPProxyPassword' => array(
		),
    'LocalIPAddress' => array(
        'type' => IP,
		),
    'Debug' => array(
        'type' => STR_yes_no,
		),
	);
?>
