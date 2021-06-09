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

class Anchor extends FilterBase
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispAction();
		$this->dispValue('direction', _TITLE('Direction'));
		$this->dispInterface();
		$this->dispValue('proto', _TITLE('Proto'));
		$this->dispSrcDest();
		$this->dispValue('state-filter', _TITLE('State'));
		$this->dispQueue();
		$this->dispInline();
		$this->dispTail($ruleNumber, $count);
	}
	
	/**
	 * Counts the lines in inline rules.
	 * 
	 * Inline rules always span beyond the anchor rule itself.
	 */
	function countLines()
	{
		if (isset($this->rule['inline'])) {
			// Add 1 for anchor-close line
			return count(explode("\n", $this->rule['inline'])) + 1;
		} else {
			return 0;
		}
	}

	function dispAction()
	{
		?>
		<td title="<?php echo _TITLE('Id') ?>" nowrap="nowrap">
			<?php
			if (isset($this->rule['identifier'])) {
				echo $this->rule['identifier'];
			}
			?>
		</td>
		<?php
	}

	/**
	 * Displays inline rules.
	 * 
	 * We enclose all tabs into code tags, otherwise indentation is lost.
	 */
	function dispInline()
	{
		?>
		<td title="<?php echo _TITLE('Inline rules') ?>" colspan="2" nowrap="nowrap">
			<?php
			if (isset($this->rule['inline'])) {
				echo str_replace("\t", "<code>\t</code><code>\t</code>", nl2br(htmlentities($this->rule['inline'])));
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('identifier');

		$this->inputFilterHead();
		$this->inputFilterOpts();

		$this->inputInline();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	/**
	 * Gets submitted inline rules.
	 * 
	 * inputKey() trims, hence this new method.
	 */
	function inputInline()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			// textarea inserts \r\n instead of just \n, which pfctl complains about, so delete \r chars
			$this->rule['inline']= preg_replace('/\r/', '', filter_input(INPUT_POST, 'inline'));
		}
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editText('identifier', _TITLE('Identifier'), 'anchor-id', NULL, _CONTROL('name, may be nested'));

		$this->editFilterHead();
		$this->editFilterOpts();

		$this->editInlineRules();

		$this->editComment();
		$this->editTail();
	}

	function editInlineRules()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Inline Rules').':' ?>
			</td>
			<td>
				<textarea cols="80" rows="5" id="inline" name="inline" placeholder="<?php echo _CONTROL('Enter inline rules here') ?>"><?php echo isset($this->rule['inline']) ? $this->rule['inline'] : ''; ?></textarea>
				<?php $this->editHelp('inline') ?>
			</td>
		</tr>
		<?php
	}
}
?>
