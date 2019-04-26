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

require_once('class_vars.inc');
require_once('tools.inc');
require_once('setup.inc');

class Lexer {
    var $filename;
    var $lineno;
    var $lineitems;
    var $line;
    var $buffer;
    var $token;
    var $unget;
    var $keywordchrs;

    function __construct() {
	$this->filename = '';
	$this->lineno = 0;
	$this->buffer = array();
	$this->line = array();
	$this->eof = 0;
	$this->token = '';
	$this->unget = array();
	$this->keywordchrs = '/ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.';
    }

    function is_eof() {
	return $this->eof;
    }

    function load($filename = '') {
	global $symon;

	if ($filename == '') {
	    runtime_error('layout parser: no file to load');
	}
	$filename = normalise_filename($filename);
	$filename = $symon['layout_dir'].'/'.normalise_filename($filename).'.layout';
	$this->filename = $filename;
	$buffer = load($filename);
	$this->buffer = preg_split("/\n/", $buffer);
    }

    function next_token() {
	global $symon;

	$token = $this->_next_token();

	if (isset($symon['lexer_debug'])) {
	    $this->_display();
	}

	return $token;
    }

    function parse_error($errorstring, $lineno = 0) {
	if ($lineno == 0) {
	    $lineno = $this->lineno;
	}

	runtime_error("parse error: $this->filename : $lineno : '$this->token' : $errorstring");
    }

    function parse_semicolon() {
	if ($this->eof) {
	    $this->parse_error('Expected ;');
	}
	$token = $this->next_token();
	if ($token != ';') {
	    $this->parse_error('Expected ;');
	}
    }

    function unget($token) {
	$items = preg_split('//', $token, -1, PREG_SPLIT_NO_EMPTY);
	foreach ($items as $k => $item) {
	    $this->_unget_chr($item);
	}
    }

    function _get_chr() {
	$item = $this->_get_chr_and_nl();
	if ($item == "\n") {
	    $item = ' ';
	}
	return $item;
    }

    function _get_chr_and_nl() {
	if ($this->eof) {
	    runtime_error('internal: trying to get_chr while eof has been signalled');
	}
	if (count($this->unget)) {
	    return array_shift($this->unget);
	}
	$item = '';
	while ($item == '') {
	    if (!is_array($this->lineitems) || count($this->lineitems) == 0) {
		$this->_get_next_line();
		if ($this->eof) {
		    return;
		}
		return "\n";
	    }
	    $item = array_shift($this->lineitems);
	}
	return $item;
    }

    function _get_next_line() {
	if ($this->eof) {
	    runtime_error('internal: trying to get_next_line while eof has been signalled');
	}
	if (count($this->buffer) == 0) {
	    $this->eof = 1;
	    return;
	}
	$this->line = array_shift($this->buffer);
	$this->lineno += 1;
	$this->lineitems = preg_split('//', $this->line, -1, PREG_SPLIT_NO_EMPTY);
    }

    function _next_token() {
	if ($this->eof) {
	    runtime_error('internal: trying next_token while eof has been signalled');
	}

	$this->token = '';
	$ch = $this->_get_chr();

	while ($this->eof || !(strpos(" \t\r\n#", $ch) === FALSE)) {
	    if ($this->eof) {
		return;
	    }
	    if ($ch == '#') {
		$this->_get_next_line();
	    }
	    $ch = $this->_get_chr();
	}

	if ($this->eof) {
	    $this->token = '';
	    return $this->token;
	}

	if ($ch == '"') {
	    $this->token = $this->_get_quoted_string('"');
	    return $this->token;
	}
	if ($ch == "'") {
	    $this->token = $this->_get_quoted_string("'");
	    return $this->token;
	}
	if (!(strpos($this->keywordchrs, $ch) === FALSE)) {
	    $this->token = '';
	    while (!(strpos($this->keywordchrs, $ch) === FALSE) || $this->eof) {
		$this->token .= $ch;
		$ch = $this->_get_chr();
	    }
	    if (!$this->eof) {
		$this->_unget_chr($ch);
	    }
	    return $this->token;
	}
	$this->token = $ch;
	return $this->token;
    }

    function _get_quoted_string($quote) {

	$start_lineno = $this->lineno;
	$this->token = '';
	$delimited = 0;

	$ch = $this->_get_chr_and_nl();
	while ($ch != $quote) {
	    if ($this->eof) {
		$this->parse_error("starting $quote without closing $quote", $start_lineno);
		return;
	    }
	    if ($ch == "\\" && $quote == '"') {
		$ch = $this->_get_chr_and_nl();
	    }
	    $this->token .= $ch;
	    $ch = $this->_get_chr_and_nl();
	}
	return $this->token;
    }

    function _unget_chr($ch) {
	$this->unget[] = $ch;
    }

    function _display() {
	print "<pre>\xa -- token = $this->token";
	print "\xa lexing $this->filename:$this->lineno";
	print "\xa line = $this->line";
	print "\xa items = ";
	if (count($this->lineitems)) {
	    foreach ($this->lineitems as $k => $item) {
		print $item. '|';
	    }
	}
	print "\xa unget = ";
	if (count($this->unget) > 0) {
	    foreach ($this->unget as $item) {
		print $item. '|';
	    }
	}
	print "\xa eof = $this->eof </pre>";
    }

    function _test() {
	global $symon;

	$this->load('hifn_test.layout');
	while(!$this->eof) {
	    $token = $this->next_token();
	}
    }
}
?>
