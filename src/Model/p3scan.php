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

require_once($MODEL_PATH.'/model.php');

class P3scan extends Model
{
	public $Name= 'p3scan';
	public $User= '_p3scan';
	
	const CONFIG_DIR= '/etc/p3scan';
	const RE_LC_SUFFIX= '/p3scan-(.*)\.mail$/';
	
	public $NVPS= '=';
	public $ConfFile= '/etc/p3scan/p3scan.conf';
	public $LogFile= '/var/log/p3scan.log';

	public $VersionCmd= '/usr/local/sbin/p3scan -v';

	public $PidFile= UTMFWDIR.'/run/p3scan/p3scan.pid';
	
	function __construct()
	{
		parent::__construct();
		
		$this->StartCmd= '/usr/local/sbin/p3scan -f /etc/p3scan/p3scan.conf';
	
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetCurrentLocale'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get current locale'),
					),
				
				'GetLocales'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get locales'),
					),
				
				'ChangeLocal'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Change p3scan report language'),
					),
				)
			);
	}

	function GetVersion()
	{
		return Output($this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -2 | /usr/bin/tail -1'));
	}
					
	/**
	 * Gets current locale
	 */
	function GetCurrentLocale()
	{
		$localcf= self::CONFIG_DIR.'/p3scan.mail';
		
		if (is_link($localcf) && ($linkedfile= readlink($localcf))) {
			if (preg_match(self::RE_LC_SUFFIX, $linkedfile, $match)) {
				return Output($match[1]);
			}
		}
		return FALSE;
	}
	
	/**
	 * Gets list of locale files
	 */
	function GetLocales()
	{
		$files= $this->GetFiles(self::CONFIG_DIR.'/p3scan-*.mail');
		$files= explode("\n", $files);

		$locales= array();
		foreach ($files as $file) {
			if (!is_dir($file) && !preg_match('/.*\.(bak|orig)$/', $file)) {
				if (preg_match(self::RE_LC_SUFFIX, $file, $match)) {
					$locales[]= $match[1];
				}
			}
		}
		return Output(implode("\n", $locales));
	}
	
	/**
	 * Changes local.cf link to given locale
	 *
	 * @param string $locale Locale name, e.g. Turkish.
	 * @return string Command output
	 */
	function ChangeLocal($locale)
	{
		return $this->RunShellCommand('/bin/ln -sf '.self::CONFIG_DIR."/p3scan-$locale.mail ".self::CONFIG_DIR."/p3scan.mail");
	}
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip, $Re_User;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re= "/USER '(.*)'$/";
			if (preg_match($re, $logline, $match)) {
				$cols['User']= $match[1];
				return TRUE;
			}

			$re_clientip= "($Re_Ip)";
			$re_num= '(\d+)';
			$re_user= "( spuser ($Re_User)|)";

			$re= "/(POP3S|POP3) Connection from $re_clientip:$re_num$re_user$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Proto']= strtolower($match[1]);
				$cols['SrcIP']= $match[2];
				$cols['SPUser']= $match[5] !== '' ? $match[5] : _('Unknown');
				return TRUE;
			}

			$re= "/Real-server address is $re_clientip:$re_num$/";
			if (preg_match($re, $logline, $match)) {
				$cols['DstIP']= $match[1];
				return TRUE;
			}

			$re_result= '(.*)';

			$re= "/Session done \($re_result\). Mails: $re_num Bytes: $re_num$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Result']= $match[1];
				$cols['Mails']= $match[2];
				$cols['Bytes']= $match[3];
				return TRUE;
			}

			$re_user= "( spuser: ($Re_User)|)";

			// POP3 from 192.168.10.2:47845 to 10.0.0.10:110 from Soner Tari <sonertari@gmail.com> to sonertari@gmail.com user: soner virus: Eicar-Test-Signature file: /p3scan.8c0Ph spuser: soner
			$re= "/(POP3S|POP3) from $Re_Ip:\d+ to $Re_Ip:\d+ from (.+) to (.+) user: .+ virus: (.+) file:.*$re_user$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Proto']= strtolower($match[1]);
				$cols['From']= $match[2];
				$cols['To']= $match[3];
				$cols['Virus']= $match[4];
				$cols['SPUser']= $match[6] !== '' ? $match[6] : _('Unknown');
				return TRUE;
			}
			return TRUE;
		}
		return FALSE;
	}
}

$ModelConfig = array(
    'checkspam' => array(
        'type' => FALSE,
		),
    'justdelete' => array(
        'type' => FALSE,
		),
    'maxchilds' => array(
        'type' => UINT,
		),
    'bytesfree' => array(
        'type' => UINT,
		),
    'debug' => array(
        'type' => FALSE,
		),
    'quiet' => array(
        'type' => FALSE,
		),
	);
?>
