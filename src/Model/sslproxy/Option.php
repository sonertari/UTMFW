<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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

class Option extends Rule
{
	protected $typeMacro= array(
		'option' => array(
			'require' => TRUE,
			'regex' => RE_ID,
			),
		'value' => array(
			'require' => TRUE,
			'regex' => SPRE_VALUE,
			),
		);

	function __construct($str)
	{
		$this->typedef = array_merge(
			$this->typeMacro,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function parse($str)
	{
		$this->str= $str;
		$this->init();
		$this->parseComment();
		$this->sanitize();
		$this->split();

		$this->index= 0;
		if (isset($this->words[$this->index]))
			$this->rule['option']= $this->words[$this->index++];

		$value= array();
		while (!$this->isEndOfWords()) {
			$value[]= $this->words[$this->index++];
		}
		$this->rule['value']= implode(' ', $value);
	}

	function generate()
	{
		$this->str= '';
		if (isset($this->rule['option'])) {
			$this->str.= $this->rule['option'] . ' ';
		}
		if (isset($this->rule['value'])) {
			$this->str.= $this->rule['value'];
		}
		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
