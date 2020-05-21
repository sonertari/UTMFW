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

require_once('include.php');

$LogConf = array(
	'httpd' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		),
	);

class Httpd extends View
{
	public $Model= 'httpd';
	public $Layout= 'httpd';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('Web Server');

		$this->LogsHelpMsg= _HELPWINDOW('These logs may be important for diagnosing web server related problems.');

		$this->ConfHelpMsg= _HELPWINDOW('Since this web administration interface runs on the web server, you should be careful while modifying its options. By default, the web server is configured to serve this web interface only, hence the default values should suffice for most purposes.');
	
		$this->Config= array(
			'prefork' => array(
				'title' => _TITLE2('Start Servers'),
				'info' => _HELPBOX2('Number of servers to start initially --- should be a reasonable ballpark figure.'),
				),
			);
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= htmlspecialchars($cols['Log']);
	}
}

$View= new Httpd();
?>
