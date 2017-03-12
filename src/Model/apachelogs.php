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
 * Apache access logs.
 */

require_once($MODEL_PATH.'/apache.php');

class Apachelogs extends Apache
{
	public $Name= 'apachelogs';
	
	public $LogFile= '/var/www/logs/access_log';

	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;
	
		//10.0.0.11 - - [26/Sep/2009:03:03:45 +0300] "GET /symon/graph.php?61ea9e7cb820b19cc116fc5eb37490de HTTP/1.1" 200 31949
		//10.0.0.11 - - [26/Sep/2009:03:04:23 +0300] "GET /images/run.png HTTP/1.1" 304 -
		//10.0.0.11 - - [25/Sep/2009:20:05:54 +0300] "POST /snortips/conf.php HTTP/1.1" 200 11063
		//10.0.0.11 - - [08/Oct/2009:12:56:03 +0300] "GET / HTTP/1.1" 302 5
		$datetime= '\[(\d+\/\w+\/\d+):(\d+:\d+:\d+)\s*[\w+]*\]';
		$ip= "($Re_Ip)";
		$mtd= '(GET|POST|\S+)';
		$link= '(\S*)';
		$code= '(\d+)';
		$size= '(\d+|-)';

		$re= "/^$ip\s+.*\s+$datetime\s+\"$mtd\s+$link\s+HTTP\/\d+\.\d+\"\s+$code\s+$size$/";
		if (preg_match($re, $logline, $match)) {
			$cols['IP']= $match[1];
			$cols['Date']= $match[2];
			$cols['Time']= $match[3];
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];;
			$cols['Mtd']= $match[4];
			$cols['Link']= $match[5];
			$cols['Code']= $match[6];
			$cols['Size']= $match[7];
			if ($cols['Size'] == '-') {
				$cols['Size']= 0;
			}
			return TRUE;
		}
		else if ($this->ParseSyslogLine($logline, $cols)) {
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			$cols['IP']= _('NA');
			return TRUE;
		}
		return FALSE;
	}

	function PostProcessCols(&$cols)
	{
		// Exclude encoded image names, but include submenus
		preg_match('/^([^?]+(\?submenu=.*|)).*$/', $cols['Link'], $match);
		$cols['Link']= $match[1];
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
}
?>
