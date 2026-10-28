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
				return TRUE;
			}

			// CONN: https [0,0] 192.168.210.2 39616 199.185.178.80 443 www.openbsd.org GET / 200 - 3622 sni:www.openbsd.org names:www.openbsd.org/ftplist1.openbsd.org/libressl.org/openbsd.org/openiked.org/openssh.com/openssh.org/www.libressl.org/www.openbsd.org/www.openiked.org/www.openrsync.org/www.openssh.com/www.openssh.org sproto:TLSv1.3:TLS_AES_128_GCM_SHA256 dproto:TLSv1.3:TLS_AES_256_GCM_SHA384 origcrt:6777392240B8976FECF86604CDE344EDB0473B2E usedcrt:8145965E0D22970381CACCC092E051D01005098C user:utmfw
			// STREAM: h2 [29,0] 192.168.210.2 52990 104.18.4.215 443 slashdot.org GET / - - - sni:slashdot.org names:slashdot.org/*.p.slashdot.org/*.slashdot.org/slashdot.org sproto:TLSv1.3:TLS_AES_128_GCM_SHA256 dproto:TLSv1.3:TLS_AES_256_GCM_SHA384 origcrt:3744045EEA0DE82040BDF7D8177FE63AAF7846A1 usedcrt:706E256104C0D78817BEDBABD463E94D1398A1E0 user:utmfw
			$re= "/^(CONN|STREAM):\s+(\S+)\s+\[\d+,\d+\]\s+(\S+)\s+(\d+)\s+(\S+)\s+(\d+)(\s+.*sproto:(\S+)\s+dproto:(\S+)|)(.*\s+user:(\S+|-))$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['Proto']= $match[2];
				$cols['SrcAddr']= $match[3];
				$cols['SrcPort']= $match[4];
				$cols['DstAddr']= $match[5];
				$cols['DstPort']= $match[6];
				$cols['SProto']= $match[8];
				$cols['DProto']= $match[9];
				$cols['User']= $match[11];
				return TRUE;
			}

			// IDLE: atime=114, ctime=114, src_addr=192.168.3.24:56530, dst_addr=192.168.111.130:443, user=soner, valid=0
			$re= "/^IDLE: atime=(\d+), ctime=(\d+), src_addr=(\S+|-):(\d+|-), dst_addr=(\S+|-):(\d+|-), user=(\S+|-), valid=\d+$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['IdleTime']= $match[1];
				$cols['IdleDuration']= $match[2];
				$cols['IdleSrcAddr']= $match[3];
				$cols['IdleDstAddr']= $match[5];
				$cols['IdleUser']= $match[7];
			} else {
				// EXPIRED: atime=124, ctime=124, src_addr=192.168.3.24:56530, dst_addr=192.168.111.130:443, user=soner, valid=0
				$re= "/^EXPIRED: atime=(\d+), ctime=\d+, src_addr=(\S+|-):(\d+|-), dst_addr=(\S+|-):(\d+|-), user=(\S+|-), valid=\d+$/";
				if (preg_match($re, $cols['Log'], $match)) {
					$cols['ExpiredIdleTime']= $match[1];
					$cols['ExpiredSrcAddr']= $match[2];
					$cols['ExpiredDstAddr']= $match[4];
					$cols['ExpiredUser']= $match[6];
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
