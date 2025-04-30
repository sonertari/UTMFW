<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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

/** @file
 * Edit page.
 */

require_once ('sslproxy.php');

if (isset($edit) && array_key_exists($edit, $ruleType2Class)) {
	$ruleObj->input();

	if (isset($_SESSION['saved_edit']) && ($_SESSION['saved_edit']['cat'] == 'ProxySpecStruct' || $_SESSION['saved_edit']['cat'] == 'FilterStruct')) {
		$mainRuleset= $_SESSION['saved_edit']['ruleset'];
		$mainRuleNumber= $_SESSION['saved_edit']['ruleNumber'];
		$mainRuleObj= $_SESSION['saved_edit']['object'];

		$tmpRuleSet= clone $ruleSet;
		$tmpRuleSet->rules[$ruleNumber]= $ruleObj;
		$View->Controller($inline, 'GenerateRules', json_encode($tmpRuleSet->rules), 0, 1);

		$mainRuleObj->rule['inline']= implode("\n", $inline);

		$testResult= $mainRuleset->test($mainRuleNumber, $mainRuleObj);
	} else {
		$testResult= $ruleSet->test($ruleNumber, $ruleObj);
	}

	$ruleSet->cancel();
	$ruleSet->save($action, $ruleNumber, $ruleObj, $testResult);

	$modified= TRUE;
	if ($action != 'create') {
		$modified= $ruleSet->isModified($ruleNumber, $ruleObj);
	}

	$force= 0;
	if (filter_has_var(INPUT_POST, 'forcegenerate')) {
		$force= 1;
	}

	$generateResult= $View->Controller($Output, 'GenerateRule', json_encode($ruleObj), $ruleNumber, $force);
	if ($generateResult || $force) {
		/// @attention Inline rules are multi-line, hence implode.
		$ruleStr= implode("\n", $Output);
	} else {
		$ruleStr= _NOTICE('ERROR') . ': ' . _NOTICE('Cannot generate rule');
	}

	if (!$testResult && $ErrorMsg == '') {
		$ErrorMsg= _NOTICE('Rule test failed without an error message');
	}
	require_once($VIEW_PATH.'/header.php');
	?>
	<table class="shadowbox">
		<tr>
			<td>
			<?php
				/// @attention $ruleStr is passed as a global var.
				$ruleObj->edit($ruleNumber, $modified, $testResult, $generateResult, $action);
			?>
			</td>
		</tr>
	</table>
	<?php
	require_once($VIEW_PATH.'/footer.php');
	exit;
}
?>
