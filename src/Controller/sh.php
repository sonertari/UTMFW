#!/usr/bin/env php
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

/** @file
 * Login shell for users.
 *
 * We set the login shells of admin and user users to sh.php.
 *
 * Using a shell script to pass args to the Controller commands would expand
 * those args, hence could cause security issues.
 *
 * Now instead we make sure the args are never expanded and the users cannot
 * drop to a command shell:
 * - use sh.php as login shell
 * - pass all args to it as an ssh command (the -c option of phpseclib channel
 *   exec), without any shell expansion
 * - validate all args within sh.php
 * - convert them to a string enclosed between single quotes, so no expansion
 *   again
 * - exec ctlr.php passing the args string to it
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

$retval= 1;

// Set $args before expanding and validating args, before $ArgV is modified
$args= implode(' ', $ArgV);

if (!ExpandArgs($ArgV, $Locale, $View, $Command)) {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed expanding args: '.print_r($argv, TRUE));
	goto out;
}

if (!array_key_exists($View, $ModelFiles)) {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "View not in ModelFiles: $View");
	goto out;
}

// The vars in the model file must be global, so include it here
require_once($MODEL_PATH . '/' . $ModelFiles[$View]);

if (!ValidateCommand($ArgV, $Locale, $View, $Command, TRUE, $Model)) {
	$ErrorStr= print_r($argv, TRUE);
	Error(_('Failed validating command line')." $ErrorStr");
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed validating command line: $ErrorStr");
	goto out;
}

$cwd= dirname(__FILE__);

// Run the ctlr using doas and passing -n for no arg validation, as we have already done that above
// Remove errout (redirect to /dev/null), otherwise breaks json encoded output
exec("/usr/bin/doas $cwd/ctlr.php -n ".escapeshellarg($args)." 2>/dev/null", $encoded, $retval);
// There must be only one element in $encoded array, but do not miss the others if any
$encoded= implode(' ', $encoded);
echo $encoded;
exit($retval);

out:
$msg= array($Output, $Error, $retval);
$encoded= json_encode($msg, JSON_UNESCAPED_SLASHES);

if ($encoded !== NULL) {
	echo $encoded;
} else {
	ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed encoding output, error, and retval: '.print_r($msg, TRUE));
}
exit($retval);
?>
