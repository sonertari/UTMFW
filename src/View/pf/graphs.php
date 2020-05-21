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

require_once('pf.php');

$Submenu= SetSubmenu('ifs');

switch ($Submenu) {
	case 'ifs':
		$View->Layout= 'ifs';
		$View->GraphHelpMsg= _HELPWINDOW('Loopback is a logical interface.');
		break;

	case 'transfer':
		$View->Layout= 'transfer';
		$View->GraphHelpMsg= _HELPWINDOW('This graph shows the data transfer rate of packet filter. The transfer is between interfaces.');
		break;

	case 'states':
		$View->Layout= 'states';
		$View->GraphHelpMsg= _HELPWINDOW('State operations are perhaps the most meaningful measure of packet filter load.');
		break;

	case 'mbufs':
		$View->Layout= 'mbufs';
		$View->GraphHelpMsg= _HELPWINDOW('Mbufs indicate kernel memory management for networking.');
		break;

	case 'hosts':
		$View->GraphHelpMsg= _HELPWINDOW('These graphs show network usage per host. From top to bottom, host usage graphs are for the last hour, last 8-hour, daily, weekly, and monthly usages.');
		require_once("hostgraphs.php");
		exit;

	case 'protocol':
		$View->Layout= 'protograph';
		$View->GraphHelpMsg= _HELPWINDOW('This graph shows overall network usage based on certain protocols. Note that each protocol may cover more than one port.');
		break;
}

require_once('../lib/graphs.php');
?>
