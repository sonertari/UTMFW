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

require_once('include.php');

$LogConf = array(
	'snortalerts' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Log' => _TITLE('Log'),
			'Prio' => _TITLE('Prio'),
			'Proto' => _TITLE('Proto'),
			'SrcIP' => _TITLE('SrcIP'),
			'SPort' => _TITLE('SPort'),
			'DstIP' => _TITLE('DstIP'),
			'DPort' => _TITLE('DPort'),
			),
		'HighlightLogs' => array(
			'Col' => 'Prio',
			),
		),
	);

class Snortalerts extends View
{
	public $Model= 'snortalerts';
	public $LogsPage= 'alerts.php';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->LogsHelpMsg= _HELPWINDOW('These are intrusion alerts. Note that these alerts can be considered as guesses at best. While configuring the IPS, make use of priorities and keywords.');
	}
	
	function getLogLineClass($line, $cols)
	{
		$class= '';
		if (preg_match('/(\d+)/', $line, $match)) {
			$priority= $match[1];

			if ($priority >= 6) {
				$class= 'red';
			}
			else if ($priority >= 3) {
				$class= 'yellow';
			}
		}
		return $class;
	}
}

$View= new Snortalerts();
?>
