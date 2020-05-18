#!/usr/bin/env php
<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
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

// -c is added when phpseclib channel exec() is used, discard it
if ($ArgV[0] === '-c') {
	$ArgV= array_slice($ArgV, 1);
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

// Arg 0 contains all of the args as a json encoded string
if (count($ArgV) !== 1) {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Too many args: '.print_r($ArgV, TRUE));
	goto out;
}

$decoded= json_decode($ArgV[0], TRUE);
if ($decoded !== NULL && is_array($decoded)) {
	$ArgV= $decoded;
} else {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed decoding args: '.print_r($ArgV, TRUE));
	goto out;
}

if (count($ArgV) < 3) {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Not enough args: '.print_r($ArgV, TRUE));
	goto out;
}

// Controller runs using the session locale of View
$Locale= $ArgV[0];
$View= $ArgV[1];
$Command= $ArgV[2];

$ArgV= array_slice($ArgV, 3);

if (array_key_exists($View, $ModelFiles)) {
	require_once($MODEL_PATH . '/' . $ModelFiles[$View]);
} else {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "View not in ModelFiles: $View");
	goto out;
}

if (class_exists($Models[$View])) {
	$Model= new $Models[$View]();
} else {
	require_once($MODEL_PATH.'/model.php');
	$Model= new Model();
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "View not in Models: $View");
	goto out;
}

/// @attention Do not set the locale until after the model file is included and the model is created,
/// otherwise strings recorded into logs are also translated, such as the strings on the Commands array of models.
/// Strings cannot be detranslated.
if (!array_key_exists($Locale, $LOCALES)) {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Locale not in LOCALES: $Locale");
	goto out;
}

putenv('LC_ALL='.$Locale);
putenv('LANG='.$Locale);

$Domain= 'utmfw';
bindtextdomain($Domain, $VIEW_PATH.'/locale');
bind_textdomain_codeset($Domain, $LOCALES[$Locale]['Codeset']);
textdomain($Domain);

if (!method_exists($Model, $Command)) {
	$ErrorStr= "$Models[$View]->$Command()";
	Error(_('Method does not exist').": $ErrorStr");
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Method does not exist: $ErrorStr");
	goto out;
}

if (!array_key_exists($Command, $Model->Commands)) {
	Error(_('Unsupported command').": $Command");
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Unsupported command: $Command");
	goto out;
}

ComputeArgCounts($Model->Commands, $ArgV, $Command, $ActualArgC, $ExpectedArgC, $AcceptableArgC, $ArgCheckC);

if ($ActualArgC < $AcceptableArgC) {
	$ErrorStr= "[$AcceptableArgC]: $ActualArgC";
	Error(_('Not enough args')." $ErrorStr");
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Not enough args $ErrorStr");
	goto out;
}

if ($ActualArgC > $ExpectedArgC) {
	$ErrorStr= "[$ExpectedArgC]: $ActualArgC: ".implode(', ', array_slice($ArgV, $ExpectedArgC));
	Error(_('Too many args')." $ErrorStr");
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Too many args $ErrorStr");
	goto out;
}

// Check only the relevant args
if ($ArgCheckC === 0 || ValidateArgs($Model->Commands, $Command, $ArgV, $ArgCheckC)) {
	if (call_user_func_array(array($Model, $Command), $ArgV)) {
		$retval= 0;
	} else {
		ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Command failed: $Command");
	}
} else {
	Error(_('Not running command').": $Command");
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Not running command: $Command");
}

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
