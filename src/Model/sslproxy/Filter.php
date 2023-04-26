<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * PFRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PFRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PFRE.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SSLproxy;

class Filter extends Rule
{
	protected $keyAction= array(
		'Divert' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'Split' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'Pass' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'Block' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'Match' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		);

	protected $keyPart= array(
		'from' => array(
			'method' => 'parseFrom',
			'params' => array(),
			),
		'to' => array(
			'method' => 'parseTo',
			'params' => array(),
			),
		'log' => array(
			'method' => 'parseLog',
			'params' => array(),
			),
		'*' => array(
			'method' => 'parseBoolNVP',
			'params' => array('all'),
			),
		);

	protected $typeAction= array(
		'action' => array(
			'regex' => SPRE_ACTION,
			),
		);

	protected $typeFrom= array(
		'all' => array(
			'regex' => RE_BOOL,
			),
		'from_all' => array(
			'regex' => RE_BOOL,
			),
		'from' => array(
			'regex' => RE_BOOL,
			),
		'user' => array(
			'regex' => SPRE_WORD,
			),
		'desc' => array(
			'regex' => SPRE_WORD,
			),
		'src_ip' => array(
			'regex' => SPRE_IP,
			),
		);

	protected $typeTo= array(
		'to' => array(
			'regex' => RE_BOOL,
			),
		'to_all' => array(
			'regex' => RE_BOOL,
			),
		'sni' => array(
			'regex' => SPRE_HOST,
			),
		'cn' => array(
			'regex' => SPRE_HOST,
			),
		'host' => array(
			'regex' => SPRE_HOST,
			),
		'uri' => array(
			'regex' => SPRE_HOST,
			),
		'dst_ip' => array(
			'regex' => SPRE_IP,
			),
		'port' => array(
			'regex' => SPRE_PORT,
			),
		);

	protected $typeLog= array(
		'log' => array(
			'multi' => TRUE,
			'regex' => SPRE_LOG,
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keywords,
			$this->keyAction,
			$this->keyPart,
			);

		$this->typedef= array_merge(
			$this->typedef,
			$this->typeAction,
			$this->typeFrom,
			$this->typeTo,
			$this->typeLog,
			$this->typeComment,
			);

		parent::__construct($str);
	}

	function parseFrom()
	{
		$this->rule['from']= TRUE;

		while (isset($this->words[$this->index + 1])) {
			$next_word= $this->words[$this->index + 1];

			if (($next_word == 'user') ||
					($next_word == 'desc')) {
				$this->index++;
				$this->parseNextValue();
			}
			else if ($next_word == 'ip') {
				$this->index++;
				$this->parseNextNVP('src_ip');
			}
			else if ($next_word == '*') {
				$this->rule['from_all']= TRUE;
				$this->index++;
			}
			else {
				break;
			}
		}
	}

	function parseTo()
	{
		$this->rule['to']= TRUE;

		while (isset($this->words[$this->index + 1])) {
			$next_word= $this->words[$this->index + 1];

			if (($next_word == 'sni') ||
					($next_word == 'cn') ||
					($next_word == 'host') ||
					($next_word == 'uri') ||
					($next_word == 'port')) {
				$this->index++;
				$this->parseNextValue();
			}
			else if ($next_word == 'ip') {
				$this->index++;
				$this->parseNextNVP('dst_ip');
			}
			else if ($next_word == '*') {
				$this->rule['to_all']= TRUE;
				$this->index++;
			}
			else {
				break;
			}
		}
	}

	function parseLog()
	{
		$this->rule['log']= array();

		while (isset($this->words[$this->index + 1])) {
			$next_word= $this->words[$this->index + 1];

			if (($next_word == 'connect') ||
					($next_word == '!connect') ||
					($next_word == 'master') ||
					($next_word == '!master') ||
					($next_word == 'cert') ||
					($next_word == '!cert') ||
					($next_word == 'content') ||
					($next_word == '!content') ||
					($next_word == 'pcap') ||
					($next_word == '!pcap') ||
					($next_word == 'mirror') ||
					($next_word == '!mirror') ||
					($next_word == '*') ||
					($next_word == '!*') ||
					preg_match('/\$.+/', $next_word)) {
				$this->rule['log'][]= $next_word;
			}
			else {
				break;
			}
			$this->index++;
		}
	}

	/**
	 * Generates rule.
	 * 
	 * The output of this function is returned to the View on the command line,
	 * so we return the generated rule string.
	 * 
	 * @return string String rule.
	 */
	function generate()
	{
		$this->genAction();

		$this->genFrom();
		$this->genTo();
		$this->genLog();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genAction()
	{
		if (isset($this->rule['action'])) {
			$this->str= $this->rule['action'];
		}
	}

	function genFrom()
	{
		if (isset($this->rule['all'])) {
			$this->str.= ' *';
		}
		if (isset($this->rule['from'])) {
			$this->str.= ' from';
		}
		if (isset($this->rule['from_all'])) {
			$this->str.= ' *';
		}
		if (isset($this->rule['user'])) {
			$this->str.= ' user ' . $this->rule['user'];
		}
		if (isset($this->rule['desc'])) {
			$this->str.= ' desc ' . $this->rule['desc'];
		}
		if (isset($this->rule['src_ip'])) {
			$this->str.= ' ip ' . $this->rule['src_ip'];
		}
	}

	function genTo()
	{
		if (isset($this->rule['to'])) {
			$this->str.= ' to';
		}
		if (isset($this->rule['to_all'])) {
			$this->str.= ' *';
		}
		if (isset($this->rule['sni'])) {
			$this->str.= ' sni ' . $this->rule['sni'];
		}
		if (isset($this->rule['cn'])) {
			$this->str.= ' cn ' . $this->rule['cn'];
		}
		if (isset($this->rule['host'])) {
			$this->str.= ' host ' . $this->rule['host'];
		}
		if (isset($this->rule['uri'])) {
			$this->str.= ' uri ' . $this->rule['uri'];
		}
		if (isset($this->rule['dst_ip'])) {
			$this->str.= ' ip ' . $this->rule['dst_ip'];
		}
		if (isset($this->rule['port'])) {
			$this->str.= ' port ' . $this->rule['port'];
		}
	}

	function genLog()
	{
		if (isset($this->rule['log'])) {
			$this->str.= ' log';
			
			foreach ($this->rule['log'] as $action) {
				$this->str.= " $action";
			}
		}
	}
}
?>
