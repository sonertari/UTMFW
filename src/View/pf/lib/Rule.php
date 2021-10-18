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

/** @file
 * Contains base class for rules.
 */

namespace View;

/**
 * Base class for all rule types.
 */
class Rule
{
	/**
	 * Category or type of rule.
	 */
	public $cat= '';

	/**
	 * Internal structure of rule properties in NVP format.
	 */
	public $rule= array();

	/**
	 * Name used in href.
	 */
	protected $ref= '';

	/**
	 * Common part of edit links.
	 */
	protected $href= '';

	/**
	 * Rule number.
	 * 
	 * Normally rules do not know their order in the ruleset. We pass this number
	 * when necessary only. Otherwise, rules can freely move up or down in the ruleset.
	 */
	protected $ruleNumber= 0;
	
	/**
	 * Used by certain rule types while displaying themselves on the list.
	 */
	protected $arr= array();

	/**
	 * Current count of rows on edit pages.
	 * 
	 * This number changes dynamically based on the properties of the rule that the user modifies.
	 * For example, if the user sets a state, the edit page displays state options too.
	 * This is used to alternate the color of consecutive rows.
	 */
	protected $editIndex= 0;

	function __construct()
	{
		$this->cat= str_replace(__NAMESPACE__ . '\\', '', get_called_class());
		$this->ref= strtolower(ltrim($this->cat, '_'));
		/// @todo Make conf.php a parameter
		$this->href= 'conf.php?sender=' . $this->ref . '&amp;rulenumber=';
		$this->setType();
	}

	/**
	 * Sets the type of certain Translation rules.
	 * 
	 * The 'type' key keeps the translation type of such rules. We set the value of this key
	 * to either the rule type itself or the translation type of certain rules, such as Route.
	 * Also note that, we need a default type for Route rules, so such rules set this to
	 * one of the possible translation types they allow, when they are first created.
	 */
	function setType()
	{
	}

	/**
	 * Processes inputs submitted on the edit page.
	 * 
	 * All child classes should override this method.
	 * 
	 * @param int $ruleNumber Rule number.
	 * @param int $count Number of rules in the ruleset.
	 */
	function display($ruleNumber, $count)
	{
	}

	/**
	 * Prints rule number, rule type, and line number.
	 * 
	 * Used by almost all rule types.
	 * Passes $count to dispHeadEditLinks() to disable up or down edit links of the first and
	 * the last rule in the rule set.
	 *
	 * @param int $ruleNumber Rule number.
	 * @param int $count Number of rules in the ruleset.
	 */
	function dispHead($ruleNumber, $count)
	{
		global $lineNumber, $ruleCategoryNames;

		$ruleType= $ruleCategoryNames[$this->ref];
		$lineCount= $this->countLines();
		$title= _TITLE('<RULE_TYPE> rule');
		$title= str_replace('<RULE_TYPE>', $ruleType, $title);
		?>
		<tr title="<?php echo $title ?>"<?php echo ($ruleNumber % 2 ? ' class="oddline"' : ' class="evenline"') ?>>
			<td title="<?php echo _TITLE('Rule number') ?>" class="center">
				<?php echo $ruleNumber; ?>
			</td>
			<?php
			$this->dispHeadEditLinks($ruleNumber, $count);
			?>
			<td title="<?php echo _TITLE('Category') ?>" class="category">
				<?php echo $ruleType; ?>
			</td>
			<td title="<?php echo _TITLE('Line number') ?>" class="center">
				<?php
				$lines= array();
				for ($i= 0; $i <= $lineCount; $i++) {
					$lines[]= ($lineNumber + $i);
				}
				echo implode('<br>', $lines);
				?>
			</td>
		<?php
		$lineNumber+= $lineCount;
	}

