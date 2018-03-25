<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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

class Spamassassin extends Model
{
	public $Name= 'spamassassin';
	public $User= 'root|_spamdaemon';
	
	private $confDir= '/etc/mail/spamassassin';
	private $re_LcSuffix= '/local-(\w+)\.cf$/';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/mail/spamassassin/local.cf';
	public $LogFile= '/var/log/maillog';
	
	/// Have to unset LC_ALL and LANG, otherwise perl complains
	public $VersionCmd= 'unset LC_ALL; unset LANG; /usr/local/bin/spamd -V';

	public $PidFile= '/var/run/spamassassin.pid';

	public $StartCmd= 'unset LC_ALL; unset LANG; /usr/local/bin/spamd -L -d -x -u _spamdaemon -r /var/run/spamassassin.pid';

	function __construct()
	{
		global $TmpFile;
		
		parent::__construct();
		
		$this->Proc= 'spamd';

		$this->StartCmd= "unset LC_ALL; unset LANG; /usr/local/bin/spamd -L -d -x -u _spamdaemon -r /var/run/spamassassin.pid > $TmpFile 2>&1 &";
		
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
					'desc'	=> _('Change spamassassin local'),
					),
				)
			);
	}

	function GetVersion()
	{
		return Output($this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -2'));
	}
	
	function Stop()
	{
		return $this->Kill();
	}
	
	/**
	 * Gets current locale
	 */
	function GetCurrentLocale()
	{
		$localcf= $this->confDir.'/local.cf';
		
		if (is_link($localcf) && ($linkedfile= readlink($localcf))) {
			if (preg_match($this->re_LcSuffix, $linkedfile, $match)) {
				return Output($match[1]);
			}
		}
		return FALSE;
	}
	
	/**
	 * Gets locale files
	 */
	function GetLocales()
	{
		$files= $this->GetFiles($this->confDir.'/local-*.cf');
		$files= explode("\n", $files);

		$locales= array();
		foreach ($files as $file) {
			if (!is_dir($file) && !preg_match('/.*\.(bak|orig)$/', $file)) {
				if (preg_match($this->re_LcSuffix, $file, $match)) {
					$locales[]= $match[1];
				}
			}
		}
		return Output(implode("\n", $locales));
	}
	
	/**
	 * Change local.cf link
	 */
	function ChangeLocal($locale)
	{
		return $this->RunShellCommand("cd $this->confDir; /bin/ln -sf local-$locale.cf local.cf");
	}
	
	function ParseLogLine($logline, &$cols)
	{
		if ($this->ParseSyslogLine($logline, $cols)) {
			if (stripos($cols['Log'], 'clean message ') !== FALSE) {
				$cols['Ham']= 1;
			}
			else if (stripos($cols['Log'], 'identified spam ') !== FALSE) {
				$cols['Spam']= 1;
			}

			$re= '/^.* for (\S+):\S+ in (\d+\.\d+) seconds, (\d+) bytes\.$/';
			if (preg_match($re, $logline, $match)) {
				$cols['User']= $match[1];
				$cols['Seconds']= $match[2];
				$cols['Bytes']= $match[3];
			}
			return TRUE;
		}
		return FALSE;
	}
	
	function _getFileLineCount($file, $re= '', $needle= '', $month='', $day='', $hour='', $minute='')
	{
		$cmd= "/usr/bin/grep -a ' spamd\[' $file";

		if ($month != '' || $day != '' || $hour != '' || $minute != '') {
			$cmd.= ' | /usr/bin/grep -a -E "' . $this->formatDateHourRegexpDayLeadingZero($month, $day, $hour, $minute) . '"';
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
		$cmd= "/usr/bin/grep -a ' spamd\[' $file";

		if ($month != '' || $day != '' || $hour != '' || $minute != '') {
			$cmd.= ' | /usr/bin/grep -a -E "' . $this->formatDateHourRegexpDayLeadingZero($month, $day, $hour, $minute) . '"';
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
		$cmd= "/usr/bin/grep -a ' spamd\[' $file";
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

	function GetDateRegexp($date)
	{
		global $MonthNames;

		if ($date['Month'] == '') {
			$re= '.*';
		}
		else {
			$re= $MonthNames[$date['Month']].'\s+';
			if ($date['Day'] == '') {
				$re.= '.*';
			}
			else {
				$re.= sprintf('%02d', $date['Day']);
			}
		}
		return $re;
	}
}

$ModelConfig = array(
    'rewrite_header Subject' => array(
		),
    'report_safe' => array(
        'type' => UINT_0_2,
		),
    'trusted_networks' => array(
        'type' => IP,
		),
    'lock_method' => array(
		),
    'required_score' => array(
        'type' => FLOAT,
		),
    'use_bayes' => array(
        'type' => UINT_0_1,
		),
    'bayes_path' => array(
		),
    'bayes_auto_learn' => array(
        'type' => UINT_0_1,
		),
    'bayes_ignore_header X-Bogosity' => array(
        'type' => FALSE,
		),
    'bayes_ignore_header X-Spam-Flag' => array(
        'type' => FALSE,
		),
    'bayes_ignore_header X-Spam-Status' => array(
        'type' => FALSE,
		),
	);
?>
