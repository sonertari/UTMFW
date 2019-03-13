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

/** @file
 * OpenBSD/spamd, spam deferral daemon.
 */

require_once($MODEL_PATH.'/model.php');

class Spamd extends Model
{
	public $Name= 'spamd';
	public $User= '_spamd';
	
	public $LogFile= '/var/log/spamd.log';
	
	function __construct()
	{
		global $TmpFile;
		
		parent::__construct();
		
		$this->StartCmd= "/usr/libexec/spamd > $TmpFile 2>&1 &";
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetWhitelist'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get spamd whitelist'),
					),
				
				'GetGreylist'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get spamd blacklist'),
					),
				
				'SetStartupIf'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Set startup if'),
					),
				)
			);
	}
	
	function Stop()
	{
		$this->Pkill('spamlogd');
		return parent::Stop();
	}

	function Start()
	{
		/// @attention Start spamd first, otherwise spamlogd starts only
		$retval= parent::Start();
		
		/// @attention Always start spamlogd, do not check $retval, spamd may take some time to start
		// spamlogd_flags="-i em1"
		if (($if= $this->GetNVP($this->rcConfLocal, 'spamlogd_flags', '"')) === FALSE) {
			if (($if= $this->GetDisabledNVP($this->rcConfLocal, 'spamlogd_flags', '"')) === FALSE) {
				return FALSE;
			}
		}
		$this->RunShellCommand("/usr/libexec/spamlogd $if");

		return $retval;
	}

	function GetWhitelist()
	{
		return Output($this->RunShellCommand('/usr/sbin/spamdb | grep WHITE'));
	}

	function GetGreylist()
	{
		return Output($this->RunShellCommand('/usr/sbin/spamdb | grep GREY'));
	}

	function SetStartupIf($if)
	{
        $re= '/(\h*spamlogd_flags\h*=\h*"\h*-i\h+)(\w+\d+)(".*)/m';
		return $this->ReplaceRegexp($this->rcConfLocal, $re, '${1}'.$if.'${3}');
	}

	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_srcip= "($Re_Ip)";
			$re_num= '(\d+)';

			//87.109.52.69: disconnected after 466 seconds.
			$re= "/$re_srcip: disconnected after $re_num seconds\.$/";
			if (preg_match($re, $logline, $match)) {
				$cols['IP']= $match[1];
				$cols['Seconds']= $match[2];
			}

			//122.45.35.135: disconnected after 462 seconds. lists: korea
			$re= "/$re_srcip: disconnected after $re_num seconds\. lists: (.*)$/";
			if (preg_match($re, $logline, $match)) {
				$cols['IP']= $match[1];
				$cols['Seconds']= $match[2];
				$cols['List']= $match[3];
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
