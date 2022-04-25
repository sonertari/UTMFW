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

class NatBase extends Filter
{
	protected $typeRedirPort= array(
		'redirport' => array(
			'regex' => RE_PORTSPEC,
			),
		);

	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keywords,
			$this->keyPoolType
			);

		$this->typedef= array_merge(
			$this->typedef,
			$this->typeRedirHost,
			$this->typeRedirPort,
			$this->typePoolType
			);

		parent::__construct($str);
	}

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genFilterOpts();

		$this->genValue('type');
		$this->genItems('redirhost');
		$this->genValue('redirport', 'port ');
		$this->genPoolType();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
