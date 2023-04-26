<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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

require_once('include.php');

$LogConf = array(
	'e2guardianlogs' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'User' => _TITLE('User'),
			'IP' => _TITLE('IP'),
			'Link' => _TITLE('Link'),
			'Scan' => _TITLE('Scan'),
			'Mtd' => _TITLE('Mtd'),
			'Size' => _TITLE('Size'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'Col' => 'Scan',
			'REs' => array(
				'red' => array('\*DENIED\*|\*NETERROR\*'),
				'yellow' => array('Bypass cookie|Bypass URL|\*TRUSTED\*'),
				'green' => array('\*SCANNED\*'),
				),
			),
		),
	);

class E2guardianlogs extends View
{
	public $Model= 'e2guardianlogs';
	public $LogsPage= 'accesslogs.php';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->LogsHelpMsg= _HELPWINDOW('Among the web filter log messages are page denials, virus scan results, denial bypasses or exceptions.');
	}
	
	function FormatLogCols(&$cols)
	{
		$link= $cols['Link'];
		$linkbase= '';
		if (preg_match('?^(http(|s)://[^/]*)?', $cols['Link'], $match)) {
			$linkbase= $match[1];
		}
		$cols['Link']= '<a href="'.$link.'" title="'.$link.'">'.$linkbase.'</a>';
	}
}

$View= new E2guardianlogs();
?>
