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

/** @file
 * General configuration.
 */

require_once('e2guardian.php');

$Submenu= SetSubmenu('basic');

$Msg= _HELPWINDOW('The settings on this page are valid filter-wide.');

switch ($Submenu) {
	case 'basic':
		$View->ConfHelpMsg= _HELPWINDOW('IP and port configuration here are fundamental to the correct functioning of the web filter.')."\n\n".$Msg;
		break;
	case 'filter':
		$View->ConfHelpMsg= _HELPWINDOW('Phrase modes and cache settings determine how content scan works.')."\n\n".$Msg;
		break;
	case 'scan':
		$View->ConfHelpMsg= _HELPWINDOW('The web filter on UTMFW uses weighted phrase lists to scan the content of web pages, hence the name Content Scanning Web Filter. This feature of the web filter is different from and in addition to site or url lists, blacklists, and related categories.')."\n\n".$Msg;
		break;
	case 'logs':
		$View->ConfHelpMsg= _HELPWINDOW('Statistics collection entirely depends on what and how logs are recorded in the log files. So do not change these settings unless necessary.')."\n\n".$Msg;
		break;
	case 'downloads':
		$View->ConfHelpMsg= _HELPWINDOW('Fancy download manager is designed to provide download status to the user via a progress bar and text information. Therefore, it expects a web browser as user agent by default. Otherwise, downloads are handled by the default download manager.')."\n\n".$Msg;
		break;
	case 'advanced':
		$View->ConfHelpMsg= _HELPWINDOW('These advanced options can help you tune the performance of the web filter. For example, you can increase the maximum number of child processes if your internal network is large. However, the maximum number of processes a daemon user can start is restricted in login.conf file of the operating system. So make sure you adjust both settings accordingly.')."\n\n".$Msg;
		break;
}

$ViewConfigName= 'General'.$Submenu.'Config';
$View->Config= ${$ViewConfigName};
require_once('../lib/conf.php');
?>
