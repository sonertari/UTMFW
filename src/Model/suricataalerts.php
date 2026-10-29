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

class Suricataalerts extends Suricata
{
	public $Name= 'suricataalerts';
	
	public $LogFile= '/var/log/suricata/fast.log';
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		$re_datetime= '(\d+\/\d+\/\d+\-\d+:\d+:\d+)\.\d+';
		$re_marker= '\[\*\*\]';
		$re_triplet= '\[[:\d]+\]';
		$re_report= '(.*)';

		$re_priority= '\[Priority:\s*(\d+)\]';
		$re_proto= '\{([^\}]*)\}';
		$re_from= "($Re_Ip)";
		$re_to= "($Re_Ip)";
		$re_port= '(:\d+|)';

		$dateTimeFormat= 'm/d/Y-H:i:s';

		//Jun 27 18:12:41 utmfw suricata[2875]: [122:1:0] (portscan) TCP Portscan[Priority: 3]: {RESERVED} 10.0.0.11 -> 10.0.0.13
		//Jun 27 18:12:45 utmfw suricata[2875]: [116:59:1] (suricata_decoder): Tcp Window Scale Option found with length > 14[Priority: 3]: {TCP} 10.0.0.11:52936 -> 10.0.0.13:25
		// The new log format does not have a colon after Priority
		//Aug 25 09:14:55 utmfw suricata[2875]: [1:853:10] WEB-CGI wrap access [Classification: Attempted Information Leak] [Priority: 2] {TCP} 10.0.0.11:35690 -> 209.85.129.147:80

		// Suricata does not support syslog alert logs
		// 10/27/2026-22:20:27.566535  [**] [1:2200074:2] SURICATA TCPv4 invalid checksum [**] [Classification: Generic Protocol Command Decode] [Priority: 3] {TCP} 192.168.210.1:443 -> 192.168.210.2:40136
		// 10/27/2026-22:20:30.800104  [**] [1:2210044:2] SURICATA STREAM Packet with invalid timestamp [**] [Classification: Generic Protocol Command Decode] [Priority: 3] {TCP} 192.168.210.1:443 -> 192.168.210.2:40136
		$re= "/$re_datetime\s*$re_marker\s*$re_triplet\s*$re_report\s*$re_priority(:|)\s*$re_proto\s+$re_from$re_port\s*->\s*$re_to$re_port$/";
		if (preg_match($re, $logline, $match)) {
			$dt= DateTime::createFromFormat($dateTimeFormat, $match[1]);
			$ts= $dt->getTimestamp();
			$cols['Date']= date('M j', $ts);
			$cols['Time']= date("H:i:s", $ts);
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];

			$cols['Log']= $match[2];
			$cols['Prio']= $match[3];
			$cols['Proto']= $match[5];
			$cols['SrcIP']= $match[6];
			$cols['SPort']= ltrim($match[7], ':');
			$cols['DstIP']= $match[8];
			$cols['DPort']= ltrim($match[9], ':');
			return TRUE;
		}

		// 10/27/2026-22:20:34.058217  [**] [1:2200121:1] SURICATA Ethertype unknown [**] [Classification: Generic Protocol Command Decode] [Priority: 3] [**] [Raw pkt: 01 80 C2 00 00 00 34 29 12 15 9F 20 00 26 42 42 03 00 00 00 00 00 80 00 34 29 12 15 9F 20 00 00 ]
		$re= "/$re_datetime\s*$re_marker\s*$re_triplet\s*$re_report\s*$re_priority(:|)(.*)$/";
		if (preg_match($re, $logline, $match)) {
			$dt= DateTime::createFromFormat($dateTimeFormat, $match[1]);
			$ts= $dt->getTimestamp();
			$cols['Date']= date('M j', $ts);
			$cols['Time']= date("H:i:s", $ts);
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];

			$cols['Log']= $match[2].' '.$match[5];
			$cols['Prio']= $match[3];

			$cols['Proto']= '-';
			$cols['SrcIP']= '-';
			$cols['SPort']= '-';
			$cols['DstIP']= '-';
			$cols['DPort']= '-';
			return TRUE;
		}
		return FALSE;
	}

	function formatDateHourRegexp($month, $day, $hour, $minute)
	{
		// 10/27/2026-22:20:34.058217
		if ($month != '') {
			$reMonth= sprintf('%02d', $month);
		} else {
			$reMonth= '([[:digit:]][[:digit:]])';
		}

		if ($day != '') {
			$reDay= sprintf('%02d', $day);
		} else {
			$reDay= '([[:digit:]][[:digit:]])';
		}

		if ($hour != '') {
			$reHour= sprintf('%02d', $hour);
		} else {
			$reHour= '([[:digit:]][[:digit:]])';
		}

		if ($minute != '') {
			$reMinute= sprintf('%02d', $minute);
		} else {
			$reMinute= '([[:digit:]][[:digit:]])';
		}

		$reYear= '([[:digit:]][[:digit:]][[:digit:]][[:digit:]])';

		return "^$reMonth/$reDay/$reYear-$reHour:$reMinute:";
	}
}
?>
