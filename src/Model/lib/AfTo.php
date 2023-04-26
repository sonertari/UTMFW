<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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

/**
 * Class for AF Translation rules.
 */
class AfTo extends Filter
{
	protected $keyAfTo= array(
		'af-to' => array(
			'method' => 'parseAfto',
			'params' => array(),
			),
		);

	protected $typeAfTo= array(
		'rediraf' => array(
			'regex' => RE_AF,
			),
		'toredirhost' => array(
			'multi' => TRUE,
			'regex' => RE_REDIRHOST,
			),
		);

	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keyAfTo,
			$this->keyPoolType
			);

		$this->typedef= array_merge(
			$this->typeAfTo,
			$this->typeRedirHost,
			$this->typePoolType
			);

		parent::__construct($str);
	}

	function parseAfto()
	{
		$this->rule['rediraf']= $this->words[++$this->index];

		if ($this->words[$this->index + 1] === 'from') {
			$this->index++;
			$this->parseItems('redirhost');

			if ($this->words[$this->index + 1] === 'to') {
				$this->index++;
				$this->parseItems('toredirhost');
			}
		}
	}

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genFilterOpts();

		$this->genAfto();
		/// @todo Can we have pooltype in af-to rules? BNF says no, but pfctl does not complain about it
		$this->genPoolType();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genAfto()
	{
		$this->str.= ' af-to';
		$this->genValue('rediraf');
		$this->genItems('redirhost', 'from ');
		$this->genItems('toredirhost', 'to ');
	}
}
?>
