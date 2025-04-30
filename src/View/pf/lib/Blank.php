<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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

class Blank extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispBlank();
	}
	
	/**
	 * Counts lines in the rule.
	 * 
	 * @attention Decrement one more time for the rule itself (already incremented in the main display loop in conf.php).
	 */
	function countLines()
	{
		return count(explode("\n", $this->rule['blank'])) - 2;
	}

	function dispBlank()
	{
		?>
			<td class="blank" colspan="13">
				<?php echo nl2br($this->rule['blank']); ?>
			</td>
		</tr>
		<?php
	}

	function input()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			// Untaint: convert all into blank lines
			$blank= '';
			foreach (explode("\n", filter_input(INPUT_POST, 'blank')) as $line) {
				$blank.= "\n";
			}
			$this->rule['blank']= $blank;
		}

		$this->inputDelEmpty();
	}
	
	/**
	 * Prints edit page.
	 * 
	 * Blank rules are very simple and do not need to be generated.
	 * 
	 * @param int $ruleNumber Rule number.
	 * @param bool $modified Whether the rule is modified or not.
	 * @param bool $testResult Test result.
	 * @param bool $generateResult Rule generation result.
	 * @param string $action Current state of the edit page.
	 */
	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		global $ruleCategoryNames;

		$count= 0;
		if (isset($this->rule['blank'])) {
			$count= count(explode("\n", $this->rule['blank'])) - 1;
		}

		$ruleType= $ruleCategoryNames[$this->ref];
		$editHeader= _TITLE('Edit <RULE_TYPE> Rule <RULE_NUMBER>');
		$editHeader= str_replace('<RULE_TYPE>', $ruleType, $editHeader);
		$editHeader= str_replace('<RULE_NUMBER>', $ruleNumber, $editHeader);
		?>
		<h2><?php echo $editHeader . ($modified ? ' (' . _TITLE('modified') . ')' : '') ?></h2>
		<form id="editForm" action="<?php echo $this->href . $ruleNumber ?>" method="post">
			<div class="buttons">
				<input type="submit" id="apply" name="apply" value="<?php echo _CONTROL('Apply') ?>" />
				<input type="submit" id="save" name="save" value="<?php echo _CONTROL('Save') ?>" <?php echo $modified ? '' : 'disabled' ?> />
				<input type="submit" id="cancel" name="cancel" value="<?php echo _CONTROL('Cancel') ?>" />
				<input type="checkbox" id="forcesave" name="forcesave" <?php echo $modified && !$testResult ? '' : 'disabled' ?> />
				<label for="forcesave"><?php echo _CONTROL('Save with errors') ?></label>
				<input type="hidden" name="state" value="<?php echo $action ?>" />
			</div>
			<?php echo _TITLE('Number of lines') . ': ' . $count; ?><br>
			<textarea cols="80" rows="5" id="blank" name="blank" placeholder="<?php echo _CONTROL('Enter blank lines here') ?>"><?php echo isset($this->rule['blank']) ? $this->rule['blank'] : ''; ?></textarea>
		</form>
		<?php
	}
}
?>
