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

class LoadAnchor extends Rule
{
	protected $typeLoadAnchor= array(
		'anchor' => array(
			'require' => TRUE,
			'regex' => RE_ANCHOR_ID,
			),
		'file' => array(
			'require' => TRUE,
			'func' => 'IsFilePath',
			),
		);

	function __construct($str)
	{
		$this->typedef = array_merge(
			$this->typeLoadAnchor,
			$this->typeComment
			);

		parent::__construct($str);
	}

	/**
	 * Parses load anchor rule.
	 * 
	 * Load anchor rules do not need sanitization or splitting, because we use regexes for parsing.
	 * 
	 * @param string $str String to parse
	 */
	function parse($str)
	{
		$this->str= $str;
		$this->init();
		$this->parseComment();
		
		// load anchor spam from "/etc/pf-spam.conf" # Comment
		// load anchor spam from /etc/pf-spam.conf # Comment
		if ((preg_match('/^\s*load\s+anchor\s+(\S+)\s+from\s+"([^"]+)"\s*$/', $this->str, $match)) ||
			(preg_match('/^\s*load\s+anchor\s+(\S+)\s+from\s+(\S+)\s*$/', $this->str, $match))) {
			$this->rule['anchor']= $match[1];
			$this->rule['file']= $match[2];
		}
	}

	function generate()
	{
		$this->str= 'load anchor ' . $this->rule['anchor'] . ' from "' . $this->rule['file'] . '"';
		
		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
