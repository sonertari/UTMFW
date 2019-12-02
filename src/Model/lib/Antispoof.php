<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
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

class Antispoof extends Rule
{
	protected $keyAntispoof= array(
		'for' => array(
			'method' => 'parseItems',
			'params' => array('interface'),
			),
		'label' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('label'),
			),
		);

	protected $typeLabel= array(
		'label' => array(
			'regex' => RE_NAME,
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keyLog,
			$this->keyQuick,
			$this->keyAf,
			$this->keyAntispoof
			);

		$this->typedef= array_merge(
			$this->typeLog,
			$this->typeQuick,
			$this->typeAf,
			$this->typeInterface,
			$this->typeLabel,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function generate()
	{
		$this->str= 'antispoof';

		$this->genLog();
		$this->genKey('quick');
		$this->genItems('interface', 'for');
		$this->genValue('af');
		$this->genValue('label', 'label "', '"');

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
