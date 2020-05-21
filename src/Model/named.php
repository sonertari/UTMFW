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

require_once($MODEL_PATH.'/model.php');

class Named extends Model
{
	public $Name= 'named';
	public $User= 'root|_bind';
	
	public $ConfFile= '/var/named/etc/named.conf';
	public $LogFile= '/var/log/named.log';

	public $VersionCmd= '/usr/local/sbin/named -v';

	public $PidFile= '/var/named/var/run/named/named.pid';

	function __construct()
	{
		parent::__construct();
		
		$this->StartCmd= '/usr/local/sbin/named -t /var/named/';
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetListenOn'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get IP DNS listens on'),
					),

				'SetListenOn'	=> array(
					'argv'	=> array(IPADRLIST|STR),
					'desc'	=> _('Set IP DNS listens on'),
					),

				'GetForwarders'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get DNS forwarders'),
					),

				'SetForwarders'	=> array(
					'argv'	=> array(IPADRLIST),
					'desc'	=> _('Set DNS forwarders'),
					),
				)
			);
	}

	/**
	 * Gets the IP address(es) that the name server listens on for requests.
	 *
	 * @return string IP address, semi-colon separated addresses, or any.
	 */
	function GetListenOn()
	{
		return Output($this->SearchFile($this->ConfFile, "/^\h*listen-on\h*{\h*(.*)\h*\;\h*}\h*\;\h*$/m"));
	}

	/**
	 * Sets the IP address that the name server listens on for requests.
	 *
	 * @param string $listenon Semicolon separated list of IPs, or any.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetListenOn($listenon)
	{
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*listen-on\h*{\h*)(.*)(\h*\;\h*}\h*\;\h*)$/m", '${1}'.$listenon.'${3}');
	}
	
	/**
	 * Gets name server forwarders.
	 *
	 * @return Forwarders IP, semi-colon separated.
	 * @todo Is semi-colon separated list fine?
	 */
	function GetForwarders()
	{
		return Output($this->SearchFile($this->ConfFile, "/^\h*forwarders\h*{\h*(.*)\h*\;\h*}\h*\;\h*$/m"));
	}

	/**
	 * Sets name server forwarders.
	 *
	 * @param string $forwarders Semicolon separated list of forwarder IP addresses.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetForwarders($forwarders)
	{
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*forwarders\h*{\h*)(.*)(\h*\;\h*}\h*\;\h*)$/m", '${1}'.$forwarders.'${3}');
	}
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_clientip= "($Re_Ip)";
			$re_num= '(\d+)';
			$re_domain= '(\S+)';
			$re_type= '(\S+\s+\S+)';

			// Older: client 127.0.0.1#31874: query: www.openbsd.org IN A +
			// Old: client 192.168.5.2#49585 (detectportal.firefox.com): query: detectportal.firefox.com IN AAAA + (192.168.5.1)
			// New: client @0x1512ca59d450 192.168.11.2#49672 (detectportal.firefox.com): query: detectportal.firefox.com IN AAAA + (192.168.11.1)
			$re= "/client\s+\S+\s+$re_clientip#$re_num(\h*\([^\)]*\)|):\s+query:\s+$re_domain\s+$re_type\s+.*\s+\(($Re_Ip)\)$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['IP']= $match[1];
				// Skip port
				$cols['Domain']= $match[4];
				// Type field is for statistics only, not shown on Logs pages
				$cols['Type']= $match[5];
				// Log field is displayed on the Log column on Logs pages
				// Since we have further parsed the Log field now, update it with the Type value
				$cols['Log']= $cols['Type'];
			} else {
				// client @0x151299cc5450 192.168.11.2#49672 (detectportal.firefox.com): query failed (SERVFAIL) for detectportal.firefox.com/IN/A at /usr/obj/ports/isc-bind-9.11.3/bind-9.11.3/bin/named/query.c:6885
				$re= "/client\s+\S+\s+$re_clientip#$re_num(\h*\([^\)]*\)|):\s+query\s+failed\s+\((.+)\)\s+for\s+$re_domain\/(.*)\/(.*)\s+at\s+.*$/";
				if (preg_match($re, $cols['Log'], $match)) {
					$cols['IP']= $match[1];
					$cols['Reason']= $match[4];
					$cols['Domain']= $match[5];
					$cols['Type']= $match[6].' '.$match[7];
					$cols['Log']= $cols['Type'].' query failed: '.$cols['Reason'];
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
