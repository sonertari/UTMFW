<?php
/*
 * Copyright (C) 2004-2017 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @file
 * Edit page.
 */

require_once ('pf.php');

if (isset($edit) && array_key_exists($edit, $ruleType2Class)) {
	$cat= $ruleType2Class[$edit];

	$View->RuleSet->setupEditSession($cat, $action, $ruleNumber);

	$ruleObj= &$_SESSION['edit']['object'];
	$ruleObj->input();

	$testResult= $View->RuleSet->test($ruleNumber, $ruleObj);
	$View->RuleSet->cancel();
	$View->RuleSet->save($action, $ruleNumber, $ruleObj, $testResult);

	$modified= TRUE;
	if ($action != 'create') {
		$modified= $View->RuleSet->isModified($ruleNumber, $ruleObj);
	}

	$force= 0;
	if (filter_has_var(INPUT_POST, 'forcegenerate')) {
		$force= 1;
	}

	$generateResult= $View->Controller($Output, 'GeneratePfRule', json_encode($ruleObj), $ruleNumber, $force);
	if ($generateResult || $force) {
		/// @attention Inline anchor rules are multi-line, hence implode.
		$ruleStr= implode("\n", $Output);
	} else {
		$ruleStr= _NOTICE('ERROR') . ': ' . _NOTICE('Cannot generate rule');
	}

	require_once($VIEW_PATH.'/header.php');
	/// @attention $ruleStr is passed as a global var.
	$ruleObj->edit($ruleNumber, $modified, $testResult, $generateResult, $action);
	require_once($VIEW_PATH.'/footer.php');

	exit;
}
?>
