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

use SSLproxy\RuleSet;

require_once('sslproxy.php');

function indentLines(&$lines)
{
	$lines= explode("\n", trim(implode("\n", $lines)));
	$nesting_count= 1;
	for ($i= 0; $i < count($lines); $i++) {
		if (preg_match('/^\s*}\s*$/', $lines[$i])) {
			$nesting_count--;
		}
		$lines[$i]= str_repeat("\t", $nesting_count).trim($lines[$i]);
		if (preg_match('/^\s*(ProxySpec|FilterRule)\s*{\s*$/', $lines[$i])) {
			$nesting_count++;
		}
	}
}

if (!isset($_SESSION['saved_edit'])) {
	$_SESSION['saved_edit']= array();
	$_SESSION['saved_edit']['ruleset']= $ruleSet;
	$_SESSION['saved_edit']['object']= $ruleObj;
	$_SESSION['saved_edit']['cat']= $cat;
	$_SESSION['saved_edit']['ruleNumber']= $ruleNumber;
	$_SESSION['saved_edit']['action']= $action;

	$rulesArray= array();
	if (isset($ruleObj->rule['inline'])) {
		$View->Controller($Output, 'ParseRules', json_encode($ruleObj->rule['inline']), 1);
		$rulesArray= json_decode($Output[0], TRUE)['rules'];
	}

	$ruleSet= new RuleSet();
	$ruleSet->filename= $ruleSetFilename;
	$ruleSet->loadArray($rulesArray);

	$_SESSION['sslproxy']['ruleset']= $ruleSet;
	$_SESSION['edit']['object']= $ruleObj;
}

$ruleObj->input();

if (filter_has_var(INPUT_POST, 'inline')) {
	$rulesArray= array();
	$View->Controller($Output, 'ParseRules', json_encode($ruleObj->rule['inline']), 1);
	$rulesArray= json_decode($Output[0], TRUE)['rules'];

	$ruleSet= new RuleSet();
	$ruleSet->filename= $ruleSetFilename;
	$ruleSet->loadArray($rulesArray);
}
else {
	$generated= $View->Controller($inline, 'GenerateRules', json_encode($ruleSet->rules), 0, 1);
	indentLines($inline);
	$ruleObj->rule['inline']= implode("\n", $inline);
}

if (filter_has_var(INPUT_GET, 'up')) {
	$ruleSet->up(filter_input(INPUT_GET, 'up'));
}

if (filter_has_var(INPUT_GET, 'down')) {
	$ruleSet->down(filter_input(INPUT_GET, 'down'));
}

if (filter_has_var(INPUT_GET, 'del')) {
	$ruleSet->del(filter_input(INPUT_GET, 'del'));
}

if (filter_has_var(INPUT_POST, 'move')) {
	if (filter_has_var(INPUT_POST, 'ruleNumber') && filter_input(INPUT_POST, 'ruleNumber') !== '' &&
		filter_has_var(INPUT_POST, 'moveTo') && filter_input(INPUT_POST, 'moveTo') !== '') {
		$ruleSet->move(filter_input(INPUT_POST, 'ruleNumber'), filter_input(INPUT_POST, 'moveTo'));
	}
}

if (filter_has_var(INPUT_POST, 'delete')) {
	$ruleSet->del(filter_input(INPUT_POST, 'ruleNumber'));
}

if (filter_has_var(INPUT_POST, 'deleteAll')) {
	$ruleSet->deleteRules();
	PrintHelpWindow(_NOTICE('Ruleset deleted'));
}

if (filter_has_var(INPUT_GET, 'comment')) {
	$ruleSet->comment(filter_input(INPUT_GET, 'comment'));
}

if (filter_has_var(INPUT_GET, 'uncomment')) {
	$ruleSet->uncomment(filter_input(INPUT_GET, 'uncomment'));
}

if (filter_has_var(INPUT_GET, 'separate')) {
	$ruleSet->separate(filter_input(INPUT_GET, 'separate'));
}

if (filter_has_var(INPUT_POST, 'parse')) {
	$ruleSet->parse();
}

$mainRuleset= $_SESSION['saved_edit']['ruleset'];
$mainRuleNumber= $_SESSION['saved_edit']['ruleNumber'];

if (filter_has_var(INPUT_POST, 'cancel') && (filter_input(INPUT_POST, 'cancel') == _CONTROL('Cancel'))) {
	$_SESSION['sslproxy']['ruleset']= &$_SESSION['saved_edit']['ruleset'];
	$_SESSION['edit']['object']= &$_SESSION['saved_edit']['object'];
	unset($_SESSION['saved_edit']);
}
$mainRuleset->cancel();

