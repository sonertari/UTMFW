<?php
/*
 * Copyright (C) 2004-2022 Soner Tari
 *
 * This file is part of PFRE.
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

namespace Model;

class Filter extends FilterBase
{
	protected $keyAction= array(
		'pass' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'match' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'block' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'drop' => array(
			'method' => 'parseNVP',
			'params' => array('blockoption'),
			),
		'return' => array(
			'method' => 'parseNVP',
			'params' => array('blockoption'),
			),
		'return-rst' => array(
			'method' => 'parseBlockOption',
			'params' => array(),
			),
		'return-icmp' => array(
			'method' => 'parseBlockOption',
			'params' => array(),
			),
		'return-icmp6' => array(
			'method' => 'parseBlockOption',
			'params' => array(),
			),
		);

	protected $keyPoolType= array(
		'bitmask' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'least-states' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'round-robin' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'random' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'source-hash' => array(
			'method' => 'parseSourceHash',
			'params' => array(),
			),
		'sticky-address' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		);

	protected $keyInterface= array(
		'on' => array(
			'method' => 'parseInterface',
			'params' => array(),
			),
		);

	protected $typeAction= array(
		'action' => array(
			'regex' => RE_ACTION,
			),
		'blockoption' => array(
			'regex' => RE_BLOCKOPTION,
			),
		'block-ttl' => array(
			'regex' => RE_NUM,
			),
		'block-icmpcode' => array(
			'regex' => RE_ICMPCODE,
			),
		'block-icmp6code' => array(
			'regex' => RE_ICMPCODE,
			),
		);

	protected $typeType= array(
		'type' => array(
			'regex' => RE_TYPE,
			),
		);

	protected $typeRedirHost= array(
		'redirhost' => array(
			'multi' => TRUE,
			'regex' => RE_REDIRHOST,
			),
		);

	protected $typePoolType= array(
		'bitmask' => array(
			'regex' => RE_BOOL,
			),
		'least-states' => array(
			'regex' => RE_BOOL,
			),
		'round-robin' => array(
			'regex' => RE_BOOL,
			),
		'random' => array(
			'regex' => RE_BOOL,
			),
		'source-hash' => array(
			'regex' => RE_BOOL,
			),
		'source-hash-key' => array(
			'regex' => RE_SOURCE_HASH_KEY,
			),
		'sticky-address' => array(
			'regex' => RE_BOOL,
			),
		);

	protected $typeDivertPort= array(
		'divertport' => array(
			'regex' => RE_PORT,
			),
		);

	protected $typeInterface= array(
		'interface' => array(
			'multi' => TRUE,
			'regex' => RE_IFSPEC,
			),
		'rdomain' => array(
			'regex' => RE_NUM,
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keywords,
			$this->keyAction,
			$this->keyLog,
			$this->keyQuick
			);

		$this->typedef= array_merge(
			$this->typedef,
			$this->typeAction,
			$this->typeLog,
			$this->typeQuick,
			$this->typeType
			);

		parent::__construct($str);
	}

	function parseInterface()
	{
		if ($this->words[$this->index + 1] == 'rdomain') {
			$this->index++;
			$this->rule['rdomain']= $this->words[++$this->index];
		} else {
			$this->parseItems('interface');
		}
	}

	function parseRedirHostPort($hostKey= 'redirhost', $portKey= 'redirport')
	{
		$this->parseNVP('type');

		/// @todo Fix these off-by-N errors
		if ($this->words[$this->index + 1] != 'port') {
			$this->parseItems($hostKey);
		}
		// @attention Do not use else here
		if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == 'port')) {
			$this->index+= 2;
			$this->rule[$portKey]= $this->words[$this->index];
		}
	}

	function parseBlockOption()
	{
		$this->parseNVP('blockoption');

		if ($this->rule['blockoption'] == 'return-rst') {
			if ($this->words[$this->index + 1] == '(' && $this->words[$this->index + 2] == 'block-ttl') {
				$this->index+= 3;
				$this->rule['block-ttl']= $this->words[$this->index];
			}
		} elseif ($this->rule['blockoption'] == 'return-icmp') {
			if ($this->words[$this->index + 1] == '(') {
				$this->index+= 2;
				$this->rule['block-icmpcode']= $this->words[$this->index];

				if ($this->words[$this->index + 1] == ',') {
					$this->index+= 2;
					$this->rule['block-icmp6code']= $this->words[$this->index];
				}
			}
		} elseif ($this->rule['blockoption'] == 'return-icmp6') {
			if ($this->words[$this->index + 1] == '(') {
				$this->index+= 2;
				$this->rule['block-icmp6code']= $this->words[$this->index];
			}
		}
	}
	
	/**
	 * Parses source hash and its key, if any.
	 * 
	 * @attention There is no pattern for hash key or string, so we check keywords instead.
	 * This is one of the benefits of using keyword lists instead of switch/case structs while parsing.
	 */
	function parseSourceHash()
	{
		$this->parseBool();

		//if (preg_match('/^[a-f\d]{16,}$/', $this->words[$this->index + 1])) {
		if (!in_array($this->words[$this->index + 1], $this->keywords)) {
			$this->rule['source-hash-key']= $this->words[++$this->index];
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

		$this->genFilterHead();
		$this->genFilterOpts();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genAction()
	{
		if (isset($this->rule['action'])) {
			$this->str= $this->rule['action'];
			if ($this->rule['action'] == 'block') {
				$this->genBlockOption();
			}
		}
	}

	function genBlockOption()
	{
		$this->genValue('blockoption');

		if (isset($this->rule['blockoption'])) {
			if ($this->rule['blockoption'] == 'return-rst') {
				$this->genValue('block-ttl', '( ttl ', ' )');
			} elseif ($this->rule['blockoption'] == 'return-icmp') {
				$this->arr= array();

				if (isset($this->rule['block-icmpcode'])) {
					$this->arr[]= $this->rule['block-icmpcode'];

					if (isset($this->rule['block-icmp6code'])) {
						$this->arr[]= $this->rule['block-icmp6code'];
					}
				}

				if (count($this->arr)) {
					$this->str.= ' ( ';
					$this->str.= implode(', ', $this->arr);
					$this->str.= ' )';
				}
			} elseif ($this->rule['blockoption'] == 'return-icmp6') {
				$this->genValue('block-icmp6code', '( ', ' )');
			}
		}
	}
	
	function genInterface()
	{
		if (isset($this->rule['interface'])) {
			$this->genItems('interface', 'on');
		} else {
			$this->genValue('rdomain', 'on rdomain ');
		}
	}

	function genPoolType()
	{
		$this->genKey('bitmask');
		$this->genKey('least-states');
		$this->genKey('random');
		$this->genKey('round-robin');

		$this->genKey('source-hash');
		if (isset($this->rule['source-hash'])) {
			$this->genValue('source-hash-key');
		}

		$this->genKey('sticky-address');
	}
}
?>
