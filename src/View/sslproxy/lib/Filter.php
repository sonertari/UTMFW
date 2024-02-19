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

class Filter extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber, $count);
		$this->dispAction();
		$this->dispFrom();
		$this->dispTo();
		$this->dispLog();
		$this->dispTail($ruleNumber);
	}
	
	function dispAction()
	{
		?>
		<td title="<?php echo _TITLE('Action') ?>" class="<?php echo isset($this->rule['action']) ? strtolower($this->rule['action']) : ''; ?>" nowrap="nowrap">
			<?php
			if (isset($this->rule['action'])) {
				echo $this->rule['action'];
			}
			?>
		</td>
		<?php
	}

	function dispFrom($colspan= 1)
	{
		?>
		<td title="From" colspan="<?php echo $colspan; ?>">
			<?php
			$s= '';
			if (isset($this->rule['all'])) {
				$s.= 'all';
			}
			if (isset($this->rule['from_all'])) {
				$s.= ', all';
			}
			if (isset($this->rule['user'])) {
				$s.= ', user: '.$this->rule['user'];
			}
			if (isset($this->rule['desc'])) {
				$s.= ', desc: '.$this->rule['desc'];
			}
			if (isset($this->rule['src_ip'])) {
				$s.= ', ip: '.$this->rule['src_ip'];
			}
			echo trim($s, ', ');
			?>
		</td>
		<?php
	}

	function dispTo($colspan= 1)
	{
		?>
		<td title="To" colspan="<?php echo $colspan; ?>">
			<?php
			$s= '';
			if (isset($this->rule['to_all'])) {
				$s.= 'all';
			}
			if (isset($this->rule['sni'])) {
				$s.= ', sni: '.$this->rule['sni'];
			}
			if (isset($this->rule['cn'])) {
				$s.= ', cn: '.$this->rule['cn'];
			}
			if (isset($this->rule['host'])) {
				$s.= ', host: '.$this->rule['host'];
			}
			if (isset($this->rule['uri'])) {
				$s.= ', uri: '.$this->rule['uri'];
			}
			if (isset($this->rule['dst_ip'])) {
				$s.= ', ip: '.$this->rule['dst_ip'];
			}
			if (isset($this->rule['port'])) {
				$s.= ', port: '.$this->rule['port'];
			}
			echo trim($s, ', ');
			?>
		</td>
		<?php
	}

	function dispLog($colspan= 1)
	{
		?>
		<td title="Log" colspan="<?php echo $colspan; ?>">
			<?php
			if (isset($this->rule['log'])) {
				$s= '';
				if (is_array($this->rule['log'])) {
					foreach ($this->rule['log'] as $v) {
						$s.= ", $v";
					}
				}
				echo trim($s, ', ');
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputReset();

		$this->inputKey('action');

		$this->inputKey('all');

		$this->inputKey('from_all');
		$this->inputKey('user');
		$this->inputKey('desc');
		$this->inputKey('src_ip');

		$this->inputKey('to_all');
		$this->inputKey('sni');
		$this->inputKey('cn');
		$this->inputKey('host');
		$this->inputKey('uri');
		$this->inputKey('dst_ip');
		$this->inputKey('port');

		$this->inputLog('log-all');
		$this->inputLog('log-connect');
		$this->inputLog('log-master');
		$this->inputLog('log-cert');
		$this->inputLog('log-content');
		$this->inputLog('log-pcap');
		$this->inputLog('log-mirror');
		$this->inputLog('log-none');
		$this->inputLog('log-noconnect');
		$this->inputLog('log-nomaster');
		$this->inputLog('log-nocert');
		$this->inputLog('log-nocontent');
		$this->inputLog('log-nopcap');
		$this->inputLog('log-nomirror');

		$this->inputKey('comment');
		$this->inputDelEmpty();

		$this->inputSet();
	}

	function inputLog($key)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$value= trim(filter_input(INPUT_POST, $key), "\" \t\n\r\0\x0B");
			$this->rule['log'][]= $value;
		}
	}

	function inputReset()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			unset($this->rule['from']);
			unset($this->rule['to']);

			unset($this->rule['log']);
			$this->rule['log']= array();
		}
	}

	function inputSet()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$from= isset($this->rule['from_all']) + isset($this->rule['user']) + isset($this->rule['desc']) + isset($this->rule['src_ip']);
			$to = isset($this->rule['to_all']) + isset($this->rule['sni']) + isset($this->rule['cn']) + isset($this->rule['host']) +
					isset($this->rule['uri']) + isset($this->rule['dst_ip']) + isset($this->rule['port']);

			if ($from > 0)
				$this->rule['from']= 1;
			if ($to > 0)
				$this->rule['to']= 1;

 			if (isset($this->rule['all']) ||
					((isset($this->rule['from_all']) || isset($this->rule['src_ip'])) && $from > 1) ||
					((isset($this->rule['user']) || isset($this->rule['desc'])) && $from > 2)) {
				unset($this->rule['from']);
				unset($this->rule['from_all']);
				unset($this->rule['user']);
				unset($this->rule['desc']);
				unset($this->rule['src_ip']);
			}

			if (isset($this->rule['all']) ||
					(isset($this->rule['to_all']) && $to > 1) || 
					(!isset($this->rule['to_all']) && !isset($this->rule['port']) && $to > 1) ||
					(!isset($this->rule['to_all']) && isset($this->rule['port']) && $to > 2)) {
				unset($this->rule['to']);
				unset($this->rule['to_all']);
				unset($this->rule['sni']);
				unset($this->rule['cn']);
				unset($this->rule['host']);
				unset($this->rule['uri']);
				unset($this->rule['dst_ip']);
				unset($this->rule['port']);
			}

			if (isset($this->rule['log'])) {
				if (in_array('*', $this->rule['log'])) {
					unset($this->rule['log']);
					$this->rule['log']= array('*');
				}
				else if (in_array('!*', $this->rule['log'])) {
					unset($this->rule['log']);
					$this->rule['log']= array('!*');
				}
				else if (count($this->rule['log']) > 6) {
					unset($this->rule['log']);
				}
			}
		}
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action);

		$this->editAction();
		$this->editFrom();
		$this->editTo();
		$this->editLog();

		$this->editComment();
		$this->editTail();
	}

	function editAction()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Action').':' ?>
			</td>
			<td>
				<select id="action" name="action">
					<option label="Divert" <?php echo isset($this->rule['action']) && $this->rule['action'] == 'Divert' ? 'selected' : ''; ?>>Divert</option>
					<option label="Split" <?php echo isset($this->rule['action']) && $this->rule['action'] == 'Split' ? 'selected' : ''; ?>>Split</option>
					<option label="Pass" <?php echo isset($this->rule['action']) && $this->rule['action'] == 'Pass' ? 'selected' : ''; ?>>Pass</option>
					<option label="Block" <?php echo isset($this->rule['action']) && $this->rule['action'] == 'Block' ? 'selected' : ''; ?>>Block</option>
					<option label="Match" <?php echo isset($this->rule['action']) && $this->rule['action'] == 'Match' ? 'selected' : ''; ?>>Match</option>
				</select>
				<?php
				if (isset($this->rule['action'])) {
					$this->editHelp($this->rule['action'], 'sslproxy.conf');
				}
				?>
			</td>
		</tr>
		<?php
	}

	function editFrom()
	{
		$disabled_from_all = isset($this->rule['all']) || isset($this->rule['user']) || isset($this->rule['desc']) || isset($this->rule['src_ip']) ? 'disabled' : '';
		$disabled_user_desc = isset($this->rule['all']) || isset($this->rule['from_all']) || isset($this->rule['src_ip']) ? 'disabled' : '';
		$disabled_src_ip = isset($this->rule['all']) || isset($this->rule['from_all']) || isset($this->rule['user']) || isset($this->rule['desc']) ? 'disabled' : '';
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('From').':' ?>
			</td>
			<td>
				<input type="checkbox" id="<?php echo 'all' ?>" name="<?php echo 'all' ?>" value="<?php echo '1' ?>" <?php echo (isset($this->rule['all']) ? 'checked' : '') ?>/>
				<label for="all"><?php echo _TITLE('all') ?></label>
				<br/>
				<input type="checkbox" id="<?php echo 'from_all' ?>" name="<?php echo 'from_all' ?>" value="<?php echo '1' ?>" <?php echo (isset($this->rule['from_all']) ? 'checked' : '') ?>  <?php echo $disabled_from_all; ?> />
				<label for="from_all"><?php echo _TITLE('from all') ?></label>
				<br/>
				<input type="text" id="user" name="user" value="<?php echo (isset($this->rule['user']) ? $this->rule['user'] : ''); ?>" placeholder="<?php echo _CONTROL('user name, macro or *') ?>" <?php echo $disabled_user_desc; ?> />
				<label for="user"><?php echo _TITLE('user') ?></label>
				<br/>
				<input type="text" id="desc" name="desc" value="<?php echo (isset($this->rule['desc']) ? $this->rule['desc'] : ''); ?>" placeholder="<?php echo _CONTROL('description, macro or *') ?>" <?php echo $disabled_user_desc; ?> />
				<label for="desc"><?php echo _TITLE('description') ?></label>
				<br/>
				<input type="text" id="src_ip" name="src_ip" value="<?php echo (isset($this->rule['src_ip']) ? $this->rule['src_ip'] : ''); ?>" placeholder="<?php echo _CONTROL('ip address, macro or *') ?>" <?php echo $disabled_src_ip; ?> />
				<label for="src_ip"><?php echo _TITLE('source ip') ?></label>
				<?php
				$this->editHelp('from');
				?>
			</td>
		</tr>
		<?php
	}

	function editTo()
	{
		$disabled_all = isset($this->rule['all']) || isset($this->rule['sni']) || isset($this->rule['cn']) || isset($this->rule['host']) || isset($this->rule['uri']) || isset($this->rule['dst_ip']) || isset($this->rule['port']) ? 'disabled' : '';
		$disabled_sni = isset($this->rule['all']) || isset($this->rule['to_all']) || isset($this->rule['cn']) || isset($this->rule['host']) || isset($this->rule['uri']) || isset($this->rule['dst_ip']) ? 'disabled' : '';
		$disabled_cn = isset($this->rule['all']) || isset($this->rule['to_all']) || isset($this->rule['sni']) || isset($this->rule['host']) || isset($this->rule['uri']) || isset($this->rule['dst_ip']) ? 'disabled' : '';
		$disabled_host = isset($this->rule['all']) || isset($this->rule['to_all']) || isset($this->rule['sni']) || isset($this->rule['cn']) || isset($this->rule['uri']) || isset($this->rule['dst_ip']) ? 'disabled' : '';
		$disabled_uri = isset($this->rule['all']) || isset($this->rule['to_all']) || isset($this->rule['sni']) || isset($this->rule['cn']) || isset($this->rule['host']) || isset($this->rule['dst_ip']) ? 'disabled' : '';
		$disabled_dst_ip = isset($this->rule['all']) || isset($this->rule['to_all']) || isset($this->rule['sni']) || isset($this->rule['cn']) || isset($this->rule['host']) || isset($this->rule['uri']) ? 'disabled' : '';
		$disabled_port = isset($this->rule['all']) || isset($this->rule['to_all']) ? 'disabled' : '';
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('To').':' ?>
			</td>
			<td>
				<input type="checkbox" id="<?php echo 'to_all' ?>" name="<?php echo 'to_all' ?>" value="<?php echo '1' ?>" <?php echo (isset($this->rule['to_all']) ? 'checked' : '') ?>  <?php echo $disabled_all; ?> />
				<label for="to_all"><?php echo _TITLE('to all') ?></label>
				<br/>
				<input type="text" id="sni" name="sni" value="<?php echo (isset($this->rule['sni']) ? $this->rule['sni'] : ''); ?>" placeholder="<?php echo _CONTROL('sni, macro or *') ?>" size="50" <?php echo $disabled_sni; ?> />
				<label for="sni"><?php echo _TITLE('sni') ?></label>
				<br/>
				<input type="text" id="cn" name="cn" value="<?php echo (isset($this->rule['cn']) ? $this->rule['cn'] : ''); ?>" placeholder="<?php echo _CONTROL('common name, macro or *') ?>" size="50" <?php echo $disabled_cn; ?> />
				<label for="cn"><?php echo _TITLE('common name') ?></label>
				<br/>
				<input type="text" id="host" name="host" value="<?php echo (isset($this->rule['host']) ? $this->rule['host'] : ''); ?>" placeholder="<?php echo _CONTROL('host name, macro or *') ?>" size="50" <?php echo $disabled_host; ?> />
				<label for="host"><?php echo _TITLE('host') ?></label>
				<br/>
				<input type="text" id="uri" name="uri" value="<?php echo (isset($this->rule['uri']) ? $this->rule['uri'] : ''); ?>" placeholder="<?php echo _CONTROL('uri, macro or *') ?>" size="50" <?php echo $disabled_uri; ?> />
				<label for="uri"><?php echo _TITLE('uri') ?></label>
				<br/>
				<input type="text" id="dst_ip" name="dst_ip" value="<?php echo (isset($this->rule['dst_ip']) ? $this->rule['dst_ip'] : ''); ?>" placeholder="<?php echo _CONTROL('ip address, macro or *') ?>" <?php echo $disabled_dst_ip; ?> />
				<label for="dst_ip"><?php echo _TITLE('target ip') ?></label>
				<br/>
				<input type="text" id="port" name="port" value="<?php echo (isset($this->rule['port']) ? $this->rule['port'] : ''); ?>" placeholder="<?php echo _CONTROL('port, macro or *') ?>" <?php echo $disabled_port; ?> />
				<label for="port"><?php echo _TITLE('target port') ?></label>
				<?php
				$this->editHelp('to');
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Prints edit controls for log specifications.
	 */
	function editLog()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline') ?>">
			<td class="title">
				<?php echo _TITLE('Log').':' ?>
			</td>
			<td>
				<input type="checkbox" id="log-all" name="log-all" value="*" <?php echo (isset($this->rule['log']) && in_array('*', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">all</label>
				<input type="checkbox" id="log-connect" name="log-connect" value="connect" <?php echo (isset($this->rule['log']) && in_array('connect', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">connect</label>
				<input type="checkbox" id="log-master" name="log-master" value="master" <?php echo (isset($this->rule['log']) && in_array('master', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">master</label>
				<input type="checkbox" id="log-cert" name="log-cert" value="cert" <?php echo (isset($this->rule['log']) && in_array('cert', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">cert</label>
				<input type="checkbox" id="log-content" name="log-content" value="content" <?php echo (isset($this->rule['log']) && in_array('content', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">content</label>
				<input type="checkbox" id="log-pcap" name="log-pcap" value="pcap" <?php echo (isset($this->rule['log']) && in_array('pcap', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">pcap</label>
				<input type="checkbox" id="log-mirror" name="log-mirror" value="mirror" <?php echo (isset($this->rule['log']) && in_array('mirror', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">mirror</label>
				<br/>
				<input type="checkbox" id="log-none" name="log-none" value="!*" <?php echo (isset($this->rule['log']) && in_array('!*', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">none</label>
				<input type="checkbox" id="log-noconnect" name="log-noconnect" value="!connect" <?php echo (isset($this->rule['log']) && in_array('!connect', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">no connect</label>
				<input type="checkbox" id="log-nomaster" name="log-nomaster" value="!master" <?php echo (isset($this->rule['log']) && in_array('!master', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">no master</label>
				<input type="checkbox" id="log-nocert" name="log-nocert" value="!cert" <?php echo (isset($this->rule['log']) && in_array('!cert', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">no cert</label>
				<input type="checkbox" id="log-nocontent" name="log-nocontent" value="!content" <?php echo (isset($this->rule['log']) && in_array('!content', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">no content</label>
				<input type="checkbox" id="log-nopcap" name="log-nopcap" value="!pcap" <?php echo (isset($this->rule['log']) && in_array('!pcap', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">no pcap</label>
				<input type="checkbox" id="log-nomirror" name="log-nomirror" value="!mirror" <?php echo (isset($this->rule['log']) && in_array('!mirror', $this->rule['log']) ? 'checked' : '') ?>/>
				<label for="log">no mirror</label>
				<?php $this->editHelp('log') ?>
			</td>
		</tr>
		<?php
	}
}
?>
