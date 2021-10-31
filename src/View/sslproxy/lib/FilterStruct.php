<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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

class FilterStruct extends RuleStruct
{
	protected $name= 'FilterRule';
	protected $title;
	protected $placeHolder;
	protected $editHelp= 'Filter';
	protected $cssClass= 'filterrule';

	function __construct()
	{
		$this->title= _TITLE('Structured Filter Rule');
		$this->placeHolder= _CONTROL('Enter filtering rule here');

		parent::__construct($str);
	}
}
?>
