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
 * Project-wide common functions.
 */

/**
 * Validates the given file path.
 * 
 * If we are not testing the controller, we should expect regular file paths.
 * For example, _Include or LoadAnchor type of rules accept regular paths, not test ones.
 * 
 * @attention ? should never appear in regex patterns, this is better than using / or | chars.
 * 
 * @param string $filepath File path to validate.
 * @return bool TRUE if valid.
 */
function IsFilePath($filepath)
{
	global $PF_CONFIG_PATH, $TMP_PATH, $TEST_DIR_PATH;

	return
		// pf configuration files
		preg_match("?^($TEST_DIR_PATH|)$PF_CONFIG_PATH/\w[\w.\-_]*$?", $filepath)
		|| preg_match("?^($TEST_DIR_PATH|)/etc/\w[\w.\-_]*$?", $filepath)
		// Uploaded tmp files
		|| preg_match("?^($TEST_DIR_PATH|)$TMP_PATH/\w[\w.\-_]*$?", $filepath)
		// Log files
		|| preg_match('?^/var/log/\w[\w./\-_]*$?', $filepath)
		// Statistics and uncompressed logs
		|| preg_match('?^/var/tmp/utmfw/\w[\w.\-_/]*$?', $filepath)
		|| preg_match('|^/var/www/logs/\w[\w./\-_]*$|', $filepath)
		// Messaging logs
		|| preg_match('|^/var/log/imspector/\w[^$`]*$|', $filepath)
		|| preg_match('|^/etc/sslproxy/ca.crt$|', $filepath);
}

/**
 * Converts an array to a simple value.
 * 
 * @attention Don't use 0 as key to fetch the last value; the last key index may not be 0.
 * 
 * @param array $array Array to flatten.
 */
function FlattenArray(&$array)
{
	if (count($array) == 1) {
		$array= $array[key($array)];
	}
}

/**
 * Escapes chars.
 *
 * Prevents double escapes by default.
 *
 * preg_quote() double escapes, thus is not suitable. It is not possible to
 * make sure that strings contain no escapes, because this function is used
 * over strings obtained from config files too, which we don't have any control over.
 *
 * Example: $no_double_escapes as FALSE is used in the code to double escape the $ char.
 *
 * @param string $str String to process.
 * @param string $chars Chars to escape.
 * @param bool $no_double_escapes Whether to prevent double escapes.
 * @return string Escaped string.
 */
function Escape($str, $chars, $no_double_escapes= TRUE)
{
	if ($chars !== '') {
		$chars_array= str_split($chars);
		foreach ($chars_array as $char) {
			$esc_char= preg_quote($char, '/');
			if ($no_double_escapes) {
				/// First remove existing escapes
				$str= preg_replace("/\\\\$esc_char/", $char, $str);
			}
			$str= preg_replace("/$esc_char/", "\\\\$char", $str);
		}
	}
 	return $str;
}
?>
