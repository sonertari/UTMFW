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

class State extends Timeout
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispState();
		$this->dispTail($ruleNumber);
	}
	
	function dispState()
	{
		?>
		<td title="<?php echo _TITLE('State Defaults') ?>" colspan="12">
			<?php
			$this->arr= array();

			$this->dispText('max');
			$this->dispText('max-src-states');
			$this->dispText('max-src-nodes');
			$this->dispText('max-src-conn');
			$this->dispText('max-src-conn-rate');

			$this->dispBool('sloppy');
			$this->dispBool('no-sync');
			$this->dispBool('pflow');

			$this->dispBool('if-bound');
			$this->dispBool('floating');

			$this->dispOverload();
			$this->dispSourceTrack();

			$this->dispTimeoutOpts();

			echo implode(', ', $this->arr);
			?>
		</td>
		<?php
	}

	function dispText($key)
	{
		if (isset($this->rule[$key])) {
			$this->arr[]= "$key: " . $this->rule[$key];
		}
	}
	
	function dispBool($key)
	{
		if (isset($this->rule[$key])) {
			$this->arr[]= $key;
		}
	}
	
	function dispOverload()
	{
		if (isset($this->rule['overload'])) {
			$str= 'overload: <' . $this->rule['overload'] . '>';
			if (isset($this->rule['flush'])) {
				$str.= ' flush';
				if (isset($this->rule['global'])) {
					$str.= ' global';
				}
			}
			$this->arr[]= htmlentities($str);
		}
	}
	
	function dispSourceTrack()
	{
		if (isset($this->rule['source-track'])) {
			$str= 'source-track';
			if (isset($this->rule['source-track-option'])) {
				$str.= ' ' . $this->rule['source-track-option'];
			}
			$this->arr[]= $str;
		}
	}
	
	function input()
	{
		$this->inputState();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function inputState()
	{
		$this->inputKey('max');
		$this->inputKey('max-src-states');
		$this->inputKey('max-src-nodes');
		$this->inputKey('max-src-conn');
		$this->inputKey('max-src-conn-rate');

		$this->inputBool('sloppy');
		$this->inputBool('no-sync');
		$this->inputBool('pflow');

		$this->inputBool('if-bound');
		if (!isset($this->rule['if-bound'])) {
			$this->inputBool('floating');
		}

		$this->inputKey('overload');
		$this->inputBool('flush');
		$this->inputBool('global');

		$this->inputBool('source-track');
		$this->inputKey('source-track-option');

		$this->inputTimeout();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editState();

		$this->editComment();
		$this->editTail();
	}

	function editState()
	{
		$this->editText('max', _TITLE('Max States'), 'max', 10, _CONTROL('number'));
		$this->editText('max-src-states', _TITLE('Max Single Host States'), 'max-src-states', 10, _CONTROL('number'));
		$this->editText('max-src-nodes', _TITLE('Max Addresses'), 'max-src-nodes', 10, _CONTROL('number'));
		$this->editText('max-src-conn', _TITLE('Max Connection'), 'max-src-conn', 10, _CONTROL('number'));
		$this->editText('max-src-conn-rate', _TITLE('Max Connection Rate'), 'max-src-conn-rate', 20, _CONTROL('number/number'));
		$this->editCheckbox('sloppy', _TITLE('Sloppy Tracker'));
		$this->editCheckbox('no-sync', _TITLE('No pfsync'));
		$this->editCheckbox('pflow', _TITLE('Export To pflow'));
		$this->editIfBinding();
		$this->editOverload();
		$this->editSourceTrack();
		$this->editTimeout();
	}

	function editIfBinding()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Interface Binding').':' ?>
			</td>
			<td>
				<input type="checkbox" id="if-bound" name="if-bound" value="if-bound" <?php echo (isset($this->rule['if-bound']) && $this->rule['if-bound'] ? 'checked' : ''); ?> <?php echo (isset($this->rule['floating']) ? 'disabled' : ''); ?> />
				<label for="if-bound">if-bound</label>
				<input type="checkbox" id="floating" name="floating" value="floating" <?php echo (isset($this->rule['floating']) && $this->rule['floating'] ? 'checked' : ''); ?> <?php echo (isset($this->rule['if-bound']) ? 'disabled' : ''); ?> />
				<label for="floating">floating</label>
				<?php $this->editHelp('if-binding') ?>
			</td>
		</tr>
		<?php
	}

	function editOverload()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Overload').':' ?>
			</td>
			<td>
				<input type="text" size="20" id="overload" name="overload" value="<?php echo isset($this->rule['overload']) ? $this->rule['overload'] : ''; ?>"  placeholder="<?php echo _CONTROL('string') ?>"/>
				<input type="checkbox" id="flush" name="flush" value="flush" <?php echo (isset($this->rule['flush']) && $this->rule['flush'] ? 'checked' : ''); ?> <?php echo (!isset($this->rule['overload']) ? 'disabled' : ''); ?> />
				<label for="flush">flush</label>
				<input type="checkbox" id="global" name="global" value="global" <?php echo (isset($this->rule['global']) && $this->rule['global'] ? 'checked' : ''); ?> <?php echo (!isset($this->rule['flush']) ? 'disabled' : ''); ?> />
				<label for="global">global</label>
				<?php $this->editHelp('overload') ?>
			</td>
		</tr>
		<?php
	}

	function editSourceTrack()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Enable Source Track').':' ?>
			</td>
			<td>
				<input type="checkbox" id="source-track" name="source-track" value="source-track" <?php echo (isset($this->rule['source-track']) && $this->rule['source-track'] ? 'checked' : ''); ?> />
				<select id="source-track-option" name="source-track-option" <?php echo (!isset($this->rule['source-track']) ? 'disabled' : ''); ?>>
					<option value=""></option>
					<option value="rule" <?php echo (isset($this->rule['source-track-option']) && $this->rule['source-track-option'] == 'rule' ? 'selected' : ''); ?>>rule</option>
					<option value="global" <?php echo (isset($this->rule['source-track-option']) && $this->rule['source-track-option'] == 'global' ? 'selected' : ''); ?>>global</option>
				</select>
				<?php $this->editHelp('source-track') ?>
			</td>
		</tr>
		<?php
	}
}
?>
