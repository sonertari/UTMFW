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

/** @file
 * Contains the RuleSet class.
 */

/**
 * @namespace Model
 * 
 * Model of MVC: Generally, the Model is responsible for executing commands from the View.
 * 
 * In PFRE, the Model:
 * \li Loads rules from strings given to it or from files in the file system
 * \li Generates rules or rulesets given by the View
 * \li Validates the values in rules: Untaints all externally supplied data
 * \li Tests rules using pfctl, and returns the test results
 * \li Installs, saves, or deletes rule files
 * \li Authenticates users
 * \li Configures PFRE settings
 */
namespace SSLproxy;

/**
 * Loads, validates, parses, and generates a list of rules.
 */
class RuleSet
{
	public $rules= array();
	
	/**
	 * Loads the rule definitions given in an array.
	 * 
	 * The rule definitions in $rulesArray contains the type and NVP elements of the rule.
	 * For each item in $rulesArray, we create an empty rule object of type mentioned in the rule definition and
	 * call the load method of the rule with the NVP elements.
	 * 
	 * Rule number is determined by the order of the rule in the given $rulesArray.
	 * 
	 * If loading produces errors, we can force loading by setting $force to TRUE. The user is allowed to do this on the
	 * WUI, so that rule sets with errors can be loaded and fixed using PFRE.
	 * 
	 * Note that loading does not stop even if $force is set to FALSE, we partially load the current rule with error and
	 * continue loading the next rule in the array.
	 * 
	 * Also, this is where we set the nesting string, which is used in reporting errors in structured proxyspecs.
	 *
	 * @param array $rulesArray List of rule definitions in an array.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool Load result.
	 */
	function load($rulesArray, $force= FALSE)
	{
		global $Nesting;
		$nestingStr= $Nesting > 0 ? "Nesting $Nesting, " : '';

		$this->deleteRules();
	
		$retval= TRUE;
		$ruleNumber= 0;
		foreach ($rulesArray as $ruleDef) {
			$cat= __NAMESPACE__ . '\\' . $ruleDef['cat'];
			$ruleObj= new $cat('');
			if (!$ruleObj->load($ruleDef['rule'], $ruleNumber, $force)) {
				if (!$force) {
					Error($nestingStr . _('Rule') . " $ruleNumber: " . _('Error loading, rule loaded partially'));
					ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, $nestingStr . "Rule $ruleNumber: Error loading, rule loaded partially");
				} else {
					Error($nestingStr . _('Rule') . " $ruleNumber: " . _('Error loading, rule load forced'));
					ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, $nestingStr . "Rule $ruleNumber: Error loading, rule load forced");
				}
				$retval= FALSE;
			}
			$this->rules[]= $ruleObj;
			$ruleNumber++;
		}
		return $retval;
	}
	
	/**
	 * Deletes all the rules in the ruleset.
	 */
	function deleteRules()
	{
		$this->rules= array();
	}
	
	/**
	 * Parses the given rule string.
	 * 
	 * These are the steps:
	 * \li First delete any existing rules
	 * \li Sort out inline comments and new lines
	 * \li Divide the given string into lines, which are to be parsed by rule objects
	 * \li Determine the type of rule on each line
	 * \li Insert any accumulated comments or blank lines
	 * \li Collect structured proxyspecs which span multiple lines
	 * \li Finally create a rule object of the type determined above and pass the line to be parsed to that object
	 * 
	 * We validate the ruleset after all those steps are completed.
	 *
	 * @param string $text Rule set in string format to parse.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool Validation result.
	 */
	function parse($text, $force= FALSE)
	{
		$this->deleteRules();
		$ruleLines= array();

		$text= preg_replace("/\n#/", "\n# ", $text);
		$text= str_replace("\\\n", '', $text);

		$ruleLines= explode("\n", $text);

		$blank= '';

		for ($index= 0; $index < count($ruleLines); $index++) {
			$str= $ruleLines[$index];
			$words= preg_split('/[\s,\t]+/', trim($str), -1);
			
			$type= $words[0];
            // Do not search in comment lines
			if ($type !== '' && $type !== '#') {
				if (preg_match('/\s+(Divert|Split|Pass|Block|Match)\s+/', $str, $match)) {
					$type= $match[1];
				}
			}

			// Add any accumulated comment or blank lines if a non-comment/blank rule is next
			if ($type !== '' && $type !== '#') {
				if (isset($comment)) {
					$this->rules[]= new Comment($comment);
					unset($comment);
				}
				if ($blank != '') {
					$this->rules[]= new Blank($blank);
					$blank= '';
				}
			}

			if ($type === 'ProxySpec' && preg_match('/^.*{\s*$/', $str)) {
				$this->parseInlineRules($ruleLines, $str, $index, $force);
			}

			switch ($type) {
				case '':
					if (isset($comment)) {
						$this->rules[]= new Comment($comment);
						unset($comment);
					}
					$blank.= "\n";
					break;
				case '#':
					if ($blank != '') {
						$this->rules[]= new Blank($blank);
						$blank= '';
					}
					if (!isset($comment)) {
						$comment= trim(substr($str, 1));
					} else {
						$comment.= "\n" . trim(substr($str, 1));
					}
					break;
				case 'Include':
					$this->rules[]= new _Include($str);
					break;
				case 'Define':
					$this->rules[]= new Macro($str);
					break;
				case 'Divert':
					if (($words[1] == 'yes') || ($words[1] == 'no')) {
						$this->rules[]= new Option($str);
						break;
					}
					/* fall-through */
				case 'Split':
				case 'Pass':
				case 'Block':
				case 'Match':
					$this->rules[]= new Filter($str);
					break;
				case 'ProxySpec':
					if ($words[1] == '{')
						$this->rules[]= new ProxySpecStruct($str);
					else
						$this->rules[]= new ProxySpecLine($str);
					break;
				default:
					$this->rules[]= new Option($str);
					break;
			}
		}
        
        // Necessary if there is no non-comment rule at the end of ruleset
        if (isset($comment)) {
            $this->rules[]= new Comment($comment);
        }
        /// @attention Do not append accumulated blank lines to the end
		
		return $this->validate($force);
	}

	/**
	 * Validates the ruleset.
	 * 
	 * Since encoding and decoding the rules array produce an array with the elements we need,
	 * we reload the already loaded ruleset using json encode and decode functions. The load
	 * method validates the rules in the ruleset.
	 *
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool Validation result.
	 */
	function validate($force= FALSE)
	{
		// Reload for validation
		$rulesArray= json_decode(json_encode($this->rules), TRUE);
		if (!$this->load($rulesArray, $force)) {
			Error(_('Load Error') . ': ' . _('Ruleset contains errors'));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Load Error: Ruleset contains errors');
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Collects structured proxyspecs spanning multiple lines.
	 * 
	 * Inline rule starts with an opening brace at the end of the structured proxyspec and ends with
	 * a closing brace at the beginning of the last line, possibly followed by an inline comment.
	 * 
	 * Since structured proxyspecs span multiple lines, we combine those lines and append them at the
	 * end of the structured proxyspec after the 'inline' keyword. This process advances the line index
	 * to the line following the last inline rule.
	 * 
	 * @param array $ruleLines Rule strings in array format.
	 * @param string $str Current structured proxyspec in string format.
	 * @param int $index Index pointing at the current line.
	 * @param bool $force Forces collection of structured proxyspecs even after nesting limit is reached.
	 */
	function parseInlineRules($ruleLines, &$str, &$index, $force= FALSE)
	{
		if (preg_match('/^(.*){\s*$/', $str, $match)) {
			$str= $match[1] . ' inline ';

			$nesting= 1;
			$index++;
			while ($index < count($ruleLines)) {
				$line= $ruleLines[$index];

				// proxyspec-close = "}", but there may be a comment after it, hence match
				if (!preg_match('/^\s*}(.*)$/', $line, $match)) {
					$str.= "$line\n";
					/// @todo Use recursion instead?
					if (preg_match('/^.*{\s*$/', $line)) {
						// Do not allow more than one structured proxyspecs
						if (++$nesting > 1) {
							Error(_('Parse Error') . ': ' . _('Reached max nesting for structured proxyspecs') . ': <pre>' . htmlentities(print_r($line, TRUE)) . '</pre>');
							ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Parse Error: Reached max nesting for structured proxyspecs: $line");
							if (!$force) {
								break;
							}
						}
					}
				} else {
					if (--$nesting == 0) {
						// Discard the last proxyspec-close, keep the trailing text
						$str.= $match[1] . "\n";
						break;
					} else {
						// Don't discard the proxyspec-close of structured proxyspecs
						$str.= "$line\n";
					}
				}
				$index++;
			}
		}
	}
	
	/**
	 * Prints the ruleset.
	 * 
	 * We iterate over all the rule objects and ask them to return string representations of themselves.
	 * We combine these strings and print on the Display page.
	 * 
	 * The default on the Display page is to print line numbers in front of each line. 
	 *
	 * @param bool $printNumbers Print line numbers.
	 * @return string Generated rules.
	 */
	function generate($printNumbers= FALSE)
	{
		$str= '';
		foreach ($this->rules as $rule) {
			$str.= $rule->generate();
		}
        
		// Do not merge this loop with the generate loop above, because there are rules which produce
		// multiline string representations, such as comments and structured proxyspecs.
		if ($printNumbers) {
			$ruleNumber= 0;
			$s= '';
			foreach (explode("\n", $str) as $line) {
				$s.= sprintf('% 4d', $ruleNumber++) . ": $line\n";
			}
			$str= $s;
		}
		return $str;
	}
}
?>
