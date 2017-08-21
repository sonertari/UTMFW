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

/** @file
 * Group configuration.
 */

require_once('e2guardian.php');

$Submenu= SetSubmenu('groups');

$Msg= _HELPWINDOW('These settings apply to currently active group only.');

switch ($Submenu) {
	case 'groups':
		require_once('conf.groups.php');
		exit;
	case 'basic':
		$View->ConfHelpMsg= _HELPWINDOW('The options on this page determine basic features of the group. Group name shows on the Denied page and logs.')."\n\n".$Msg;
		break;
	case 'scan':
		$View->ConfHelpMsg= _HELPWINDOW('Thresholds and settings here are related with the content scanning feature of the web filter.')."\n\n".$Msg;
		break;
	case 'blanket':
		$View->ConfHelpMsg= _HELPWINDOW('Blanket block configuration is used to block all traffic for that rule.')."\n\n".$Msg;
		break;
	case 'bypass':
		$View->ConfHelpMsg= _HELPWINDOW('The options here are for the Denied page which the user receives instead of the page requested.')."\n\n".$Msg;
		break;
	case 'email':
		$View->ConfHelpMsg= _HELPWINDOW('The web filter can report incidents via e-mails. You can setup which incidents to be reported based on violation types and thresholds. Do not forget the single quotes around e-mail addresses and strings.')."\n\n".$Msg;
		break;
	case 'ssl':
		$View->ConfHelpMsg= _HELPWINDOW('The web filter can decrypt SSL content, scan as if plain, and then re-encrypt.')."\n\n".$Msg;
		break;
}

$ViewConfigName= $Submenu.'Config';
$View->Config= ${$ViewConfigName};
/// conf.php included can print DG group change form.
/// Default is FALSE.
$PRINT_CONFOPT_FORM= TRUE;
require_once('../lib/conf.php');
?>
