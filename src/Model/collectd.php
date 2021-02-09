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

require_once($MODEL_PATH.'/monitoring.php');

class Collectd extends Monitoring
{
	public $Name= 'collectd';
	public $User= 'root';

	public $ConfFile= '/etc/collectd.conf';

	public $VersionCmd= '/usr/local/sbin/collectd -h 2>&1';

	protected $LogFilter= 'collectd';
	protected $RrdFolder= '/var/collectd/localhost/ping';

	function __construct()
	{
		parent::__construct();

		$this->StartCmd= '/usr/local/sbin/collectd';

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetPingHosts'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get ping hosts'),
					),

				'SetGatewayPingHost'=>	array(
					'argv'	=>	array(IPADR|URL),
					'desc'	=>	_('Set gateway ping host'),
					),

				'SetRemotePingHost'=>	array(
					'argv'	=>	array(IPADR|URL),
					'desc'	=>	_('Set remote ping host'),
					),
				)
			);
	}

	function GetVersion()
	{
		return Output(explode(',', $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -21 | /usr/bin/tail -1'))[0]);
	}

	function _getModuleStatus($generate_info= FALSE, $start= 0)
	{
		$status= parent::_getModuleStatus($generate_info, $start);

		if ($generate_info) {
			$gateway_host= $this->getGatewayPingHost();
			$status['info']['gateway_ping_time']= $this->getPingAverage($gateway_host);

			$remote_host= $this->getRemotePingHost();
			$status['info']['remote_ping_time']= $this->getPingAverage($remote_host);
		}
		return $status;
	}

	function getPingAverage($host, $start= '1min')
	{
		exec("/usr/local/bin/rrdtool fetch -s -$start $this->RrdFolder/ping-$host.rrd AVERAGE", $output);
		$total= 0;
		$count= 0;
		foreach ($output as $a) {
			// 1612786040: nan
			// 1612785390: 7.0429000000e+01
			if (preg_match('/^\d+:\s*(\S+)$/', $a, $match)) {
				if ($match[1] != 'nan') {
					$total+= floatval($match[1]);
					$count++;
				}
			}
		}
		return sprintf('%6.1lf ms', $count ? $total/$count : '0');
	}

	/**
	 * Gets the first two hosts set for the collectd ping plugin.
	 *
	 * @return array Ping hosts as array.
	 */
	function GetPingHosts()
	{
		$gateway= $this->getSystemGateway();
		$gateway_host= $this->getGatewayPingHost();
		if ($gateway !== $gateway_host) {
			Error(_('System and ping gateway addresses do not match').": $gateway, $gateway_host");
		}

		return Output(json_encode(array($gateway_host, $this->getRemotePingHost())));
	}

	function getGatewayPingHost()
	{
		return $this->SearchFileAll($this->ConfFile, '|^<Plugin ping>\s+Host\s+"(\S+)"\s+Host\s+"(\S+)"\s+</Plugin>$|m', 1);
	}

	function getRemotePingHost()
	{
		return $this->SearchFileAll($this->ConfFile, '|^<Plugin ping>\s+Host\s+"(\S+)"\s+Host\s+"(\S+)"\s+</Plugin>$|m', 2);
	}

	function SetGatewayPingHost($host)
	{
		$gateway_host= $this->getGatewayPingHost();
		$remote_host= $this->getRemotePingHost();
		return $this->ReplaceRegexp($this->ConfFile, "|^(<Plugin ping>\s+Host\s+\")($gateway_host)(\"\s+Host\s+\"$remote_host\"\s+</Plugin>)$|m", '${1}'.$host.'${3}');
	}

	function SetRemotePingHost($host)
	{
		$gateway_host= $this->getGatewayPingHost();
		$remote_host= $this->getRemotePingHost();
		return $this->ReplaceRegexp($this->ConfFile, "|^(<Plugin ping>\s+Host\s+\"$gateway_host\"\s+Host\s+\")($remote_host)(\"\s+</Plugin>)$|m", '${1}'.$host.'${3}');
	}
}
?>
