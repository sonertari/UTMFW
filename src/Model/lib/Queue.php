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

class Queue extends Rule
{
	protected $keyQueue= array(
		'queue' => array(
			'method' => 'parseNextNVP',
			'params' => array('name'),
			),
		'parent' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'bandwidth' => array(
			'method' => 'parseBandwidth',
			'params' => array('bw-burst', 'bw-time'),
			),
		'min' => array(
			'method' => 'parseBandwidth',
			'params' => array('min-burst', 'min-time'),
			),
		'max' => array(
			'method' => 'parseBandwidth',
			'params' => array('max-burst', 'max-time'),
			),
		'qlimit' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'flows' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'quantum' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'default' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		);

	protected $typeQueue= array(
		'name' => array(
			'regex' => RE_NAME,
			),
		'parent' => array(
			'regex' => RE_NAME,
			),
		'bandwidth' => array(
			'regex' => RE_BANDWIDTH,
			),
		'bw-burst' => array(
			'regex' => RE_BANDWIDTH,
			),
		'bw-time' => array(
			'regex' => RE_BWTIME,
			),
		'min' => array(
			'regex' => RE_BANDWIDTH,
			),
		'min-burst' => array(
			'regex' => RE_BANDWIDTH,
			),
		'min-time' => array(
			'regex' => RE_BWTIME,
			),
		'max' => array(
			'regex' => RE_BANDWIDTH,
			),
		'max-burst' => array(
			'regex' => RE_BANDWIDTH,
			),
		'max-time' => array(
			'regex' => RE_BWTIME,
			),
		'flows' => array(
			'regex' => RE_NUM,
			),
		'quantum' => array(
			'regex' => RE_NUM,
			),
		'qlimit' => array(
			'regex' => RE_NUM,
			),
		'default' => array(
			'regex' => RE_BOOL,
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keyInterface,
			$this->keyQueue
			);

		$this->typedef = array_merge(
			$this->typeInterface,
			$this->typeQueue,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/\(/', ' ( ', $this->str);
		$this->str= preg_replace('/\)/', ' ) ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
	}

	/**
	 * Parses bandwidth.
	 * 
	 * "burst" bandwidth-spec "for" number "ms", hence time is obligatory after burst,
	 * but we allow for time only definitions, so the user can fix the rule on the WUI.
	 * 
	 * @param string $burst bw-burst, min-burst, or max-burst
	 * @param string $time bw-time, min-time, or max-time
	 */
	function parseBandwidth($burst, $time)
	{
		$this->parseNextValue();

		if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == 'burst')) {
			$this->index+= 2;
			$this->rule[$burst]= $this->words[$this->index];
		}
		if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == 'for')) {
			$this->index+= 2;
			$this->rule[$time]= $this->words[$this->index];
		}
	}

	function generate()
	{
		$this->str= 'queue ' . (isset($this->rule['name']) ? $this->rule['name'] : '');
		$this->genInterface();
		$this->genValue('parent', 'parent ');
		$this->genBandwidth('bandwidth', 'bw');
		$this->genBandwidth('min', 'min');
		$this->genBandwidth('max', 'max');
		$this->genValue('flows', 'flows ');
		$this->genValue('quantum', 'quantum ');
		$this->genValue('qlimit', 'qlimit ');
		$this->genKey('default');

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genBandwidth($key, $pre)
	{
		if (isset($this->rule[$key])) {
			$this->str.= " $key " . $this->rule[$key] . (isset($this->rule["$pre-burst"]) ? ' burst ' . $this->rule["$pre-burst"] : '') . (isset($this->rule["$pre-time"]) ? ' for ' . $this->rule["$pre-time"] : '');
		}
	}
}
?>
