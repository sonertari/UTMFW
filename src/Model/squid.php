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
 * HTTP proxy.
 */

require_once($MODEL_PATH.'/model.php');

class Squid extends Model
{
	public $Name= 'squid';
	public $User= 'root|_squid';
	
	public $NVPS= '\h';
	public $ConfFile = '/etc/squid/squid.conf';
	public $LogFile= '/var/log/squid.log';

	public $VersionCmd= '/usr/local/sbin/squid -v';

	public $PidFile= '/var/squid/logs/squid.pid';

	function __construct()
	{
		global $TmpFile;
		
		parent::__construct();
		
		$this->StartCmd= "/usr/local/sbin/squid > $TmpFile 2>&1 &";
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetIpPort'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get HTTP proxy IP:port'),
					),

				'DelIpPort'	=> array(
					'argv'	=> array(IPPORT),
					'desc'	=> _('Delete HTTP proxy IP:port'),
					),

				'AddIpPort'	=> array(
					'argv'	=> array(IPPORT),
					'desc'	=> _('Add HTTP proxy IP:port'),
					),
				)
			);
	}

	/**
	 * Adds IP:port to squid config file.
	 *
	 * Adds the line above cache_dir comment.
	 * Cleans up duplicates first
	 * 
	 * @param string $if Interface IP:port.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function AddIpPort($if)
	{
		$this->DelIpPort($if);
		return $this->ReplaceRegexp($this->ConfFile, "/(\h*#\h*Uncomment and adjust the following to add a disk cache directory\..*)/m", "http_port $if\n".'${1}');
	}

	/**
	 * Deletes IP:port from squid config file.
	 *
	 * @warning If port is not provided, deletes all ports with that IP.
	 * 
	 * @param string $if Interface IP:port.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DelIpPort($if)
	{
		$if= Escape($if, '.');
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*http_port\h*$if\b.*(\s|))/m", '');
	}

	/**
	 * Extract all http IP:ports which squid listens to.
	 */
	function GetIpPort()
	{
		global $Re_IpPort;

		return Output($this->SearchFileAll($this->ConfFile, "/^\h*http_port\h*($Re_IpPort)\b.*\h*$/m"));
	}
}

$ModelConfig = array(
    'no_cache deny localhost' => array(
        'type' => FALSE,
		),
    'log_ip_on_direct' => array(
        'type' => STR_on_off,
		),
    'debug_options' => array(
		),
    'log_fqdn' => array(
        'type' => STR_on_off,
		),
    'client_netmask' => array(
        'type' => IP,
		),
    'http_access allow localhost' => array(
        'type' => FALSE,
		),
    'http_access deny all' => array(
        'type' => FALSE,
		),
    'cache_mgr' => array(
		),
    'logfile_rotate' => array(
        'type' => UINT,
		),
	);
?>
