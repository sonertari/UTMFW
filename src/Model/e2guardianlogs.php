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
 * E2guardian access logs.
 */

require_once($MODEL_PATH.'/e2guardian.php');

class E2guardianlogs extends E2guardian
{
	public $Name= 'e2guardianlogs';
	
	public $LogFile= '/var/log/e2guardian/access.log';

	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		$re_datetime= '(\d+\.\d+\.\d+) (\d+:\d+:\d+)';
		$re_pip= "($Re_Ip|-)";
		$re_srcip= "($Re_Ip)";
		$re_link= '(http:\/\/[^ \/]*|https:\/\/[^ \/]*)(\S*)';
		$re_result= '(.*|)';
		$re_mtd= '(GET|PUT|ICY|COPY|HEAD|LOCK|MOVE|POLL|POST|BCOPY|BMOVE|MKCOL|TRACE|LABEL|MERGE|DELETE|SEARCH|UNLOCK|REPORT|UPDATE|NOTIFY|BDELETE|CONNECT|OPTIONS|CHECKIN|PROPFIND|CHECKOUT|CCM_POST|SUBSCRIBE|PROPPATCH|BPROPFIND|BPROPPATCH|UNCHECKOUT|MKACTIVITY|MKWORKSPACE|UNSUBSCRIBE|RPC_CONNECT|VERSION-CONTROL|BASELINE-CONTROL)';
		$re_size= '(\d+)';
		$re_ttl= '(-{0,1}\d+)';
		$re_rest= '(.*)';
		
		$re_nonempty= '(\S+|)';
		$re_num= '(-{0,1}\d+|)';
		$re_restorempty= '(.*|)';
		
		// 2007.12.29 20:46:18 - 192.168.1.33 http://URL.com *DENIED* Banned site: URL.com GET 0 0 Cleaning Domains 1 403 -   -
		// 2007.12.29 20:10:15 - 192.168.1.34 http://URL.com  GET 1632 0  1 404 text/html   -
		// 2007.12.29 20:09:57 - 192.168.1.34 http://URL.com *SCANNED*  GET 5137 -20  1 200 text/html   -
		$re= "/^$re_datetime\s+$re_pip\s+$re_srcip\s+$re_link\s+$re_result\s+$re_mtd\s+$re_size\s+$re_ttl\s+$re_restorempty\s*$re_num\s+$re_num\s+$re_rest$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['IPsrc']= $match[3];
			$cols['IP']= $match[4];
			$cols['Link']= $match[5].$match[6];
			$cols['Scan']= $match[7];
			$cols['Mtd']= $match[8];
			$cols['Size']= $match[9];
			$cols['TTL']= $match[10];
			$log= $match[11].' '.$match[12].' '.$match[13].' '.$match[14];
			/// @todo What are the other category names?
			if (preg_match('/(\S+)\s+(Domains|URLs|Sites|Phrases)/', $log, $cats)) {
				$cols['Cat']= $cats[1];
			}
			$cols['Log']= $log;
			return TRUE;
		}
		else {
			$cols['IP']= _('Unknown');
			$cols['Link']= _('Unknown');

			$re= "/^$re_datetime$re_result\s+$re_mtd\s+$re_nonempty\s+$re_nonempty\s+$re_nonempty\s+$re_link\s+$re_rest$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Date']= $match[1];
				$cols['Time']= $match[2];
				$cols['Mtd']= $match[4];
				$cols['Scan']= $match[6].' '.$match[3].' '.$match[5].' '.$match[7];
				$cols['Link']= $match[8].$match[9];
				$cols['Log']= $match[10];
				return TRUE;
			}
			else if ($this->ParseSyslogLine($logline, $cols)) {
				$cols['IP']= _('NA');
				$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function PostProcessCols(&$cols)
	{
		if (preg_match('?(http|https)://([^/]*)?', $cols['Link'], $match)) {
			$cols['Proto']= $match[1];
			$cols['Link']= $match[2];
		}

		if (preg_match('/(\d+)\.(\d+)\.(\d+)/', $cols['Date'], $match)) {
			$cols['Date']= $match[1].'.'.($match[2] + 0).'.'.($match[3] + 0);
		}

		$time= explode(':', $cols['Time'], 3);
		$cols['Time']= sprintf('%02d', $time[0]).':'.sprintf('%02d', $time[1]).':'.sprintf('%02d', $time[2]);
	}
	
	function GetDateRegexp($date)
	{
		// Match all years
		$re= '.*\.';
		if ($date['Month'] == '') {
			$re.= '.*';
		}
		else {
			$re.= ($date['Month'] + 0).'\.';
			if ($date['Day'] == '') {
				$re.= '.*';
			}
			else {
				$re.= ($date['Day'] + 0);
			}
		}
		return $re;
	}

	function formatDateHourRegexp($month, $day, $hour, $minute)
	{
		global $Re_MonthNumbersNoLeadingZeros, $Re_DaysNoLeadingZeros;

		// 2017.9.7 1:06:16
		$reYear= '20[[:digit:]][[:digit:]]';

		if ($month != '') {
			$reMonth= $month + 0;
		} else {
			$reMonth= '(' . $Re_MonthNumbersNoLeadingZeros . ')';
		}

		if ($day != '') {
			$reDay= $day + 0;
		} else {
			$reDay= '(' . $Re_DaysNoLeadingZeros . ')';
		}

		if ($hour != '') {
			$reHour= $hour + 0;
		} else {
			$reHour= '([[:digit:]]|[[:digit:]][[:digit:]])';
		}

		if ($minute != '') {
			$reMinute= $minute;
		} else {
			$reMinute= '([[:digit:]][[:digit:]])';
		}

		return "^$reYear.$reMonth.$reDay $reHour:$reMinute:";
	}
}
?>
