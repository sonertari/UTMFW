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
 * Category configuration.
 */

require_once('e2guardian.php');

$Submenu= SetSubmenu('sites');

switch ($Submenu) {
	case 'sites':
		$View->ConfHelpMsg= _HELPWINDOW('UTMFW Web Filter uses site lists categorized in different packages for your convenience. You can enable or disable categories you want on this page.');
		break;
	case 'urls':
		$View->ConfHelpMsg= _HELPWINDOW('UTMFW Web Filter uses URL lists categorized in different packages for your convenience. You can enable or disable categories you want on this page.');
		break;
	case 'phrases':
		$View->ConfHelpMsg= _HELPWINDOW('UTMFW Web Filter uses phrase lists categorized in different packages for your convenience. You can enable or disable categories you want on this page.');
		break;
	case 'blacklists':
		require_once('blacklists.php');
		exit;
}
require_once('include.cats.php');
?>
