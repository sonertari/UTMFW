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

namespace View;

class _Include extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispInclude();
		$this->dispTail($ruleNumber, $count);
	}
	
	function dispInclude()
	{
		?>
		<td class="include">
			<?php echo 'include' ?>
		</td>
		<td title="<?php echo _TITLE('File') ?>" colspan="11">
			<?php echo $this->rule['file'] ?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('file');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editInclude();

		$this->editComment();
		$this->editTail();
	}

	function editInclude()
	{
		global $View, $PF_CONFIG_PATH;

		$View->Controller($ruleFiles, 'GetPfRuleFiles');
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('File').':' ?>
			</td>
			<td>
				<select id="file" name="file">
					<option value=""></option>
					<?php
					foreach ($ruleFiles as $file) {
						$file= "$PF_CONFIG_PATH/$file";
						?>
						<option value="<?php echo $file ?>" label="<?php echo $file ?>" <?php echo ($this->rule['file'] == $file ? 'selected' : ''); ?>><?php echo $file ?></option>
						<?php
					}
					?>
				</select>

			</td>
			<td class="none">
				<?php PrintHelpBox(_HELPBOX("Only files under the $PF_CONFIG_PATH folder can be included here.")) ?>
			</td>
		</tr>
		<?php
	}
}
?>
