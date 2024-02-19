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
	'openssh' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'Col' => 'Log',
			'REs' => array(
				'red' => array('Failed'),
				'yellow' => array('WARNING'),
				'green' => array('Accepted'),
				),
			),
		),
	);

class Openssh extends View
{
	public $Model= 'openssh';
	public $Layout= 'sshd';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('OpenSSH');

		$this->LogsHelpMsg= _HELPWINDOW('OpenSSH logs detailed information on all connections, successful logins and failures. Also provided are warnings based on reverse DNS lookups.');
		$this->ConfHelpMsg= _HELPWINDOW('Thanks to OpenSSH, remote root login to the system is permitted by default. TCP Keep Alive and Use DNS options may help you resolve some issues.');
	
		$this->Config = array(
			'Port' => array(
				'title' => _TITLE2('Port'),
				'info' => _HELPBOX2('The strategy used for options in the default sshd_config shipped with OpenSSH is to specify options with their default value where possible, but leave them commented.  Uncommented options change a default value.'),
				),
			'Protocol' => array(
				'title' => _TITLE2('Protocol'),
				),
			'AddressFamily' => array(
				'title' => _TITLE2('Address Family'),
				),
			'ListenAddress' => array(
				'title' => _TITLE2('Listen Address'),
				),
			'ServerKeyBits' => array(
				'title' => _TITLE2('Server Key Bits'),
				),
			'SyslogFacility' => array(
				'title' => _TITLE2('Syslog Facility'),
				'info' => _HELPBOX2('Obsoletes QuietMode and FascistLogging'),
				),
			'LogLevel' => array(
				'title' => _TITLE2('Log Level'),
				),
			'LoginGraceTime' => array(
				'title' => _TITLE2('Login Grace Time'),
				),
			'PermitRootLogin' => array(
				'title' => _TITLE2('Permit Root Login'),
				),
			'MaxAuthTries' => array(
				'title' => _TITLE2('Max Auth Tries'),
				),
			'PermitEmptyPasswords' => array(
				'title' => _TITLE2('Permit Empty Passwords'),
				),
			'PrintMotd' => array(
				'title' => _TITLE2('Print Motd'),
				),
			'PrintLastLog' => array(
				'title' => _TITLE2('Print Last Log'),
				),
			'TCPKeepAlive' => array(
				'title' => _TITLE2('TCP Keep Alive'),
				),
			'UseDNS' => array(
				'title' => _TITLE2('Use DNS'),
				),
			'MaxStartups' => array(
				'title' => _TITLE2('MaxStartups'),
				),
			'Banner' => array(
				'title' => _TITLE2('Banner'),
				'info' => _HELPBOX2('No default banner path'),
				),
			'Subsystem\s+sftp' => array(
				'title' => _TITLE2('Subsystem sftp'),
				'info' => _HELPBOX2('Override default of no subsystems'),
				),
			);
	}

	static function DisplayDashboardExtras()
	{
		global $ServiceInfo;
		?>
		<tr>
			<td colspan="4">
				<table>
					<tr id="dashboard">
						<td class="halfcountleft" title="<?php echo _TITLE2('Successful logins to the SSH Server') ?>">
							<a class="transparent-white" href="/openssh/stats.php">
							<div class="halfcount" style="background-color: green">
								<table>
									<tr class="count">
										<td class="count">
										<?php echo $ServiceInfo['openssh']['accepted'] ?>
										</td>
									</tr>
									<tr>
										<td class="prio">
										<?php echo _TITLE('ACCEPTED') ?>
										</td>
									</tr>
								</table>
							</div>
							</a>
						</td>
						<td class="halfcountright" title="<?php echo _TITLE2('Failed login attempts to the SSH Server') ?>">
							<a class="transparent-white" href="/openssh/stats.php">
							<div class="halfcount" style="background-color: red">
								<table>
									<tr class="count">
										<td class="count">
										<?php echo $ServiceInfo['openssh']['failed'] ?>
										</td>
									</tr>
									<tr>
										<td class="prio">
										<?php echo _TITLE('FAILED') ?>
										</td>
									</tr>
								</table>
							</div>
							</a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php
	}
}

$View= new Openssh();
?>
