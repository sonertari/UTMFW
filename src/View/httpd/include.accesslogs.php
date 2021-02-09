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

require_once('include.php');

$LogConf = array(
	'httpdlogs' => array(
		'Fields' => array(
			'DateTime' => _TITLE('DateTime'),
			'IP' => _TITLE('IP'),
			'Mtd' => _TITLE('Mtd'),
			'Link' => _TITLE('Link'),
			'Code' => _TITLE('Code'),
			'Size' => _TITLE('Size'),
			),
		'HighlightLogs' => array(
			'Col' => 'Code',
			'REs' => array(
				'red' => array('5\d\d'),
				'yellow' => array('4\d\d'),
				),
			),
		),
	);

class Httpdlogs extends View
{
	public $Model= 'httpdlogs';
	public $LogsPage= 'accesslogs.php';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->LogsHelpMsg= _HELPWINDOW('These are the access logs of OpenBSD/httpd. Logs contain client IP addresses and pages accessed.');
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Link']= htmlspecialchars($cols['Link']);
	}
}

$View= new Httpdlogs();
?>
