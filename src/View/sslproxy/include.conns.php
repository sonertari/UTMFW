<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
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
	'sslproxyconns' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'REs' => array(
				'red' => array('EXPIRED:'),
				'yellow' => array('IDLE:'),
				),
			),
		),
	);

class Sslproxyconns extends View
{
	public $Model= 'sslproxyconns';
	public $StatsPage= 'connstats.php';
	public $LogsPage= 'conns.php';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->LogsHelpMsg= _HELPWINDOW('The SSL proxy takes 3 different kinds of connection logs: (1) CONN for connection details at establisment time, (2) IDLE for slow connections, and (3) EXPIRED for timed-out connections which are closed by the SSL proxy.');
	}

	/**
	 * Displays parsed log line.
	 *
	 * @param array $cols Columns parsed.
	 * @param int $linenum Line number to print as the first column.
	 * @param array $lastlinenum Last line number, used to detect the last line
	 */
	function PrintLogLine($cols, $linenum, $lastlinenum)
	{
		$class= $this->getLogLineClass($cols['Log'], $cols);
		PrintLogCols($linenum, $cols, $lastlinenum, $class);
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= wordwrap($cols['Log'], 150, '<br />', TRUE);
	}
}

$View= new Sslproxyconns();
?>
