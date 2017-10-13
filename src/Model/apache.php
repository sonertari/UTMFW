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

class Apache extends Model
{
	public $Name= 'apache';
	public $User= 'root|www';
	
	public $NVPS= '\h';
	public $ConfFile= '/var/www/conf/httpd.conf';
	public $LogFile= '/var/www/logs/error_log';

	public $VersionCmd= '/usr/local/sbin/httpd -v';
	private $phpVersionCmd= '/usr/local/bin/php -v';

	protected $dateTimeFormat= 'M j Y H:i:s';

	function __construct()
	{
		parent::__construct();
		
		$this->Proc= 'httpd';
	
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SetWebalizerHostname'=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set webalizer hostname'),
					),
				)
			);
	}

	function GetVersion()
	{
		return Output($this->RunShellCommand($this->VersionCmd)."\n".
			$this->RunShellCommand($this->phpVersionCmd.' | /usr/bin/head -1'));
	}
	
	function Restart()
	{
		/// @todo Add a restart button for apache, currently unused
		return $this->RunShellCommand('/usr/local/sbin/apachectl restart');
	}

	function Stop()
	{
		return $this->RunShellCommand('/usr/local/sbin/apachectl stop');
	}
	
	function ParseLogLine($logline, &$cols)
	{
		//[Mon Sep 10 22:00:01 2007] [notice] Accept mutex: sysvsem (Default: sysvsem)
		$re_datetime= '\[([a-zA-Z]+)\s+([a-zA-Z]+)\s+(\d+)\s+(\d+:\d+:\d+)\s+(\d+)\]';
		$re_loglevel= '\[([a-zA-Z]+)\]';
		$re_rest= '(.*)';

		$re= "/^$re_datetime\s+$re_loglevel\s+$re_rest$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= "$match[2] $match[3] $match[5]";
			$cols['Time']= $match[4];
			$cols['Prio']= $match[6];
			$cols['Log']= $match[7];
			return TRUE;
		}
		else {
			if ($this->ParseSyslogLine($logline, $cols)) {
				$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
				return TRUE;
			}
			else {
				// There are very simple log lines too, e.g. "man: Formatting manual page..."
				// So parser never fails
				$cols['Log']= $logline;
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function SetWebalizerHostname($ip)
	{
		global $Re_Ip;
        
        $re= "/^(\h*HostName\h+)($Re_Ip|[\w\.]+)(\s+.*)/ms";
		return $this->ReplaceRegexp('/etc/webalizer.conf', $re, '${1}'.$ip.'${3}');
	}

	function formatDateHourRegexp($month, $day, $hour, $minute)
	{
		global $MonthNames, $Re_MonthNames, $Re_WeekDays;

		// [Mon Sep  4 23:51:13 2017]
		if ($month != '') {
			$reMonth= $MonthNames[$month];
		} else {
			$reMonth= '('.$Re_MonthNames.')';
		}

		if ($day != '') {
			$reDay= sprintf('% 2d', $day);
		} else {
			$reDay= '([[:digit:][:blank:]][[:digit:]])';
		}

		if ($hour != '') {
			$reHour= $hour;
		} else {
			$reHour= '([[:digit:]][[:digit:]])';
		}

		if ($minute != '') {
			$reMinute= $minute;
		} else {
			$reMinute= '([[:digit:]][[:digit:]])';
		}

		$reWeekDays= '('.$Re_WeekDays.')';

		return "^\[$reWeekDays $reMonth $reDay $reHour:$reMinute:";
	}

	function formatErrorNeedle($needle)
	{
		return "[$needle]";
	}
}

/**
 * Configuration.
 *
 * If type field is missing, default type, STR, is assumed.
 *
 * If type field is FALSE, the configuration does not have a Value, it may just
 * be an enable/disable configuration.
 *
 * @param string type Configuration Value type, regexp definition, defaults to STR.
 */
$ModelConfig = array(
    'ServerAdmin' => array(
		),
    'HostnameLookups' => array(
        'type' => STR_On_Off,
		),
    'LogLevel' => array(
		),
    'Timeout' => array(
        'type' => UINT,
		),
    'KeepAlive' => array(
        'type' => STR_On_Off,
		),
    'MaxKeepAliveRequests' => array(
        'type' => UINT,
		),
    'KeepAliveTimeout' => array(
        'type' => UINT,
		),
    'MinSpareServers' => array(
        'type' => UINT,
		),
    'MaxSpareServers' => array(
        'type' => UINT,
		),
    'StartServers' => array(
        'type' => UINT,
		),
    'MaxClients' => array(
        'type' => UINT,
		),
	);
?>
