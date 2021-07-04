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
 * Includes, defines, and functions used in the Model.
 */

$ROOT= dirname(dirname(dirname(__FILE__)));
$SRC_ROOT= dirname(dirname(__FILE__));

require_once($SRC_ROOT . '/lib/defs.php');
require_once($SRC_ROOT . '/lib/setup.php');
require_once($SRC_ROOT . '/lib/lib.php');

require_once($MODEL_PATH.'/validate.php');

require_once($MODEL_PATH.'/lib/RuleSet.php');
require_once($MODEL_PATH.'/lib/Rule.php');
require_once($MODEL_PATH.'/lib/Timeout.php');
require_once($MODEL_PATH.'/lib/State.php');
require_once($MODEL_PATH.'/lib/FilterBase.php');
require_once($MODEL_PATH.'/lib/Filter.php');
require_once($MODEL_PATH.'/lib/Antispoof.php');
require_once($MODEL_PATH.'/lib/Anchor.php');
require_once($MODEL_PATH.'/lib/NatBase.php');
require_once($MODEL_PATH.'/lib/NatTo.php');
require_once($MODEL_PATH.'/lib/BinatTo.php');
require_once($MODEL_PATH.'/lib/RdrTo.php');
require_once($MODEL_PATH.'/lib/AfTo.php');
require_once($MODEL_PATH.'/lib/DivertTo.php');
require_once($MODEL_PATH.'/lib/DivertPacket.php');
require_once($MODEL_PATH.'/lib/Route.php');
require_once($MODEL_PATH.'/lib/Macro.php');
require_once($MODEL_PATH.'/lib/Table.php');
require_once($MODEL_PATH.'/lib/Queue.php');
require_once($MODEL_PATH.'/lib/Scrub.php');
require_once($MODEL_PATH.'/lib/Option.php');
require_once($MODEL_PATH.'/lib/Limit.php');
require_once($MODEL_PATH.'/lib/LoadAnchor.php');
require_once($MODEL_PATH.'/lib/Include.php');
require_once($MODEL_PATH.'/lib/Comment.php');
require_once($MODEL_PATH.'/lib/Blank.php');

/**
 * Shell command argument types.
 *
 * @attention PHP is not compiled, otherwise would use bindec().
 * 
 * @warning Do not use bitwise shift operator either, would mean 100+ shifts for constant values!
 */
define('NONE',			1);
define('FILEPATH',		2);
define('NAME',			4);
define('NUM',			8);
define('SHA1STR',		16);
define('BOOL',			32);
define('SAVEFILEPATH',	64);
define('JSON',			128);
define('EMPTYSTR',		256);
define('REGEXP',		512);
define('SERIALARRAY',	1024);
define('IPADRLIST',		2048);
define('STR',			4096);
define('IPADR',			8192);
define('HOST',			16384);
define('URL',			32768);
define('EMAIL',			65536);
define('DATETIME',		131072);
define('IPRANGE',		262144);
define('TAIL',			524288);
define('ASTERISK',		1048576);
define('CONFNAME', 		2097152);
define('AFTERHOURS',	4194304);
define('EXT',	 		8388608);
define('MIME',	 		16777216);
define('IPPORT',		33554432);
define('DGIPRANGE', 	67108864);
define('DGSUBCAT',		134217728);

$Output= '';
$Error= '';

/**
 * Sets or updates $Output with the given message.
 *
 * Output strings are accumulated in global $Output var and returned to View.
 * 
 * $msg may be the output of another function call. So, we first check
 * to see if it is FALSE, meaning the function had failed or not.
 * 
 * @attention Note that we never send bool messages.
 * 
 * @param string $msg Output message.
 * @return bool TRUE on success, FALSE on fail.
 */
function Output($msg)
{
	global $Output;

	if ($msg !== FALSE) {
		if ($Output === '') {
			$Output= $msg;
		}
		else if ($msg !== '') {
			$Output.= "\n".$msg;
		}
		return TRUE;
	}
	return FALSE;
}

/**
 * Sets or updates $Error with the given message.
 *
 * Error strings are accumulated in global $Error var and returned to View.
 * 
 * @param string $msg Error message.
 */
function Error($msg)
{
	global $Error;

	if ($Error === '') {
		$Error= $msg;
	}
	else if ($msg !== '') {
		$Error.= "\n".$msg;
	}
}

function convertBinary($value)
{
	$g= round($value / 1073741824);
	if ($g) {
		return $g . 'G';
	}

	$m= round($value / 1048576);
	if ($m) {
		return $m . 'M';
	}

	$k= round($value / 1024);
	if ($k) {
		return $k . 'K';
	}

	return $value;
}

function convertDecimal($value)
{
	$g= round($value / 1000000000);
	if ($g) {
		return $g . 'G';
	}

	$m= round($value / 1000000);
	if ($m) {
		return $m . 'M';
	}

	$k= round($value / 1000);
	if ($k) {
		return $k . 'K';
	}

	return $value;
}

/**
 * Wrapper for controller error logging via syslog.
 *
 * A global $LOG_LEVEL is set in setup.php.
 *
 * @param int $prio	Log priority checked against $LOG_LEVEL
 * @param string $file Source file the function is in
 * @param string $func Function where the log is taken
 * @param int $line	Line number within the function
 * @param string $msg Log message
 */
function ctlr_syslog($prio, $file, $func, $line, $msg)
{
	global $LOG_LEVEL, $LOG_PRIOS;

	$msg= trim($msg);
	try {
		openlog('ctlr', LOG_PID, LOG_LOCAL0);
		
		if ($prio <= $LOG_LEVEL) {
			$func= $func == '' ? 'NA' : $func;
			$log= "$LOG_PRIOS[$prio] $file: $func ($line): $msg";
			if (!syslog($prio, $log)) {
				$log.= "\n";
				if (!fwrite(STDERR, $log)) {
					echo $log;
				}
			}
		}
		closelog();
	}
	catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
		echo "ctlr_syslog() failed: $prio, $file, $func, $line, $msg\n";
		// No need to closelog(), it is optional
	}
}
?>
