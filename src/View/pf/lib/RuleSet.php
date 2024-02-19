<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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
 * Contains the ruleset class which runs maintains rules.
 */

/**
 * @namespace View
 * 
 * View of MVC: Generally, the View displays Model data and allows modification of the data.
 * 
 * The View does not know how to parse or generate pf rules. It only maintains a list of rules
 * in an internal data structure, the format of which is exactly the same as the one in
 * the Model.
 * 
 * In PFRE, the View:
 * \li Loads rules from a rules array
 * \li Displays the ruleset in a list
 * \li Enables user to edit individual rules
 * \li Displays generated rules and test results
 * \li Enables user to install, save, or delete rules and rulesets
 * \li Provides a login page for user authentication
 * \li Enables user to configure PFRE settings
 */
namespace View;

/**
 * Loads and maintains rules.
 */
class RuleSet
{
	/**
	 * The name of the currently loaded file.
	 */
	public $filename= '';

	/**
	 * Whether the file is uploaded or not.
	 */
	public $uploaded= FALSE;

	/**
	 * The list holding the ruleset.
	 */
	public $rules= array();
			
	/**
	 * Loads the rules in a given file.
	 * 
	 * Refers to the Model to parse and create the rules array, which the Model
	 * returns as json encoded string.
	 * 
	 * $filename and $tmpFilename are displayed on rules and install pages.
	 *
	 * @param string $filename File name in the filesystem.
	 * @param int $tmp Whether the file is uploaded from a browser.
	 * @param int $force Whether to force loading the file even with errors.
	 * @param string $tmpFilename Name if the temporary file uploaded.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function load($filename, $tmp= 0, $force= 0, $tmpFilename= '')
	{
		global $View;

		$retval= TRUE;
		if ($filename == '/etc/pf.conf') {
			$retval= $View->Controller($Output, 'GetRules', $filename, 0, $force);
		} else {
			$retval= $View->Controller($Output, 'GetRules', $filename, $tmp, $force);
		}

		if ($retval !== FALSE || $force) {
			if ($tmp && $tmpFilename !== '') {
				$this->filename= $tmpFilename;
				// Mark uploaded files as such
				$this->uploaded= TRUE;
			} else {
				$this->filename= $filename;
				$this->uploaded= FALSE;
			}
			$rulesArray= json_decode($Output[0], TRUE)['rules'];
		} else {
			return FALSE;
		}

		$this->loadArray($rulesArray);

		return TRUE;
	}
			
	/**
	 * Loads the rules from a given rules array.
	 * 
	 * First, we delete the rules list, then create the rule objects.
	 * A simple assignment of rule structure to the rules member var is sufficient.
	 * 
	 * @param array $rulesArray Rules in an array.
	 */
	function loadArray($rulesArray)
	{
		$this->deleteRules();
		foreach ($rulesArray as $ruleDef) {
			$cat= __NAMESPACE__ . '\\' . $ruleDef['cat'];
			$ruleObj= new $cat();
			$ruleObj->rule= $ruleDef['rule'];
			$this->rules[]= $ruleObj;
		}
	}
	
	function deleteRules()
	{
		$this->rules= array();
	}
	
	/**
	 * Moves a rule up in the ruleset.
	 * 
	 * Simply switches the places of two rules.
	 * 
	 * @param int $ruleNumber The number of the rule to move up.
	 */
	function up($ruleNumber)
	{
		if (isset($this->rules[$ruleNumber - 1])) {
			$tmp= $this->rules[$ruleNumber - 1];
			$this->rules[$ruleNumber - 1]= $this->rules[$ruleNumber];
			$this->rules[$ruleNumber]= $tmp;
		}
	}
	
	/**
	 * Moves a rule down in the ruleset.
	 * 
	 * Simply switches the places of two rules.
	 * 
	 * @param int $ruleNumber The number of the rule to move down.
	 */
	function down($ruleNumber)
	{
		if (isset($this->rules[$ruleNumber + 1])) {
			$tmp= $this->rules[$ruleNumber + 1];
			$this->rules[$ruleNumber + 1]= $this->rules[$ruleNumber];
			$this->rules[$ruleNumber]= $tmp;
		}
	}
	
	/**
	 * Deletes a rule from the ruleset.
	 * 
	 * @attention We slice the array to update the keys, otherwise the unset() call leaves a hole in the ruleset indeces.
	 * 
	 * @param int $ruleNumber The number of the rule to delete.
	 */
	function del($ruleNumber)
	{
		unset($this->rules[$ruleNumber]);
		// Fake slice to update the keys
		$this->rules= array_slice($this->rules, 0);
	}
	
