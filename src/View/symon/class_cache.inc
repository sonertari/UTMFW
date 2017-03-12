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
 * This class provides a filesystem cache. Cache entries can be requested and
 * are dated at the time of the request.
 *
 * The current implementation gets the date once at class creation. All files
 * in the cache are evaluated on class creation and are deleted if eligable.
 *
 * $symon['cache_keepstale'] : set to disable file deletion.
 *
 * Typical use:
 *
 * $key = $cache->insert(content, <content_identifiers>+);
 * $filename = $cache->getfilename($key);
 * $cache->expire($key);
 *
 * Use obtain_file_cache(key) to get a direct filename to a cache entry with
 * key <key>.
 */
require_once("setup.inc");
require_once("tools.inc");

class Cache {
    var $db;
    var $timestamp;

    function Cache() {
	global $symon;

	if (!isset($symon["cache_duration"])) {
	    config_error("cache_duration", "is not set");
	    return 0;
	}
	if (!isset($symon["cache_dir"])) {
	    config_error("cache_dir", "is not set");
	    return 0;
	}
	if (!is_dir($symon["cache_dir"])) {
	    config_error("cache_dir", "does not point to a directory");
	    return 0;
	}

	return $this->_load();
    }

    function _load() {
	global $symon;

	$ts = time();
	$this->timestamp = $ts - ($ts % $symon["cache_duration"]);

	$dir = dir($symon["cache_dir"]);
	while ($file = $dir->read()) {
	    if ($file == '.' || $file == '..') {
		continue;
	    }
	    if (!is_file($symon["cache_dir"].'/'.$file)) {
		runtime_error("'".$file."' is not a cache file");
		return 0;
	    }

	    $this->_cache($file);
	}
    }

    function _cache($filename) {
	global $symon;

	if (preg_match("/^([0-9]+)_([0-9a-f]+)(\.[a-z]+)?$/i",
		       $filename, $match)) {
	    $this->db[$match[2]] = $filename;
	    if (($this->timestamp > $match[1]) &&
		(!isset($symon['cache_keepstale']))) {
		$this->expire_key($match[2]);
	    }
	} else {
	    runtime_error("'".$filename."' is not a cache file");
	    return 0;
	}
    }

    function _iscached($filename) {
	if (preg_match("/^([0-9]+)_([0-9a-f]+)(\.[a-z]+)?$/i",
		       $filename, $match)) {
	    return isset($this->db[$match[2]]);
	} else {
	    if (preg_match("/^([0-9a-f]+)(\.[a-z]+)?$/i",
			   $filename, $match)) {
		return isset($this->db[$match[1]]);
	    } else {
		return isset($this->db[$filename]);
	    }
	}
    }

    function _get($filename) {
	if (preg_match("/^([0-9]+)_([0-9a-f]+)(\.[a-z]+)?$/i",
		       $filename, $match)) {
	    if (isset($this->db[$match[2]])) {
		return $this->db[$match[2]];
	    }
	} else {
	    if (preg_match("/^([0-9a-f]+)(\.[a-z]+)?$/i",
			   $filename, $match)) {
		if (isset($this->db[$match[1]])) {
		    return $this->db[$match[1]];
		}
	    } else {
		if (isset($this->db[$filename])) {
		    return $this->db[$filename];
		}
	    }
	}

	return undef;
    }

    function insert(&$content, $extension = '') { /* variadric */
	$args = '';
	$maxargs = func_num_args();
	$arg_list = func_get_args();
	for ($i = 0; $i < $maxargs; $i++) {
	    if (is_array($arg_list[$i])) {
		$args .= join($arg_list[$i]);
	    } elseif (is_scalar($arg_list[$i])) {
		$args .= $arg_list[$i];
	    } else {
		runtime_error('cache: wrong type of argument '.gettype($arg_list[$i]) .' in "insert"');
	    }
	}

	$key = md5($args);

	if (!$this->_iscached($key)) {
	    $this->_cache($this->timestamp . '_' . $key . $extension);
	    $filename = $this->getfilename($key);
	    if (strlen($content) > 0) {
		save($filename, $content);
	    }
	}

	return $key;
    }

    function obtain_filecache($key) {
	if (!$this->_iscached($key)) {
	    $this->_cache($this->timestamp . '_' . $key);
	}

	return $this->getfilename($key);
    }

    function getfilename($key) {
	global $symon;

	if ($this->_iscached($key)) {
	    $filename = $this->_get($key);
	    return $symon["cache_dir"].'/'.$filename;
	} else {
	    return '';
	}
    }

    function expire_key($key) {
	global $runtime_args;

	$filename = $this->getfilename($key);
	if (is_file($filename)) {
	    if (!unlink($filename)) {
		runtime_error("'".$filename."' cannot be deleted");
	    }
	}

	unset($this->db[$key]);
    }

    function _expire_all() {
	if (is_array($this->db)) {
	    foreach($this->db as $key => $value) {
		$this->expire_key($key);
	    }
	}
    }

    function _display() {
	if (is_array($this->db)) {
	    foreach($this->db as $key => $value) {
		print "\xa ".$key.'='.$value;
	    }
	}
    }

    function _test() {
	$r1 = 'this is recipe 1';
	$r2 = 'this is recipe 2';

	print "\xa display current cache";
	$this->_display();

	print "\xa flushing cache";
	$this->_expire_all();

	print "\xa insert tests 1 2 3";
	$c1 = $this->insert($r1);
	$c2 = $this->insert($r1, '.txt');
	$c3 = $this->insert($r2);

	print "\xa display current cache\xa";
	$this->_display();
	print "\xa flushing cache";
	$this->_expire_all();
    }
}

if (!isset($cache)) {
    $cache = new Cache();
}
?>
