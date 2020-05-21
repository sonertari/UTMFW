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

class DivertTo extends Filter
{
	protected $typeDivertHost= array(
		'diverthost' => array(
			'regex' => RE_HOST,
			),
		);

	function __construct($str)
	{
		$this->keywords = array(
			'divert-to' => array(
				'method' => 'parseRedirHostPort',
				'params' => array('diverthost', 'divertport'),
				),
			);

		$this->typedef= array_merge(
			$this->typeDivertHost,
			$this->typeDivertPort
			);

		parent::__construct($str);
	}

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genFilterOpts();

		$this->genValue('type');
		$this->genValue('diverthost');
		$this->genValue('divertport', 'port ');

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
