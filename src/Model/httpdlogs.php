<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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
 * Httpd access logs.
 */

require_once($MODEL_PATH.'/httpd.php');

class Httpdlogs extends Httpd
{
	public $Name= 'httpdlogs';
	
	public $LogFile= '/var/log/httpd/access.log';

	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;
	
		//Oct 21 23:58:56 utmfw62 httpd[29450]: INFO: utmfw 192.168.8.2 - - [21/Oct/2017:23:58:56 +0300] "GET /utmfw.css HTTP/1.1" 304 0
		$datetime= '\[(\d+\/\w+\/\d+):(\d+:\d+:\d+)\s*[\w+]*\]';
		$ip= "($Re_Ip)";
		$mtd= '(\S+)';
		$link= '(\S*)';
		$code= '(\d+)';
		$size= '(\d+|-)';

		if ($this->ParseSyslogLine($logline, $cols)) {
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			$cols['Link']= $cols['Log'];

			$re= "/^\S+\s+$ip\s+.*\s+$datetime\s+\"$mtd\s+$link\s+HTTP\/\d+\.\d+\"\s+$code\s+$size$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['IP']= $match[1];
				$cols['Mtd']= $match[4];
				$cols['Link']= $match[5];
				$cols['Code']= $match[6];
				$cols['Size']= $match[7];
				if ($cols['Size'] == '-') {
					$cols['Size']= 0;
				}
			} else {
				/// @todo Check the reason of this 408 log
				// Oct 22 00:18:50 utmfw62 httpd[28407]: INFO: utmfw 192.168.8.2 - - [22/Oct/2017:00:18:50 +0300] "<UNKNOWN> " 408 0
				$re= "/^\S+\s+$ip\s+.*\s+$datetime\s+\"(.+)\"\s+$code\s+$size$/";
				if (preg_match($re, $cols['Log'], $match)) {
					$cols['IP']= $match[1];
					$cols['Link']= $match[4];
					$cols['Code']= $match[5];
					$cols['Size']= $match[6];
					if ($cols['Size'] == '-') {
						$cols['Size']= 0;
					}
				}
			}
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
}
?>
