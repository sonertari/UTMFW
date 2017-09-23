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
 * HTTP proxy.
 */

require_once($MODEL_PATH.'/model.php');

class Squid extends Model
{
	public $Name= 'squid';
	public $User= 'root|_squid';
	
	public $NVPS= '\h';
	public $ConfFile = '/etc/squid/squid.conf';
	public $LogFile= '/var/squid/logs/access.log';

	public $VersionCmd= '/usr/local/sbin/squid -v';

	public $PidFile= '/var/squid/logs/squid.pid';

	function __construct()
	{
		global $TmpFile;
		
		parent::__construct();
		
		$this->StartCmd= "/usr/local/sbin/squid > $TmpFile 2>&1 &";
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetIpPort'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get HTTP proxy IP:port'),
					),

				'DelIpPort'	=> array(
					'argv'	=> array(IPPORT),
					'desc'	=> _('Delete HTTP proxy IP:port'),
					),

				'AddIpPort'	=> array(
					'argv'	=> array(IPPORT),
					'desc'	=> _('Add HTTP proxy IP:port'),
					),
				)
			);
	}

	/**
	 * Adds IP:port to squid config file.
	 *
	 * Adds the line above cache_dir comment.
	 * Cleans up duplicates first
	 * 
	 * @param string $if Interface IP:port.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function AddIpPort($if)
	{
		$this->DelIpPort($if);
		return $this->ReplaceRegexp($this->ConfFile, "/(\h*#\h*Uncomment and adjust the following to add a disk cache directory\..*)/m", "http_port $if\n".'${1}');
	}

	/**
	 * Deletes IP:port from squid config file.
	 *
	 * @warning If port is not provided, deletes all ports with that IP.
	 * 
	 * @param string $if Interface IP:port.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DelIpPort($if)
	{
		$if= Escape($if, '.');
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*http_port\h*$if\b.*(\s|))/m", '');
	}

	/**
	 * Extract all http IP:ports which squid listens to.
	 */
	function GetIpPort()
	{
		global $Re_IpPort;

		return Output($this->SearchFileAll($this->ConfFile, "/^\h*http_port\h*($Re_IpPort)\b.*\h*$/m"));
	}
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;
	
		//23/Aug/2017:08:25:52 +0300  11463 127.0.0.1 TCP_MISS/200 6796 GET http://openbsd.org/images/cd45-s.gif - DIRECT/199.185.137.3 image/gif
		//23/Aug/2017:08:25:52 +0300      0 127.0.0.1 TCP_HIT/302 823 GET http://fxfeeds.mozilla.com/firefox/headlines.xml - NONE/- text/html
		$re_datetime= '(\d+\/\w+\/\d+):(\d+:\d+:\d+)\s*[\w+]*';
		$re_size= '(\d+)';
		$re_clientip= "($Re_Ip|-)";
		$re_cache= '(\S+)';
		$re_code= '(\d+)';
		$re_mtd= '(GET|POST|\S+)';
		// @attention https should come first, otherwise http always matches
		$re_link= '((https|http)\S+)';
		$re_direct= '(\S+)';
		$re_targetip= "($Re_Ip|\S+|-)";
		$re_type= '(\S+)';

		$re= "/^$re_datetime\s+\d+\s+$re_clientip\s+$re_cache\/$re_code\s+$re_size\s+$re_mtd\s+$re_link.*\s+$re_direct\/$re_targetip\s+$re_type$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			$cols['Client']= $match[3];
			$cols['Cache']= $match[4];
			$cols['Code']= $match[5];
			$cols['Size']= $match[6];
			$cols['Mtd']= $match[7];
			$cols['Link']= $match[8];
			$cols['Proto']= $match[9];
			$cols['Direct']= $match[10];
			$cols['Target']= $match[11];
			$cols['Type']= $match[12];
			return TRUE;
		}
		else if ($this->ParseSyslogLine($logline, $cols)) {
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			// Squid Logs page does not have a Log column, use Type column for Log field
			$cols['Type']= $cols['Log'];
			return TRUE;
		}
		return FALSE;
	}
	
	function PostProcessCols(&$cols)
	{
		preg_match('?http(|s)://([^/]*)?', $cols['Link'], $match);
		$cols['Link']= $match[2];
	}

	function GetDateRegexp($date)
	{
		global $MonthNames;
		
		// Match all years
		$re= '.*';
		if ($date['Month'] == '') {
			$re= '.*\/'.$re;
		}
		else {
			$re= $MonthNames[$date['Month']].'\/'.$re;
			if ($date['Day'] == '') {
				$re= '.*\/'.$re;
			}
			else {
				$re= $date['Day'].'\/'.$re;
			}
		}
		return $re;
	}

	function formatDateHourRegexp($month, $day, $hour, $minute)
	{
		global $MonthNames, $Re_MonthNames;

		// 05/Sep/2017:00:05:11
		$reYear= '20[[:digit:]][[:digit:]]';

		if ($month != '') {
			$reMonth= $MonthNames[$month];
		} else {
			$reMonth= '('.$Re_MonthNames.')';
		}

		if ($day != '') {
			$reDay= $day;
		} else {
			$reDay= '([[:digit:]][[:digit:]])';
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

		return "^$reDay/$reMonth/$reYear:$reHour:$reMinute:";
	}
}

$ModelConfig = array(
    'no_cache deny localhost' => array(
        'type' => FALSE,
		),
    'log_ip_on_direct' => array(
        'type' => STR_on_off,
		),
    'debug_options' => array(
		),
    'log_fqdn' => array(
        'type' => STR_on_off,
		),
    'client_netmask' => array(
        'type' => IP,
		),
    'http_access allow localhost' => array(
        'type' => FALSE,
		),
    'http_access deny all' => array(
        'type' => FALSE,
		),
    'cache_mgr' => array(
		),
    'logfile_rotate' => array(
        'type' => UINT,
		),
	);
?>
