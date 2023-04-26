<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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

/// @attention SetSubmenu() needs a $View, so create Clamd here.
require_once('clamd.php');

$Submenu= SetSubmenu('clamd');

require_once("$Submenu.php");

if (filter_has_var(INPUT_POST, 'Delete')) {
	/// @attention Mirrors is an array, so filter_input() does not work here.
	foreach ($_POST['Mirrors'] as $Mirror) {
		$View->Controller($Output, 'DelMirror', $Mirror);
	}
}
if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'MirrorToAdd')) {
	$View->Controller($Output, 'AddMirror', filter_input(INPUT_POST, 'MirrorToAdd'));
}

if ($Submenu == 'freshclam') {
	$CustomFunc= 'PrintDatabaseMirrorsForm';
}

require_once('../lib/conf.php');
?>
