<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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

class ProxySpecLine extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispProto();
		$this->dispSpec();
		$this->dispTail($ruleNumber);
	}
	
	function dispProto()
	{
		?>
		<td class="proxyspec">
			<?php echo 'ProxySpec' ?>
		</td>
		<td title="<?php echo _TITLE('Proto') ?>" class="<?php echo isset($this->rule['proto']) ? strtolower($this->rule['proto']) : ''; ?>" nowrap="nowrap">
			<?php
			if (isset($this->rule['proto'])) {
				echo $this->rule['proto'];
			}
			?>
		</td>
		<?php
	}

	function dispSpec()
	{
		?>
		<td title="Specification" colspan="2">
			<?php
			$s= '';
			if (isset($this->rule['addr'])) {
				$s.= ' '.$this->rule['addr'];
			}
			if (isset($this->rule['port'])) {
				$s.= ' '.$this->rule['port'];
			}
			if (isset($this->rule['divertport'])) {
				$s.= ' up:'.$this->rule['divertport'];
			}
			if (isset($this->rule['divertaddress'])) {
				$s.= ' ua:'.$this->rule['divertaddress'];
			}
			if (isset($this->rule['returnaddress'])) {
				$s.= ' ra:'.$this->rule['returnaddress'];
			}
			if (isset($this->rule['natengine'])) {
				$s.= ' '.$this->rule['natengine'];
			}
			if (isset($this->rule['targetaddress'])) {
				$s.= ' '.$this->rule['targetaddress'];
			}
			if (isset($this->rule['targetport'])) {
				$s.= ' '.$this->rule['targetport'];
			}
			if (isset($this->rule['sniport'])) {
				$s.= ' sni '.$this->rule['sniport'];
			}
			echo trim($s, ', ');
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('proto');
		$this->inputKey('addr');
		$this->inputKey('port');
		$this->inputKey('divertport');
		$this->inputKey('divertaddress');
		$this->inputKey('returnaddress');
		$this->inputKey('natengine');
		$this->inputKey('targetaddress');
		$this->inputKey('targetport');
		$this->inputKey('sniport');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action, 'sslproxy.conf');

		$this->editProto();
		$this->editText('addr', _TITLE('Address'), FALSE, NULL, _CONTROL('ip address'));
		$this->editText('port', _TITLE('Port'), FALSE, NULL, _CONTROL('port number'));
		$this->editText('divertport', _TITLE('Divert Port'), FALSE, NULL, _CONTROL('port number'));
		$this->editText('divertaddress', _TITLE('Divert Address'), FALSE, NULL, _CONTROL('ip address'));
		$this->editText('returnaddress', _TITLE('Return Address'), FALSE, NULL, _CONTROL('ip address'));
		$this->editText('natengine', _TITLE('NAT Engine'), FALSE, NULL, _CONTROL('pf, ipfw, netfilter, or tproxy'));
		$this->editText('targetaddress', _TITLE('Target Address'), FALSE, NULL, _CONTROL('ip address or host name'));
		$this->editText('targetport', _TITLE('Target Port'), FALSE, NULL, _CONTROL('port number'));
		$this->editText('sniport', _TITLE('SNI Port'), FALSE, NULL, _CONTROL('port number'));

		$this->editComment();
		$this->editTail();
	}

	function editProto()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Proto').':' ?>
			</td>
			<td>
				<select id="proto" name="proto">
					<option label="tcp" <?php echo isset($this->rule['proto']) && $this->rule['proto'] == 'tcp' ? 'selected' : ''; ?>>tcp</option>
					<option label="ssl" <?php echo isset($this->rule['proto']) && $this->rule['proto'] == 'ssl' ? 'selected' : ''; ?>>ssl</option>
					<option label="http" <?php echo isset($this->rule['proto']) && $this->rule['proto'] == 'http' ? 'selected' : ''; ?>>http</option>
					<option label="https" <?php echo isset($this->rule['proto']) && $this->rule['proto'] == 'https' ? 'selected' : ''; ?>>https</option>
					<option label="pop3" <?php echo isset($this->rule['proto']) && $this->rule['proto'] == 'pop3' ? 'selected' : ''; ?>>pop3</option>
					<option label="pop3s" <?php echo isset($this->rule['proto']) && $this->rule['proto'] == 'pop3s' ? 'selected' : ''; ?>>pop3s</option>
					<option label="smtp" <?php echo isset($this->rule['proto']) && $this->rule['proto'] == 'smtp' ? 'selected' : ''; ?>>smtp</option>
					<option label="smtps" <?php echo isset($this->rule['proto']) && $this->rule['proto'] == 'smtps' ? 'selected' : ''; ?>>smtps</option>
					<option label="autossl" <?php echo isset($this->rule['proto']) && $this->rule['proto'] == 'autossl' ? 'selected' : ''; ?>>autossl</option>
				</select>
				<?php
				$this->editHelp('proto');
				?>
			</td>
		</tr>
		<?php
	}
}
?>