// Force generate
$generateResult= $View->Controller($inline, 'GenerateRules', json_encode($ruleSet->rules), 0, 1);
if ($generateResult) {
	/// @attention Inline rules are multi-line, hence implode.
	indentLines($inline);
	$ruleObj->rule['inline']= implode("\n", $inline);
}

$testResult= $mainRuleset->test($mainRuleNumber, $ruleObj);

$mainAction= $_SESSION['saved_edit']['action'];

if (filter_has_var(INPUT_POST, 'save') && (filter_input(INPUT_POST, 'save') == _CONTROL('Save'))){
	if ($testResult || filter_input(INPUT_POST, 'forcesave')) {
		$_SESSION['sslproxy']['ruleset']= $_SESSION['saved_edit']['ruleset'];
		unset($_SESSION['saved_edit']);
	}
}

$mainRuleset->save($mainAction, $mainRuleNumber, $ruleObj, $testResult);

$modified= TRUE;
if ($action != 'create') {
	$modified= $ruleSet->isModified($ruleNumber, $ruleObj);
}

$force= 0;
if (filter_has_var(INPUT_POST, 'forcegenerate')) {
	$force= 1;
}

if (!$testResult && $ErrorMsg == '') {
	$ErrorMsg= _NOTICE('Rule test failed without an error message');
}

$generateResult= $View->Controller($Output, 'GenerateRule', json_encode($ruleObj), $ruleNumber, $force);
if ($generateResult || $force) {
	/// @attention Inline rules are multi-line, hence implode.
	$ruleStr= implode("\n", $Output);
} else {
	$ruleStr= _NOTICE('ERROR') . ': ' . _NOTICE('Cannot generate rule');
}

require_once($VIEW_PATH.'/header.php');
?>
<table class="shadowbox">
	<tr>
		<td>
		<?php
			/// @attention $ruleStr is passed as a global var.
			$ruleObj->edit($mainRuleNumber, $modified, $testResult, $generateResult, $action);
		?>
		</td>
	</tr>
</table>
<fieldset>
	<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
		<input type="submit" name="show" value="<?php echo _CONTROL('Show') ?>" />
		<select id="category" name="category">
			<option value="all"><?php echo _CONTROL('All') ?></option>
			<?php
			foreach ($ruleCategoryNames as $category => $name) {
				if (in_array($category, $unsupportedRuleCategories)) {
					continue;
				}
				?>
				<option value="<?php echo $category; ?>" <?php echo (filter_input(INPUT_POST, 'category') == $category || $show == $category ? 'selected' : ''); ?>><?php echo $name; ?></option>
				<?php
			}
			?>
		</select>
		<input type="submit" name="add" value="<?php echo _CONTROL('Add') ?>" />
		<label for="ruleNumber"><?php echo _CONTROL('as rule number') ?>:</label>
		<input type="text" name="ruleNumber" id="ruleNumber" size="5" value="<?php echo $ruleSet->nextRuleNumber(); ?>" placeholder="<?php echo _CONTROL('number') ?>" />
		<input type="submit" name="edit" value="<?php echo _CONTROL('Edit') ?>" />
		<input type="submit" name="delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _CONTROL('Are you sure you want to delete the rule?') ?>')"/>
		<input type="text" name="moveTo" id="moveTo" size="5" value="<?php echo filter_input(INPUT_POST, 'moveTo') ?>" placeholder="<?php echo _CONTROL('move to') ?>" />
		<input type="submit" name="move" value="<?php echo _CONTROL('Move') ?>" />
		<input type="submit" id="deleteAll" name="deleteAll" value="<?php echo _CONTROL('Delete All') ?>" onclick="return confirm('<?php echo _CONTROL('Are you sure you want to delete the entire ruleset?') ?>')"/>
		<input type="submit" name="parse" value="<?php echo _CONTROL('Parse') ?>"  title="<?php echo _TITLE('Merges separated comments') ?>"/>
		<input type="hidden" name="nested" value="<?php echo $baseCat ?>" />
	</form>
</fieldset>
<table id="logline">
	<tr>
		<th><?php echo _TITLE('Rule') ?></th>
		<th><?php echo _TITLE('Edit') ?></th>
		<th><?php echo _TITLE('Type') ?></th>
		<th><?php echo _TITLE('Line') ?></th>
		<th colspan="4"><?php echo _TITLE('Rule') ?></th>
		<th><?php echo _TITLE('Comment') ?></th>
	</tr>
	<?php
	$ruleNumber= 0;
	// Passed as a global var.
	$lineNumber= 1;
	$count = count($ruleSet->rules) - 1;
	foreach ($ruleSet->rules as $rule) {
		if ($show == 'all' || $ruleType2Class[$show] == $rule->cat) {
			$rule->display($ruleNumber, $count);
		}
		$ruleNumber++;
		$lineNumber++;
	}
	?>
</table>
<?php
require_once($VIEW_PATH . '/footer.php');
exit;
?>
