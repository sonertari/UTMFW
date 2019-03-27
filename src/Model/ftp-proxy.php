<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
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

require_once($MODEL_PATH.'/model.php');

class Ftpproxy extends Model
{
	public $Name= 'ftp-proxy';
	public $User= '_ftp_pro\w*';
	
	public $LogFile= '/var/log/ftp-proxy.log';
	
	function __construct()
	{
		parent::__construct();
		
		$this->StartCmd= '/usr/sbin/ftp-proxy -vv -D6';
	}
					
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			//#1 FTP session 1/100 started: client 192.168.1.1 to server 129.128.5.191 via proxy 10.0.0.13
			$re= "/FTP session \d+\/\d+ started: client ($Re_Ip) to server ($Re_Ip)/";
			if (preg_match($re, $logline, $match)) {
				$cols['Client']= $match[1];
				$cols['Server']= $match[2];
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
