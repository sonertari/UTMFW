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
 * Layout class
 *
 * - load a layout from disk if a layout was specified
 * - provide a default layout with basic graphs on the host tree if no layout
 *   was specified
 */
require_once('class_vars.inc');
require_once('class_graph.inc');
require_once('class_text.inc');
require_once('class_lexer.inc');
require_once('setup.inc');
require_once('tools.inc');

class Layout {
	var $groups;
	var $groupvars;

	function Layout($name = '') {
		global $symon;

		$this->_reset();

		if ($name != '') {
			if (!isset($symon["layout_dir"])) {
			config_error("layout_dir", "is not set");
			return 0;
			}
			if (!is_dir($symon["layout_dir"])) {
			config_error("layout_dir", "does not point to a directory");
			return 0;
			}

				if (isset($symon["isolate_hosts"]) &&
					($symon["isolate_hosts"] > 0) &&
					is_dir($symon["host_tree"]."/".normalise_filename($name))) {
					$this->_generate_host($name);
				} else {
					$this->_load($name);
				}
		} else {
			$this->_generate_default();
		}

		if (!isset($this->groups)) {
			runtime_error('layout: no valid layout selected');
			return 0;
		}
	}

	function _generate_default() {
		global $symon;

		if (!is_dir($symon["host_tree"])) {
			config_error("host_tree", "does not point to a directory");
			return 0;
		}

		$root_dir = dir($symon["host_tree"]);
		while ($host = $root_dir->read()) {
				$this->_generate_host($host);
			}

		if (!isset($this->groups)) {
			runtime_error('layout: need $symon["host_tree"]/hostname/cpu0.rrd like files to generate the default layout - none found.');
			return 0;
		}
	}

	function _generate_host($machine) {
		global $symon;

		if (!is_dir($symon["host_tree"])) {
			config_error("host_tree", "does not point to a directory");
			return 0;
		}

		if (is_file($symon["host_tree"]."/".$machine)) {
			runtime_error('$symon["host_tree"] should contain machine directories, not files. ($symon["host_tree"]/cpu0.rrd => $symon["host_tree"]/localhost/cpu0.rrd)');
		}

		if ($machine != '.' &&
			$machine != '..' &&
			is_dir($symon["host_tree"]."/".$machine)) {
			$groupvars = new Vars();
			$groupvars->set('host', $machine);
			$groupvars->set('dir', $symon["host_tree"].'/'.$machine);
			$groupvars->set('name', $machine);
			$this->_add_group($groupvars);
			/* get all graphs */
			$graph_dir = dir($symon["host_tree"].'/'.$machine);
			$files = array();
			while ($item = $graph_dir->read()) {
				if ($item != '.' &&
					$item != '..' &&
					preg_match("/.rrd$/", $item)) {
					$files[] = $item;
				}
			}
			asort($files);
			/* get combined graphs */
			foreach ($symon['combine'] as $type => $enabled) {
				if ($enabled) {
					$combine = array();
					$newfiles = array();
					foreach ($files as $key => $item) {
						if (preg_match("/^".$type."[0-9_]/", $item)) {
							$combine[] = $item;
						} else {
							$newfiles[] = $item;
						}
					}

					$files = $newfiles;

					if (count($combine)) {
						$v = new Vars();
						foreach ($combine as $key => $item) {
							$v->set('rrdfile'.$key, $symon["host_tree"].'/'.$machine.'/'.$item);
						}
						$g = new Graph($groupvars);
						$g->set_graph_vars($v);
						$this->_add_item($machine, $g);
					}
				}
			}

			foreach ($files as $key => $item) {
				$v = new Vars();
				$v->set('rrdfile', $symon["host_tree"].'/'.$machine.'/'.$item);
				$g = new Graph($groupvars);
				$g->set_graph_vars($v);
				$this->_add_item($machine, $g);
			}
		}
	}

	function _add_group(&$vars) {
		if (strtolower(get_class($vars)) != 'vars') {
			print_r($vars);
			runtime_error('layout: internal: add_group(vars) - vars are not variables');
		}

		if (!$vars->defp('name')) {
			$this->_display();
			runtime_error('layout: need a valid group name for a group to add');
		}

		$name = $vars->get('name');
		if (isset($this->groupvars[$name])) {
			$this->_display();
			runtime_error("layout: group $name redefined");
		}
		$this->groupvars[$name] = $vars;
		$this->groups[$name] = array();
	}

