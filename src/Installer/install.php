#!/usr/bin/env php
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

/** @file
 * Configuration script used both during installation and by the web interface.
 */

/// This is a command line tool, should never be requested on the web interface.
if (isset($_SERVER['SERVER_ADDR'])) {
	header('Location: /index.php');
	exit(1);
}

// Report only errors during installation
error_reporting(E_ERROR);

// chdir is for libraries
chdir(dirname(__FILE__));

$ROOT= dirname(dirname(__FILE__));
$VIEW_PATH= $ROOT.'/View/';

require_once($ROOT.'/lib/setup.php');
/// Log all during installation.
$LOG_LEVEL= LOG_DEBUG;
require_once($ROOT.'/lib/defs.php');
require_once($SRC_ROOT.'/lib/lib.php');

require_once($VIEW_PATH.'/lib/libauth.php');

$Auto= FALSE;
$FirstBoot= FALSE;
if ($_SERVER['argv'][1]) {
	if ($_SERVER['argv'][1] == '-a') {
		$Auto= TRUE;
	}
	else if ($_SERVER['argv'][1] == '-f') {
		$Auto= TRUE;
		$FirstBoot= TRUE;
	}
}

require_once('lib.php');

require_once($VIEW_PATH.'/lib/view.php');
$View= new View();
$View->Model= 'system';

/// @attention This script is executed on the command line, so we don't have access to cookie and session vars here.
// Do not use SSH to run Controller commands
$UseSSH= FALSE;

if ($View->Controller($Output, 'GetConfig')) {
	$Config= json_decode($Output[0], TRUE);

	if (InitIfs()) {
		if (!$Auto) {
			GetIfSelection();
			CreateUsers();
		}
		
		if (ApplyConfig($Auto)) {
			$msg= 'Successfully configured the system';
			echo $msg.".\n";
			wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, $msg);
			
			if ($FirstBoot) {
				FirstBootTasks();
			}
			exit(0);
		}
		else {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed applying configuration');
		}
	}
}
else {
	wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot get configuration');
}

wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Configuration failed');
exit(1);
?>
