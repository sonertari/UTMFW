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

/*
 * This class provides a convenient interface to rrdtool.
 */
require_once('setup.inc');
require_once('tools.inc');

class RRDTool {
    var $cmdline;
    var $error;
    var $output;
    var $method;

    function __construct() {
	global $symon;

	$this->method = '';

	if (isset($symon['rrdtool_path'])) {
	    if (!is_executable($symon['rrdtool_path'])) {
		config_error('rrdtool_path', 'is not an executable');
		return 0;
	    }
	} else {
	    config_error('rrdtool_path', 'is not set');
	    return 0;
	}

	return 1;
    }

    function graph($filename, $args) {
	global $symon;

	if (!is_array($args)) {
	    $temp = preg_split("/\n/", $args);
	} else {
	    $temp = $args;
	}

	$args = array();
	reset($temp);
	foreach ($temp as $t) {
	    if ($t == '') continue;
	    if (preg_match("/^\s+$/s", $t)) continue;
	    array_push($args, $t);
	}

	$cmdline = $symon['rrdtool_path'] . ' graph '. $filename .
	    hash2cmdline($args) . ' 2>&1';

	$result = $this->exec($cmdline);

	if (isset($symon['rrdtool_debug'])) {
	    $output = "\xa rrdtool graph debug output \xa cmdline: '$cmdline'\xa errors:'".$this->get_error()."'\xa output:'".$this->get_output()."'";
	    error_log($output);
	}

	return $result;
    }

    function get_error() {
	return $this->error;
    }

    function get_output() {
	return $this->output;
    }

    function _test() {
	global $symon;

	$cmdline = $symon['rrdtool_path'] . ' -v';
	if ($this->exec($cmdline) == 0) {
	    runtime_error('apache or php setup faulty: cannot execute '.$symon['rrdtool_path']);
	    return 0;
	} else {
	    $version_info = $this->get_output();
	    if (strlen($version_info) == 0) {
		runtime_error('apache or php setup faulty: can execute '.$symon['rrdtool_path']. ', but no results are returned');
		return 0;
	    } else {
		print "\xa rrdtool version: ";
		if (preg_match("/rrdtool\s+(\S+)/i", $version_info, $match)) {
		    print $match[1];
		} else {
		    print "unknown";
		}
		return 1;
	    }
	}
    }

    function exec($cmdline) {
	global $runtime;

	$this->error = '';
	$this->output = '';
	$this->cmdline = $cmdline;

	unset($reta);
	exec($cmdline, $reta, $ret);

	if ($ret != 0) {
	    foreach($reta as $key => $value) {
		$this->error .= '<pre>'.$value.'</pre><br>';
	    }
	    return 0;
	} else {
	    foreach($reta as $value) {
		if ($value != "") {
		    $this->output .= '<p>'.$value.'</p>';
		}
	    }
	    if (strstr($this->output, '<p>ERROR:')) {
		return 0;
	    }
	    return 1;
	}
	return 1;
    }
}
?>
