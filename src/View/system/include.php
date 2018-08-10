<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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

/**
 * Menu configuration.
 *
 * This array provides menu configuration parameters needed for each module.
 * Detailed behaviour of each module is defined thru the settings in this array.
 *
 * @param SubMenus	Each module page may have its own submenus (pages).
 *					These sub-fields also point to another array. The indeces
 *					of this array are used for $_GET method url, and are
 *					associated with the translatable title of sub-page.
 */
$Menu = array(
	'info' => array(
		'Name' => _MENU('Info'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'dashboard' => _MENU('Dashboard'),
			'system' => _MENU('System'),
			),
		),
	'graphs' => array(
		'Name' => _MENU('Graphs'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'cpus' => _MENU('CPUs'),
			'sensors' => _MENU('Sensors'),
			'memory' => _MENU('Memory'),
			'disks' => _MENU('Disks'),
			'partitions' => _MENU('Partitions'),
			),
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
		'SubMenu' => array(
			'basic' => _MENU('Basic'),
			'net' => _MENU('Network'),
			'init' => _MENU('Init'),
			'startup' => _MENU('Startup'),
			'logs' => _MENU('Logs'),
			'wui' => _MENU('WUI'),
			'notifier' => _MENU('Notifier'),
			),
		),
	'procs' => array(
		'Name' => _MENU('Processes'),
		'Perms' => $ALL_USERS,
		),
	);

/**
 * Log configuration.
 *
 * This array provides log configuration parameters needed for each module.
 * Detailed behaviour of each module is defined thru the settings in this array.
 *
 * @param Name				Translatable name of the module.
 * @param Fields			Array of columns to show on Logs pages. Parsers may
 * 							produce more fields than those listed here, e.g. for
 * 							statistics functions.
 * @param HighlightLogs		Used for coloring log lines, holds params for coloring function
 * @param HighlightLogs>Col	Column/field to search for keywords (to color the line)
 * @param HighlightLogs>Tag	Tag to use in the HTML style, usually 'class', but also 'id'
 * @param HighlightLogs>Keywords	Contains keywords for red, yellow, and green.
 * 									Precedence being in that order.
 */
$LogConf = array(
	'system' => array(
		'Name' => _TITLE2('System'),
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		),
	);

class System extends View
{
	public $Model= 'system';
	public $Layout= 'system';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('System');
		$this->LogsHelpMsg= _HELPWINDOW('This page shows system-wide logs. Most services on UTMFW write to their own log files, but a few processes may log messages to this system file. Pay special attention to error messages.');
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= wordwrap($cols['Log'], 150, '<br />', TRUE);
	}
}

$View= new System();
?>
