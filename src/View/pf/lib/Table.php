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

class Table extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispId();
		$this->dispKey('const', _TITLE('Flag'));
		$this->dispKey('persist', _TITLE('Flag'));
		$this->dispKey('counters', _TITLE('Flag'));
		// Dummy params
		$this->dispValues('', '');
		$this->dispTail($ruleNumber);
	}

	function dispId()
	{
		?>
		<td title="Id">
			<?php
			if (isset($this->rule['identifier'])) {
				echo htmlentities($this->rule['identifier']);
			}
			?>
		</td>
		<?php
	}

	function dispValues($key, $title)
	{
		?>
		<td title="<?php echo _TITLE('Values') ?>" colspan="8">
			<?php
			if (isset($this->rule['data'])) {
				$this->printValue($this->rule['data']);
			}
			if (isset($this->rule['file'])) {
				$this->printValue($this->rule['file'], 'file "', '"');
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('identifier');
		$this->inputBool('const');
		$this->inputBool('persist');
		$this->inputBool('counters');
		$this->inputDel('data', 'delValue');
		$this->inputAdd('data', 'addValue');
		$this->inputDel('file', 'delFile');
		$this->inputAdd('file', 'addFile');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editText('identifier', _TITLE('Identifier'), FALSE, NULL, _CONTROL('string'));
		$this->editFlags();
		// Dummy params
		$this->editValues('', '', '', '', '');

		$this->editComment();
		$this->editTail();
	}

	function editFlags()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Flags').':' ?>
			</td>
			<td>
				<input type="checkbox" id="const" name="const" value="const" <?php echo isset($this->rule['const']) ? 'checked' : ''; ?> />
				<label for="const">const</label>
				<?php $this->editHelp('const') ?>
				<br>
				<input type="checkbox" id="persist" name="persist" value="persist" <?php echo isset($this->rule['persist']) ? 'checked' : ''; ?> />
				<label for="persist">persist</label>
				<?php $this->editHelp('persist') ?>
				<br>
				<input type="checkbox" id="counters" name="counters" value="counters" <?php echo isset($this->rule['counters']) ? 'checked' : ''; ?> />
				<label for="counters">counters</label>
				<?php $this->editHelp('counters') ?>
			</td>
		</tr>
		<?php
	}

	function editValues($key, $title, $delName, $addName, $hint, $help= NULL, $size= 0, $disabled= FALSE)
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Values').':' ?>
			</td>
			<td>
				<?php
				if (isset($this->rule['data'])) {
					$this->editDeleteValueLinks($this->rule['data'], 'delValue');
				}
				if (isset($this->rule['file'])) {
					$this->editDeleteValueLinks($this->rule['file'], 'delFile', 'file "', '"');
				}
				$this->editAddValueBox('addValue', _TITLE('add host or network'), _CONTROL('host or network'), 30);
				echo '<br />';
				$this->editAddValueBox('addFile', _TITLE('add file'), _CONTROL('filename'), 30);
				?>
			</td>
		</tr>
		<?php
	}
}
?>
