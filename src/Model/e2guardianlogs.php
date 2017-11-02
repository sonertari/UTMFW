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
		
		if ($this->ParseSyslogLine($logline, $cols)) {
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];

			// - 192.168.1.33 http://URL.com *DENIED* Banned site: URL.com GET 0 0 Cleaning Domains 1 403 -   -
			// - 192.168.1.34 http://URL.com  GET 1632 0  1 404 text/html   -
			// - 192.168.1.34 http://URL.com *SCANNED*  GET 5137 -20  1 200 text/html   -
			$re= "/^$re_pip\s+$re_srcip\s+$re_link\s+$re_result\s+$re_mtd\s+$re_size\s+$re_ttl\s+$re_restorempty\s*$re_num\s+$re_num\s+$re_rest$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['IPsrc']= $match[1];
				$cols['IP']= $match[2];
				$cols['Link']= $match[3].$match[4];
				$cols['Scan']= $match[5];
				$cols['Mtd']= $match[6];
				$cols['Size']= $match[7];
				$cols['TTL']= $match[8];
				$log= $match[9].' '.$match[10].' '.$match[11].' '.$match[12];
				/// @todo What are the other category names?
				if (preg_match('/(.+)\s+(Domains|URLs|Sites|Phrases)/', $log, $cats)) {
					$cols['Cat']= $cats[1];
				}
				$cols['Log']= $log;
			}
			else {
				$cols['IP']= _('Unknown');
				$cols['Link']= _('Unknown');

				$re= "/^$re_result\s+$re_mtd\s+$re_nonempty\s+$re_nonempty\s+$re_nonempty\s+$re_link\s+$re_rest$/";
				if (preg_match($re, $cols['Log'], $match)) {
					$cols['Mtd']= $match[2];
					$cols['Scan']= $match[4].' '.$match[1].' '.$match[3].' '.$match[5];
					$cols['Link']= $match[6].$match[7];
					$cols['Log']= $match[8];
				}
			}
			return TRUE;
		}
		return FALSE;
	}
	
	function PostProcessCols(&$cols)
	{
		if (preg_match('?(http|https)://([^/]*)?', $cols['Link'], $match)) {
			$cols['Proto']= $match[1];
			$cols['Link']= $match[2];
		}
	}
}
?>