	/**
	 * Moves a rule to another location in the ruleset.
	 * 
	 * @param int $ruleNumber The number of the rule to move.
	 * @param int $moveTo The location to move the rule to.
	 */
	function move($ruleNumber, $moveTo)
	{
		if ($ruleNumber < 0 || $ruleNumber >= count($this->rules)) {
			PrintHelpWindow(_NOTICE('FAILED') . ': ' . _NOTICE('Invalid rule number') . ": $ruleNumber", 'auto', 'ERROR');
			return;
		}
		if ($moveTo < 0 || $moveTo >= count($this->rules) || $ruleNumber == $moveTo) {
			PrintHelpWindow(_NOTICE('FAILED') . ': ' . _NOTICE('Invalid destination rule number') . ": $moveTo", 'auto', 'ERROR');
			return;
		}

		$rule= $this->rules[$ruleNumber];
		unset($this->rules[$ruleNumber]);
		// array_slice() takes care of possible off-by-one error due to unset above
		$head= array_slice($this->rules, 0, $moveTo);
		$tail= array_slice($this->rules, $moveTo);
		$this->rules= array_merge($head, array($rule), $tail);
	}
	
	/**
	 * Adds an empty rule location to the ruleset.
	 * 
	 * @param int $ruleNumber The location of the new rule.
	 * @return int The actual rule location allocated.
	 */
	function add($ruleNumber= 0)
	{
		if (count($this->rules) == 0 || ($ruleNumber >= $this->nextRuleNumber())) {
			// Add the first rule or append a new one to the end
			array_push($this->rules, array());
			return $this->nextRuleNumber();
		} else {
			// Insert a new rule in the middle
			$head= array_slice($this->rules, 0, $ruleNumber);
			$tail= array_slice($this->rules, $ruleNumber);
			/// @attention We cannot simply insert an empty array(), array_merge() discards it
			// hence insert an array with an empty string element, which will be overwritten by the caller
			// or we could use array_push($head, array()) instead
			$this->rules= array_merge($head, array(''), $tail);
			return $ruleNumber;
		}
	}

	function comment($ruleNumber)
	{
		global $View;

		$retval= $View->Controller($output, 'GenerateRule', json_encode($this->rules[$ruleNumber]), $ruleNumber, 1);
		if (!$retval) {
			PrintHelpWindow(_NOTICE('ERROR') . ': ' . _NOTICE('Cannot generate rule'), 'auto', 'ERROR');
		}

		/// @attention Inline rules are multi-line, hence implode.
		$output= explode("\n", trim(implode("\n", $output)));
		for ($i= 0; $i < count($output); $i++) {
			$output[$i]= '# '.trim($output[$i]);
		}
		$ruleStr= implode("\n", $output);

		unset($output);
		$rulesArray= array();

		$retval= $View->Controller($output, 'ParseRules', json_encode($ruleStr), 1);
		if (!$retval) {
			PrintHelpWindow(_NOTICE('ERROR') . ': ' . _NOTICE('Cannot parse rules'), 'auto', 'ERROR');
		}

		$rulesArray= json_decode($output[0], TRUE)['rules'];

		$ruleSet= new RuleSet();
		$ruleSet->loadArray($rulesArray);

		unset($this->rules[$ruleNumber]);
		// array_slice() takes care of possible off-by-one error due to unset above
		$head= array_slice($this->rules, 0, $ruleNumber);
		$tail= array_slice($this->rules, $ruleNumber);
		$this->rules= array_merge($head, $ruleSet->rules, $tail);
	}

	function uncomment($ruleNumber)
	{
		global $View;

		$rulesArray= array();
		$retval= $View->Controller($output, 'ParseRules', json_encode($this->rules[$ruleNumber]->rule['comment']), 1);
		if (!$retval) {
			PrintHelpWindow(_NOTICE('ERROR') . ': ' . _NOTICE('Cannot parse rules'), 'auto', 'ERROR');
		}

		$rulesArray= json_decode($output[0], TRUE)['rules'];

		$ruleSet= new RuleSet();
		$ruleSet->loadArray($rulesArray);

		unset($this->rules[$ruleNumber]);
		// array_slice() takes care of possible off-by-one error due to unset above
		$head= array_slice($this->rules, 0, $ruleNumber);
		$tail= array_slice($this->rules, $ruleNumber);

		if (count($ruleSet->rules)) {
			$this->rules= array_merge($head, $ruleSet->rules, $tail);
		}
		else {
			$blank= new Blank();
			$blank->rule['blank']= "\n";
			$this->rules= array_merge($head, array($blank), $tail);
		}
	}

