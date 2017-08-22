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

require_once($MODEL_PATH.'/snort.php');

class Snortinline extends Snort
{
	public $Name= 'snortinline';
	
	public $ConfFile= '/etc/snort/snortinline.conf';
	
	protected $psCmd= '/bin/ps arwwx -o pid,start,%cpu,time,%mem,rss,vsz,stat,pri,nice,tty,user,group,command | /usr/bin/grep "\-Q" | /usr/bin/grep -v -e grep | /usr/bin/grep -E <PROC>';

	function __construct()
	{
		parent::__construct();
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				// Override the parent's Start command
				'Start'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Start snort'),
					),
				)
			);
	}

	function GetVersion()
	{
		return FALSE;
	}

	function Start()
	{
		global $TmpFile;

		$cmd= "/usr/local/bin/snort -D -d -Q -c $this->ConfFile -u _snort -g _snort -b -l /var/snort/log --pid-path /var/run/snort";
		$this->RunShellCommand("$cmd > $TmpFile 2>&1");

		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			if ($this->IsInstanceRunning()) {
				return TRUE;
			}
			/// @todo Check $TmpFile for error messages, if so break out instead
			exec('/bin/sleep ' . self::PROC_STAT_SLEEP_TIME);
		}

		/// Start command is redirected to tmp file
		$output= file_get_contents($TmpFile);
		Error($output);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Start failed with: $output");

		// Check one last time due to the last sleep in the loop
		return $this->IsInstanceRunning();
	}

	function Stop()
	{
		$pid= $this->FindPid();
		if ($pid > -1) {
			return $this->KillPid($pid);
		}
		return TRUE;
	}

	/**
	 * Finds the pid of snort process.
	 *
	 * @return int Pid or -1 if not running
	 */
	function FindPid()
	{
		$pidcmd= '/bin/ps arwwx | /usr/bin/grep snort | /usr/bin/grep "\-Q" | /usr/bin/grep -v -e ctlr.php -e grep';
		exec($pidcmd, $output, $retval);

		foreach ($output as $psline) {
			$re= '/\h+-Q\h+/';
			if (preg_match($re, $psline)) {
				$re= '/^\s*(\d+)\s+/';
				if (preg_match($re, $psline, $match)) {
					if ($match[1] !== '') {
						return $match[1];
					}
				}
			}
		}
		return -1;
	}

	/**
	 * Checks if the process(es) is running.
	 *
	 * Uses ps with -U option.
	 *
	 * @return bool TRUE if running
	 */
	function IsInstanceRunning()
	{
		$re= "\/usr\/local\/bin\/snort\s+[^\n]*-Q\s+[^\n]*";

		$output= $this->RunShellCommand('/bin/ps arwwx -U_snort');
		if (preg_match("/$re/m", $output)) {
			return TRUE;
		}
		return FALSE;
	}
}
?>
