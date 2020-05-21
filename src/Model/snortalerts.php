<?php
/*
 * Copyright (C) 2004-2020 Soner Tari
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

class Snortalerts extends Snort
{
	public $Name= 'snortalerts';
	
	public $LogFile= '/var/log/snort/alert.log';
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$logline= $cols['Log'];

			$re_triplet= '\[[:\d]+\]';
			$re_report= '(.*)';
			
			$re_priority= '\[Priority:\s*(\d+)\]';
			$re_proto= '\{([^\}]*)\}';
			$re_from= "($Re_Ip)";
			$re_to= "($Re_Ip)";
			$re_port= '(:\d+|)';

			//Jun 27 18:12:41 utmfw snort[2875]: [122:1:0] (portscan) TCP Portscan[Priority: 3]: {RESERVED} 10.0.0.11 -> 10.0.0.13
			//Jun 27 18:12:45 utmfw snort[2875]: [116:59:1] (snort_decoder): Tcp Window Scale Option found with length > 14[Priority: 3]: {TCP} 10.0.0.11:52936 -> 10.0.0.13:25
			//Aug  6 12:35:41 utmfw snort[2875]: [1:853:10] WEB-CGI wrap access [Classification: Attempted Information Leak] [Priority: 2]: {TCP} 10.0.0.11:35690 -> 209.85.129.147:80
			// The new log format does not have a colon after Priority
			//Aug 25 09:14:55 utmfw snort[2875]: [1:853:10] WEB-CGI wrap access [Classification: Attempted Information Leak] [Priority: 2] {TCP} 10.0.0.11:35690 -> 209.85.129.147:80
			$re= "/$re_triplet\s*$re_report\s*$re_priority(:|)\s*$re_proto\s+$re_from$re_port\s*->\s*$re_to$re_port$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Log']= $match[1];
				$cols['Prio']= $match[2];
				$cols['Proto']= $match[4];
				$cols['SrcIP']= $match[5];
				$cols['SPort']= ltrim($match[6], ':');
				$cols['DstIP']= $match[7];
				$cols['DPort']= ltrim($match[8], ':');
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
