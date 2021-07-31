#!/usr/bin/env php
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
 * Proxy to run all shell commands.
 * 
 * This way we have only one entry in doas.conf.
 * 
 * @todo Continually check for security issues.
 */

/// @todo Is there a better way?
$ROOT= dirname(dirname(dirname(__FILE__)));
$SRC_ROOT= dirname(dirname(__FILE__));

require_once($SRC_ROOT . '/lib/defs.php');
require_once($SRC_ROOT . '/lib/setup.php');

// chdir is for PCRE, libraries
chdir(dirname(__FILE__));

// Include constant definitions here, otherwise command arg validation fails
require_once($MODEL_PATH.'/include.php');

require_once('lib.php');

/// This is a command line tool, should never be requested on the web interface.
if (filter_has_var(INPUT_SERVER, 'SERVER_ADDR')) {
	/// @attention ctlr_syslog() is in the Model, use after including model
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Requested on the wui, exiting...');
	header('Location: /index.php');
	exit;
}

$ArgV= array_slice($argv, 1);

$ValidateArgs= TRUE;
if ($ArgV[0] === '-n') {
	$ArgV= array_slice($ArgV, 1);
	$ValidateArgs= FALSE;
}

if ($ArgV[0] === '-t') {
	$ArgV= array_slice($ArgV, 1);

	$TEST_ROOT= dirname(dirname(dirname(__FILE__)));
	$TEST_DIR= '/tests/phpunit/root';
	$TEST_DIR_PATH= $TEST_ROOT . $TEST_DIR;
	$TEST_DIR_SRC= $TEST_DIR . '/var/www/htdocs/utmfw';

	$INSTALL_USER= posix_getpwuid(posix_getuid())['name'];
}

$retval= 1;

if (!ExpandArgs($ArgV, $Locale, $View, $Command)) {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed expanding args: '.print_r($argv, TRUE));
	goto out;
}

if (!array_key_exists($View, $ModelFiles)) {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "View not in ModelFiles: $View");
	goto out;
}

// Include the model file here to make the vars in the model file global
require_once($MODEL_PATH . '/' . $ModelFiles[$View]);

if (!ValidateCommand($ArgV, $Locale, $View, $Command, $ValidateArgs, $Model)) {
	$ErrorStr= print_r($argv, TRUE);
	Error(_('Failed validating command line')." $ErrorStr");
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed validating command line: $ErrorStr");
	goto out;
}

if (!call_user_func_array(array($Model, $Command), $ArgV)) {
	ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Command failed: $Command");
	goto out;
}

$retval= 0;

out:
/// @attention Always return errors, success or fail.
/// @attention We need to include $retval in the array too, because phpseclib exec() does not provide access to retval.
// Return an encoded array, so that the caller can easily separate output, error, and retval
$msg= array($Output, $Error, $retval);
/// @attention If json_encode() inserts slashes, it is hard to decode the base64 encoded graph string at the receiving end
$encoded= json_encode($msg, JSON_UNESCAPED_SLASHES);

if ($encoded !== NULL) {
	echo $encoded;
} else {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed encoding output, error, and retval: '.print_r($msg, TRUE));
}

exit($retval);
?>
