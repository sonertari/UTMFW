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

class AfTo extends Filter
{
	function setType()
	{
		$this->rule['type']= 'af-to';
	}

	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispAction();
		$this->dispValue('direction', _TITLE('Direction'));
		$this->dispInterface();
		$this->dispLog();
		$this->dispValue('proto', _TITLE('Proto'));
		$this->dispSrcDest();
		$this->dispValue('rediraf', _TITLE('Redirect Address Family'));
		$this->dispValues('redirhost', _TITLE('From Redirect Host'));
		$this->dispValues('toredirhost', _TITLE('To Redirect Host'));
		$this->dispTail($ruleNumber, $count);
	}
	
	function input()
	{
		$this->inputAction();

		$this->inputFilterHead();

		$this->inputLog();
		$this->inputBool('quick');

		$this->inputKey('rediraf');
		$this->inputDel('redirhost', 'delRedirHost');
		$this->inputAdd('redirhost', 'addRedirHost');
		$this->inputDel('toredirhost', 'delToRedirHost');
		$this->inputAdd('toredirhost', 'addToRedirHost');

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

		$this->editRedirAf();
		$this->editValues('redirhost', _TITLE('From Redirect Host'), 'delRedirHost', 'addRedirHost', _CONTROL('ip, host, table or macro'), 'Nat', NULL);
		$this->editValues('toredirhost', _TITLE('To Redirect Host'), 'delToRedirHost', 'addToRedirHost', _CONTROL('ip, host, table or macro'), 'Nat', NULL);

		$this->editPoolType();

		$this->editFilterOpts();

		$this->editComment();
		$this->editTail();
	}

	function editRedirAf()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Redirect Address Family').':' ?>
			</td>
			<td>
				<select id="rediraf" name="rediraf">
					<option value="" label=""></option>
					<option value="inet" label="inet" <?php echo ($this->rule['rediraf'] == 'inet' ? 'selected' : ''); ?>>inet</option>
					<option value="inet6" label="inet6" <?php echo ($this->rule['rediraf'] == 'inet6' ? 'selected' : ''); ?>>inet6</option>
				</select>			
				<?php $this->editHelp('address-family') ?>
			</td>
		</tr>
		<?php
	}
}
?>