	/**
	 * Returns the number of extra lines.
	 * 
	 * Almost all rules occupy only one line, except possibly Blank, Comment, or Anchor rules.
	 * To compute the actual number of lines a rule occupy we need a method like this, which
	 * those rule types override and return the extra number of lines they occupy.
	 *
	 * @return int Number of extra lines in the rule.
	 */
	function countLines()
	{
		return 0;
	}

	/**
	 * Prints inline comments and edit links.
	 * 
	 * Used by almost all rule types.
	 *
	 * @param int $ruleNumber Rule number.
	 */
	function dispTail($ruleNumber)
	{
		?>
			<td class="comment">
				<?php
				if (isset($this->rule['comment'])) {
					echo htmlentities(stripslashes($this->rule['comment']));
				}
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Prints edit links in a cell.
	 * 
	 * Certain rule types do not have inline comments, such as Blank and Comment rules.
	 * Such rules call this method, instead of dispTail().
	 * Passes $count to dispEditLinks() to disable up or down edit links of the first and
	 * the last rule in the rule set.
	 *
	 * @param int $ruleNumber Rule number.
	 * @param int $count Number of rules in the ruleset.
	 */
	function dispHeadEditLinks($ruleNumber, $count)
	{
		?>
			<td class="<?php echo ($ruleNumber % 2 ? 'editoddline' : 'edit') ?>">
				<?php
				$this->dispEditLinks($ruleNumber, $count);
				?>
			</td>
		<?php
	}

	/**
	 * Prints e, u, d, and x links.
	 * 
	 * $count is used to disable up or down edit links of the first and the last rule in the rule set.
	 *
	 * @param int $ruleNumber Rule number.
	 * @param int $count Number of rules in the ruleset.
	 * @param string $up Used in href for GET input, currently the default value is used only.
	 * @param string $down Used in href for GET input, currently the default value is used only.
	 * @param string $del Used in href for GET input, currently the default value is used only.
	 */
	function dispEditLinks($ruleNumber, $count, $up= 'up', $down= 'down', $del= 'del')
	{
		global $ruleCategoryNames;
		?>
		<a href="<?php echo $this->href . $ruleNumber ?>" title="<?php echo _TITLE('Edit') ?>">
			<input type="button" value="E" /></a>
		<?php
		if ($ruleNumber > 0) {
			?>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?<?php echo $up ?>=<?php echo $ruleNumber ?>" title="<?php echo _TITLE('Move up') ?>">
				<input type="button" value="U" /></a>
			<?php
		} else {
			?>
			<input type="button" value="U" title="<?php echo _TITLE('Move up') ?>" disabled/>
			<?php
		}
		if ($ruleNumber < $count) {
			?>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?<?php echo $down ?>=<?php echo $ruleNumber ?>" title="<?php echo _TITLE('Move down') ?>">
				<input type="button" value="D" /></a>
			<?php
		} else {
			?>
			<input type="button" value="D" title="<?php echo _TITLE('Move down') ?>" disabled/>
			<?php
		}

		$ruleType= $ruleCategoryNames[$this->ref];
		$confirmMsg= _CONTROL('Are you sure you want to delete <RULE_TYPE> rule number <RULE_NUMBER>?');
		$confirmMsg= str_replace('<RULE_TYPE>', $ruleType, $confirmMsg);
		$confirmMsg= str_replace('<RULE_NUMBER>', $ruleNumber, $confirmMsg);
		?>
		<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?<?php echo $del ?>=<?php echo $ruleNumber ?>" title="<?php echo _TITLE('Delete') ?>" onclick="return confirm('<?php echo $confirmMsg ?>')">
			<input type="button" value="X" /></a>
		<?php
		if ($this->cat != 'Comment') {
			?>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?comment=<?php echo $ruleNumber ?>" title="<?php echo _TITLE('Comment') ?>">
				<input type="button" value="C" /></a>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?separate=<?php echo $ruleNumber ?>" title="<?php echo _TITLE('Separate') ?>">
				<input type="button" value="S"  disabled/></a>
			<?php
		} else {
			// countLines() returns extra lines, hence > 0, not > 1
			$disabled= $this->countLines() > 0 ? '' : 'disabled';
			?>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?uncomment=<?php echo $ruleNumber ?>" title="<?php echo _TITLE('Uncomment') ?>">
				<input type="button" value="N" /></a>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?separate=<?php echo $ruleNumber ?>" title="<?php echo _TITLE('Separate') ?>">
				<input type="button" value="S" <?php echo $disabled ?>/></a>
			<?php
		}
	}

	/**
	 * Displays the given key in a cell if its value is TRUE.
	 * 
	 * Used with boolean key values only.
	 * 
	 * @param string $key Key to print.
	 * @param string $title Title of the cell.
	 */
	function dispKey($key, $title)
	{
		?>
		<td title="<?php echo $title ?>">
			<?php
			if (isset($this->rule[$key])) {
				echo $key;
			}
			?>
		</td>
		<?php
	}

	/**
	 * Displays the value(s) of the given key in a cell.
	 * 
	 * 
	 * @param string $key Key to print the value of.
	 * @param string $title Title of the cell.
	 */
	function dispValue($key, $title)
	{
		?>
		<td title="<?php echo $title ?>">
			<?php
			if (isset($this->rule[$key])) {
				$this->printValue($this->rule[$key]);
			}
			?>
		</td>
		<?php
	}

	/**
	 * Displays the value(s) of the given host key in a cell.
	 * 
	 * @param string $key Host key to print the value of.
	 * @param string $title Title of the cell.
	 */
	function dispValues($key, $title)
	{
		?>
		<td title="<?php echo $title ?>">
			<?php
			if (isset($this->rule[$key])) {
				$this->printHostPort($this->rule[$key]);
			}
			?>
		</td>
		<?php
	}

	function dispInterface()
	{
		?>
		<td title="<?php echo _TITLE('Interface') ?>">
			<?php
			if (isset($this->rule['interface'])) {
				$this->printValue($this->rule['interface']);
			}
			?>
		</td>
		<?php
	}

	/**
	 * Prints the given value(s).
	 * 
	 * @param mixed $value Value to print.
	 * @param string $pre Prefix to print.
	 * @param string $post Postfix to print.
	 * @param int $count Max number of values to print.
	 */
	function printValue($value, $pre= '', $post= '', $count= 10)
	{
		if ($value) {
			if (!is_array($value)) {
				// Add <br> to call this function twice
				echo "$pre$value$post<br>";
			} else {
				$i= 1;
				foreach ($value as $v) {
					echo "$pre$v$post<br>";
					if (++$i > $count) {
						echo '+' . (count($value) - $count) . ' more entries (not displayed)<br>';
						break;
					}
				}
			}
		}
	}

	/**
	 * Displays log specification in a cell.
	 * 
	 * @param int $colspan Number of columns to span.
	 */
	function dispLog($colspan= 1)
	{
		?>
		<td title="Log" colspan="<?php echo $colspan; ?>">
			<?php
			if (isset($this->rule['log'])) {
				if (is_array($this->rule['log'])) {
					$s= 'log ';
					foreach ($this->rule['log'] as $k => $v) {
						$s.= (is_bool($v) ? "$k" : "$k=$v") . ', ';
					}
					echo trim($s, ', ');
				} else {
					echo 'log';
				}
			}
			?>
		</td>
		<?php
	}

	/**
	 * Prints host or port values.
	 * 
	 * @param mixed $value Value to print.
	 * @param bool $noAny Whether to print 'any' or not.
	 * @param int $count Max number of values to print.
	 */
	function printHostPort($value, $noAny= TRUE, $count= 10)
	{
		if (!is_array($value)) {
			echo $value || $noAny ? htmlentities($value) : 'any';
		} else {
			$i= 1;
			foreach ($value as $v) {
				echo htmlentities($v) . '<br>';
				if (++$i > $count) {
					echo '+' . (count($value) - $count) . ' more entries (not displayed)<br>';
					break;
				}
			}
		}
	}

	/**
	 * Processes inputs submitted on the edit page.
	 * 
	 * All child classes should override this method.
	 */
	function input()
	{
	}

	/**
	 * Gets the value of the given POST input key.
	 * 
	 * If a $parent is supplied, we use it as the parent of the given key.
	 * 
	 * Note that we use 'state' POST input variable to make sure if the submit form
	 * on the edit page has been used or not. All edit pages have 'state' var as hidden
	 * input of the submit form. Otherwise, the rules page has submit forms using POST
	 * vars and they are visible on edit pages too. So, this is the way we differentiate
	 * the POST vars on the edit pages from the ones on the rules page.
	 * 
	 * @param string $key Input key or variable to get the value of.
	 * @param string $parent Parent of the given key.
	 */
	function inputKey($key, $parent= NULL)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			//$value= preg_replace('/"/', '', filter_input(INPUT_POST, $key));
			$value= trim(filter_input(INPUT_POST, $key), "\" \t\n\r\0\x0B");

			if ($parent == NULL) {
				$this->rule[$key]= $value;
			} else {
				$this->rule[$parent][$key]= $value;
			}
		}
	}

	/**
	 * Gets the boolean value of the given POST input key.
	 * 
	 * If a $parent is supplied, we use it as the parent of the given key.
	 * 
	 * @param string $key Input key or variable to get the value of.
	 * @param string $parent Parent of the given key.
	 */
	function inputBool($key, $parent= NULL)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$value= (filter_has_var(INPUT_POST, $key) ? TRUE : '');

			if ($parent == NULL) {
				$this->rule[$key]= $value;
			} else {
				$this->rule[$parent][$key]= $value;
			}
		}
	}

	/**
	 * Gets the value of the given POST input key, if another given key exists already.
	 * 
	 * Used to implement dependencies, e.g. between source-hash and source-hash-key
	 * 
	 * @param string $key Input key or variable to get the value of.
	 * @param string $var Key to check if exists.
	 */
	function inputKeyIfHasVar($key, $var)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if (isset($this->rule[$var])) {
				$this->rule[$key]= filter_input(INPUT_POST, $key);
			}
		}
	}

	/**
	 * Gets the value of the given GET input var and deletes it from the given key.
	 * 
	 * This method is used to get the values of delete links.
	 * 
	 * If a $parent is supplied, we use it as the parent of the given key.
	 * 
	 * @param string $key Key to delete the value from.
	 * @param string $var Input key or variable to get the value of.
	 * @param string $parent Parent of the given key.
	 */
	function inputDel($key, $var, $parent= NULL)
	{
		if (count($_GET)) {
			if (filter_has_var(INPUT_GET, $var)) {
				$this->inputDelValue($key, filter_input(INPUT_GET, $var), $parent);
			}
		}
	}

	/**
	 * Deletes the given value from the given key.
	 * 
	 * If the number of values drops to 1, we convert the values from array to simple value.
	 * 
	 * If a $parent is supplied, we use it as the parent of the given key.
	 * 
	 * @param string $key Key to delete the value from.
	 * @param string $value Value to delete.
	 * @param string $parent Parent of the given key.
	 */
	function inputDelValue($key, $value, $parent= NULL)
	{
		$rule= &$this->rule;
		if ($parent !== NULL) {
			$rule= &$this->rule[$parent];
		}

		if (is_array($rule[$key])) {
			$index= array_search($value, $rule[$key]);
			if ($index !== FALSE) {
				unset($rule[$key][$index]);
				/// @todo After unseting, should we also update the keys in the array?
			}

			FlattenArray($rule[$key]);
		} else {
			unset($rule[$key]);
		}
	}

	/**
	 * Gets the value of the given POST input var and adds it to the given key.
	 * 
	 * This method is used to get the values of multi-valued keys.
	 * All such inputs should have matching delete links, and visa-versa.
	 * 
	 * If a $parent is supplied, we use it as the parent of the given key.
	 * 
	 * @param string $key Key to add the value to.
	 * @param string $var Input key or variable to get the value of.
	 * @param string $parent Parent of the given key.
	 */
	function inputAdd($key, $var, $parent= NULL)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if (filter_has_var(INPUT_POST, $var) && filter_input(INPUT_POST, $var) !== '') {
				$this->inputAddValue($key, preg_replace('/"/', '', filter_input(INPUT_POST, $var)), $parent);
			}
		}
	}

	/**
	 * Adds the given value to the given key.
	 * 
	 * Converts a simple value to an array, if the number of values becomes more than 1.
	 * 
	 * If a $parent is supplied, we use it as the parent of the given key.
	 * 
	 * @param string $key Key to add the value to.
	 * @param string $value Value to add.
	 * @param string $parent Parent of the given key.
	 */
	function inputAddValue($key, $value, $parent= NULL)
	{
		$rule= &$this->rule;
		if ($parent !== NULL) {
			$rule= &$this->rule[$parent];
		}

		if (!isset($rule[$key])) {
			$rule[$key]= $value;
		} else { 
			if (!is_array($rule[$key])) {
				// Make array
				$tmp= $rule[$key];
				unset($rule[$key]);
				$rule[$key][]= $tmp;
			}
			$rule[$key][]= $value;
			$rule[$key]= array_unique($rule[$key]);
		}
	}

	/**
	 * Deletes or adds interface definitions.
	 * 
	 * This is an example that all such inputs should have matching delete links and add input boxes.
	 * 
	 * @todo We should force such dependencies: If there is addInterface, then delInterface should exist.
	 */
	function inputInterface()
	{
		$this->inputDel('interface', 'delInterface');
		$this->inputAdd('interface', 'addInterface');
	}

	/**
	 * Gets the POST input vars of log specifications.
	 */
	function inputLog()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$this->inputBool('log');

			if ($this->rule['log'] == TRUE) {
				if (filter_has_var(INPUT_POST, 'log-all') || filter_has_var(INPUT_POST, 'log-matches') ||
					filter_has_var(INPUT_POST, 'log-user') || (filter_has_var(INPUT_POST, 'log-to') && filter_input(INPUT_POST, 'log-to') !== '')) {
					$this->rule['log']= array();
					if (filter_has_var(INPUT_POST, 'log-all')) {
						$this->rule['log']['all']= TRUE;
					}
					if (filter_has_var(INPUT_POST, 'log-matches')) {
						$this->rule['log']['matches']= TRUE;
					}
					if (filter_has_var(INPUT_POST, 'log-user')) {
						$this->rule['log']['user']= TRUE;
					}
					if (filter_has_var(INPUT_POST, 'log-to') && filter_input(INPUT_POST, 'log-to') !== '') {
						$this->rule['log']['to']= filter_input(INPUT_POST, 'log-to');
					}
				}
			}
		}
	}

	/**
	 * Deletes empty values in internal rule structure.
	 * 
	 * Converts single valued arrays to simple values, if $flatten is TRUE.
	 * 
	 * @param bool $flatten Whether to flatten arrays with single values.
	 */
	function inputDelEmpty($flatten= TRUE)
	{
		/// @todo Check why we cannot combine inputDelEmpty() with inputDelEmptyRecursive()
		$this->rule= $this->inputDelEmptyRecursive($this->rule, $flatten);
	}

	/**
	 * Recursively deletes empty values in NVP arrays.
	 * 
	 * Converts single valued arrays to simple values, if $flatten is TRUE.
	 * 
	 * @param array $array Array to delete empty values from.
	 * @param bool $flatten Whether to flatten arrays with single values.
	 * @return array Converted array.
	 */
	function inputDelEmptyRecursive($array, $flatten)
	{
		foreach ($array as $key => $value) {
			if ($value == '') {
				unset($array[$key]);
			} elseif (is_array($value)) {
				/// @todo Is there a better way? Passing $flatten=FALSE down from Timeout and Limit objects does not work, Filter objects need TRUE
				/// @attention Do not flatten timeout, limit, and log options; their structure should always be array.
				$array[$key]= $this->inputDelEmptyRecursive($value, in_array($key, array('timeout', 'limit', 'log')) ? FALSE : $flatten);

				if (count($array[$key]) == 0) {
					// Array is empty, delete it
					unset($array[$key]);
				} elseif (count($array[$key]) == 1 && $flatten && !in_array($key, array('timeout', 'limit', 'log'))) {
					// Array has only one element, convert from array to simple NVP
					$v= current($array[$key]);
					unset($array[$key]);
					$array[$key]= $v;
				}
			}
		}
		return $array;
	}

	/**
	 * Prints edit page.
	 * 
	 * All child classes should override this method.
	 * 
	 * @param int $ruleNumber Rule number.
	 * @param bool $modified Whether the rule is modified or not.
	 * @param bool $testResult Test result.
	 * @param bool $generateResult Rule generation result.
	 * @param string $action Current state of the edit page.
	 */
	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
	}

	/**
	 * Prints a checkbox for the given key, within a table row.
	 * 
	 * Used for keys with boolean values.
	 * 
	 * @param string $key Id and name of the checkbox.
	 * @param string $title Title for the key.
	 */
	function editCheckbox($key, $title)
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<input type="checkbox" id="<?php echo $key ?>" name="<?php echo $key ?>" value="<?php echo $key ?>" <?php echo (isset($this->rule[$key]) ? 'checked' : '') ?> />
				<?php $this->editHelp($key) ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Prints an edit box for the given key, within a table row.
	 * 
	 * Used for keys with a single value.
	 * 
	 * @param string $key Id and name of the checkbox.
	 * @param string $title Title for the key.
	 * @param string $help Anchor in the html file for the pf.conf(5) man page.
	 * @param mixed $size Size of the input, int or NULL.
	 * @param string $hint Hint text.
	 */
	function editText($key, $title, $help= NULL, $size= 0, $hint= '')
	{
		$help= $help === NULL ? $key : $help;
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline') ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<input type="text" id="<?php echo $key ?>" name="<?php echo $key ?>" value="<?php echo isset($this->rule[$key]) ? $this->rule[$key] : '' ?>" size="<?php echo $size ?>" placeholder="<?php echo $hint ?>" />
				<?php
				if ($help !== FALSE) {
					$this->editHelp($help);
				}
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Prints delete links and input boxes for multi-valued keys, within a table row.
	 * 
	 * @param string $key Id and name of the checkbox.
	 * @param string $title Title for the key.
	 * @param string $delName Var name to use in delete links.
	 * @param string $addName Id of input box.
	 * @param string $hint Hint text.
	 * @param string $help Anchor in the html file for the pf.conf(5) man page.
	 * @param mixed $size Size of the input, int or NULL.
	 * @param bool $disabled Condition to disable the input
	 */
	function editValues($key, $title, $delName, $addName, $hint, $help= NULL, $size= 0, $disabled= FALSE)
	{
		$help= $help === NULL ? $key : $help;
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline') ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<?php
				if (isset($this->rule[$key])) {
					$this->editDeleteValueLinks($this->rule[$key], $delName);
				}
				$this->editAddValueBox($addName, NULL, $hint, $size, $disabled);
				if ($help !== FALSE) {
					$this->editHelp($help);
				}
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Prints rule title, generated rule, and form header at the top of the edit page.
	 * 
	 * Also prints buttons and checkboxes to apply, save or cancel any modification on the edit page.
	 * Almost all edit pages use this method.
	 * 
	 * @param bool $modified Whether the rule is modified or not.
	 * @param bool $testResult Test result of the rule.
	 * @param bool $generateResult Rule generation result.
	 * @param string $action State of the edit page.
	 */
	function editHead($modified, $testResult, $generateResult, $action)
	{
		global $ruleStr, $ruleCategoryNames;

		$ruleType= $ruleCategoryNames[$this->ref];
		$editHeader= _TITLE('Edit <RULE_TYPE> Rule <RULE_NUMBER>');
		$editHeader= str_replace('<RULE_TYPE>', $ruleType, $editHeader);
		$editHeader= str_replace('<RULE_NUMBER>', $this->ruleNumber, $editHeader);
		?>
		<h2><?php echo $editHeader . ($modified ? ' (' . _TITLE('modified') . ')' : '') ?><?php $this->editHelp(ltrim($this->cat, '_')) ?></h2>
		<h4><?php echo str_replace("\t", "<code>\t</code><code>\t</code>", nl2br(htmlentities($ruleStr))) ?></h4>
		<form id="editForm" name="editForm" action="<?php echo $this->href . $this->ruleNumber ?>" method="post">
			<div class="buttons">
				<input type="submit" id="apply" name="apply" value="<?php echo _CONTROL('Apply') ?>" />
				<input type="submit" id="save" name="save" value="<?php echo _CONTROL('Save') ?>" <?php echo $modified ? '' : 'disabled' ?> />
				<input type="submit" id="cancel" name="cancel" value="<?php echo _CONTROL('Cancel') ?>" />
				<input type="checkbox" id="forcesave" name="forcesave" <?php echo $modified && !$testResult ? '' : 'disabled' ?> />
				<label for="forcesave"><?php echo _CONTROL('Save with errors') ?></label>
				<input type="checkbox" id="forcegenerate" name="forcegenerate" <?php echo !$generateResult ? '' : 'disabled' ?> <?php echo filter_has_var(INPUT_POST, 'forcegenerate') ? 'checked' : '' ?> />
				<label for="forcegenerate"><?php echo _CONTROL('Generate with errors') ?></label>
				<input type="hidden" name="state" value="<?php echo $action ?>" />
			</div>
			<table id="nvp">
			<?php
	}

	/**
	 * Closes the table and form on the edit page.
	 * 
	 * Almost all edit pages use this method.
	 */
	function editTail()
	{
			?>
			</table>
		</form>
		<?php
	}

	function editInterface()
	{
		$this->editValues('interface', 'Interface', 'delInterface', 'addInterface', _CONTROL('if or macro'), NULL, 10);
	}

	function editAf()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Address Family').':' ?>
			</td>
			<td>
				<select id="af" name="af">
					<option value="" label=""></option>
					<option value="inet" label="inet" <?php echo (isset($this->rule['af']) && $this->rule['af'] == 'inet' ? 'selected' : '') ?>>inet</option>
					<option value="inet6" label="inet6" <?php echo (isset($this->rule['af']) && $this->rule['af'] == 'inet6' ? 'selected' : '') ?>>inet6</option>
				</select>
				<?php $this->editHelp('address-family') ?>
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
				<?php echo _TITLE('Logging').':' ?>
			</td>
			<td>
				<input type="checkbox" id="log" name="log" value="log" <?php echo (isset($this->rule['log']) ? 'checked' : '') ?> />
				<label for="log">Log</label>
				<?php
				$disabled= isset($this->rule['log']) ? '' : 'disabled';
				?>
				<label for="log"><?php echo _TITLE('to') ?>:</label>
				<input type="text" id="log-to" name="log-to" value="<?php echo (isset($this->rule['log']['to']) ? $this->rule['log']['to'] : ''); ?>" placeholder="<?php echo _CONTROL('logging interface') ?>" <?php echo $disabled; ?> />
				<input type="checkbox" id="log-all" name="log-all" value="log-all" <?php echo (isset($this->rule['log']['all']) ? 'checked' : '') ?> <?php echo $disabled ?> />
				<label for="log">all</label>
				<input type="checkbox" id="log-matches" name="log-matches" value="log-matches" <?php echo (isset($this->rule['log']['matches']) ? 'checked' : ''); ?> <?php echo $disabled ?> />
				<label for="log">matches</label>
				<input type="checkbox" id="log-user" name="log-user" value="log-user" <?php echo (isset($this->rule['log']['user']) ? 'checked' : '') ?> <?php echo $disabled ?> />
				<label for="log">user</label>
				<?php $this->editHelp('log') ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Prints input box for inline comments.
	 */
	function editComment()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Comment').':' ?>
			</td>
			<td>
				<input type="text" id="comment" name="comment" value="<?php echo isset($this->rule['comment']) ? stripslashes($this->rule['comment']) : '' ?>" size="80" placeholder="<?php echo _CONTROL('enter comment, such as a description of the rule') ?>" />
			</td>
		</tr>
		<?php
	}

	/**
	 * Prints delete links for values.
	 * 
	 * Used for multi-value fields in rules.
	 * Followed by add input box.
	 *
	 * @param mixed $value Value for delete link, array or string.
	 * @param string $name Get input var name, usually with a del prefix.
	 * @param string $prefix Prefix to print.
	 * @param string $postfix Postfix to print.
	 */
	function editDeleteValueLinks($value, $name, $prefix= '', $postfix= '')
	{
		global $action;

		if (is_array($value)) {
			foreach ($value as $v) {
				$v= htmlentities($v);
				echo "$prefix$v$postfix";
				?>
				<a href="<?php echo $this->href . $this->ruleNumber ?>&amp;<?php echo $name ?>=<?php echo $v ?>&amp;state=<?php echo $action ?>"><?php echo _CONTROL('delete') ?></a><br>
				<?php
			}
		} else {
			$value= htmlentities($value);
			echo "$prefix$value$postfix";
			?>
			<a href="<?php echo $this->href . $this->ruleNumber ?>&amp;<?php echo $name ?>=<?php echo $value ?>&amp;state=<?php echo $action ?>"><?php echo _CONTROL('delete') ?></a><br>
			<?php
		}
		?>
		<hr style="border: 0; border-bottom: 1px solid #CCC;" />
		<?php
	}

	/**
	 * Prints add input box for values.
	 * 
	 * Simply a text input box and a label.
	 * Used for multi-value fields in rules.
	 *
	 * @param string $id Id of the input
	 * @param mixed $label Label, string or NULL.
	 * @param string $hint Hint text.
	 * @param string $value Value instead of hint.
	 * @param mixed $size Size of the input, int or NULL.
	 * @param bool $disabled Condition to disable the input.
	 */
	function editAddValueBox($id, $label, $hint, $size= 0, $disabled= FALSE)
	{
		?>
		<input type="text" id="<?php echo $id ?>" name="<?php echo $id ?>" size="<?php echo $size ?>" placeholder="<?php echo $hint ?>" <?php echo $disabled ? 'disabled' : '' ?> />
		<label for="<?php echo $id ?>"><?php echo $label ?></label>
		<?php
	}

	/**
	 * Prints help image with a link to an anchor in pf.conf man page.
	 * 
	 * pf.conf man page is an html file generated from the actual pf.conf(5).
	 * The anchors are added manually to match with the labels given here.
	 *
	 * @param string $label Anchor in pf.conf man page.
	 */
	function editHelp($label)
	{
		global $IMG_PATH;
		?>
		<a target="<?php echo $label ?>" href="/pf/pf.conf.html#<?php echo $label ?>">
			<img src="<?php echo "$IMG_PATH/help.png" ?>" name="<?php echo $label ?>" alt="(?)" border="0" width="12" height="12">
		</a>
		<?php
	}
}
?>
