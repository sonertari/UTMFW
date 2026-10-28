<?php
/*
 * Copyright (C) 2004-2026 Soner Tari
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

class Icap extends Rule
{
	protected $typeIcap= array(
		'service' => array(
			'require' => TRUE,
			'regex' => SPRE_VALUE,
			),
		);

	function __construct($str)
	{
		$this->typedef = array_merge(
			$this->typeIcap,
			$this->typeComment
			);

		parent::__construct($str);
	}

	/**
	 * Parses Icap rule.
	 * 
	 * Icap rules do not need sanitization or splitting, because we use regexes for parsing.
	 * 
	 * @param string $str String to parse
	 */
	function parse($str)
	{
		$this->str= $str;
		$this->init();
		$this->parseComment();

		// Icap icap://127.0.0.1:1344,suricata,suricata,no,no,60,1024,0,16384,yes,no # Comment
		if ((preg_match('/^\s*Icap\s+(\S+)\s*$/', $this->str, $match))){
				$this->rule['service']= $match[1];
		}
	}

	function generate()
	{
		$this->str= 'Icap ' . (isset($this->rule['service']) ? $this->rule['service'] : '');

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
