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

require_once('include.php');

$LogConf = array(
    'apachelogs' => array(
        'Fields' => array(
            'DateTime',
            'IP',
            'Mtd',
            'Link',
            'Code',
            'Size',
    		),
		),
	);

class Apachelogs extends View
{
	public $Model= 'apachelogs';
	public $LogsPage= 'accesslogs.php';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->LogsHelpMsg= _HELPWINDOW('These are access logs of the Apache web server. Logs contain logged-in users, client IP addresses, and pages accessed.');
	}
	
	function FormatDate($date)
	{
		global $MonthNames;
		
		return $date['Day'].'/'.$MonthNames[$date['Month']].'/'.date('Y');
	}

	function FormatDateArray($datestr, &$date)
	{
		global $MonthNumbers;

		if (preg_match('/^(\d+)\/(\w+)\/(\d+)$/', $datestr, $match)) {
			$date['Day']= $match[1];
			$date['Month']= $MonthNumbers[$match[2]];
			return TRUE;
		}
		else if (preg_match('/(\w+)\s+(\d+)/', $datestr, $match)) {
			if (array_key_exists($match[1], $MonthNumbers)) {
				$date['Month']= sprintf('%02d', $MonthNumbers[$match[1]]);
				$date['Day']= sprintf('%02d', $match[2]);
				return TRUE;
			}
		}
		return FALSE;
	}
}

$View= new Apachelogs();
?>
