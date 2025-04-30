<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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
	
	public $PidFile= UTMFWDIR.'/run/clamav/freshclam.pid';
	
	function __construct()
	{
		parent::__construct();
		
		$this->StartCmd= '/usr/local/bin/freshclam -d';
	
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
