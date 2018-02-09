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

require_once('snort.php');

$Submenu= SetSubmenu('basic');

switch ($Submenu) {
	case 'basic':
		$View->ConfHelpMsg= _HELPWINDOW('You should configure the IDS based on the services running on your network.');
		break;

	case 'advanced':
		break;

	case 'rules':
		require_once('conf.rules.php');
		exit;
}

$ViewConfigName= $Submenu.'Config';
$View->Config= ${$ViewConfigName};
$PRINT_CONFOPT_FORM= TRUE;
require_once('../lib/conf.php');
?>