	function separate($ruleNumber)
	{
		global $View;

		$rulesArray= array();

		// Can be used to merge separated comments by reloading rules
		$lines= explode("\n", $this->rules[$ruleNumber]->rule['comment']);
		for ($i= 0; $i < count($lines); $i++) {
			$ruleStr= '# '.trim($lines[$i]);

			$retval= $View->Controller($parseOut, 'ParseRules', json_encode($ruleStr), 1);
			if (!$retval) {
				PrintHelpWindow(_NOTICE('ERROR') . ': ' . _NOTICE('Cannot parse rules'), 'auto', 'ERROR');
			}

			$rulesArray[]= json_decode($parseOut[0], TRUE)['rules'][0];
		}

		$ruleSet= new RuleSet();
		$ruleSet->loadArray($rulesArray);

		unset($this->rules[$ruleNumber]);
		// array_slice() takes care of possible off-by-one error due to unset above
		$head= array_slice($this->rules, 0, $ruleNumber);
		$tail= array_slice($this->rules, $ruleNumber);
		$this->rules= array_merge($head, $ruleSet->rules, $tail);
	}

	function parse()
	{
		global $View;

		// Merge comments by reloading rules
		$retval= $View->Controller($output, 'GenerateRules', json_encode($this->rules), 0, 1);
		if (!$retval) {
			PrintHelpWindow(_NOTICE('ERROR') . ': ' . _NOTICE('Cannot generate rules'), 'auto', 'ERROR');
		}

		$ruleStr= trim(implode("\n", $output));

		unset($output);
		$retval= $View->Controller($output, 'ParseRules', json_encode($ruleStr), 1);
		if (!$retval) {
			PrintHelpWindow(_NOTICE('ERROR') . ': ' . _NOTICE('Cannot parse rules'), 'auto', 'ERROR');
		}

		$rulesArray= json_decode($output[0], TRUE)['rules'];
		$this->loadArray($rulesArray);
	}
	
	/**
	 * Computes the actual rule number which can be allocated.
	 * 
	 * @param int $ruleNumber The rule number requested.
	 * @return int Actual rule number which can be allocated.
	 */
	function computeNewRuleNumber($ruleNumber= 0)
	{
		if (count($this->rules) == 0 || ($ruleNumber >= $this->nextRuleNumber())) {
			// Add the first rule or append a new one to the end
			return $this->nextRuleNumber();
		} else {
			// Insert a new rule in the middle
			return $ruleNumber;
		}
	}
		
	/**
	 * Returns the number of a new rule to be appended to the end of the ruleset.
	 * 
	 * This is simply the count of the rules in the ruleset, because the list is zero-based.
	 * 
	 * @return int New rule number.
	 */
	function nextRuleNumber()
	{
		return count($this->rules);
	}
	
	/**
	 * Sets the current state of the edit page and sets up the session vars.
	 * 
	 * If we change the rule number requested, perhaps because it is not a valid number in the ruleset,
	 * we change the current state to 'add'.
	 * 
	 * We change the state from 'create' to 'add' if the session properties do not match. Such a mismatch
	 * means that the user was editing a different rule previously.
	 * 
	 * If the state is 'edit' and the session variables are not set up yet, we clone the rule for editing.
	 * However, we also check if the user arrives the edit page by requesting on the address line, in which case
	 * we should make sure that the rule number exists and there was a previous edit session.
	 * 
	 * Note that the 'add' state means that we should reinitialize the session variables.
	 * 
	 * Note also that we always work on a clone of the rule, not the actual rule in the ruleset. Because we should
	 * not modify the rule until the user saves the changes s/he made.
	 * 
	 * One purpose of this state machine is that we should not reinitialize the session vars each time the user submits
	 * the edit page.
	 * 
	 * @todo We need a state diagram for this FSM.
	 * 
	 * @param string $cat Type of the current rule to be edited.
	 * @param string $action Current state of the edit page.
	 * @param int $ruleNumber Rule number.
	 */
	function setupEditSession($cat, &$action, &$ruleNumber)
	{
		global $View;

		// Make sure we deal only with rule numbers possible in the current ruleset
		if (!array_key_exists($ruleNumber, $View->RuleSet->rules)) {
			$ruleNumber= $this->computeNewRuleNumber($ruleNumber);
			if ($action == 'edit') {
				$action= 'add';
			}
		}

		if ($action == 'create') {
			if (!isset($_SESSION['edit']['object']) || $_SESSION['edit']['object']->cat!= $cat || $_SESSION['edit']['ruleNumber'] != $ruleNumber) {
				$action= 'add';
			}
		}

		if ($action == 'edit') {
			if (!isset($_SESSION['edit']['object']) || $_SESSION['edit']['object']->cat != $cat || $_SESSION['edit']['ruleNumber'] != $ruleNumber ||
				$View->RuleSet->rules[$ruleNumber]->cat != $cat) {
				// The rule being edited has changed, setup a new edit session
				if (array_key_exists($ruleNumber, $View->RuleSet->rules)) {
					// Rule exists, so clone from the ruleset
					unset($_SESSION['edit']);
					$_SESSION['edit']['ruleNumber']= $ruleNumber;
					$_SESSION['edit']['object']= clone $this->rules[$ruleNumber];
				} elseif (!isset($_SESSION['edit'])) {
					/// @attention Add and del operations on multi-valued vars use GET method, so check if there is an active edit session.
					// Rule does not exists, assume add if we are not already editing a new rule
					// Assume a new rule requested, if the page is submitted on the address line with a non-existing rule number
					$action= 'add';
				}
			}
		}

		if ($action == 'add') {
			// Create a new rule and setup a new edit session
			// Change action state to create, so we don't come back here to reinit session
			$action= 'create';
			unset($_SESSION['edit']);
			$_SESSION['edit']['ruleNumber']= $ruleNumber;
			$cat= __NAMESPACE__ . '\\' . $cat;
			$_SESSION['edit']['object']= new $cat('');
		}
	}

