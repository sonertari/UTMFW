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
	'logs' => array(
		'Name' => _MENU('Logs'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'archives' => _MENU('Archives'),
			'live' => _MENU('Live'),
			),
		),
	'database' => array(
		'Name' => _MENU('Spamd DB'),
		'Perms' => $ALL_USERS,
		),
	);

$LogConf = array(
	'spamd' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'REs' => array(
				'red' => array(),
				'yellow' => array('\bconnected\b'),
				'green' => array('\bdisconnected\b'),
				),
			),
		),
	);

class Spamd extends View
{
	public $Model= 'spamd';
	public $Layout= 'spamd';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('SPAM Deferral');
		$this->LogsHelpMsg= _HELPWINDOW('Spamd logs connections from spam sources and greylisted clients. Also recorded is length of time the client remained connected to spamd.');
	}
}

$View= new Spamd();

/**
 * Parses spamd database output line.
 *
 * @todo Should return something?
 * @todo Move this to Model
 *
 * @param string $logline DB output line to parse.
 * @param array $cols Parser output, parsed fields.
 */
function ParseSpamdDBLine($logline, &$cols)
{
	global $Re_Ip;

	$re_srcip= "($Re_Ip)";
	$re_num= '(\d+)';

	//WHITE|89.37.208.82|||1173738113|1173739941|1176851242|3|1
	$re= "/^WHITE\|$re_srcip\|\|\|$re_num\|$re_num\|$re_num\|$re_num\|$re_num$/";
	if (preg_match($re, $logline, $match)) {
		$cols['IP']= $match[1];
		$cols['First']= date('d.m.Y H:i', $match[2]);
		$cols['Listed']= date('d.m.Y H:i', $match[3]);
		$cols['Expire']= date('d.m.Y H:i', $match[4]);
		$cols['#Blocked']= $match[5];
		$cols['#Passed']= $match[6];
	}

	$re_domain= '([^|]+)';
	$re_email= '([^|]+)';

	//GREY|83.13.153.59|mail.optimeyes.com|<rifling@optimeyes.com>|<info@comixpbx.com>|1176311682|1176326082|1176326082|1|0
	$re= "/^GREY\|$re_srcip\|$re_domain\|$re_email\|$re_email\|$re_num\|$re_num\|$re_num\|$re_num\|$re_num$/";
	if (preg_match($re, $logline, $match)) {
		$cols['IP']= $match[1];
		$cols['From']= str_replace(array('<', '>'), '', $match[3]);
		$cols['To']= str_replace(array('<', '>'), '', $match[4]);
		$cols['First']= date('d.m.Y H:i', $match[5]);
		$cols['Listed']= date('d.m.Y H:i', $match[6]);
		$cols['Expire']= date('d.m.Y H:i', $match[7]);
		$cols['#Blocked']= $match[8];
		$cols['#Passed']= $match[9];
	}
	$cols['Log']= '';
}

/**
 * Displays spamd DB output lines.
 *
 * @param string $logline DB output to parse.
 * @param int $linenum Line number to print as the first column.
 */
function PrintSpamdDBLine($logline, $linenum)
{
	ParseSpamdDBLine($logline, $cols);
	PrintLogCols($linenum, $cols);
}
?>
