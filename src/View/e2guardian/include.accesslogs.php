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
    'e2guardianlogs' => array(
        'Fields' => array(
            'Date',
            'Time',
            'IP',
            'Link',
            'Scan',
            'Mtd',
            'Size',
            'Log',
    		),
        'HighlightLogs' => array(
            'Col' => 'Scan',
            'REs' => array(
                'red' => array('\*DENIED\*'),
                'yellow' => array('Bypass cookie|Bypass URL'),
                'green' => array('\*SCANNED\*|\*TRUSTED\*'),
        		),
    		),
		),
	);

class E2guardianlogs extends View
{
	public $Model= 'e2guardianlogs';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->LogsHelpMsg= _HELPWINDOW('Among web filter log messages are page denials, virus scan results, denial bypasses or exceptions. However, some details can be found in HTTP proxy logs only, such as the sizes of file downloads if the download manager is engaged.');
	}
	
	/**
	 * Builds DG specific string from $date.
	 *
	 * The datetimes in log lines are different for each module.
	 * Does the opposite of FormatDateArray()
	 *
	 * @param array $date Datetime struct
	 * @return string Date
	 */
	function FormatDate($date)
	{
		return date('Y').'.'.$date['Month'].'.'.$date['Day'];
	}

	/**
	 * Builds DG specific $date from string.
	 */
	function FormatDateArray($datestr, &$date)
	{
		global $MonthNumbers;

		if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $datestr, $match)) {
			$date['Month']= $match[2];
			$date['Day']= $match[3];
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
	
	function FormatLogCols(&$cols)
	{
		$link= $cols['Link'];
		if (preg_match('|^(http://[^/]*)|', $cols['Link'], $match)) {
			$linkbase= $match[1];
		}
		$cols['Link']= '<a href="'.$link.'" title="'.$link.'">'.wordwrap($linkbase, 40, '<br />', TRUE).'</a>';
		$cols['Scan']= wordwrap($cols['Scan'], 40, '<br />', TRUE);
	}
}

$View= new E2guardianlogs();
?>
