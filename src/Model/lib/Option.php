<?php 
/*
 * Copyright (C) 2004-2017 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Model;

class Option extends Rule
{
	protected $keyOption = array(
		'loginterface' => array(
			'method' => 'parseOption',
			'params' => array(),
			),
		'block-policy' => array(
			'method' => 'parseOption',
			'params' => array(),
			),
		'state-policy' => array(
			'method' => 'parseOption',
			'params' => array(),
			),
		'optimization' => array(
			'method' => 'parseOption',
			'params' => array(),
			),
		'ruleset-optimization' => array(
			'method' => 'parseOption',
			'params' => array(),
			),
		'debug' => array(
			'method' => 'parseOption',
			'params' => array(),
			),
		'hostid' => array(
			'method' => 'parseOption',
			'params' => array(),
			),
		'skip' => array(
			'method' => 'parseSkip',
			'params' => array(),
			),
		'fingerprints' => array(
			'method' => 'parseFingerprints',
			'params' => array(),
			),
		'reassemble' => array(
			'method' => 'parseReassemble',
			'params' => array(),
			),
		);

	protected $typeOption= array(
		'type' => array(
			'regex' => RE_TYPE,
			),
		'loginterface' => array(
			'regex' => RE_IF,
			),
		'block-policy' => array(
			'regex' => RE_BLOCKPOLICY,
			),
		'state-policy' => array(
			'regex' => RE_STATEPOLICY,
			),
		'optimization' => array(
			'regex' => RE_OPTIMIZATION,
			),
		'ruleset-optimization' => array(
			'regex' => RE_RULESETOPTIMIZATION,
			),
		'debug' => array(
			'regex' => RE_DEBUG,
			),
		'hostid' => array(
			'regex' => RE_NUM,
			),
		'skip' => array(
			'multi' => TRUE,
			'regex' => RE_IF,
			),
		'fingerprints' => array(
			'func' => 'IsFilePath',
			),
		'reassemble' => array(
			'regex' => RE_REASSEMBLE,
			),
		'no-df' => array(
			'regex' => RE_BOOL,
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyOption;

		$this->typedef = array_merge(
			$this->typeOption,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function parseOption()
	{
		$this->rule['type']= $this->words[$this->index];
		$this->rule[$this->words[$this->index]]= $this->words[++$this->index];
	}

	function parseSkip()
	{
		$this->rule['type']= 'skip';
		$this->index++;
		$this->parseItems('skip');
	}

	function parseFingerprints()
	{
		$this->rule['type']= 'fingerprints';
		// File name is in quotes
		$this->parseDelimitedStr('fingerprints');
	}

	function parseReassemble()
	{
		$this->parseOption();
		if ($this->words[$this->index + 1] === 'no-df') {
			$this->index++;
			$this->parseBool();
		}
	}

	function generate()
	{
		$this->str= '';

		$this->genOption('block-policy');
		$this->genOption('debug');
		$this->genOption('fingerprints', '"', '"');
		$this->genOption('hostid');
		$this->genOption('loginterface');
		$this->genOption('optimization');
		$this->genOption('ruleset-optimization');
		$this->genOption('state-policy');
		$this->genSkip();
		$this->genReassemble();
		
		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genOption($key, $head= '', $tail= '')
	{
		if (isset($this->rule[$key])) {
			$this->str.= "set $key " . $head . preg_replace('/"/', '', $this->rule[$key]) . $tail;
		}
	}

	function genSkip()
	{
		if (isset($this->rule['skip'])) {
			if (!is_array($this->rule['skip'])) {
				$this->genOption('skip', 'on ');
			} else {
				$this->str.= 'set skip on { ' . implode(', ', $this->rule['skip']) . ' }';
			}
		}
	}

	function genReassemble()
	{
		if (isset($this->rule['reassemble'])) {
			$this->genOption('reassemble');
			$this->genKey('no-df');
		}
	}
}
?>