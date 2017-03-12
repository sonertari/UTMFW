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
 * UTMFW logs.
 */

require_once($MODEL_PATH.'/apache.php');

class Fwlogs extends Apache
{
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_user= '(\w+|)';
			$re_host= "($Re_Ip|)";
			$re_loglevel= '(LOG_EMERG|LOG_ALERT|LOG_CRIT|LOG_ERR|LOG_WARNING|LOG_NOTICE|LOG_INFO|LOG_DEBUG)';
			$re_file= '([\w\/\.]+\.php)';
			$re_func= '(\w+)';
			$re_line= '(\d+)';
			
			$re_logheader= "\s+$re_loglevel\s*($re_user@$re_host|)\s+$re_file:\s+$re_func\s+\($re_line\):";
			
			if (preg_match("/$re_logheader/", $logline, $match)) {
				$cols['LogLevel']= $match[1];
				$cols['User']= $match[3];
				$cols['IP']= $match[4];
				$cols['File']= $match[5];
				$cols['Function']= $match[6];
				$cols['Line']= $match[7];
				
				$re_nocolon= '([^:]+)';
				$re_rest= '(.*)';
				
				$re= "/$re_logheader\s+$re_nocolon:\s+$re_rest$/";
				if (preg_match($re, $logline, $match)) {
					$cols['Reason']= $match[8];
					$cols['Log']= $match[9];
				}
				else {
					$re= "/$re_logheader\s+$re_rest$/";
					if (preg_match($re, $logline, $match)) {
						$cols['Reason']= $match[8];
						// Reset Log column set to $logline by ParseSyslogLine()
						$cols['Log']= '';
					}
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
