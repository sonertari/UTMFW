<?php 
/*
 * Copyright (C) 2004-2022 Soner Tari
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

class Option extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispOption();
		$this->dispTail($ruleNumber);
	}
	
	function dispOption()
	{
		?>
		<td title="<?php echo _TITLE('Option') ?>" colspan="12">
			<?php
			if (isset($this->rule['type'])) {
				$value= isset($this->rule[$this->rule['type']]) ? $this->rule[$this->rule['type']] : '';
				if (in_array($this->rule['type'], array('loginterface', 'optimization', 'ruleset-optimization', 'block-policy', 'state-policy', 'debug', 'fingerprints', 'hostid'))) {
					echo $this->rule['type'] . ": $value";
				} elseif ($this->rule['type'] == 'skip') {
					if (!is_array($value)) {
						echo "skip on $value";
					} else {
						echo 'skip on ' . implode(', ', $value);
					}
				} elseif ($this->rule['type'] == 'reassemble') {
					echo $this->rule['type'] . ": $value";
					if (isset($this->rule['no-df'])) {
						echo ' no-df';
					}
				} elseif ($this->rule['type'] == 'syncookies') {
					echo $this->rule['type'] . ": $value";
					if ($value === 'adaptive') {
						echo ' (start ' . (isset($this->rule['start']) ? $this->rule['start'] : '') . ', end ' . (isset($this->rule['end']) ? $this->rule['end'] : '') . ')';
					}
				}
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('block-policy');
		$this->inputKey('optimization');
		$this->inputKey('ruleset-optimization');
		$this->inputKey('state-policy');
		$this->inputKey('fingerprints');
		$this->inputKey('hostid');
		$this->inputKey('loginterface');
		$this->inputKey('debug');
		$this->inputDel('skip', 'delSkip');
		$this->inputAdd('skip', 'addSkip');
		$this->inputKey('reassemble');
		$this->inputBool('no-df');
		$this->inputKey('syncookies');
		$this->inputKey('start');
		$this->inputKey('end');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		if (filter_has_var(INPUT_POST, 'state') && filter_has_var(INPUT_POST, 'type')) {
			$this->rule['type']= filter_input(INPUT_POST, 'type');
		}

		if (!isset($this->rule['type'])) {
			$this->editSelectOption();
			/// @todo Should we disable the Save button by turning $modified off?
			// Otherwise, the user can save a blank option rule. But this may not be a mistake.
			//$modified= FALSE;
		}

		$this->editBlockPolicy();
		$this->editOptimization();
		$this->editRulesetOptimization();
		$this->editStatePolicy();
		$this->editFingerprints();
		$this->editHostid();
		$this->editLogInterface();
		$this->editDebug();
		$this->editSkip();
		$this->editReassemble();
		$this->editSyncookies();

		if (isset($this->rule['type'])) {
			$this->editComment();
		}
		$this->editTail();
	}

	function editSelectOption()
	{
		?>
		<tr class="oddline">
			<td class="title">
				<?php echo _TITLE('Select Option Type').':' ?>
			</td>
			<td>
				<select id="type" name="type">
					<option value="block-policy" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'block-policy' ? 'selected' : ''); ?>>block-policy</option>
					<option value="optimization" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'optimization' ? 'selected' : ''); ?>>optimization</option>
					<option value="ruleset-optimization" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'ruleset-optimization' ? 'selected' : ''); ?>>ruleset-optimization</option>
					<option value="state-policy" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'state-policy' ? 'selected' : ''); ?>>state-policy</option>
					<option value="fingerprints" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'fingerprints' ? 'selected' : ''); ?>>fingerprints</option>
					<option value="hostid" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'hostid' ? 'selected' : ''); ?>>hostid</option>
					<option value="loginterface" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'loginterface' ? 'selected' : ''); ?>>loginterface</option>
					<option value="debug" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'debug' ? 'selected' : ''); ?>>debug</option>
					<option value="skip" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'skip' ? 'selected' : ''); ?>>skip</option>
					<option value="reassemble" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'reassemble' ? 'selected' : ''); ?>>reassemble</option>
					<option value="syncookies" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'syncookies' ? 'selected' : ''); ?>>syncookies</option>
				</select>
			</td>
		</tr>
		<?php
	}

	function editBlockPolicy()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'block-policy') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Block Policy').':' ?>
				</td>
				<td>
					<select id="block-policy" name="block-policy">
						<option value="drop" label="drop" <?php echo (isset($this->rule['block-policy']) && $this->rule['block-policy'] == 'drop' ? 'selected' : ''); ?>>drop</option>
						<option value="return" label="return" <?php echo (isset($this->rule['block-policy']) && $this->rule['block-policy'] == 'return' ? 'selected' : ''); ?>>return</option>
					</select>
					<?php $this->editHelp('block-policy') ?>
				</td>
			</tr>
			<?php
		}
	}

	function editOptimization()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'optimization') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Optimization').':' ?>
				</td>
				<td>
					<select id="optimization" name="optimization">
						<option value="normal" <?php echo (isset($this->rule['optimization']) && $this->rule['optimization'] == 'normal' ? 'selected' : ''); ?>>normal</option>
						<option value="high-latency" <?php echo (isset($this->rule['optimization']) && $this->rule['optimization'] == 'high-latency' ? 'selected' : ''); ?>>high-latency</option>
						<option value="satellite" <?php echo (isset($this->rule['optimization']) && $this->rule['optimization'] == 'satellite' ? 'selected' : ''); ?>>satellite</option>
						<option value="aggressive" <?php echo (isset($this->rule['optimization']) && $this->rule['optimization'] == 'aggressive' ? 'selected' : ''); ?>>aggressive</option>
						<option value="conservative" <?php echo (isset($this->rule['optimization']) && $this->rule['optimization'] == 'conservative' ? 'selected' : ''); ?>>conservative</option>
					</select>
					<?php $this->editHelp('optimization') ?>
				</td>
			</tr>
			<?php
		}
	}

	function editRulesetOptimization()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'ruleset-optimization') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Ruleset Optimization').':' ?>
				</td>
				<td>
					<select id="ruleset-optimization" name="ruleset-optimization">
						<option value="none" <?php echo (isset($this->rule['ruleset-optimization']) && $this->rule['ruleset-optimization'] == 'none' ? 'selected' : ''); ?>>none</option>
						<option value="basic" <?php echo (isset($this->rule['ruleset-optimization']) && $this->rule['ruleset-optimization'] == 'basic' ? 'selected' : ''); ?>>basic</option>
						<option value="profile" <?php echo (isset($this->rule['ruleset-optimization']) && $this->rule['ruleset-optimization'] == 'profile' ? 'selected' : ''); ?>>profile</option>
					</select>
					<?php $this->editHelp('ruleset-optimization') ?>
				</td>
			</tr>
			<?php
		}
	}

	function editStatePolicy()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'state-policy') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('State Policy').':' ?>
				</td>
				<td>
					<select id="state-policy" name="state-policy">
						<option value="if-bound" <?php echo (isset($this->rule['state-policy']) && $this->rule['state-policy'] == 'if-bound' ? 'selected' : ''); ?>>if-bound</option>
						<option value="floating" <?php echo (isset($this->rule['state-policy']) && $this->rule['state-policy'] == 'floating' ? 'selected' : ''); ?>>floating</option>
					</select>
					<?php $this->editHelp('state-policy') ?>
				</td>
			</tr>
			<?php
		}
	}

	function editFingerprints()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'fingerprints') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Fingerprints File').':' ?>
				</td>
				<td>
					<input type="text" size="50" id="fingerprints" name="fingerprints" value="<?php echo isset($this->rule['fingerprints']) ? $this->rule['fingerprints'] : ''; ?>" placeholder="<?php echo _CONTROL('filename') ?>"/>
					<?php $this->editHelp('fingerprints') ?>
				</td>
			</tr>
			<?php
		}
	}

	function editHostid()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'hostid') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Host Id').':' ?>
				</td>
				<td>
					<input type="text" size="20" id="hostid" name="hostid" value="<?php echo isset($this->rule['hostid']) ? $this->rule['hostid'] : ''; ?>"  placeholder="<?php echo _CONTROL('number') ?>"/>
					<?php $this->editHelp('hostid') ?>
				</td>
			</tr>
			<?php
		}
	}

	function editLogInterface()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'loginterface') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Log Interface').':' ?>
				</td>
				<td>
					<input type="text" size="10" id="loginterface" name="loginterface" value="<?php echo isset($this->rule['loginterface']) ? $this->rule['loginterface'] : ''; ?>"  placeholder="<?php echo _CONTROL('interface') ?>"/>
					<?php $this->editHelp('loginterface') ?>
				</td>
			</tr>
			<?php
		}
	}

	function editDebug()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'debug') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Debug').':' ?>
				</td>
				<td>
					<select id="debug" name="debug">
						<option value="emerg" <?php echo (isset($this->rule['debug']) && $this->rule['debug'] == 'emerg' ? 'selected' : ''); ?>>emerg</option>
						<option value="alert" <?php echo (isset($this->rule['debug']) && $this->rule['debug'] == 'alert' ? 'selected' : ''); ?>>alert</option>
						<option value="crit" <?php echo (isset($this->rule['debug']) && $this->rule['debug'] == 'crit' ? 'selected' : ''); ?>>crit</option>
						<option value="err" <?php echo (isset($this->rule['debug']) && $this->rule['debug'] == 'err' ? 'selected' : ''); ?>>err</option>
						<option value="warning" <?php echo (isset($this->rule['debug']) && $this->rule['debug'] == 'warning' ? 'selected' : ''); ?>>warning</option>
						<option value="notice" <?php echo (isset($this->rule['debug']) && $this->rule['debug'] == 'notice' ? 'selected' : ''); ?>>notice</option>
						<option value="info" <?php echo (isset($this->rule['debug']) && $this->rule['debug'] == 'info' ? 'selected' : ''); ?>>info</option>
						<option value="debug" <?php echo (isset($this->rule['debug']) && $this->rule['debug'] == 'debug' ? 'selected' : ''); ?>>debug</option>
					</select>
					<?php $this->editHelp('debug') ?>
				</td>
			</tr>
			<?php
		}
	}

	function editSkip()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'skip') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Skip Interfaces').':' ?>
				</td>
				<td>
					<?php
					if (isset($this->rule['skip'])) {
						$this->editDeleteValueLinks($this->rule['skip'], 'delSkip');
					}
					$this->editAddValueBox('addSkip', NULL, _CONTROL('if or macro'), 40);
					$this->editHelp('skip');
					?>
				</td>
			</tr>
			<?php
		}
	}

	function editReassemble()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'reassemble') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Reassemble').':' ?>
				</td>
				<td>
					<select id="reassemble" name="reassemble">
						<option value="yes" <?php echo (isset($this->rule['reassemble']) && $this->rule['reassemble'] == 'yes' ? 'selected' : ''); ?>><?php echo _CONTROL('yes') ?></option>
						<option value="no" <?php echo (isset($this->rule['reassemble']) && $this->rule['reassemble'] == 'no' ? 'selected' : ''); ?>><?php echo _CONTROL('no') ?></option>
					</select>
					<?php $this->editHelp('reassemble') ?>
					<input type="checkbox" id="no-df" name="no-df" value="no-df" <?php echo (isset($this->rule['no-df']) && $this->rule['no-df'] ? 'checked' : ''); ?> />
					<label for="no-df">no-df</label>
				</td>
			</tr>
			<?php
		}
	}

	function editSyncookies()
	{
		if (isset($this->rule['type']) && $this->rule['type'] == 'syncookies') {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('Syncookies').':' ?>
				</td>
				<td>
					<select id="syncookies" name="syncookies">
						<option value="never" <?php echo (isset($this->rule['syncookies']) && $this->rule['syncookies'] == 'never' ? 'selected' : ''); ?>><?php echo _CONTROL('never') ?></option>
						<option value="always" <?php echo (isset($this->rule['syncookies']) && $this->rule['syncookies'] == 'always' ? 'selected' : ''); ?>><?php echo _CONTROL('always') ?></option>
						<option value="adaptive" <?php echo (isset($this->rule['syncookies']) && $this->rule['syncookies'] == 'adaptive' ? 'selected' : ''); ?>><?php echo _CONTROL('adaptive') ?></option>
					</select>
					<input type="text" id="start" name="start" value="<?php echo isset($this->rule['start']) ? $this->rule['start'] : ''; ?>" size="10" placeholder="<?php echo _CONTROL('number%') ?>" <?php echo isset($this->rule['syncookies']) && $this->rule['syncookies'] !== 'adaptive' ? 'disabled' : ''; ?> />
					<label for="start">start</label>
					<input type="text" id="end" name="end" value="<?php echo isset($this->rule['end']) ? $this->rule['end'] : ''; ?>" size="10" placeholder="<?php echo _CONTROL('number%') ?>" <?php echo isset($this->rule['syncookies']) && $this->rule['syncookies'] !== 'adaptive' ? 'disabled' : ''; ?> />
					<label for="end">end</label>
					<?php $this->editHelp('syncookies') ?>
				</td>
			</tr>
			<?php
		}
	}
}
?>
