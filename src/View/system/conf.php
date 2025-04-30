<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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

$Submenu= SetSubmenu('basic');

switch ($Submenu) {
	case 'basic':
		require_once('conf.basic.php');
		break;

	case 'net':
		require_once('conf.net.php');
		break;

	case 'init':
		require_once('conf.init.php');
		break;

	case 'startup':
		require_once('conf.startup.php');
		break;

	case 'logs':
		require_once('conf.logs.php');
		break;

	case 'wui':
		require_once('conf.wui.php');
		break;

	case 'users':
		require_once('conf.users.php');
		break;

	case 'notifier':
		require_once('conf.notifier.php');
		break;
}
?>
