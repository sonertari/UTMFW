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

namespace View;

class Antispoof extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispInterface();
		$this->dispKey('quick', _TITLE('Quick'));
		$this->dispValue('af', _TITLE('Address Family'));
		$this->dispLog(8);
		$this->dispValue('label', _TITLE('Label'));
		$this->dispTail($ruleNumber);
	}
	
	function input()
	{
		$this->inputLog();
		$this->inputBool('quick');

		$this->inputInterface();
		$this->inputKey('af');
		$this->inputKey('label');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editLog();
		$this->editCheckbox('quick', _TITLE('Quick'));

		$this->editInterface();
		$this->editAf();
		$this->editText('label', _TITLE('Label'), NULL, NULL, _CONTROL('string'));

		$this->editComment();
		$this->editTail();
	}
}
?>
