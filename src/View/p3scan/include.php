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
	'p3scan' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'REs' => array(
				'red' => array('Critial abort', 'Cannot connect', 'ERR:', '\bvirus:'),
				'yellow' => array('Connection from\b'),
				'green' => array('Clean Exit'),
				),
			),
		),
	);

class P3scan extends View
{
	public $Model= 'p3scan';
	public $Layout= 'p3scan';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('POP3 Proxy');

		$this->LogsHelpMsg= _HELPWINDOW('POP3 proxy logs connection information and process exit status.');
		$this->ConfHelpMsg= _HELPWINDOW('You may want to set the report language for your locale. By default, POP3 proxy scans for both spam and viruses.');
	
		/**
		 * P3scan configuration.
		 *
		 * See E2guardian $basicConfig for details.
		 */
		$this->Config = array(
			'checkspam' => array(
				'title' => _TITLE2('Scan Spam'),
				'info' => _HELPBOX2('If enabled, will scan for Spam before scanning for a virus. You should start spamd before running p3scan.'),
				),
			'justdelete' => array(
				'title' => _TITLE2('Just Delete'),
				'info' => _HELPBOX2('Instead of keeping an infected message in the Virus Directory, delete it after reporting it to the user.
		default: Keep infected messages in Virus Directory'),
				),
			'maxchilds' => array(
				'title' => _TITLE2('Max Childs'),
				'info' => _HELPBOX2('The maximum number of connections we will handle at once. Any further connections will be dropped. Keep in mind that a number of 10 also means that 10 viruscanner can run at once.'),
				),
			'bytesfree' => array(
				'title' => _TITLE2('Bytes Free'),
				'info' => _HELPBOX2('The number of KB\'s there must be free before processing any mail. If there is less than this amount, p3scan will terminate any connections until the problem is resolved.
		default: 100MB'),
				),
			'debug' => array(
				'title' => _TITLE2('Debug'),
				'info' => _HELPBOX2('Turn on debugging.'),
				),
			'quiet' => array(
				'title' => _TITLE2('Quiet'),
				'info' => _HELPBOX2('Disable reporting of normal operating messages. Only report errors or critical information.
		default: display all except debug info'),
				),
			);
	}

	function FormatLogCols(&$cols)
	{
		$cols['Log']= htmlspecialchars($cols['Log']);
	}
}

$View= new P3scan();
?>