	/**
	 * Tests the given rule.
	 * 
	 * We create a rules array upto the rule number provided, and append the rule to be tested
	 * to the end. Note that the $ruleObj is the the clone rule modified on the edit page.
	 * 
	 * We simply use json encode and decode functions to create the rules array, because json
	 * encode function includes only the public member variables only. Note also that we pass
	 * TRUE for $assoc param, to convert objects to arrays.
	 * 
	 * @param int $ruleNumber The number of the rule to test.
	 * @param \View\Rule $ruleObj The rule object to test.
	 * @return bool Test result returned from the Model.
	 */
	function test($ruleNumber, $ruleObj)
	{
		global $View;
		
		$rulesArray= array_slice(json_decode(json_encode($this), TRUE)['rules'], 0, $ruleNumber);
		$rulesArray[]= json_decode(json_encode($ruleObj), TRUE);

		return $View->Controller($Output, 'TestRules', json_encode($rulesArray));
	}
	
	/**
	 * Cancels the edit session.
	 * 
	 * First checks if the user has clicked the Cancel button.
	 */
	function cancel()
	{
		if (filter_has_var(INPUT_POST, 'cancel') && (filter_input(INPUT_POST, 'cancel') == _CONTROL('Cancel'))) {
			unset($_SESSION['edit']);
			/// @todo Make conf.php a parameter
			header('Location: /pf/conf.php');
			exit;
		}
	}
	
	/**
	 * Saves the modified rule.
	 * 
	 * First checks if the user has clicked the Save button.
	 * 
	 * We don't save the rule if there are errors, unless the user forces saving.
	 * 
	 * We need the $action param to see if we are editing an existing rule or adding a new one.
	 * So if the user was editing a new rule, it does not exist in the ruleset yet. Because we do not
	 * modify the ruleset until the rule is saved.
	 * 
	 * @param string $action Current state of the edit page.
	 * @param int $ruleNumber Rule number.
	 * @param object $ruleObj Modified rule object to save.
	 * @param bool $testResult Test result of the rule.
	 */
	function save($action, $ruleNumber, $ruleObj, $testResult)
	{
		if (filter_has_var(INPUT_POST, 'save') && filter_input(INPUT_POST, 'save') == _CONTROL('Save')) {
			if ($testResult || filter_input(INPUT_POST, 'forcesave')) {
				if ($action == 'create') {
					$this->add($ruleNumber);
				}
				$this->rules[$ruleNumber]= $ruleObj;
				unset($_SESSION['edit']);
				/// @todo Make conf.php a parameter
				header('Location: /pf/conf.php');
				exit;
			}
		}
	}
	
	/**
	 * Checks if the rule is modified.
	 * 
	 * First checks if the rule already exists in the ruleset, i.e. the user is editing
	 * an existing rule. Otherwise, we always return TRUE for new rules.
	 * 
	 * We sort the keys because the modification of the rule on the edit page may result in
	 * different key orders.
	 * 
	 * Serialization allows for an easy comparison of the rules.
	 * 
	 * @param int $ruleNumber Rule number.
	 * @param object $ruleObj Rule object to check.
	 * @return bool TRUE if the rule is modified, or FALSE otherwise.
	 */
	function isModified($ruleNumber, $ruleObj)
	{
		$modified= TRUE;

		if (key_exists($ruleNumber, $this->rules)) {
			// Make sure keys are sorted before comparison
			$newRule= $ruleObj->rule;
			ksort($newRule);

			$origRule= $this->rules[$ruleNumber]->rule;
			ksort($origRule);

			if (serialize($newRule) === serialize($origRule)) {
				$modified= FALSE;
			}
		}

		return $modified;
	}
	
	/**
	 * Returns the list of queues defined in the current ruleset.
	 * 
	 * @return array List of queues.
	 */
	function getQueueNames()
	{
		$queues= array();
		foreach ($this->rules as $ruleObj) {
			if  ($ruleObj->cat == 'Queue' && isset($ruleObj->rule['name'])) {
				$queues[]= $ruleObj->rule['name'];
			}
		}
		return $queues;
	}
}
?>
