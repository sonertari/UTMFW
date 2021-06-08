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

require_once ('pf.php');

$printNumbers= 1;
if (count($_POST) && !filter_has_var(INPUT_POST, 'numbers')) {
	$printNumbers= 0;
}

$testResult= $View->Controller($Output, 'TestPfRules', json_encode($View->RuleSet->rules));
if ($testResult) {
	if (filter_has_var(INPUT_POST, 'install') && filter_input(INPUT_POST, 'install') == _CONTROL('Install')) {
		if ($View->Controller($Output, 'InstallPfRules', json_encode($View->RuleSet->rules))) {
			PrintHelpWindow(_NOTICE('Installed successfully'));
		} else {
			PrintHelpWindow('<br>' . _NOTICE('There was an error while installing'), NULL, 'ERROR');
		}
	}
} else {
	PrintHelpWindow('<br>' . _NOTICE('Failed testing ruleset'), NULL, 'ERROR');
}

$force= 0;
if (filter_has_var(INPUT_POST, 'forcedisplay')) {
	$force= 1;
}

$StrRules= array();
$generated= FALSE;
if ($testResult || $force) {
	$generated= $View->Controller($StrRules, 'GeneratePfRules', json_encode($View->RuleSet->rules), $printNumbers, $force);
}

require_once($VIEW_PATH.'/header.php');
?>
<table class="shadowbox">
	<tr>
		<td>
			<fieldset>
				<form id="installForm" name="installForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
					<input type="checkbox" id="numbers" name="numbers" <?php echo $printNumbers ? 'checked' : '' ?> onclick="document.installForm.apply.click()" />
					<label for="numbers"><?php echo _CONTROL('Display line numbers') ?></label>
					<input type="checkbox" id="forcedisplay" name="forcedisplay" <?php echo filter_has_var(INPUT_POST, 'forcedisplay') ? 'checked' : ''; ?> <?php echo $testResult ? 'disabled' : ''; ?> onclick="document.installForm.apply.click()" />
					<label for="forcedisplay"><?php echo _CONTROL('Display with errors') ?></label>
					<input type="submit" id="apply" name="apply" value="<?php echo _CONTROL('Apply') ?>" />
					<input type="submit" id="install" name="install" value="<?php echo _CONTROL('Install') ?>" <?php echo $testResult ? '' : 'disabled' ?> />
					<label for="install"><?php echo _CONTROL('Install as main ruleset') ?>: /etc/pf.conf</label>
				</form>
			</fieldset>
<?php
echo _TITLE('Rules file') . ': ' . $View->RuleSet->filename . ($View->RuleSet->uploaded ? ' (' . _TITLE('uploaded') . ')' : '');
?>
<hr style="border: 0; border-bottom: 1px solid gray;" />

<pre id="rules">
<?php
if ($generated || $force) {
	echo htmlentities(implode("\n", $StrRules));
}
?>
</pre>
		</td>
	</tr>
</table>
<?php
require_once($VIEW_PATH.'/footer.php');
?>
