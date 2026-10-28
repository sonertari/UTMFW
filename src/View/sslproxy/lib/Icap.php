<?php
/*
 * Copyright (C) 2004-2026 Soner Tari
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

class Icap extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispIcap();
		$this->dispTail($ruleNumber);
	}
	
	function dispIcap()
	{
		?>
		<td class="include">
			<?php echo 'Icap' ?>
		</td>
		<td title="<?php echo _TITLE('Service') ?>" colspan="3">
			<?php
			if (isset($this->rule['service'])) {
				echo $this->rule['service'];
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('service');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action, 'sslproxy.conf');

		$this->editText('service', _TITLE('Service'), FALSE, 200, _CONTROL('add value'));

		$this->editComment();
		$this->editTail();
	}
}
?>
