<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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

class _Include extends Rule
{
	protected $typeInclude= array(
		'file' => array(
			'require' => TRUE,
			'func' => 'IsFilePath',
			),
		);

	function __construct($str)
	{
		$this->typedef = array_merge(
			$this->typeInclude,
			$this->typeComment
			);

		parent::__construct($str);
	}

	/**
	 * Parses include rule.
	 * 
	 * Include rules do not need sanitization or splitting, because we use regexes for parsing.
	 * 
	 * @param string $str String to parse
	 */
	function parse($str)
	{
		$this->str= $str;
		$this->init();
		$this->parseComment();

		// include "/etc/pf/sub.filter.conf" # Comment
		// include /etc/pf/sub.filter.conf # Comment
		if ((preg_match('/^\s*include\s+"([^"]+)"\s*$/', $this->str, $match)) ||
			(preg_match('/^\s*include\s+(\S+)\s*$/', $this->str, $match))){
				$this->rule['file']= $match[1];
		}
	}

	function generate()
	{
		$this->str= 'include "' . (isset($this->rule['file']) ? $this->rule['file'] : '') . '"';

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
