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

require_once ('pf.php');

$ruleCategoryNames = array(
	'filter' => _CONTROL('Filter'),
	'antispoof' => _CONTROL('Antispoof'),
	'anchor' => _CONTROL('Anchor'),
	'macro' => _CONTROL('Macro'),
	'table' => _CONTROL('Table'),
	'afto' => _CONTROL('Af Translate'),
	'natto' => _CONTROL('Nat'),
	'binatto' => _CONTROL('Binat'),
	'divertto' => _CONTROL('Divert'),
	'divertpacket' => _CONTROL('Divert Packet'),
	'rdrto' => _CONTROL('Redirect'),
	'route' => _CONTROL('Route'),
	'queue' => _CONTROL('Queue'),
	'scrub' => _CONTROL('Scrub'),
	'option' => _CONTROL('Option'),
	'timeout' => _CONTROL('Timeout'),
	'limit' => _CONTROL('Limit'),
	'state' => _CONTROL('State Defaults'),
	'loadanchor' => _CONTROL('Load Anchor'),
	'include' => _CONTROL('Include'),
	'comment' => _CONTROL('Comment'),
	'blank' => _CONTROL('Blank Line'),
	);

$ruleType2Class= array(
	'filter' => 'Filter',
	'antispoof' => 'Antispoof',
	'anchor' => 'Anchor',
	'macro' => 'Macro',
	'table' => 'Table',
	'afto' => 'AfTo',
	'natto' => 'NatTo',
	'binatto' => 'BinatTo',
	'divertto' => 'DivertTo',
	'divertpacket' => 'DivertPacket',
	'rdrto' => 'RdrTo',
	'route' => 'Route',
	'queue' => 'Queue',
	'scrub' => 'Scrub',
	'option' => 'Option',
	'timeout' => 'Timeout',
	'limit' => 'Limit',
	'state' => 'State',
	'loadanchor' => 'LoadAnchor',
	'include' => '_Include',
	'comment' => 'Comment',
	'blank' => 'Blank',
	);

if (filter_has_var(INPUT_GET, 'sender') && array_key_exists(filter_input(INPUT_GET, 'sender'), $ruleCategoryNames)) {
	$edit= filter_input(INPUT_GET, 'sender');
	$ruleNumber= filter_input(INPUT_GET, 'rulenumber');
	
	if (filter_has_var(INPUT_GET, 'state') && filter_input(INPUT_GET, 'state') == 'create') {
		// Get action has precedence
		// Accept only create action here
		$action= 'create';
	} elseif (filter_has_var(INPUT_POST, 'state') && filter_input(INPUT_POST, 'state') == 'create') {
		// Post action is used while saving new rules, create is the next state after add
		// Accept only create action here
		$action= 'create';
	} else {
		// Default action is edit, which takes care of unacceptable actions too
		$action= 'edit';
	}
}

$show= 'all';
if (isset($_SESSION['show'])) {
	$show= $_SESSION['show'];
}

if (filter_has_var(INPUT_POST, 'ruleNumber') && filter_input(INPUT_POST, 'ruleNumber') !== '') {
	if (filter_has_var(INPUT_POST, 'add')) {
		$edit= filter_input(INPUT_POST, 'category');
		$edit= $edit == 'all' ? 'filter' : $edit;
		$ruleNumber= filter_input(INPUT_POST, 'ruleNumber');
		$action= 'add';
	} elseif (filter_has_var(INPUT_POST, 'edit')) {
		$ruleNumber= filter_input(INPUT_POST, 'ruleNumber');
		if (array_key_exists($ruleNumber, $View->RuleSet->rules)) {
			$edit= array_search($View->RuleSet->rules[$ruleNumber]->cat, $ruleType2Class);
			$action= 'edit';
		} else {
			// Will add a new rule of category $edit otherwise
			$edit= filter_input(INPUT_POST, 'category');
			$edit= $edit == 'all' ? 'filter' : $edit;
			$action= 'add';
		}
	} elseif (filter_has_var(INPUT_POST, 'show')) {
		$show= filter_input(INPUT_POST, 'category');
		$_SESSION['show']= $show;
	}
}

