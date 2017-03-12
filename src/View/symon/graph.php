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

$symon['cache_keepstale']=1;

require_once("setup.inc");
require_once("class_cache.inc");
require_once("class_rrdtool.inc");
require_once("tools.inc");

function output_picture($filename) {
    global $symon;

    $wait = $symon['graph_max_wait'];

    while (!is_readable($filename) && $wait > 0) {
	exec('/bin/sleep .1');
	$wait -=1;
    }

    if (!is_readable($filename)) {
	runtime_error("cannot read graph file");
    }

    header("content-type: image/gif");
    readfile($filename);
}

/* get request */
if ($_REQUEST) {
    list($k) = array_keys($_REQUEST);
    if (preg_match("/^([0-9a-f]+)/", $k, $match)) {
	$key = $match[1];
	$filename = $cache->getfilename($key);
	$extension = get_extension($filename);
	if ($extension == 'txt') {
	    $definition = load($filename);
	    $cache->expire_key($key);
	    $rrdtool = new RRDTool();
	    $graph_file = $cache->obtain_filecache($key);
	    $result = $rrdtool->graph($graph_file, $definition);
	} else {
	    $graph_file = $filename;
	    $result = 1;
	}

	if ($result == 1) {
	    output_picture($graph_file);
	}
    }
}
?>