	function _add_item($groupname, &$value) {
		if (!isset($this->groupvars[$groupname])) {
			$this->_display();
			runtime_error("layout: cannot add item $value : group $groupname has not been defined");
		}
		array_push($this->groups[$groupname], $value);
	}

	function _reset() {
		/* reset current layout */
		unset($this->groups);
		unset($this->groupvars);
	}

	function _load($name) {
		global $symon;

		if ($name == '' || $name == 'default' || $name == 'empty') {
			runtime_error('layout: need a valid layout name to load');
			return 0;
		}

		$name = normalise_filename($name);
		$file = $symon["layout_dir"].'/'.$name.'.layout';

		$lexer = new Lexer();
		$lexer->load($name);
		$groupvars = 0;

		$token = $lexer->next_token();
		do {
			if ($token === 0) {
			$token_string = '';
			} else {
			$token_string = $token;
			}

			switch ($token_string) {
			case 'group':
			$groupvars = new Vars();
			$groupvars->parse($lexer);
			$lexer->parse_semicolon();
			$this->_add_group($groupvars);
			$groupname = $groupvars->get('name');
			break;

			case 'graph':
			$graph = new Graph($groupvars);
			$graph->parse($lexer);
			$lexer->parse_semicolon();
			$this->_add_item($groupname, $graph);
			break;

			case 'text':
			$text = new Text();
			$text->parse($lexer);
			$lexer->parse_semicolon();
			$this->_add_item($groupname, $text);
			break;

			default:
			$lexer->parse_error('Expected group|graph|text');
			break;
			}

			$token = $lexer->next_token();
		} while (!$lexer->is_eof());

		if (isset($symon['layout_debug'])) {
			$this->_display();
		}
	}

	function render($html= true) {
		$LinkNames = array(
			_TITLE('CPU Load'),
			_TITLE('Temperature'),
			_TITLE('Fan'),
			_TITLE('Memory Usage'),
			_TITLE('Disk I/O'),
			_TITLE('Partitions Usages'),
			_TITLE('Loopback Interface'),
			_TITLE('Internal Interface'),
			_TITLE('External Interface'),
			_TITLE('State Statistics'),
			_TITLE('State Searches vs Packets'),
			_TITLE('Data Transfer'),
			_TITLE('Mbuf Statistics'),
			_TITLE('Network Protocol Usage'),
			_TITLE('Number of Processes'),
			_TITLE('Process CPU Load'),
		);
		
		$graphs= array();

		reset($this->groups);
		if (is_array($this->groups)) {
			foreach($this->groups as $group => $items) {
				if (array_key_exists($group, $LinkNames)) {
					$LinkName= $LinkNames[$group];
				}
				else {
					$LinkName= $group;
				}

				if ($html) {
					print '	<div class="group">
							<h3 class="header" style="margin: 4px 0 2px 0;"><a name="' . $group . '">' . _($LinkName) . '</a></h3>
						</div>';
				}

				reset($items);
				foreach($items as $item) {
					$graphs[$LinkName]= $item->render($html);
				}

				if ($html) {
					print '<div class="item-footer"></div>';
				}
			}
		}
		return $graphs;
	}

	function getgrouptitles() {
		$a = array();
		if (is_array($this->groups)) {
			reset($this->groups);
			foreach($this->groups as $group => $value) {
			array_push($a, $group);
			}
		}
		return $a;
	}

	function _display() {
		print "<pre>";
		if (is_array($this->groups)) {
			reset($this->groups);
			foreach($this->groups as $group => $items) {
			print "\xa\xa group ";
			$vars = $this->groupvars[$group]->tostring();
			if (strlen($vars) > 0) {
				print $vars;
			}
			print ";";
			foreach ($items as $key => $value) {
				print $value->_display();
			}
			}
		}
		print "</pre>";
	}

	function _test() {
		print "\xa layout definition:";
		$this->_display();
		print "\xa\xa render layout:";
		$this->render();
	}
}
?>
