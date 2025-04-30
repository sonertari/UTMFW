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

class Limit extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispLimit();
		$this->dispTail($ruleNumber);
	}
	
	function dispLimit()
	{
		?>
		<td title="<?php echo _TITLE('Limit') ?>" colspan="12">
			<?php
			$this->arr= array();
			if (isset($this->rule['limit']) && count($this->rule['limit'])) {
				reset($this->rule['limit']);
				foreach ($this->rule['limit'] as $key => $val) {
					$this->arr[]= "$key: $val";
				}
			}
			echo implode(', ', $this->arr);
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('states', 'limit');
		$this->inputKey('frags', 'limit');
		$this->inputKey('src-nodes', 'limit');
		$this->inputKey('tables', 'limit');
		$this->inputKey('table-entries', 'limit');
		$this->inputKey('pktdelay_pkts', 'limit');
		$this->inputKey('anchors', 'limit');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editLimit();

		$this->editComment();
		$this->editTail();
	}

	function editLimit()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('States').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="states" name="states" value="<?php echo isset($this->rule['limit']['states']) ? $this->rule['limit']['states'] : ''; ?>" placeholder="<?php echo _CONTROL('number') ?>" />
				<?php $this->editHelp('states') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Frags').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="frags" name="frags" value="<?php echo isset($this->rule['limit']['frags']) ? $this->rule['limit']['frags'] : ''; ?>" placeholder="<?php echo _CONTROL('number') ?>" />
				<?php $this->editHelp('frags') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Src Nodes').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="srcnodes" name="src-nodes" value="<?php echo isset($this->rule['limit']['src-nodes']) ? $this->rule['limit']['src-nodes'] : ''; ?>" placeholder="<?php echo _CONTROL('number') ?>" />
				<?php $this->editHelp('src-nodes') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Tables').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="tables" name="tables" value="<?php echo isset($this->rule['limit']['tables']) ? $this->rule['limit']['tables'] : ''; ?>" placeholder="<?php echo _CONTROL('number') ?>" />
				<?php $this->editHelp('tables') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Table Entries').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="table-entries" name="table-entries" value="<?php echo isset($this->rule['limit']['table-entries']) ? $this->rule['limit']['table-entries'] : ''; ?>" placeholder="<?php echo _CONTROL('number') ?>" />
				<?php $this->editHelp('table-entries') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Pktdelay pkts').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="pktdelay_pkts" name="pktdelay_pkts" value="<?php echo isset($this->rule['limit']['pktdelay_pkts']) ? $this->rule['limit']['pktdelay_pkts'] : ''; ?>" placeholder="<?php echo _CONTROL('number') ?>" />
				<?php $this->editHelp('pktdelay_pkts') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Anchors').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="anchors" name="anchors" value="<?php echo isset($this->rule['limit']['anchors']) ? $this->rule['limit']['anchors'] : ''; ?>" placeholder="<?php echo _CONTROL('number') ?>" />
				<?php $this->editHelp('anchors') ?>
			</td>
		</tr>
		<?php
	}
}
?>
