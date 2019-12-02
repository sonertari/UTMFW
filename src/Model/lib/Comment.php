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

class Comment extends Rule
{
	/**
	 * Type definition for comments.
	 * 
	 * We should never have a Comment object without 'comment' key, hence 'require'.
	 */
	protected $typedef= array(
		'comment' => array(
			'require' => TRUE,
			'regex' => RE_COMMENT,
			),
		);

	function parse($str)
	{
		$this->init();
		$this->rule['comment']= $str;
	}

	function generate()
	{
		$this->str= '';
		
		$lines= preg_split("/\n/", stripslashes($this->rule['comment']));
		foreach ($lines as $line) {
			$this->str.= "# $line\n";
		}
		return $this->str;
	}
}
?>
