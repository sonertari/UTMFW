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
 * Graph class
 *
 * Generates rrdtool recipes by rewriting a template using a joined set of all
 * variables (layout vars, graph vars, session vars - in order of precendence).
 */
require_once('class_session.inc');
require_once('class_cache.inc');
require_once('setup.inc');
require_once('class_vars.inc');
require_once('graph_defaults.inc');

class Graph {
    var $template;
    var $vars;
    var $graph_vars;
    var $group_vars;
    var $definition;
    var $url;

    function __construct(&$group_vars) {
	$this->group_vars = $group_vars;
    }

    function set_graph_vars(&$graph_vars) {
	$this->graph_vars = $graph_vars;
    }

    function _makevars() {
	global $session;
	global $symon;

	$this->definition = '';
	unset($this->url);

	/* create local state by combining group and local graph args */
	$this->vars = new Vars();
	$this->vars->addvars($session->getvars());
	$this->vars->addvars($this->graph_vars);
	$this->vars->addvars($this->group_vars);

	if ($this->vars->defp('rrdfile')) {
	    $this->_parse_filename($this->vars->get('rrdfile'));
	} else if ($this->vars->defp('rrdfile0')) {
	    $n = 0;
	    $this->_parse_filename($this->vars->get('rrdfile0'));
	    while ($this->vars->defp('rrdfile'.$n)) {
		$this->_parse_filename($this->vars->get('rrdfile'.$n), $n);
		$n++;
	    }
	}

	if (!$this->vars->defp('template')) {
	    if ($this->vars->defp('rrdtype0')) {
		$template = get_combined_template($this->vars->get('rrdtype0'), $n);
	    } else if ($this->vars->defp('rrdtype')) {
		$template = get_single_template($this->vars->get('rrdtype'));
	    } else {
		warning('graph: cannot load graph template: filename does not yield a graphtype ('. $this->vars->get('rrdfile'));
		if (isset($symon['graph_debug'])) {
		    $this->_display();
		}
	    }
	} else {
	    $template = $this->vars->get('template');
	}

	if ($template != "") {
	    $this->template = preg_split("/\n/", $template);
	} else {
	    if (isset($symon['graph_debug'])) {
		$this->_display();
		warning('graph: template not set');
	    }
	}
    }

    function _parse_filename($filename, $index='') {
	if (preg_match("/^(.*\/)?((cpu|if|io|mem|pf|pfq|proc|debug|mbuf|sensor|df)([0-9]{0,2}))(_([a-z0-9\.]+))?([0-9]{0,4}[a-z]{0,1}[0-9]{0,1}[a-z]{0,1}).rrd$/",
		       $filename, $match)) {
	    $this->vars->def('rrdtype'. $index, $match[2]);
	    $this->vars->def('rrdwhat'. $index, $match[6]);
	    $this->vars->def('rrdwhich'. $index, $match[7]);
	    $this->vars->def('title'. $index,
			     $match[2].'('.$match[6].$match[7].') of '. $this->vars->get('name'));
	}
	else if (preg_match("/^(.*\/)?(sensor)(it[0-9]+\.[a-z]+)?([0-9]{0,1}).rrd$/",
		       $filename, $match)) {
	    $this->vars->def('rrdtype'. $index, $match[2]);
	    $this->vars->def('rrdwhat'. $index, $match[3]);
	    $this->vars->def('rrdwhich'. $index, $match[4]);
	    $this->vars->def('title'. $index,
			     $match[2].'('.$match[3].$match[4].') of '. $this->vars->get('name'));
	} else {
	    if (isset($symon['graph_debug'])) {
		$this->_display();
		warning('graph: cannot determine rrd type from filename "'.$filename.'"');
	    }
	}
    }

    function parse(&$lexer) {
	$this->graph_vars = new Vars();
	$this->graph_vars->parse($lexer);
    }

    function render($html= true) {
		$this->_save();
		if (isset($this->template)) {
			if ($html) {
				print '<img src="../symon/graph.php?' . $this->_url() . '" class="graph">';
			}
		}
		return $this->_url();
	}

	function _url() {
	if (!isset($this->url)) {
	    runtime_error("graph: internal error : need to save the graph before an url can be requested");
	    return '';
	} else {
	    return $this->url;
	}
    }


