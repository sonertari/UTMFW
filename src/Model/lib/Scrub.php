<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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

class Scrub extends Filter
{
	protected $keyScrub= array(
		'min-ttl' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-mss' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'no-df' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'random-id' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'reassemble' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		);

	protected $typeScrub= array(
		'min-ttl' => array(
			'regex' => RE_NUM,
			),
		'max-mss' => array(
			'regex' => RE_NUM,
			),
		'no-df' => array(
			'regex' => RE_BOOL,
			),
		'random-id' => array(
			'regex' => RE_BOOL,
			),
		'reassemble' => array(
			'regex' => RE_REASSEMBLE_TCP,
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyScrub;

		$this->typedef= $this->typeScrub;

		parent::__construct($str);
	}

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genScrub();
		$this->genFilterOpts();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genScrub()
	{
		$this->str.= ' scrub';
		$opt= '';
		if (isset($this->rule['no-df'])) {
			$opt.= 'no-df';
		}
		if (isset($this->rule['min-ttl'])) {
			$opt.= ', min-ttl ' . $this->rule['min-ttl'];
		}
		if (isset($this->rule['max-mss'])) {
			$opt.= ', max-mss ' . $this->rule['max-mss'];
		}
		if (isset($this->rule['random-id'])) {
			$opt.= ', random-id';
		}
		if (isset($this->rule['reassemble'])) {
			$opt.= ', reassemble ' . $this->rule['reassemble'];
		}
		if ($opt !== '') {
			$this->str.= ' (' . trim($opt, ' ,') . ')';
		}
	}
}
?>
