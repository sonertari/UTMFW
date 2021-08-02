<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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

class Dnsmasq extends Model
{
	public $Name= 'dnsmasq';
	public $User= '_dnsmasq';
	
	public $ConfFile= '/etc/dnsmasq.conf';
	public $LogFile= '/var/log/dnsmasq.log';

	public $VersionCmd= '/usr/local/sbin/dnsmasq -v';

	public $PidFile= '/var/log/utmfw/run/dnsmasq.pid';

	function __construct()
	{
		parent::__construct();
		
		$this->StartCmd= '/usr/local/sbin/dnsmasq';
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetListenOn'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get interface DNS forwarder listens on'),
					),

				'SetListenOn'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Set interface DNS forwarder listens on'),
					),
				)
			);
	}

	function GetVersion()
	{
		$version= $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -1');
		if (preg_match('/(.+)\s+Copyright/', $version, $match)) {
			$version= $match[1];
		}
		return Output($version);
	}

	/**
	 * Gets the interface that the DNS forwarder binds to listen for requests.
	 *
	 * @return string Interface name.
	 */
	function GetListenOn()
	{
		return Output($this->GetNVP($this->ConfFile, 'interface'));
	}

	/**
	 * Sets the interface that the DNS forwarder binds to listen for requests.
	 *
	 * @param string $if Interface to bind.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetListenOn($if)
	{
		return $this->SetNVP($this->ConfFile, 'interface', $if);
	}

	function ParseLogLine($logline, &$cols)
	{
		if ($this->ParseSyslogLine($logline, $cols)) {
			// query[A] www.openbsd.org from 192.168.0.2
			// query[AAAA] 1.ubuntu.pool.ntp.org from 192.168.0.2
			$re= "/query\[(\S+)\]\s+(\S+)\s+from\s+(\S+)/";
			if (preg_match($re, $cols['Log'], $match)) {
				// Type field is for statistics only, not shown on Logs pages
				$cols['Type']= $match[1];
				$cols['Domain']= $match[2];
				$cols['IP']= $match[3];
				$cols['Reason']= 'query';
				// Log field is displayed on the Log column on Logs pages
				// Since we have further parsed the Log field now, update it
				$cols['Log']= $cols['Reason'].' '.$cols['Type'];
			} else {
				// cached yahoo.com is 74.6.231.20
				// cached detectportal.firefox.com is <CNAME>
				// cached connectivity-check.ubuntu.com is NODATA-IPv6
				if (preg_match("/^cached\s+(\S+)\s+is\s+/", $cols['Log'])) {
					$cols['Reason']= 'cached';
					$cols['Log']= htmlentities($cols['Log']);
				} else {
					// config error is REFUSED
					if (preg_match('/\s+is\s+REFUSED$/', $cols['Log'])) {
						$cols['Reason']= 'REFUSED';
					}
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}

$ModelConfig = array(
    'interface' => array(
        'type' => '\w+\d+',
		),
	);
?>
