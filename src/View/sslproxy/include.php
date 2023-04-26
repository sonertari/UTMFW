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

require_once($SRC_ROOT.'/lib/defs.php');
require_once($SRC_ROOT.'/lib/setup.php');
require_once($SRC_ROOT.'/lib/lib.php');

require_once($VIEW_PATH.'/lib/setup.php');

$SSLPROXY_PATH= $VIEW_PATH.'/sslproxy';

require_once($SSLPROXY_PATH.'/lib/RuleSet.php');
require_once($SSLPROXY_PATH.'/lib/Rule.php');
require_once($SSLPROXY_PATH.'/lib/RuleStruct.php');
require_once($SSLPROXY_PATH.'/lib/ProxySpecLine.php');
require_once($SSLPROXY_PATH.'/lib/ProxySpecStruct.php');
require_once($SSLPROXY_PATH.'/lib/Filter.php');
require_once($SSLPROXY_PATH.'/lib/FilterStruct.php');
require_once($SSLPROXY_PATH.'/lib/Macro.php');
require_once($SSLPROXY_PATH.'/lib/Option.php');
require_once($SSLPROXY_PATH.'/lib/Include.php');
require_once($SSLPROXY_PATH.'/lib/Comment.php');
require_once($SSLPROXY_PATH.'/lib/Blank.php');

$Menu = array(
	'info' => array(
		'Name' => _MENU('Info'),
		'Perms' => $ALL_USERS,
		),
	'stats' => array(
		'Name' => _MENU('Statistics'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'general' => _MENU('General'),
			'daily' => _MENU('Daily'),
			'hourly' => _MENU('Hourly'),
			'live' => _MENU('Live'),
			),
		),
	'connstats' => array(
		'Model' => 'sslproxyconns',
		'Name' => _MENU('Conn Statistics'),
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
	'conns' => array(
		'Model' => 'sslproxyconns',
		'Name' => _MENU('Conns'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'archives' => _MENU('Archives'),
			'live' => _MENU('Live'),
			),
		),
	'logs' => array(
		'Name' => _MENU('Logs'),
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
			'editor' => _MENU('Editor'),
			'write' => _MENU('Display & Install'),
			'files' => _MENU('Load & Save'),
			),
		),
	);
?>
