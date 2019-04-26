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
 * Vars class
 * - provide a repository of user variables
 * - read and write the repository to a single line string
 * - merge two repositories
 */
require_once("class_lexer.inc");
require_once("tools.inc");

$chr2html = array(
    "\xa" => '&#10;',
    '|' => '&#124;',
    '=' => '&#61;',
    '&' => '&#38;',
    ';' => '&#59;');
$html2chr = array_flip($chr2html);

class Vars {
    var $vars;

    function __construct($frozen="") {
	unset($this->vars);
	if ($frozen != "") {
	    $this->inject($frozen);
	} else {
	    $this->vars = array();
	}
    }

    function tostring() {
	global $chr2html;

	reset($this->vars);
	$k= key($this->vars);
	$v= current($this->vars);
	$r = strtr($k, $chr2html).'="'.
		strtr($v, $chr2html).'"';

	foreach ($this->vars as $k => $v) {
	    $r .= ', '.strtr($k, $chr2html).'="'.
		strtr($v, $chr2html).'"';
	}
	return $r;
    }

    function merge($a, $b) {
	if (isset($a->vars)) {
	    if (isset($b->vars)) {
		$this->vars = array_merge($a->vars, $b->vars);
	    } else {
		$this->vars = $a->vars;
	    }
	} else {
	    $this->vars = $b->vars;
	}
    }

    function addvars($a) {
	if (isset($a->vars)) {
	    $b = $this->vars;
	    $this->vars = array_merge($b, $a->vars);
	}
    }

    function def($k, $v) {
	if (isset($this->vars[$k])) {
	    return;
	} else {
	    $this->vars[$k] = $v;
	}
    }

    function defp($k) {
	return isset($this->vars[$k]);
    }

    function get($n) {
	return $this->vars[$n];
    }

    function parse(&$lexer) {
	$token = '';
	$done_parsing = 0;

	while (!$done_parsing) {
	    $name = $lexer->next_token();

	    $equals = $lexer->next_token();
	    if ($equals != '=') {
		$lexer->parse_error('Expecting name=value');
	    }

	    $value = $lexer->next_token();
	    $this->set($name, $value);

	    $token = $lexer->next_token();

	    if ($token != ',') {
		$lexer->unget($token);
		$done_parsing = 1;
	    }
	}
    }

    function set($n, $v) {
	$this->vars[$n] = $v;
    }

    function _display($name="vars") {
	if (is_array($this->vars)) {
	    foreach ($this->vars as $key => $value) {
		print "\xa  ".$key.'='.$value;
	    }
	}
    }
}
?>
