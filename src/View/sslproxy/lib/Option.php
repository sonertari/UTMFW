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
		$this->dispValue('option', _TITLE('Option'));
		?>
		<td title="<?php echo _TITLE('Value') ?>" colspan="3">
			<?php
			if (isset($this->rule['value'])) {
				$this->printValue($this->rule['value']);
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('option');
		$this->inputKey('value');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified, $testResult, $generateResult, $action, 'sslproxy.conf');

		$this->editOption();
		$this->editText('value', _TITLE('Value'), FALSE, 50, _CONTROL('add value'));

		$this->editComment();
		$this->editTail();
	}

	function editOption()
	{
		$options= array(
			'CACert',
			'CAKey',
			'ClientCert',
			'ClientKey',
			'CAChain',
			'LeafKey',
			'LeafCRLURL',
			'LeafCertDir',
			'DefaultLeafCert',
			'WriteGenCertsDir',
			'WriteAllCertsDir',
			'DenyOCSP',
			'Passthrough',
			'DHGroupParams',
			'ECDHCurve',
			'SSLCompression',
			'ForceSSLProto',
			'DisableSSLProto',
			'Ciphers',
			'CipherSuites',
			'LeafKeyRSABits',
			'OpenSSLEngine',
			'NATEngine',
			'User',
			'Group',
			'Chroot',
			'PidFile',
			'ConnectLog',
			'ContentLog',
			'ContentLogDir',
			'ContentLogPathSpec',
			'LogProcInfo',
			'PcapLog',
			'PcapLogDir',
			'PcapLogPathSpec',
			'MirrorIf',
			'MirrorTarget',
			'MasterKeyLog',
			'Daemon',
			'Debug',
			'DebugLevel',
			'ConnIdleTimeout',
			'ExpiredConnCheckPeriod',
			'LogStats',
			'StatsPeriod',
			'RemoveHTTPAcceptEncoding',
			'RemoveHTTPReferer',
			'VerifyPeer',
			'AllowWrongHost',
			'UserAuth',
			'DivertUsers',
			'PassUsers',
			'UserDBPath',
			'UserTimeout',
			'UserAuthURL',
			'ValidateProto',
			'MaxHTTPHeaderSize',
			'OpenFilesLimit',
			'Divert',
			'PassSite',
		);
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Option').':' ?>
			</td>
			<td>
				<select id="option" name="option">
					<option value=""></option>
					<?php
					foreach ($options as $o) {
						?>
						<option value="<?php echo $o ?>" label="<?php echo $o ?>" <?php echo (isset($this->rule['option']) && $this->rule['option'] == $o ? 'selected' : ''); ?>><?php echo $o ?></option>
						<?php
					}
					?>
				</select>
				<?php
				if (isset($this->rule['option'])) {
					$this->editHelp($this->rule['option'], 'sslproxy.conf');
				}
				?>
			</td>
		</tr>
		<?php
	}
}
?>