if (isset($edit)) {
	require ('edit.php');
}

if (filter_has_var(INPUT_GET, 'up')) {
	$View->RuleSet->up(filter_input(INPUT_GET, 'up'));
}

if (filter_has_var(INPUT_GET, 'down')) {
	$View->RuleSet->down(filter_input(INPUT_GET, 'down'));
}

if (filter_has_var(INPUT_GET, 'del')) {
	$View->RuleSet->del(filter_input(INPUT_GET, 'del'));
}

if (filter_has_var(INPUT_POST, 'move')) {
	if (filter_has_var(INPUT_POST, 'ruleNumber') && filter_input(INPUT_POST, 'ruleNumber') !== '' &&
		filter_has_var(INPUT_POST, 'moveTo') && filter_input(INPUT_POST, 'moveTo') !== '') {
		$View->RuleSet->move(filter_input(INPUT_POST, 'ruleNumber'), filter_input(INPUT_POST, 'moveTo'));
	}
}

if (filter_has_var(INPUT_POST, 'delete')) {
	$View->RuleSet->del(filter_input(INPUT_POST, 'ruleNumber'));
}

if (filter_has_var(INPUT_POST, 'deleteAll')) {
	$View->RuleSet->deleteRules();
	PrintHelpWindow(_NOTICE('Ruleset deleted'));
}

$View->Controller($Output, 'TestPfRules', json_encode($View->RuleSet->rules));

require_once($VIEW_PATH.'/header.php');
?>
<fieldset>
	<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
		<input type="submit" name="show" value="<?php echo _CONTROL('Show') ?>" />
		<select id="category" name="category">
			<option value="all"><?php echo _CONTROL('All') ?></option>
			<?php
			foreach ($ruleCategoryNames as $category => $name) {
				?>
				<option value="<?php echo $category; ?>" <?php echo (filter_input(INPUT_POST, 'category') == $category || $show == $category ? 'selected' : ''); ?>><?php echo $name; ?></option>
				<?php
			}
			?>
		</select>
		<input type="submit" name="add" value="<?php echo _CONTROL('Add') ?>" />
		<label for="ruleNumber"><?php echo _CONTROL('as rule number') ?>:</label>
		<input type="text" name="ruleNumber" id="ruleNumber" size="5" value="<?php echo $View->RuleSet->nextRuleNumber(); ?>" placeholder="<?php echo _CONTROL('number') ?>" />
		<input type="submit" name="edit" value="<?php echo _CONTROL('Edit') ?>" />
		<input type="submit" name="delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _CONTROL('Are you sure you want to delete the rule?') ?>')"/>
		<input type="text" name="moveTo" id="moveTo" size="5" value="<?php echo filter_input(INPUT_POST, 'moveTo') ?>" placeholder="<?php echo _CONTROL('move to') ?>" />
		<input type="submit" name="move" value="<?php echo _CONTROL('Move') ?>" />
		<input type="submit" id="deleteAll" name="deleteAll" value="<?php echo _CONTROL('Delete All') ?>" onclick="return confirm('<?php echo _CONTROL('Are you sure you want to delete the entire ruleset?') ?>')"/>
	</form>
</fieldset>
<?php
echo _TITLE('Rules file') . ': ' . $View->RuleSet->filename . ($View->RuleSet->uploaded ? ' (' . _TITLE('uploaded') . ')' : '');
?>
<table id="logline">
	<tr>
		<th><?php echo _TITLE('Rule') ?></th>
		<th><?php echo _TITLE('Type') ?></th>
		<th><?php echo _TITLE('Line') ?></th>
		<th colspan="12"><?php echo _TITLE('Rule') ?></th>
		<th><?php echo _TITLE('Comment') ?></th>
		<th><?php echo _TITLE('Edit') ?></th>
	</tr>
	<?php
	$ruleNumber= 0;
	// Passed as a global var.
	$lineNumber= 0;
	$count = count($View->RuleSet->rules) - 1;
	foreach ($View->RuleSet->rules as $rule) {
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
?>
