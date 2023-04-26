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

require_once('../lib/vars.php');

$Menu = array(
	'info' => array(
		'Name' => _MENU('Info'),
		'Perms' => $ALL_USERS,
		),
	'stats' => array(
		'Model' => 'e2guardianlogs',
		'Name' => _MENU('Statistics'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'general' => _MENU('General'),
			'daily' => _MENU('Daily'),
			'hourly' => _MENU('Hourly'),
			'live' => _MENU('Live'),
			),
		),
	'graphs' => array(
		'Name' => _MENU('Graphs'),
		'Perms' => $ALL_USERS,
		),
	'logs' => array(
		'Name' => _MENU('Logs'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'archives' => _MENU('Archives'),
			'live' => _MENU('Live'),
			),
		),
	'accesslogs' => array(
		'Model' => 'e2guardianlogs',
		'Name' => _MENU('Access Logs'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'archives' => _MENU('Archives'),
			'live' => _MENU('Live'),
			),
		),
	'general' => array(
		'Name' => _MENU('General'),
		'Perms' => $ADMIN,
		'SubMenu' => array(
			'basic' => _MENU('Basic'),
			'filter' => _MENU('Filter'),
			'scan' => _MENU('Scan'),
			'logs' => _MENU('Logs'),
			'downloads' => _MENU('Downloads'),
			'advanced' => _MENU('Advanced'),
			),
		),
	'conf' => array(
		'Name' => _MENU('Groups'),
		'Perms' => $ADMIN,
		'SubMenu' => array(
			'groups' => _MENU('General'),
			'basic' => _MENU('Basic'),
			'scan' => _MENU('Scan'),
			'bypass' => _MENU('Bypass'),
			// Disable email for now
			//'email' => _MENU('Email'),
			),
		),
	'lists' => array(
		'Name' => _MENU('Lists'),
		'Perms' => $ADMIN,
		'SubMenu' => array(
			'sites' => _MENU('Sites'),
			'urls' => _MENU('URLs'),
			'exts' => _MENU('Extensions'),
			'mimes' => _MENU('Mimes'),
			'dm_exts' => _MENU('DM Exts'),
			'dm_mimes' => _MENU('DM Mimes'),
			'virus_sites' => _MENU('Virus Sites'),
			'virus_urls' => _MENU('Virus URLs'),
			'virus_exts' => _MENU('Virus Exts'),
			'virus_mimes' => _MENU('Virus Mimes'),
			),
		),
	'cats' => array(
		'Name' => _MENU('Categories'),
		'Perms' => $ADMIN,
		'SubMenu' => array(
			'sites' => _MENU('Domains'),
			'urls' => _MENU('URLs'),
			'phrases' => _MENU('Phrases'),
			'blacklists' => _MENU('Blacklists'),
			),
		),
	);
?>
