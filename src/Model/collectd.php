<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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
	private $RrdFolder= '';

	function __construct()
	{
		parent::__construct();

		$this->StartCmd= '/usr/local/sbin/collectd';
		$this->RrdFolder= "{$this->CollectdRrdFolder}/ping";

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

	/**
	 * Kills collectd with the KILL signal, if the parent Stop() call fails.
	 *
	 * If a ping target is not reachable, we have to kill collectd passing 
	 * the -KILL signal.
	 *
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Stop()
	{
		$killed= parent::Stop();
		if (!$killed) {
			ctlr_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, "Pkill $this->Proc with KILL signal");
			$killed= $this->Pkill($this->Proc, '-KILL');
		}
		return $killed;
	}

	function _getModuleInfo($start)
	{
		// We are interested in the ping times in the last 60 seconds only
		return array(
			'gateway_ping_time'	=>	$this->getPingAverage($this->getGatewayPingHost()),
			'remote_ping_time'	=>	$this->getPingAverage($this->getRemotePingHost()),
			);
	}

	/**
	 * Gets the average of ping times to the given ping host for the given period.
	 *
	 * @param string $host Ping host.
	 * @param string $start Period of time backwards from now, in seconds.
	 * @return string Average ping time in float ms.
	 */
	function getPingAverage($host, $start= 60)
	{
		$total= 0;
		$count= 0;
		if (file_exists("$this->RrdFolder/ping-$host.rrd")) {
			exec("/usr/local/bin/rrdtool fetch -s -$start $this->RrdFolder/ping-$host.rrd AVERAGE", $output);

			foreach ($output as $a) {
				// 1612786040: nan
				// 1612785390: 7.0429000000e+01
				if (preg_match('/^\d+:\s*(\S+)$/', $a, $match)) {
					if ($match[1] != 'nan') {
						/// @attention Do not multiply value with rrd resolution, the dataset type of ping plugin is gauge
						$total+= floatval($match[1]);
						$count++;
					}
				}
			}
		}
		return sprintf('%.1lf ms', $count ? $total/$count : '0');
	}

	/**
	 * Gets the first two hosts set for the collectd ping plugin.
	 *
	 * @return array Ping hosts as array.
	 */
	function GetPingHosts()
	{
		global $MODEL_PATH;

		require_once($MODEL_PATH.'/system.php');
		$system= new System();
		$gateway= $system->getSystemGateway();

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
