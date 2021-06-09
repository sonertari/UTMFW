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

class Macro extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispMacro();
		$this->dispTail($ruleNumber, $count);
	}
	
	function dispMacro()
	{
		$this->dispValue('identifier', _TITLE('Id'));
		?>
		<td title="<?php echo _TITLE('Value') ?>" colspan="11">
			<?php
			if (isset($this->rule['value'])) {
				$this->printValue($this->rule['value']);
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('identifier');
		$this->inputDel('value', 'delValue');
		$this->inputAdd('value', 'addValue');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editText('identifier', _TITLE('Identifier'), FALSE, NULL, _CONTROL('valid string'));
		$this->editValues('value', _TITLE('Value'), 'delValue', 'addValue', _CONTROL('add value'), NULL, 30);

		$this->editComment();
		$this->editTail();
	}
}
?>
