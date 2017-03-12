<?php
/*
 * Copyright (c) 2003 Willem Dijkstra
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    - Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    - Redistributions in binary form must reproduce the above
 *      copyright notice, this list of conditions and the following
 *      disclaimer in the documentation and/or other materials provided
 *      with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDERS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

require_once("setup.inc");

function hash2cmdline(&$args) {
    reset($args);
    $cmdline = '';

    foreach($args as $key => $value) {
	$cmdline .= " ".escapeshellarg($value);
    }

    return $cmdline;
}

function get_extension($filename) {
    preg_match("/\.([^\.]+)$/", $filename, $match);
    if (isset($match[1])) {
	return $match[1];
    } else {
	return '';
    }
}

function normalise_filename($filename) {
    /* remove basename and trailing extension from filename */
    $filename = basename($filename);
    if (preg_match("/^([^\.]+)/", $filename, $match)) {
	$filename = $match[1];
    }
    /* remove all but lowercase and digits */
    $filename = preg_replace("/[^a-z0-9_]/", '', $filename);

    return $filename;
}

function save($filename, &$data) {
    $fh = fopen($filename, 'w');

    if (!$fh) {
	runtime_error('tools: cannot open file "'.$filename.'" for writing');
	return 0;
    }

    if (is_array($data)) {
	$mydata = join("\xa", $data);
    } else {
	$mydata = $data;
    }

    $result = fwrite($fh, $mydata);

    fflush($fh);
    fclose($fh);

    return $result;
}

function load($filename) {
    global $symon;

    $fh = fopen($filename, 'r');

    if (!$fh) {
	runtime_error('tools: cannot open file "'.$filename.'" for reading');
	return 0;
    }

    $data = fread($fh, $symon['loadbuffer']);

    fclose($fh);

    return $data;
}

function config_error($item, $problem) {
    global $symon;
    print("\xa\xa".'<b>check setup.inc: $symon["'.$item.'"]="'.
	$symon[$item].'" '.$problem);
    exit(1);
}

function runtime_error($problem) {
    print("\xa\xa<b>".$problem.'</b>');
    error_log($problem);
    exit(1);
}

function warning($problem) {
    print("\xa\xa<b>".$problem.'</b>');
    error_log($problem);
}
?>
