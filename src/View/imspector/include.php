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

require_once('../lib/vars.php');

$Menu = array(
	'info' => array(
		'Name' => _MENU('Info'),
		'Perms' => $ALL_USERS,
		),
	'graphs' => array(
		'Name' => _MENU('Graphs'),
		'Perms' => $ALL_USERS,
		),
	'imlogs' => array(
		'Name' => _MENU('IM Logs'),
		'Perms' => $ALL_USERS,
		),
	'logs' => array(
		'Name' => _MENU('IM Proxy Logs'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'archives' => _MENU('Archives'),
			'live' => _MENU('Live'),
			),
		),
	'conf' => array(
		'Name' => _MENU('Config'),
		'Perms' => $ADMIN,
		'SubMenu' => array(
			'basic' => _MENU('Basic'),
			'acl' => _MENU('ACL'),
			'badwords' => _MENU('Badwords'),
			),
		),
	);
?>
