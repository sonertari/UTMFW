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

class FilterBase extends State
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispAction();
		$this->dispValue('direction', _TITLE('Direction'));
		$this->dispInterface();
		$this->dispLog();
		$this->dispKey('quick', _TITLE('Quick'));
		$this->dispValue('proto', _TITLE('Proto'));
		$this->dispSrcDest();
		$this->dispValue('state-filter', _TITLE('State'));
		$this->dispQueue();
		$this->dispTail($ruleNumber, $count);
	}
	
	function dispAction()
	{
		?>
		<td title="<?php echo _TITLE('Action') ?>" class="<?php echo $this->rule['action']; ?>" nowrap="nowrap">
			<?php echo $this->rule['action']; ?>
		</td>
		<?php
	}

	function dispSrcDest()
	{
		if ($this->rule['all']) {
			?>
			<td title="<?php echo _TITLE('Source->Destination') ?>" colspan="4" class="all">
				all
			</td>
			<?php
		} else {
			?>
			<td title="<?php echo _TITLE('Source') ?>">
				<?php
				if (isset($this->rule['from'])) {
					$this->printHostPort($this->rule['from']);
				} elseif (isset($this->rule['fromroute'])) {
					echo 'route ' . $this->rule['fromroute'];
				}
				?>
			</td>
			<td title="<?php echo _TITLE('Source Port') ?>">
				<?php $this->printHostPort($this->rule['fromport']); ?>
			</td>
			<td title="<?php echo _TITLE('Destination') ?>">
				<?php
				if (isset($this->rule['to'])) {
					$this->printHostPort($this->rule['to']);
				} elseif (isset($this->rule['toroute'])) {
					echo 'route ' . $this->rule['toroute'];
				}
				?>
			</td>
			<td title="<?php echo _TITLE('Destination Port') ?>">
				<?php $this->printHostPort($this->rule['toport']); ?>
			</td>
			<?php
		}
	}

	function dispQueue()
	{
		?>
		<td title="<?php echo _TITLE('Queue') ?>">
			<?php echo isset($this->rule['queue']) ? (!is_array($this->rule['queue']) ? $this->rule['queue'] : $this->rule['queue'][0] . '<br>' . $this->rule['queue'][1]) : ''; ?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputFilterHead();
		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function inputFilterHead()
	{
		$this->inputKey('direction');

		$this->inputInterface();

		$this->inputKey('af');

		$this->inputDel('proto', 'delProto');
		$this->inputAdd('proto', 'addProto');

		$this->inputDel('from', 'delFrom');
		$this->inputAdd('from', 'addFrom');

		if (!$this->rule['from']) {
			$this->inputKey('fromroute');
		}

		$this->inputDel('fromport', 'delFromPort');
		$this->inputAdd('fromport', 'addFromPort');

		$this->inputDel('to', 'delTo');
		$this->inputAdd('to', 'addTo');

		if (!$this->rule['to']) {
			$this->inputKey('toroute');
		}

		$this->inputDel('toport', 'delToPort');
		$this->inputAdd('toport', 'addToPort');

		/// @attention Process 'all' after src and dest, because inputAll() unsets src and dest if necessary.
		$this->inputAll();

		$this->inputDel('os', 'delOs');
		$this->inputAdd('os', 'addOs');
	}

	function inputFilterOpts()
	{
		$this->inputKey('state-filter');
		$this->inputState();

		$this->inputKey('flags');
		$this->inputQueue();

		$this->inputDel('icmp-type', 'delIcmpType');
		$this->inputAddIcmpType('icmp');

		$this->inputDel('icmp6-type', 'delIcmp6Type');
		$this->inputAddIcmpType('icmp6');
		
		$this->inputBool('fragment');
		$this->inputBool('allow-opts');
		$this->inputBool('once');
		$this->inputBool('divert-reply');
		
		$this->inputDel('user', 'delUser');
		$this->inputAdd('user', 'addUser');

		$this->inputDel('group', 'delGroup');
		$this->inputAdd('group', 'addGroup');

		$this->inputKey('label');
		$this->inputKey('tag');
		$this->inputKey('tagged');
		$this->inputBool('not-tagged');

		$this->inputKey('tos');
		$this->inputKey('set-tos');
		$this->inputKey('prio');

		$this->inputDel('set-prio', 'delPrio');
		$this->inputAdd('set-prio', 'addPrio');

		$this->inputKey('set-delay');
		$this->inputKey('max-pkt-rate');
		$this->inputKey('probability');

		$this->inputKey('rtable');
		$this->inputKey('received-on');
		$this->inputBool('not-received-on');
	}

	function inputQueue()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if (filter_has_var(INPUT_POST, 'queuePri') && filter_input(INPUT_POST, 'queuePri') !== '' &&
				filter_has_var(INPUT_POST, 'queueSec') && filter_input(INPUT_POST, 'queueSec') !== '') {
				$this->rule['queue']= array();
				$this->rule['queue'][0]= filter_input(INPUT_POST, 'queuePri');
				$this->rule['queue'][1]= filter_input(INPUT_POST, 'queueSec');
			} elseif (filter_has_var(INPUT_POST, 'queuePri') && filter_input(INPUT_POST, 'queuePri') !== '') {
				$this->rule['queue']= filter_input(INPUT_POST, 'queuePri');
			} else {
				unset($this->rule['queue']);
			}
		}
	}

	function inputAddIcmpType($key)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$typeVar= $key . '-type';
			if (filter_has_var(INPUT_POST, $typeVar) && filter_input(INPUT_POST, $typeVar) !== '') {
				$value= filter_input(INPUT_POST, $typeVar);

				$codeVar= $key . '-code';
				if (filter_has_var(INPUT_POST, $codeVar) && filter_input(INPUT_POST, $codeVar) !== '') {
					$value.= ' code ' . filter_input(INPUT_POST, $codeVar);
				}

				$this->inputAddValue($key . '-type', $value);
			}
		}
	}

	function inputAll()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if (filter_has_var(INPUT_POST, 'all')) {
				$this->rule['all']= TRUE;
				unset($this->rule['from']);
				unset($this->rule['fromroute']);
				unset($this->rule['fromport']);
				unset($this->rule['to']);
				unset($this->rule['toroute']);
				unset($this->rule['toport']);
			} else {
				unset($this->rule['all']);
			}
		}
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editFilterHead();
		$this->editFilterOpts();

		$this->editComment();
		$this->editTail();
	}

	function editFilterHead()
	{
		$this->editDirection();
		$this->editInterface();
		$this->editAf();
		$this->editValues('proto', _TITLE('Protocol'), 'delProto', 'addProto', _CONTROL('protocol'), NULL, 10);
		$this->editCheckbox('all', _TITLE('Match All'));
		$this->editHost('from', _TITLE('Source'), 'delFrom', 'addFrom', _CONTROL('ip, host, table or macro'), 'src-dst', NULL, isset($this->rule['all']));
		$this->editValues('fromport', _TITLE('Source Port'), 'delFromPort', 'addFromPort', _CONTROL('number, name, table or macro'), FALSE, NULL, isset($this->rule['all']));
		$this->editHost('to', _TITLE('Destination'), 'delTo', 'addTo', _CONTROL('ip, host, table or macro'), FALSE, NULL, isset($this->rule['all']));
		$this->editValues('toport', _TITLE('Destination Port'), 'delToPort', 'addToPort', _CONTROL('number, name, table or macro'), FALSE, NULL, isset($this->rule['all']));
		$this->editValues('os', _TITLE('OS'), 'delOs', 'addOs', _CONTROL('os name or macro'));
	}

	function editFilterOpts()
	{
		$this->editStateFilter();
		$this->editText('flags', _TITLE('TCP Flags'), NULL, 20, _CONTROL('defaults to S/SA'));
		$this->editQueue();
		$this->editIcmpType('icmp', _TITLE('ICMP Type'), 'delIcmpType', 'addIcmpType');
		$this->editIcmpType('icmp6', _TITLE('ICMP6 Type'), 'delIcmp6Type', 'addIcmp6Type');
		
		$this->editCheckbox('fragment', _TITLE('Fragment'));
		$this->editCheckbox('allow-opts', _TITLE('Allow Opts'));
		$this->editCheckbox('once', _TITLE('Once'));
		$this->editCheckbox('divert-reply', _TITLE('Divert Reply'));
		
		$this->editValues('user', _TITLE('User'), 'delUser', 'addUser', _CONTROL('username or userid'));
		$this->editValues('group', _TITLE('Group'), 'delGroup', 'addGroup', _CONTROL('groupname or groupid'));
		$this->editText('label', _TITLE('Label'), NULL, NULL, _CONTROL('string'));
		$this->editTagged();
		$this->editText('tag', _TITLE('Assign Tag'), NULL, NULL, _CONTROL('string'));
		$this->editText('tos', _TITLE('Match TOS'), NULL, NULL, _CONTROL('string or number'));
		$this->editText('set-tos', _TITLE('Enforce TOS'), NULL, NULL, _CONTROL('string or number'));
		$this->editText('prio', _TITLE('Match Priority'), NULL, 10, _CONTROL('number 0-7'));
		$this->editValues('set-prio', _TITLE('Assign Priority'), 'delPrio', 'addPrio', _CONTROL('number 0-7'), NULL, 10);
		$this->editText('set-delay', _TITLE('Delay Packets'), NULL, NULL, _CONTROL('milliseconds'));
		$this->editText('max-pkt-rate', _TITLE('Max Packet Rate'), NULL, 10, _CONTROL('number/seconds'));
		$this->editText('probability', _TITLE('Probability'), NULL, 10, _CONTROL('0-100% or 0-1'));
		$this->editText('rtable', _TITLE('Routing Table'), NULL, 10, _CONTROL('number'));
		$this->editReceivedOn();
	}

	function editDirection()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Direction').':' ?>
			</td>
			<td>
				<select id="direction" name="direction">
					<option value="" label=""></option>
					<option value="in" label="in" <?php echo ($this->rule['direction'] == 'in' ? 'selected' : ''); ?>>in</option>
					<option value="out" label="out" <?php echo ($this->rule['direction'] == 'out' ? 'selected' : ''); ?>>out</option>
				</select>
				<?php $this->editHelp('direction') ?>
			</td>
		</tr>
		<?php
	}

	function editHost($key, $title, $delName, $addName, $hint, $help= NULL, $size= 0, $disabled= FALSE)
	{
		$help= $help === NULL ? $key : $help;
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<?php
				$this->editDeleteValueLinks($this->rule[$key], $delName);
				$this->editAddValueBox($addName, NULL, $hint, $size, $disabled || isset($this->rule[$key . 'route']));
				?>
				<input type="text" id="<?php echo $key . 'route' ?>" name="<?php echo $key . 'route' ?>" value="<?php echo $this->rule[$key . 'route']; ?>" size="20" placeholder="<?php echo _CONTROL('label') ?>" <?php echo $disabled || isset($this->rule[$key]) ? 'disabled' : ''; ?> />
				<label for="<?php echo $key . 'route' ?>">route</label>
				<?php
				if ($help !== FALSE) {
					$this->editHelp($help);
				}
				?>
			</td>
		</tr>
		<?php
	}

	function editStateFilter()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Stateful Filtering').':' ?>
			</td>
			<td>
				<select id="state-filter" name="state-filter">
					<option value=""></option>
					<option value="no" <?php echo ($this->rule['state-filter'] == 'no' ? 'selected' : ''); ?>>No State</option>
					<option value="keep" <?php echo ($this->rule['state-filter'] == 'keep' ? 'selected' : ''); ?>>Keep State</option>
					<option value="modulate" <?php echo ($this->rule['state-filter'] == 'modulate' ? 'selected' : ''); ?>>Modulate State</option>
					<option value="synproxy" <?php echo ($this->rule['state-filter'] == 'synproxy' ? 'selected' : ''); ?>>Synproxy</option>
				</select>
				<?php $this->editHelp('state-filter') ?>
			</td>
		</tr>
		<?php
		if (isset($this->rule['state-filter'])) {
			$this->editState();
		}
	}

	function editIcmpType($key, $title, $delName, $addName)
	{
		$help= $key;
		if (isset($this->rule['proto']) && ($this->rule['proto'] == $key || is_array($this->rule['proto']) && in_array($key, $this->rule['proto']))) {
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo $title . ':' ?>
				</td>
				<td>
					<?php
					$this->editDeleteValueLinks($this->rule[$key . '-type'], $delName);
					?>
					<input type="text" id="<?php echo $key; ?>-type" name="<?php echo $key; ?>-type" placeholder="<?php echo _CONTROL('number or name') ?>" />
					<label for="<?php echo $key; ?>-type">type</label>
					<input type="text" id="<?php echo $key; ?>-code" name="<?php echo $key; ?>-code" placeholder="<?php echo _CONTROL('number or name') ?>" />
					<label for="<?php echo $key; ?>-code">code</label>
					<?php
					$this->editHelp($key. '-type');
					?>
				</td>
			</tr>
			<?php
		}
	}

	function editQueue()
	{
		global $View;
		
		$queueNames= $View->RuleSet->getQueueNames();
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Queue').':' ?>
			</td>
			<td>
				<select id="queuePri" name="queuePri">
				<?php
				if (count($queueNames) == 0) {
					?>
					<option value="" disabled><?php echo _CONTROL('No Queues defined') ?></option>
					<?php
				} else {
					?>
					<option value=""><?php echo _CONTROL('none') ?></option>
					<?php
					if (!is_array($this->rule['queue'])) {
						$queuePri= $this->rule['queue'];
					} else {
						$queuePri= $this->rule['queue'][0];
					}
					foreach ($queueNames as $queue) {
						?>
						<option value="<?php echo $queue; ?>" <?php echo $queuePri == $queue ? 'selected' : ''; ?>><?php echo $queue; ?></option>
						<?php
					}
				}
				?>
				</select>
				<?php echo _TITLE('primary') ?>

				<select id="queueSec" name="queueSec">
				<?php
				if (count($queueNames) == 0) {
					?>
					<option value="" disabled><?php echo _CONTROL('No Queues defined') ?></option>
					<?php
				} else {
					?>
					<option value=""><?php echo _CONTROL('none') ?></option>
					<?php
					if (isset($this->rule['queue'])) {
						foreach ($queueNames as $queue) {
							?>
							<option value="<?php echo $queue; ?>" <?php echo $this->rule['queue'][1] == $queue ? 'selected' : ''; ?>><?php echo $queue; ?></option>
							<?php
						}
					}
				}
				?>
				</select>	
				<?php echo _TITLE('secondary') ?>
				<?php $this->editHelp('filter-queue') ?>
			</td>
		</tr>
		<?php
	}

	function editTagged()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Match Tagged') . ':' ?>
			</td>
			<td>
				<input type="text" id="tagged" name="tagged" value="<?php echo $this->rule['tagged']; ?>" placeholder="<?php echo _CONTROL('string') ?>" />
				<?php $this->editHelp('tagged'); ?>
				<input type="checkbox" id="not-tagged" name="not-tagged" value="not-tagged" <?php echo ($this->rule['not-tagged'] ? 'checked' : ''); ?> <?php echo (!isset($this->rule['tagged']) ? 'disabled' : ''); ?> />
				<label for="not-tagged"><?php echo _TITLE('negated') ?></label>
			</td>
		</tr>
		<?php
	}

	function editReceivedOn()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Received on Interface') . ':' ?>
			</td>
			<td>
				<input type="text" id="received-on" name="received-on" value="<?php echo $this->rule['received-on']; ?>" size="10" placeholder="<?php echo _CONTROL('if or macro') ?>" />
				<?php $this->editHelp('received-on'); ?>
				<input type="checkbox" id="not-received-on" name="not-received-on" value="not-received-on" <?php echo ($this->rule['not-received-on'] ? 'checked' : ''); ?> <?php echo (!isset($this->rule['received-on']) ? 'disabled' : ''); ?> />
				<label for="not-received-on"><?php echo _TITLE('negated') ?></label>
			</td>
		</tr>
		<?php
	}
}
?>
