<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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

class Macro extends Rule
{
	protected $typeMacro= array(
		'identifier' => array(
			'require' => TRUE,
			'regex' => SPRE_ID,
			),
		'value' => array(
			'require' => TRUE,
			'multi' => TRUE,
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

		$this->index= 1;
		$this->rule['identifier']= '';
		if (!$this->isEndOfWords())
			$this->rule['identifier']= $this->words[$this->index++];

		$this->rule['value']= array();
		while (!$this->isEndOfWords()) {
			$this->rule['value'][]= $this->words[$this->index++];
		}
	}

	function generate()
	{
		$this->str= 'Define '.$this->rule['identifier'].' ';

		if (!is_array($this->rule['value'])) {
			$this->str.= $this->rule['value'];
		} else {
			$this->str.= implode(' ', $this->rule['value']);
		}

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
