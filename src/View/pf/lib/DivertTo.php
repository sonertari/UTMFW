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

class DivertTo extends Filter
{
	function setType()
	{
		$this->rule['type']= 'divert-to';
	}

	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispAction();
		$this->dispValue('direction', _TITLE('Direction'));
		$this->dispInterface();
		$this->dispLog();
		$this->dispKey('quick', _TITLE('Quick'));
		$this->dispValue('proto', _TITLE('Proto'));
		$this->dispSrcDest();
		$this->dispValue('diverthost', _TITLE('Divert Host'));
		$this->dispValue('divertport', _TITLE('Divert Port'));
		$this->dispTail($ruleNumber);
	}
	
	function input()
	{
		$this->inputAction();

		$this->inputFilterHead();

		$this->inputLog();
		$this->inputBool('quick');

		$this->inputKey('diverthost');
		$this->inputKey('divertport');

		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editAction();

		$this->editFilterHead();

		$this->editLog();
		$this->editCheckbox('quick', _TITLE('Quick'));

		$this->editText('diverthost', _TITLE('Divert Host'), 'Nat', NULL, _CONTROL('ip, host, table or macro'));
		$this->editText('divertport', _TITLE('Divert Port'), 'Nat', NULL, _CONTROL('number, name, table or macro'));

		$this->editFilterOpts();

		$this->editComment();
		$this->editTail();
	}
}
?>
