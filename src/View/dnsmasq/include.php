<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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
		),
	'stats' => array(
		'Name' => _MENU('Statistics'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'general' => _MENU('General'),
			'daily' => _MENU('Daily'),
			'hourly' => _MENU('Hourly'),
			'live' => _MENU('Live'),
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
	'dnsmasq' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'IP' => _TITLE('IP'),
			'Domain' => _TITLE('Domain'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'Col' => 'Reason',
			'REs' => array(
				'red' => array('REFUSED'),
				'yellow' => array('cached'),
				),
			),
		),
	);

class Dnsmasq extends View
{
	public $Model= 'dnsmasq';
	public $Layout= 'dnsmasq';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('DNS Forwarder');
		$this->LogsHelpMsg= _HELPWINDOW('DNS forwarder logs all queries.');
	}

	static function DisplayDashboardExtras()
	{
		?>
		<tr>
			<td colspan="4">
				<a class="transparent" href="/dnsmasq/stats.php"><img src="/system/dashboard/dns.png" name="dns" alt="dns" title="<?php echo _TITLE2('Queries handled by the DNS forwarder') ?>"></a>
			</td>
		</tr>
		<?php
	}
}

$View= new Dnsmasq();
?>
