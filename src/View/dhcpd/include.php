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

require_once('../lib/vars.php');

$Menu = array(
	'info' => array(
		'Name' => _MENU('Info'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'dhcpd' => _MENU('Dhcpd'),
			'arptable' => _MENU('Arp Table'),
			),
		),
	'graphs' => array(
		'Name' => _MENU('Graphs'),
		'Perms' => $ALL_USERS,
		),
	'logs' => array(
		'Name' => _MENU('Logs'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'archives' => _MENU('Archives'),
			'live' => _MENU('Live'),
			),
		),
	'conf' => array(
		'Name' => _MENU('Config'),
		'Perms' => $ADMIN,
		),
	);

$LogConf = array(
	'dhcpd' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		),
	'arp' => array(
		'Fields' => array(
			'IP' => _TITLE('IP'),
			'MAC' => _TITLE('MAC'),
			'Interface' => _TITLE('Interface'),
			'Expire' => _TITLE('Expire'),
			),
		),
	'lease' => array(
		'Fields' => array(
			'IP' => _TITLE('IP'),
			'Start' => _TITLE('Start'),
			'End' => _TITLE('End'),
			'MAC' => _TITLE('MAC'),
			'Host' => _TITLE('Host'),
			'Status' => _TITLE('Status'),
			),
		),
	);

class Dhcpd extends View
{
	public $Model= 'dhcpd';
	public $Layout= 'dhcpd';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('DHCP Server');
		$this->LogsHelpMsg= _HELPWINDOW('DHCP server logs the details of communications with DHCP clients.');
	}
}

$View= new Dhcpd();
?>
