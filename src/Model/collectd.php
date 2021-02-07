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

require_once($MODEL_PATH.'/monitoring.php');

class Collectd extends Monitoring
{
	public $Name= 'collectd';
	public $User= 'root';

	public $ConfFile= '/etc/collectd.conf';

	public $VersionCmd= '/usr/local/sbin/collectd -h 2>&1';

	protected $LogFilter= 'collectd';

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

	/**
	 * Gets the first two hosts set for the collectd ping plugin.
	 *
	 * @return array Ping hosts as array.
	 */
	function GetPingHosts()
	{
		$gateway= '';
		if (($mygate= $this->_getStaticGateway()) !== FALSE) {
			$gateway= trim($mygate);
		} else if (($mygate= $this->_getDynamicGateway()) !== FALSE) {
			$gateway= trim($mygate);
		}

		if ($gateway === '') {
			Error(_('System has no gateway'));
		}

		$gateway_host= $this->_getGatewayPingHost();
		if ($gateway !== $gateway_host) {
			Error(_('System and ping gateway addresses do not match').": $gateway, $gateway_host");
		}

		return Output(json_encode(array($gateway_host, $this->_getRemotePingHost())));
	}

	function _getGatewayPingHost()
	{
		return $this->SearchFileAll($this->ConfFile, '|^<Plugin ping>\s+Host\s+"(\S+)"\s+Host\s+"(\S+)"\s+</Plugin>$|m', 1);
	}

	function _getRemotePingHost()
	{
		return $this->SearchFileAll($this->ConfFile, '|^<Plugin ping>\s+Host\s+"(\S+)"\s+Host\s+"(\S+)"\s+</Plugin>$|m', 2);
	}

	function SetGatewayPingHost($host)
	{
		$gateway_host= $this->_getGatewayPingHost();
		$remote_host= $this->_getRemotePingHost();
		return $this->ReplaceRegexp($this->ConfFile, "|^(<Plugin ping>\s+Host\s+\")($gateway_host)(\"\s+Host\s+\"$remote_host\"\s+</Plugin>)$|m", '${1}'.$host.'${3}');
	}

	function SetRemotePingHost($host)
	{
		$gateway_host= $this->_getGatewayPingHost();
		$remote_host= $this->_getRemotePingHost();
		return $this->ReplaceRegexp($this->ConfFile, "|^(<Plugin ping>\s+Host\s+\"$gateway_host\"\s+Host\s+\")($remote_host)(\"\s+</Plugin>)$|m", '${1}'.$host.'${3}');
	}
}
?>
