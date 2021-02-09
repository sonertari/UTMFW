<?php 
/*
 * Copyright (C) 2004-2021 Soner Tari
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

class Limit extends Rule
{
	protected $keyLimit= array(
		'states' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		'frags' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		'src-nodes' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		'tables' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		'table-entries' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		);

	protected $typeLimit= array(
		'limit' => array(
			'values' => array(
				'states' => array(
					'regex' => RE_NUM,
					),
				'frags' => array(
					'regex' => RE_NUM,
					),
				'src-nodes' => array(
					'regex' => RE_NUM,
					),
				'tables' => array(
					'regex' => RE_NUM,
					),
				'table-entries' => array(
					'regex' => RE_NUM,
					),
				),
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyLimit;

		$this->typedef = array_merge(
			$this->typeLimit,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/\(/', ' ( ', $this->str);
		$this->str= preg_replace('/\)/', ' ) ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
		$this->str= preg_replace('/"/', ' " ', $this->str);
	}

	function parseLimit()
	{
		$this->rule['limit'][$this->words[$this->index]]= $this->words[++$this->index];
	}

	function generate()
	{
		$this->str= '';

		if (count($this->rule['limit'])) {
			reset($this->rule['limit']);

			if (count($this->rule['limit']) == 1) {
				$key= key($this->rule['limit']);
				$val= current($this->rule['limit']);
				$this->str.= "set limit $key $val";
			} else {
				$this->str= 'set limit {';
				foreach ($this->rule['limit'] as $key => $val) {
					$this->str.= " $key $val,";
				}
				$this->str= rtrim($this->str, ',');
				$this->str.= ' }';
			}
		}

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
