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
 * Session class
 * - provides support for user interaction
 * - unifies all access to information for the user: the cache used for this
 *   session is allocated here.
 * - parses information obtained from forms, holds defaults for this
 *   information if none was supplied by the user
 *
 * Currently this class does not persist user supplied information across
 * multiple requests.
 */
require_once("class_vars.inc");
require_once("tools.inc");

class Session {
    var $vars;

    function Session() {
	$this->vars = new Vars();
    }

    function getvars() {
	return $this->vars;
    }

    function getform($name, $val= '') {
	global $symon, $View, $Submenu;

	if (isset($_SESSION)) {
		if ($Submenu) {
			$session= &$_SESSION[$View->Model]['Graphs'][$Submenu];
		}
		else {
			$session= &$_SESSION[$View->Model]['Graphs'];
		}
	}

	if (isset($_REQUEST[$name])) {
	    $value = $_REQUEST[$name];
		// POSTed values have precedence over session vars
		$session[$name]= $value;
	}

	if (isset($session[$name])) {
		$value= $session[$name];
	}
	
	if ($val != '') {
		$value= $val;
	}

	if (!isset($value)) {
	    $value = $this->getformdefault($name);
	} else {
	    $value = $this->getformconstrained($name, $value);
	}

	// Do not forget to save the new/default value to session var
	$session[$name]= $value;

	/* unroll items if named var selected */
	if ($symon['defaults'][$name]['type'] == 'named') {
	    $s = $symon['defaults'][$name]['namedvalues'][$value];
	    if (is_array($s)) {
		foreach ($s as $k => $v) {
		    $this->vars->set($k, $v);
		}
	    }
	}
	$this->vars->set($name, $value);

	return $value;
    }

    function get($n) {
	if ($this->vars->defp($n)) {
	    return $this->vars->get($n);
	}
    }

    function printoptions($name, $options) {
	foreach($options as $k => $v) {
	    print '    <option ';
	    if ($this->vars->defp($name) &&
		$this->vars->get($name) == $k) {
		print 'selected ';
	    }
	    print 'value="'.$k.'">'.$v."</option>\xa";
	}
    }

    function getformdefault($name) {
	global $symon;

	if (!isset($symon['defaults'][$name]['default'])) {
	    runtime_error('session: unknown variable "'.$name.'" requested');
	}

	$value = $symon['defaults'][$name]['default'];

	return $value;
    }

    function getformconstrained($name, $value) {
	global $symon;

	if (!isset($symon['defaults'][$name])) {
	    runtime_error('session: unknown variable "'.$name.'" requested');
	}

	switch ($symon['defaults'][$name]['type']) {
	case 'named':
	    if (!isset($symon['defaults'][$name]['namedvalues'][$value])) {
		$value = $this->getformdefault($name);
	    }
	    break;
	case 'integer':
	    if ($value < $symon['defaults'][$name]['bounds']['min'] ||
		$value > $symon['defaults'][$name]['bounds']['max']) {
		$value = $this->getformdefault($name);
	    }
	    break;
	default:
	    runtime_error('session: unknown variable type "'.$symon['defaults'][$name]['type'].'" requested');
	    break;
	}

	return $value;
    }

    function purge() {
	$vars = new Vars();
    }

    function _display() {
	$this->vars->_display();
    }

    function _test() {
	$this->getform('end');
	$this->getform('heigth');
	$this->getform('layout');
	$this->getform('size');
	$this->getform('start');
	$this->getform('timespan');
	$this->getform('width');
	$this->getform('layout');
    }
}

if (!isset($session)) {
    $session = new Session();
}
?>
