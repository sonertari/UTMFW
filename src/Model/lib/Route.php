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

class Route extends Filter
{
	protected $keyRoute= array(
		'route-to' => array(
			'method' => 'parseRoute',
			'params' => array(),
			),
		'reply-to' => array(
			'method' => 'parseRoute',
			'params' => array(),
			),
		'dup-to' => array(
			'method' => 'parseRoute',
			'params' => array(),
			),
		);

	protected $typeRouteHost= array(
		'routehost' => array(
			'multi' => TRUE,
			'regex' => RE_ROUTEHOST,
			),
		);

	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keyRoute,
			$this->keyPoolType
			);

		$this->typedef= array_merge(
			$this->typeRouteHost,
			$this->typePoolType
			);

		parent::__construct($str);
	}

	function parseRoute()
	{
		$this->rule['type']= $this->words[$this->index];
		$this->parseItems('routehost');
	}

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genFilterOpts();

		$this->genValue('type');
		$this->genItems('routehost');
		$this->genPoolType();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
