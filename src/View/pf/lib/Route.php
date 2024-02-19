<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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

class Route extends Filter
{
	function setType()
	{
		/// @attention Set a default type to get an initial test result as FALSE, otherwise $testResult becomes TRUE and #forcesave remains disabled.
		$this->rule['type']= 'route-to';
	}

	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispAction();
		$this->dispValue('direction', _TITLE('Direction'));
		$this->dispInterface();
		$this->dispLog();
		$this->dispKey('quick', _TITLE('Quick'));
		$this->dispValue('proto', _TITLE('Proto'));
		$this->dispSrcDest();
		$this->dispValue('type', _TITLE('Type'));
		$this->dispValues('routehost', _TITLE('Route Host'));
		$this->dispTail($ruleNumber);
	}

	function input()
	{
		$this->inputAction();

		$this->inputFilterHead();

		$this->inputLog();
		$this->inputBool('quick');

		$this->inputKey('type');
		$this->inputDel('routehost', 'delRouteHost');
		$this->inputAdd('routehost', 'addRouteHost');
		$this->inputPoolType();

		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editAction();

		$this->editFilterHead();

		$this->editLog();
		$this->editCheckbox('quick', _TITLE('Quick'));

		$this->editRouteType();
		$this->editValues('routehost', _TITLE('Route Host'), 'delRouteHost', 'addRouteHost', _CONTROL('ip, host, table or macro'), 'Nat', NULL);
		$this->editPoolType();

		$this->editFilterOpts();

		$this->editComment();
		$this->editTail();
	}

	function editRouteType()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Route Type').':' ?>
			</td>
			<td>
				<select id="type" name="type">
					<option value="dup-to" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'dup-to' ? 'selected' : ''); ?>>dup-to</option>
					<option value="reply-to" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'reply-to' ? 'selected' : ''); ?>>reply-to</option>
					<option value="route-to" <?php echo (isset($this->rule['type']) && $this->rule['type'] == 'route-to' ? 'selected' : ''); ?>>route-to</option>
				</select>
				<?php $this->editHelp($this->rule['type']) ?>
			</td>
		</tr>
		<?php
	}
}
?>