    function _save() {
	global $cache;
	global $symon;

	$this->_makevars();

	if (isset($symon['graph_debug'])) {
	    print "<pre>";
	    $this->_display();
	    print "</pre>";
	}

	$this->_preprocess();

	if (isset($symon['graph_debug'])) {
	    print "<pre>\xa preprocessed template = ";
	    if (isset($this->definition) && $this->definition != "") {
		foreach ($this->definition as $line) {
		    print "\xa    '$line'";
		}
	    }
	    print "</pre>";
	}

	$this->_constrain();

	if (isset($symon['graph_debug'])) {
	    print "<pre>\xa constrained template = ";
	    if (isset($this->definition) && $this->definition != "") {
		foreach ($this->definition as $line) {
		    print "\xa    '$line'";
		}
	    }
	    print "</pre>";
	}

	if ($this->definition != "") {
	    $seed = $this->vars->tostring();
	    $graph_command = implode("\n", $this->definition);
	    $this->url = $cache->insert($graph_command, '.txt', $seed);

	    if (isset($symon['graph_debug'])) {
		print "<pre>\xa url = ". $this->url. "</pre>";
	    }
	}
    }

    /* fill template with variables */
    function _preprocess() {
	$definition = '';
	if (!isset($this->template) || !is_array($this->template)) {
	    return;
	}
	reset($this->template);

	foreach ($this->template as $t) {
	    $startpos = strpos($t, "%", 0);
	    if ($startpos + 1 > strlen($t)) {
		$endpos = false;
	    } else {
		$endpos = strpos($t, "%", $startpos + 1);
	    }
	    while ($startpos !== false && $endpos !== false) {
		$leader = substr($t, 0, $startpos);
		$key = substr($t, $startpos + 1, $endpos - $startpos - 1);
		$tailer = substr($t, $endpos + 1);

		$definition .= $leader;

		if ($this->vars->defp($key)) {
		    $definition .= $this->vars->get($key);
		} else {
		    $definition .= "%" . $key;
		    $tailer = "%" . $tailer;
		}

		$t = $tailer;

		$startpos=strpos($t, "%", 0);
		if ($startpos + 1 > strlen($t)) {
		    $endpos = false;
		} else {
		    $endpos = strpos($t, "%", $startpos + 1);
		}
	    }

	    $definition .= $t . "\n";
	}

	$this->definition = preg_split("/\n/", $definition);
    }
    /* constrain arguments */
    function _constrain() {
	global $symon;

	$definition = '';
	if (!is_array($this->definition)) {
	    return 1;
	}

	reset($this->definition);
	foreach ($this->definition as $t) {
	    if (preg_match("/^([^-]*)(-[^ ]+) ([^ ]+)(.*)$/", $t)) {
		while (preg_match("/^([^-]*)(-[^ ]+) ([^ ]+)(.*)$/", $t, $match)) {
		    $definition .= $match[1];
		    foreach ($symon['constraints'] as $k => $v) {
			if ($k == $match[2]) {
			    if (is_array($symon['constraints'][$k])
				&& isset($symon['constraints'][$k]["max"])
				&& ($match[3] > $symon['constraints'][$k]["max"])) {
				$match[3] = $symon['constraints'][$k]["max"];
			    }
			    if (is_array($symon['constraints'][$k])
				&& isset($symon['constraints'][$k]["min"])
				&& ($match[3] < $symon['constraints'][$k]["min"])) {
				$match[3] = $symon['constraints'][$k]["min"];
			    }
			}
		    }
		    $definition .= $match[2].' '.$match[3];
		    $t = $match[4];
		}
	    }
	    $definition .= $t . "\n";
	}
	$this->definition = preg_split("/\n/", $definition);
    }

    function _display() {
	print "\xa graph ";
	if (isset($this->template)) {
	    print "\xa  template=";
	    foreach ($this->template as $line) {
		print "\xa    '$line'";
	    }
	}
	if (isset($this->vars)) {
	    $vars = $this->vars->tostring();
	    if (strlen($vars) > 0) {
		print $vars;
	    }
	    print ";";
	} else if (isset($this->graph_vars)) {
	    $vars = $this->graph_vars->tostring();
	    if (strlen($vars) > 0) {
		print $vars;
	    }
	    print ";";
	}
    }
}

?>
