<?php
/*
 * Copyright (C) 2004-2026 Soner Tari
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

require_once($MODEL_PATH.'/suricata.php');

class Suricatainline extends Suricata
{
	public $Name= 'suricatainline';
	/// @attention Do not use the full user name '_suricata', ps reports the first 8 chars of user names
	public $User= '_suricat';
	public $Proc= 'c-icap';
	
	public $ConfFile= '/etc/suricata/c-icap.conf';

	public $VersionCmd= '/opt/local/bin/c-icap -V 2>&1';
	
	protected $psCmd= '/bin/ps arwwx -o pid,start,%cpu,time,%mem,rss,vsz,stat,pri,nice,tty,user,group,command | /usr/bin/grep -v -e grep | /usr/bin/grep -E <PROC>';

	function __construct()
	{
		parent::__construct();
		$this->CollectdName= 'suricata';
	}

	function GetVersion()
	{
		return Output($this->Proc.' '.$this->RunShellCommand($this->VersionCmd)."\nicapsuricata 0.1.0");
	}

	function Start()
	{
		$this->StartCmd= "cd /var/log/suricata/ && /opt/local/bin/c-icap -f $this->ConfFile";
		return parent::Start();
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
	 * Finds the pid of c-icap process.
	 *
	 * @return int Pid or -1 if not running
	 */
	function FindPid()
	{
		$pidcmd= "/bin/cat {$this->UTMFWDIR}/run/suricata/c-icap.pid";
		exec($pidcmd, $output, $retval);

		$re= '/^\s*(\d+)\s*/';
		if (preg_match($re, $output[0], $match)) {
			if ($match[1] !== '') {
				return $match[1];
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
	function IsRunning($proc= '')
	{
		$re= "/opt/local/bin/c-icap\s+[^\n]*-f\s+[^\n]*";

		$output= $this->RunShellCommand('/bin/ps arwwx -U_suricata');
		if (preg_match("|$re|m", $output)) {
			return TRUE;
		}
		return FALSE;
	}
}
?>
