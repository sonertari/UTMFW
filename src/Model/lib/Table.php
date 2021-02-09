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

class Table extends Rule
{
	protected $keyTable= array(
		'table' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('identifier', '<', '>'),
			),
		'persist' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'const' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'counters' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'file' => array(
			'method' => 'parseFile',
			'params' => array(),
			),
		'{' => array(
			'method' => 'parseData',
			'params' => array(),
			),
		);

	protected $typeTable= array(
		'identifier' => array(
			'require' => TRUE,
			'regex' => RE_ID,
			),
		'persist' => array(
			'regex' => RE_BOOL,
			),
		'const' => array(
			'regex' => RE_BOOL,
			),
		'counters' => array(
			'regex' => RE_BOOL,
			),
		'file' => array(
			'multi' => TRUE,
			'func' => 'IsFilePath',
			),
		'data' => array(
			'multi' => TRUE,
			'regex' => RE_TABLE_ADDRESS,
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyTable;

		$this->typedef = array_merge(
			$this->typeTable,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/</', ' < ', $this->str);
		$this->str= preg_replace('/>/', ' > ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
	}

	/**
	 * Parses file.
	 * 
	 * File definition always has 'file' keyword in the front. So we call this
	 * method each time we find a 'file' word in the rule string.
	 */
	function parseFile()
	{
		$filename= preg_replace('/"/', '', $this->words[++$this->index]);
		if (!isset($this->rule['file'])) {
			$this->rule['file']= $filename;
		} else {
			if (!is_array($this->rule['file'])) {
				$tmp= $this->rule['file'];
				unset($this->rule['file']);
				$this->rule['file'][]= $tmp;
			}
			$this->rule['file'][]= $filename;
		}
	}

	function parseData()
	{
		while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}' && !$this->isEndOfWords()) {
			$this->rule['data'][]= $this->words[$this->index];
		}
	}

	function generate()
	{
		$this->str= 'table <' . $this->rule['identifier'] . '>';
		$this->genKey('persist');
		$this->genKey('const');
		$this->genKey('counters');
		$this->genFiles();
		$this->genData();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genFiles()
	{
		if (isset($this->rule['file'])) {
			if (!is_array($this->rule['file'])) {
				$this->str.= ' file "' . $this->rule['file'] . '"';
			} else {
				foreach ($this->rule['file'] as $file) {
					$this->str.= ' file "' . $file . '"';
				}
			}
		}
	}

	function genData()
	{
		if (isset($this->rule['data'])) {
			$this->str.= ' { ';
			if (!is_array($this->rule['data'])) {
				$this->str.= $this->rule['data'];
			} else {
				$this->str.= implode(', ', $this->rule['data']);
			}
			$this->str.= ' }';
		}
	}
}
?>
