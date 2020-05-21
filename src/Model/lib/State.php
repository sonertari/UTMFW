<?php 
/*
 * Copyright (C) 2004-2020 Soner Tari
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

class State extends Timeout
{
	protected $keyState= array(
		'max' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-src-states' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-src-nodes' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-src-conn' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-src-conn-rate' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'sloppy' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'no-sync' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'pflow' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'if-bound' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'floating' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'overload' => array(
			'method' => 'parseOverload',
			'params' => array(),
			),
		'source-track' => array(
			'method' => 'parseSourceTrack',
			'params' => array(),
			),
		);

	protected $typeState= array(
		'max' => array(
			'regex' => RE_NUM,
			),
		'max-src-states' => array(
			'regex' => RE_NUM,
			),
		'max-src-nodes' => array(
			'regex' => RE_NUM,
			),
		'max-src-conn' => array(
			'regex' => RE_NUM,
			),
		'max-src-conn-rate' => array(
			'regex' => RE_CONNRATE,
			),
		'sloppy' => array(
			'regex' => RE_BOOL,
			),
		'no-sync' => array(
			'regex' => RE_BOOL,
			),
		'pflow' => array(
			'regex' => RE_BOOL,
			),
		'if-bound' => array(
			'regex' => RE_BOOL,
			),
		'floating' => array(
			'regex' => RE_BOOL,
			),
		'overload' => array(
			'regex' => RE_ID,
			),
		'flush' => array(
			'regex' => RE_BOOL,
			),
		'global' => array(
			'regex' => RE_BOOL,
			),
		'source-track' => array(
			'regex' => RE_BOOL,
			),
 		'source-track-option' => array(
			'regex' => RE_SOURCETRACKOPTION,
			),
		);

	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keywords,
			$this->keyState
			);

		$this->typedef = array_merge(
			$this->typedef,
			$this->typeState
			);

		parent::__construct($str);
		
		unset($this->keywords['frag']);
		unset($this->keywords['interval']);
	}

	function parseOverload()
	{
		$this->rule['overload']= rtrim(ltrim($this->words[++$this->index], '<'), '>');

		if ($this->words[$this->index + 1] == 'flush') {
			$this->index++;
			$this->rule['flush']= TRUE;

			if ($this->words[$this->index + 1] == 'global') {
				$this->index++;
				$this->rule['global']= TRUE;
			}
		}
	}
	
	function parseSourceTrack()
	{
		$this->rule['source-track']= TRUE;

		if ($this->words[$this->index + 1] == 'rule' || $this->words[$this->index + 1] == 'global') {
			$this->rule['source-track-option']= $this->words[++$this->index];
		}
	}
	
	function generate()
	{
		$this->str= '';
		$this->genState();
		
		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genState()
	{
		$this->arr= array();
		$this->genStateOpts();
		if (count($this->arr)) {
			$this->str= 'set state-defaults ';
			$this->str.= implode(', ', $this->arr);
		}
	}

	function genStateOpts()
	{
		$this->genText('max');
		$this->genText('max-src-states');
		$this->genText('max-src-nodes');
		$this->genText('max-src-conn');
		$this->genText('max-src-conn-rate');

		$this->genBool('sloppy');
		$this->genBool('no-sync');
		$this->genBool('pflow');

		$this->genBool('if-bound');
		$this->genBool('floating');

		$this->genOverload();
		$this->genSourceTrack();

		$this->genTimeoutOpts();
	}

	function genText($key)
	{
		if (isset($this->rule[$key])) {
			$this->arr[]= "$key " . $this->rule[$key];
		}
	}
	
	function genBool($key)
	{
		if (isset($this->rule[$key])) {
			$this->arr[]= $key;
		}
	}

	function genOverload()
	{
		if (isset($this->rule['overload'])) {
			$str= 'overload <' . $this->rule['overload'] . '>';
			if (isset($this->rule['flush'])) {
				$str.= ' flush';
				if (isset($this->rule['global'])) {
					$str.= ' global';
				}
			}
			$this->arr[]= $str;
		}
	}
	
	function genSourceTrack()
	{
		if (isset($this->rule['source-track'])) {
			$str= 'source-track';
			if (isset($this->rule['source-track-option'])) {
				$str.= ' ' . $this->rule['source-track-option'];
			}
			$this->arr[]= $str;
		}
	}
}
?>
