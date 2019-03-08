<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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

require_once($MODEL_PATH.'/sslproxy.php');

class Sslproxyconns extends Sslproxy
{
	public $Name= 'sslproxyconns';
	public $LogFile= '/var/log/sslproxy/conns.log';
	
	/**
	 * Parses SSLproxy conns logs.
	 *
	 * @param string $logline Log line to parse.
	 * @param array $cols Parser output, parsed fields.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ParseLogLine($logline, &$cols)
	{
		if ($this->ParseSyslogLine($logline, $cols)) {
			// CONN: https 192.168.3.24 60512 172.217.17.206 443 safebrowsing-cache.google.com GET /safebrowsing/rd/xxx 200 - sni:safebrowsing-cache.google.com names:- sproto:TLSv1.2:ECDHE-RSA-AES128-GCM-SHA256 dproto:TLSv1.2:ECDHE-ECDSA-AES128-GCM-SHA256 origcrt:- usedcrt:- user:soner
			// CONN: pop3s 192.168.3.24 46790 66.102.1.108 995 sni:pop.gmail.com names:- sproto:TLSv1.2:ECDHE-RSA-AES128-GCM-SHA256 dproto:TLSv1.2:ECDHE-RSA-AES128-GCM-SHA256 origcrt:- usedcrt:- user:soner
			// CONN: http 192.168.3.24 37044 129.128.5.194 80 www.openbsd.org GET /errata64.html 200 8559 user:soner 
			$re= "/^CONN:\s+(\S+)\s+(\S+)\s+(\d+)\s+(\S+)\s+(\d+)(\s+.*sproto:(\S+)\s+dproto:(\S+)|)(.*\s+user:(\S+|-))$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['Proto']= $match[1];
				$cols['SrcAddr']= $match[2];
				$cols['SrcPort']= $match[3];
				$cols['DstAddr']= $match[4];
				$cols['DstPort']= $match[5];
				$cols['SProto']= $match[7];
				$cols['DProto']= $match[8];
				$cols['User']= $match[10];
			} else {
				// IDLE: thr=0, id=1, ce=1 cc=1, at=0 ct=0, src_addr=192.168.3.24:56530, dst_addr=192.168.111.130:443, user=soner, valid=0
				$re= "/^IDLE: thr=(\d+), id=(\d+),.*, at=(\d+) ct=(\d+), src_addr=(\S+|-):(\d+|-), dst_addr=(\S+|-):(\d+|-), user=(\S+|-), valid=\d+$/";
				if (preg_match($re, $cols['Log'], $match)) {
					$cols['ThreadIdx']= $match[1];
					$cols['ConnIdx']= $match[2];
					$cols['IdleTime']= $match[3];
					$cols['IdleDuration']= $match[4];
					$cols['IdleSrcAddr']= $match[5];
					$cols['IdleDstAddr']= $match[7];
					$cols['IdleUser']= $match[9];
				} else {
					// EXPIRED: thr=1, time=0, src_addr=192.168.3.24:56530, dst_addr=192.168.111.130:443, user=soner, valid=0
					$re= "/^EXPIRED: thr=\d+, time=(\d+), src_addr=(\S+|-):(\d+|-), dst_addr=(\S+|-):(\d+|-), user=(\S+|-), valid=\d+$/";
					if (preg_match($re, $cols['Log'], $match)) {
						$cols['ExpiredIdleTime']= $match[1];
						$cols['ExpiredSrcAddr']= $match[2];
						$cols['ExpiredDstAddr']= $match[4];
						$cols['ExpiredUser']= $match[6];
					}
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
