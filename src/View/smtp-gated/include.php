<?php
/*
 * Copyright (C) 2004-2022 Soner Tari
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
	'smtp-gated' => array(
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
				'red' => array('VIRUS', 'FOUND', 'SESSION TAKEOVER', 'LOCK:LOCKED'),
				'yellow' => array('LOCK:EXPIRED', 'NEW'),
				'green' => array('CLEAN', 'CLOSE'),
				),
			),
		),
	);

class Smtpgated extends View
{
	public $Model= 'smtp-gated';
	public $Layout= 'smtp-gated';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('SMTP Proxy');

		$this->LogsHelpMsg= _HELPWINDOW('You may want to watch these logs carefully to determine infected clients in the internal network.');
		$this->ConfHelpMsg= _HELPWINDOW('If an outgoing e-mail is determined to be infected or identified as spam, the client trying to send that e-mail is blocked from sending any e-mails for a period of time defined by lock duration. You can enter your e-mail address in report e-mail option.');
		
		$this->Config = array(
			'lock_on' => array(
				'title' => _TITLE2('Lock on'),
				),
			'lock_duration' => array(
				'title' => _TITLE2('Lock duration'),
				),
			'abuse' => array(
				'title' => _TITLE2('Report e-mail'),
				),
			'priority' => array(
				'title' => _TITLE2('Priority'),
				),
			'max_connections' => array(
				'title' => _TITLE2('Maximum connections'),
				),
			'max_per_host' => array(
				'title' => _TITLE2('Maximum connections per host'),
				),
			'max_load' => array(
				'title' => _TITLE2('Maximum load'),
				),
			'scan_max_size' => array(
				'title' => _TITLE2('Maximum virus scan size'),
				),
			'spam_max_size' => array(
				'title' => _TITLE2('Maximum spam scan size'),
				),
			'spam_max_load' => array(
				'title' => _TITLE2('Maximum spam load'),
				),
			'spam_threshold' => array(
				'title' => _TITLE2('Spam threshold'),
				),
			'ignore_errors' => array(
				'title' => _TITLE2('Ignore errors'),
				),
			'spool_leave_on' => array(
				'title' => _TITLE2('Leave spool on'),
				),
			'log_helo' => array(
				'title' => _TITLE2('Log helo'),
				),
			'log_mail_from' => array(
				'title' => _TITLE2('Log mail from'),
				),
			'log_rcpt_to' => array(
				'title' => _TITLE2('Log rcpt to'),
				),
			'log_level' => array(
				'title' => _TITLE2('Log level'),
				),
			'nat_header_type' => array(
				'title' => _TITLE2('NAT header type'),
				),
			'locale' => array(
				'title' => _TITLE2('Locale'),
				),
			);
	}

	function FormatLogCols(&$cols)
	{
		$cols['Log']= htmlspecialchars($cols['Log']);
		if (isset($cols['Sender'])) {
			$cols['Sender']= htmlspecialchars($cols['Sender']);
		}
		if (isset($cols['Recipient'])) {
			$cols['Recipient']= htmlspecialchars($cols['Recipient']);
		}
	}

	static function DisplayDashboardExtras()
	{
		?>
		<tr>
			<td colspan="4">
				<a class="transparent" href="/smtp-gated/stats.php"><img src="/system/dashboard/smtp-gated.png" name="smtp-gated" alt="smtp-gated" title="<?php echo _TITLE2('Requests handled by the SMTP Proxy') ?>"></a>
			</td>
		</tr>
		<?php
	}
}

$View= new Smtpgated();
?>
