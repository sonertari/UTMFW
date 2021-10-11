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

class ProxySpecStruct extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		?>
		<td class="proxyspec">
			<?php echo 'ProxySpec' ?>
		</td>
		<?php
		$this->dispInline();
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

	/**
	 * Displays inline rules.
	 * 
	 * We enclose all tabs into code tags, otherwise indentation is lost.
	 */
	function dispInline()
	{
		?>
		<td title="<?php echo _TITLE('Inline rules') ?>" colspan="4" nowrap="nowrap">
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
		$this->inputInline();
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

		$this->editHead($modified, $testResult, $generateResult, $action, 'sslproxy.conf');

		$this->editInlineRules();

		$this->editTail();
	}

	function editInlineRules()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Structured ProxySpec').':' ?>
			</td>
			<td>
				<textarea cols="80" rows="25" id="inline" name="inline" placeholder="<?php echo _CONTROL('Enter proxy specifications here') ?>"><?php echo isset($this->rule['inline']) ? $this->rule['inline'] : ''; ?></textarea>
				<?php $this->editHelp('proxyspec') ?>
			</td>
		</tr>
		<?php
	}
}
?>
