<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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

$Submenu= SetSubmenu('cpus');

switch ($Submenu) {
	case 'cpus':
		$View->Layout= 'cpus';
		$View->GraphHelpMsg= _HELPWINDOW('If your system has a multi-core CPU or more than one CPU, you can run the SMP kernel. If this page does not display multiple graphs, one for each CPU, after you switch to the SMP kernel, go to system configuration pages, apply automatic configuration, and reinitialize graph files.');
		break;

	case 'sensors':
		$View->Layout= 'sensors';
		$View->GraphHelpMsg= _HELPWINDOW('This page displays the graphs of all temperature and fan sensors in your system. You may have multiple sensors depending on your hardware. Virtual machines may not provide any sensors at all.');
		break;

	case 'memory':
		$View->Layout= 'memory';
		$View->GraphHelpMsg= _HELPWINDOW('This page displays the graph for shared memory and swap area usage. If the swap area usage is too high, you may consider adding more RAM. For higher system performance, you want to have no swap usage at all.');
		break;

	case 'disks':
		$View->Layout= 'disks';
		$View->GraphHelpMsg= _HELPWINDOW('This page displays the I/O graphs of all the disks in your system.');
		break;

	case 'partitions':
		$View->Layout= 'partitions';
		$View->GraphHelpMsg= _HELPWINDOW('This page displays the usage graphs of all the partitions on your disks.');
		break;
}

require_once('../lib/graphs.php');
?>
