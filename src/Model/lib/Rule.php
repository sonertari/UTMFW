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
 * Contains the base class for all model rule types.
 */

namespace Model;

/**
 * Base class for all model rule types.
 */
class Rule
{
	/**
	 * Rule category.
	 */
	public $cat= '';

	/**
	 * Internal structure for rules.
	 */
	public $rule= array();
	
	protected $str= '';
	protected $index= 0;

	/**
	 * String to be parsed is split into words, this is the list holding those words.
	 */
	protected $words= array();

	protected $ruleNumber= 0;

	/**
	 * The keywords possible in this type of rule are accumulated in this array.
	 * 
	 * We search for such keywords while parsing the given strings. The parser functions are
	 * defined in 'method', and the parameters of this function are listed in an array in
	 * 'params'.
	 */
	protected $keywords = array();
	
	protected $keyInterface= array(
		'on' => array(
			'method' => 'parseItems',
			'params' => array('interface'),
			),
		);

	protected $keyAf= array(
		'inet' => array(
			'method' => 'parseNVP',
			'params' => array('af'),
			),
		'inet6' => array(
			'method' => 'parseNVP',
			'params' => array('af'),
			),
		);

	protected $keyLog= array(
		'log' => array(
			'method' => 'parseLog',
			'params' => array(),
			),
		);

	protected $keyQuick= array(
		'quick' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		);

	/**
	 * Type definitions for the rule category are accumulated in this member var.
	 */
	protected $typedef= array();

	protected $typeInterface= array(
		'interface' => array(
			'multi' => TRUE,
			'regex' => RE_IFSPEC,
			),
		);

	protected $typeAf= array(
		'af' => array(
			'regex' => RE_AF,
			),
		);

	/**
	 * Log can be of type either bool or array of values.
	 * 
	 * @attention Validate functions can handle multi-type values like this.
	 */
	protected $typeLog= array(
		'log' => array(
			'regex' => RE_BOOL,
			'values' => array(
				'all' => array(
					'regex' => RE_BOOL,
					),
				'matches' => array(
					'regex' => RE_BOOL,
					),
				'user' => array(
					'regex' => RE_BOOL,
					),
				'to' => array(
					'regex' => RE_IF,
					),
				),
			),
		);

	protected $typeQuick= array(
		'quick' => array(
			'regex' => RE_BOOL,
			),
		);

	protected $typeComment= array(
		'comment' => array(
			'regex' => RE_COMMENT_INLINE,
			),
		);

	/**
	 * Represents nesting number while reporting errors in anchor inline rules.
	 * 
	 * We allow for nested anchors in inline rules. If there are errors in such nested anchor rules,
	 * we report the nesting number of the anchor along with the line number. Otherwise, the line numbers
	 * reported in the errors are restarted from 0. This is because we create a new RuleSet object for
	 * each inline ruleset.
	 * 
	 * @bug Doxygen removes all documentation for a member var if var tag is used here.
	 */
	protected $nestingStr;

	/**
	 * Creates and initializes the rule object with the given rule string.
	 * 
	 * We first set the nesting information, which is used in reporting errors occurred during parsing.
	 * Hence this string should be set before parsing the given string.
	 * 
	 * We also set the category or type of the rule.
	 * 
	 * Finally, we parse the given string, if not empty.
	 *
	 * @param string $str String to parse.
	 */
	function __construct($str)
	{
		global $Nesting;
		$this->nestingStr= $Nesting > 0 ? "Nesting $Nesting, " : '';

		$this->cat= str_replace(__NAMESPACE__ . '\\', '', get_called_class());

		if ($str != '') {
			$this->parse($str);
		}
	}

	/**
	 * Initializes the rule object with the given data.
	 * 
	 * We first assign the given rule number to $ruleNumber. Rule objects cannot and should not have
	 * a fixed rule numbers actually. However, we do this only for the Model, not the View. Otherwise,
	 * the View can change this rule number freely, such as when the user moves the rule up or down
	 * in the rules list. Having fixed rule numbers would make such operations difficult.
	 * 
	 * We then validate the given rule array. If there are no validation errors, we simply assign the given
	 * rule array to the rule member variable. Even if validation fails, we can force this assignment
	 * using the $force parameter. 
	 *
	 * @param array $arr Rule array.
	 * @param int $ruleNumber Rule number.
	 * @param bool $force To force loading rules with validation errors.
	 * @return bool TRUE if validated, FALSE if not.
	 */
	function load($arr, $ruleNumber= 0, $force= FALSE)
	{
		$this->ruleNumber= $ruleNumber;

		$retval= $this->validate($arr, $force);
		if ($retval || $force) {
			$this->rule= $arr;
		}
		return $retval;
	}

	/**
	 * Validates rule array.
	 * 
	 * The rule array is the internal representation of pf rules. Types of the elements in different rule
	 * categories are defined in typedefs.
	 * 
	 * We try to match the keys in $ruleArray with the ones in $typedef. If the keys match,
	 * we apply the validation method defined in the typedef struct to the value of the key in the rule array.
	 *
	 * @param array $ruleArray Rule array.
	 * @param bool $force Parameter to pass to validation function, currently just IsInlineAnchor().
	 * @return bool TRUE if validated, FALSE if not.
	 */
	function validate($ruleArray, $force= FALSE)
	{
		/// Clone the rule array, because we delete array elements after validation.
		$arr= $ruleArray;
		foreach ($this->typedef as $key => $def) {
			if (!$this->validateKeyDef($arr, $key, $def, '', $force)) {
				return FALSE;
			}
		}

		if (count($arr) > 0) {
			Error($this->nestingStr . _('Rule') . " $this->ruleNumber: " . _('Validation Error') . ': ' . _('Unexpected elements') . ': ' . implode(', ', array_keys($arr)));
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, $this->nestingStr . "Rule $this->ruleNumber: Validation Error: Unexpected elements: " . implode(', ', array_keys($arr)));
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Finds the validation method to apply to rule value.
	 * 
	 * If the value is an array, we call a recursive method. This is so, because array elements
	 * may have other arrays in them too.
	 * 
	 * If the value is not an array, we directly apply the validation method. This ends the recursion, if any.
	 * 
	 * The rule array should have only the elements defined in typedef struct, and those elements only.
	 * Extra elements are considered invalid. We delete the key in the rule after applying the validation method,
	 * because after applying all defined validation methods, we'd like to see if there are any extra elements left
	 * which are not defined in typedef. This method is easier than diffing the rule array against the typedef.
	 * Note that the rule array we operate on is the clone of the actual rule array, hence we can freely delete elements.
	 *
	 * If a key in typedef is marked as 'require', it should exist in the rule array, otherwise is an error.
	 * 
	 * @param array $arr Rule array.
	 * @param string $key Key to search in both rule array and typedef.
	 * @param array $def Validation definition.
	 * @param string $parent If the value is an array within another array, this param represents the key of the parent array. Used in error reporting.
	 * @param bool $force Parameter to pass to validation function.
	 * @return bool TRUE if validated, FALSE if not.
	 */
	function validateKeyDef(&$arr, $key, $def, $parent, $force= FALSE)
	{
		if (array_key_exists($key, $arr)) {
			if (is_array($arr[$key])) {
				// Recursion
				if (!$this->validateArrayValues($arr[$key], $key, $def, $parent, $force)) {
					return FALSE;
				}
			} elseif (!$this->validateValue($key, $arr[$key], $def, $parent, $force)) {
				return FALSE;
			}
			unset($arr[$key]);
		} elseif (isset($def['require']) && $def['require']) {
			Error($this->nestingStr . _('Rule') . " $this->ruleNumber: " . _('Validation Error') . ': ' . _('Required element missing') . ': ' . ltrim("$parent.$key", '.'));
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, $this->nestingStr . "Rule $this->ruleNumber: Validation Error: Required element missing: " . ltrim("$parent.$key", '.'));
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Applies validation method to values in rule array.
	 * 
	 * Certain rule elements can have multiple values, such as source and destination addresses. These are marked as
	 * 'multi' in typedef. So we apply the same validation method to each value in the rule element.
	 * 
	 * Some rule elements can have multiple elements, such as limit definitions. These are marked as 'values' in typedef.
	 * This basically means that the rule element contains another rule array in it. So we call validateKeyDef() method
	 * to validate such embedded arrays recursively. This is the start of recursion.
	 * 
	 * Since we delete the array elements after applying the validation method, the number of elements in the array should
	 * be 0 while exiting. Otherwise is a violation.
	 * 
	 * Since this method is called for rule elements of array type only, it is a validation error if the definition for
	 * the rule element does not have 'multi' or 'values'.
	 * 
	 * @param array $arr Rule array.
	 * @param string $key Key to search in both rule array and typedef.
	 * @param array $def Validation definition.
	 * @param string $parent If the value is an array within another array, this param represents the key of the parent array. Used in error reporting.
	 * @param bool $force Parameter to pass to validation function.
	 * @return bool TRUE if validated, FALSE if not.
	 */
	function validateArrayValues(&$arr, $key, $def, $parent, $force= FALSE)
	{
		if (isset($def['multi']) && $def['multi']) {
			foreach ($arr as $v) {
				if (!$this->validateValue($key, $v, $def, $parent, $force)) {
					return FALSE;
				}
			}
		} elseif (isset($def['values']) && is_array($def['values'])) {
			foreach ($def['values'] as $k => $d) {
				// Recursion
				if (!$this->validateKeyDef($arr, $k, $d, $key, $force)) {
					return FALSE;
				}
			}

			if (count($arr) > 0) {
				Error($this->nestingStr . _('Rule') . " $this->ruleNumber: " . _('Validation Error') . ': ' . _('Unexpected elements') . ': ' . ltrim("$parent.$key", '.') . ' ' . implode(', ', array_keys($arr)));
				ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, $this->nestingStr . "Rule $this->ruleNumber: Validation Error: Unexpected elements: " . ltrim("$parent.$key", '.') . ' ' . implode(', ', array_keys($arr)));
				return FALSE;
			}
		} else {
			Error($this->nestingStr . _('Rule') . " $this->ruleNumber: " . _('Validation Error') . ': ' . _('Multiple values not allowed for') . ' ' . ltrim("$parent.$key", '.'));
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, $this->nestingStr . "Rule $this->ruleNumber: Validation Error: Multiple values not allowed for " . ltrim("$parent.$key", '.'));
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Validates a value.
	 * 
	 * This is where we apply validation methods to values.
	 * 
	 * Typedef may be a regular expression defined in 'regex' value. We apply this regex to the value using preg_match().
	 * 
	 * Typedef may instruct us to apply a validation function to the value. The name of the function is defined in 'func' value.
	 * Since we call this function directly, it should be a regular function defined in global namespace, not a class member.
	 * 
	 * If the validation function can be forced, it is marked using 'force'. In this case, we pass the $force param to the
	 * validation function.
	 * 
	 * Validation method should be defined as a regex or a function. Otherwise is an error.
	 * 
	 * @param string $key Key to search in both rule array and typedef.
	 * @param mixed $value Value to validate, array or string.
	 * @param array $def Validation definition.
	 * @param string $parent If the value is an array within another array, this param represents the key of the parent array. Used in error reporting.
	 * @param bool $force Parameter to pass to validation function.
	 * @return bool TRUE if validated, FALSE if not.
	 */
	function validateValue($key, $value, $def, $parent, $force= FALSE)
	{
		if (isset($def['regex'])) {
			$rxfn= $def['regex'];
			$result= preg_match("/$rxfn/", $value);
		} elseif (isset($def['func'])) {
			$rxfn= $def['func'];
			/// @todo Should we use call_user_func_array() and pass params in an array?
			if (isset($def['force']) && $def['force']) {
				$result= $rxfn($value, $force);
			} else {
				$result= $rxfn($value);
			}
		} else {
			Error($this->nestingStr . _('Rule') . " $this->ruleNumber: " . _('Validation Error') . ': ' . _('No regex or func def for') . ' ' . ltrim("$parent.$key", '.'));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, $this->nestingStr . "Rule $this->ruleNumber: Validation Error: No regex or func def for " . ltrim("$parent.$key", '.'));
			return FALSE;
		}

		if (!$result) {
			Error($this->nestingStr . _('Rule') . " $this->ruleNumber: " . _('Validation Error') . ': ' . _('Invalid value for') . " '" . ltrim("$parent.$key", '.') . "': <pre>" . htmlentities(print_r($value, TRUE)) . '</pre>');
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, $this->nestingStr . "Rule $this->ruleNumber: Validation Error: Invalid value for '" . ltrim("$parent.$key", '.') . "': " . print_r($value, TRUE));
			return FALSE;
		} else {
			ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, $this->nestingStr . "Rule $this->ruleNumber: Valid value for '" . ltrim("$parent.$key", '.') . "': " . print_r($value, TRUE) . ", $rxfn");
		}
		return TRUE;
	}

	/**
	 * Parses the given rule string.
	 * 
	 * We first fetch any inline comments and sanitize the given string.
	 * 
	 * We then split the string into words. Each rule type defines the keywords they accept. So we iterate
	 * over these split words to match them with the keywords. When a match is found, the method defined
	 * for the keyword is executed. The method fills the internal rule structure by fetching words
	 * from the words list, not just the matching keyword, hence may advance the word index beyond the current word.
	 * 
	 * @param string $str String to parse.
	 */
	function parse($str)
	{
		$this->str= $str;
		$this->init();
		$this->parseComment();
		$this->sanitize();
		$this->split();

		for ($this->index= 0; $this->index < count($this->words); $this->index++) {
			$key= $this->words[$this->index];
			if (array_key_exists($key, $this->keywords)) {
				$method= $this->keywords[$key]['method'];				
				if (is_callable($method, TRUE)) {
					call_user_func_array(array($this, $method), $this->keywords[$key]['params']);
				} else {
					ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Parser method '$method' not callable");
				}
			} else {
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Word '$key' not in keywords");
			}
		}
	}

	function init()
	{
		$this->rule= array();
	}

	/**
	 * Parses inline comments.
	 * 
	 * We get any inline comments and update the string by removing the comment. Such comments
	 * do not need further parsing or processing.
	 */
	function parseComment()
	{
		$pos= strpos($this->str, '#');
		if ($pos) {
			$this->rule['comment']= trim(substr($this->str, $pos + 1));
			$this->str= substr($this->str, 0, $pos);
		}
	}

	/**
	 * Sanitizes rule string.
	 * 
	 * Sanitization step prepares the given strings by making them obey certain standards, so that we only
	 * deal with the same format. However, note that sanitization may differ in different rule types,
	 * hence this method may be overridden in child classes.
	 */
	function sanitize()
	{
		$this->str= preg_replace('/! +/', '!', $this->str);
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/\(/', ' ( ', $this->str);
		$this->str= preg_replace('/\)/', ' ) ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
		$this->str= preg_replace('/"/', ' " ', $this->str);
	}

	/**
	 * Splits rule string into words.
	 * 
	 * The string is split into words at certain types of chars, such as spaces, commas, and tabs.
	 * However, this method is overridden for special processing in Anchor and Timeout classes.
	 * 
	 * We don't set any limit on the number of words, but ask for non-empty results only.
	 */
	function split()
	{
		$this->words= preg_split('/[\s,\t]+/', $this->str, -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Checks if the index points at the last word in the list.
	 * 
	 * @attention This is a very important check used in while loops advancing the index. If we
	 * do not check this, the while loop goes into an infinite loop: The index keeps advancing
	 * until the stack overflows and PHP simply gives notices for off-by-N errors.
	 */
	function isEndOfWords()
	{
		return $this->index >= count($this->words);
	}

	/**
	 * Assigns the current word to the given key.
	 * 
	 * @param string $key Key in the rule array to assign value to.
	 */
	function parseNVP($key)
	{
		$this->rule[$key]= $this->words[$this->index];
	}

	/**
	 * Assigns the current word to the given key and increments the index.
	 * 
	 * @param string $key Key in the rule array to assign value to.
	 */
	function parseNVPInc($key)
	{
		$this->rule[$key]= $this->words[$this->index++];
	}

	/**
	 * Assigns the next word to the current word as key and increments the index.
	 * 
	 * Removes any quotes.
	 */
	function parseNextValue()
	{
		$this->rule[$this->words[$this->index]]= preg_replace('/"/', '', $this->words[++$this->index]);
	}

	/**
	 * Assigns the next word to the given key and increments the index.
	 * 
	 * @param string $key Key in the rule array to assign value to.
	 */
	function parseNextNVP($key)
	{
		$this->rule[$key]= $this->words[++$this->index];
	}

	/**
	 * Assigns TRUE to the current word as key.
	 */
	function parseBool()
	{
		$this->rule[$this->words[$this->index]]= TRUE;
	}
	
	/**
	 * Assigns the items in delimiters to the given key.
	 * 
	 * Note that each item may span multiple words: parseParenthesized().
	 * 
	 * @param string $key Key in the rule array to assign value to.
	 * @param string $delimPre Opening delimiter.
	 * @param string $delimPost Closing delimiter.
	 */
	function parseItems($key, $delimPre= '{', $delimPost= '}')
	{
		$this->rule[$key]= $this->parseItem($delimPre, $delimPost);		
	}
	
	/**
	 * Gets the items in delimiters and increments the index once for each word.
	 * 
	 * There may be only one item between delimiters. In this case, we may need to convert
	 * the array into a simple value by calling FlattenArray().
	 * 
	 * Note that the index points to the closing delimiter while exiting.
	 * 
	 * @param string $delimPre Opening delimiter.
	 * @param string $delimPost Closing delimiter.
	 */
	function parseItem($delimPre= '{', $delimPost= '}')
	{
		$this->index++;
		if (($this->words[$this->index] == $delimPre)) {
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != $delimPost && !$this->isEndOfWords()) {
				$value[]= $this->parseParenthesized();
			}
		} else {
			// ($ext_if)
			$value[]= $this->parseParenthesized();
			FlattenArray($value);
		}
		return $value;
	}

	/**
	 * Gets the items in parentheses and increments the index once for each word.
	 * 
	 * Items may have more complex structures than simple words.
	 * 
	 * We also get any trailing definitions for weight and net mask.
	 */
	function parseParenthesized()
	{
		if ($this->words[$this->index] == '(') {
			while ($this->words[++$this->index] != ')' && !$this->isEndOfWords()) {
				$items[]= $this->words[$this->index];
			}
			$retval= '(' . implode(' ', $items) . ')';
		} else {
			// IP range, routehost = host "@" interface-name, IP net
			if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == '-' || $this->words[$this->index + 1] == '@' || $this->words[$this->index + 1] == '/')) {
				$retval= $this->words[$this->index] . ' ' . $this->words[$this->index + 1] . ' ' . $this->words[$this->index + 2];
				$this->index+= 2;
			// icmp-type 1 code 2
			} elseif (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == 'code')) {
				$retval= $this->words[$this->index] . ' ' . $this->words[$this->index + 1] . ' ' . $this->words[$this->index + 2];
				$this->index+= 2;
			} else {
				$retval= $this->words[$this->index];
			}
		}

		// address [ "weight" number ] | address [ "/" mask-bits ] [ "weight" number ]
		if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == 'weight')) {
			$retval= $this->words[$this->index] . ' ' . $this->words[$this->index + 1] . ' ' . $this->words[$this->index + 2];
			$this->index+= 2;
		}

		return $retval;
	}

	/**
	 * Gets port item(s) and increments the index once for each word.
	 * 
	 * Items may have more complex structures than simple words.
	 */
	function parsePortItem()
	{
		$this->index++;
		if ($this->words[$this->index] == '{') {
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}' && !$this->isEndOfWords()) {
				$this->words[$this->index]= preg_replace('/[\s,]+/', '', $this->words[$this->index]);
				$value[]= $this->parsePort();
			}
		} else {
			$value= $this->parsePort();
		}
		return $value;
	}

	/**
	 * Gets a port item and increments the index once for each word.
	 * 
	 * This methods gets only one item, which may have a more complex structure than a simple word.
	 */
	function parsePort()
	{
		if (in_array($this->words[$this->index], array('=', '!=', '<', '<=', '>', '>='))) {
			// unary-op = [ "=" | "!=" | "<" | "<=" | ">" | ">=" ] ( name | number )
			return $this->words[$this->index] . ' ' . $this->words[++$this->index];
		} elseif (isset($this->words[$this->index + 1]) && (in_array($this->words[$this->index + 1], array('<>', '><', ':')))) {
			// binary-op = number ( "<>" | "><" | ":" ) number
			// portspec = "port" ( number | name ) [ ":" ( "*" | number | name ) ]
			return $this->words[$this->index] . ' ' . $this->words[++$this->index] . ' ' . $this->words[++$this->index];
		} else {
			// ( name | number )
			return $this->words[$this->index];
		}
	}

	/**
	 * Gets the string in delimiters and assign it to the given key.
	 * 
 	 * @param string $key Key in the rule array to assign value to.
	 * @param string $delimPre Opening delimiter.
	 * @param string $delimPost Closing delimiter.
	 */
	function parseDelimitedStr($key, $delimPre= '"', $delimPost= '"')
	{
		$this->index++;
		$this->rule[$key]= $this->parseString($delimPre, $delimPost);		
	}

	/**
	 * Gets the string in delimiters and increments the index once for each word.
	 * 
	 * The string may contain single or multiple words.
	 * 
	 * We trim the string before returning.
	 * 
	 * @param string $delimPre Opening delimiter.
	 * @param string $delimPost Closing delimiter.
	 */
	function parseString($delimPre= '"', $delimPost= '"')
	{
		$value= '';
		if ($this->words[$this->index] == $delimPre) {
			while ($this->words[++$this->index] != $delimPost && !$this->isEndOfWords()) {
				$value.= ' ' . $this->words[$this->index];
			}
		} else {
			$value= $this->words[$this->index];
		}
		return trim($value);
	}

	/**
	 * Assigns any to from or to keys.
	 * 
	 * This is the method called when an 'any' key is found in the words list.
	 * 
	 * Any may be used for either source or destination. Source has precedence.
	 */
	function parseAny()
	{
		if (!isset($this->rule['from'])) {
			$this->rule['from']= 'any';
		} else {
			$this->rule['to']= 'any';
		}
	}

	/**
	 * Gets source or destination addresses and/or ports in the rule string.
	 * 
	 * This method is called if the parser finds a 'from' or 'to' keyword in the rule string.
	 * Initially, the word index points to that keyword.
	 * 
	 * We first determine if we are going to parse an address or a port portion of a host definition.
	 * Note that host definition may contain port only, without any host address.
	 * 
	 * As seen in pfctl BNF syntax definition, the address portion may be a route. If that's the case,
	 * we first set the key to 'fromroute' or 'toroute'. Otherwise, what follows may be a list of addresses.
	 * 
	 * If there is a port definition, we assign it to the port key given.
	 * 
 	 * @param string $portKey Src or dest port in the rule array to assign port to.
	 */
	function parseSrcDest($portKey)
	{
		if ($this->words[$this->index + 1] != 'port') {
			if ($this->words[$this->index + 1] == 'route') {
				$hostKey= $this->words[$this->index];
				$this->index+= 2;
				$this->rule[$hostKey . 'route']= $this->words[$this->index];
			} else {
				$this->parseItems($this->words[$this->index]);
			}
		}
		if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == 'port')) {
			$this->index++;
			$this->rule[$portKey]= $this->parsePortItem();
		}
	}

	/**
	 * Gets OS in host definition in the rule string.
	 * 
	 * This method is called if the parser finds an 'os' keyword in the rule string.
	 * 
	 * There may be multiple OSs listed. Note that OS keywords may be in quotes.
	 */
	function parseOS()
	{
		$this->index++;
		if ($this->words[$this->index] == '{') {
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}' && !$this->isEndOfWords()) {
				$this->rule['os'][]= $this->parseString();		
			}
		} else {
			$this->rule['os']= $this->parseString();		
		}
	}

	/**
	 * Gets log specification in the rule string.
	 * 
	 * This method is called if the parser finds a 'log' keyword in the rule string.
	 * 
	 * If there is no further logging details, we simply set logging to TRUE. Otherwise,
	 * any such specification must be in parentheses. In this case, we use an array which
	 * holds the values of the log key.
	 * 
	 * Therefore, checking if logging is enabled for a rule reduces to checking if
	 * the log key exists, not if it is set to TRUE or contains an array.
	 */
	function parseLog()
	{
		if ($this->words[$this->index + 1] == '(') {
			$opts= $this->parseItem('(', ')');
			$this->rule['log']= array();
			for ($i= 0; $i < count($opts); $i++) {
				if ($opts[$i] == 'to') {
					$this->rule['log']['to']= $opts[++$i];
				} else {
					$this->rule['log'][$opts[$i]]= TRUE;
				}
			}
		} else {
			$this->rule['log']= TRUE;
		}
	}
	
	/**
	 * Prints the given key.
	 * 
 	 * @param string $key Key to print.
	 */
	function genKey($key)
	{
		if (isset($this->rule[$key])) {
			$this->str.= ' ' . $key;
		}
	}

	/**
	 * Prints the value of the given key.
	 * 
	 * Caller may supply a leading and/or a trailing string to print.
	 * 
 	 * @param string $key Key to print the value of.
 	 * @param string $head Optional leading string, may be NULL.
 	 * @param string $tail Optional trailing string.
	 */
	function genValue($key, $head= '', $tail= '')
	{
		if (isset($this->rule[$key])) {
			$this->str.= ' ' . $head . $this->rule[$key] . $tail;
		}
	}

	/**
	 * Prints the values of the given key.
	 * 
	 * Puts the values between opening and closing delimiters.
	 * 
 	 * @param string $key Key to print the value of.
 	 * @param string $head Optional leading string.
	 * @param string $delimPre Opening delimiter.
	 * @param string $delimPost Closing delimiter.
	 */
	function genItems($key, $head= '', $delimPre= '{', $delimPost= '}')
	{
		if (isset($this->rule[$key])) {
			$this->str.= $this->generateItem($this->rule[$key], $head, $delimPre, $delimPost);
		}
	}

	/**
	 * Prints the given values in delimiters.
	 * 
 	 * @param mixed $items Values to print, array or string.
 	 * @param string $head Optional leading string.
	 * @param string $delimPre Opening delimiter.
	 * @param string $delimPost Closing delimiter.
	 */
	function generateItem($items, $head= '', $delimPre= '{', $delimPost= '}')
	{
		$head= $head == '' ? '' : ' ' . trim($head);
		if (is_array($items)) {
			return $head . " $delimPre " . implode(', ', $items) . " $delimPost";
		} else {
			return $head . ' ' . $items;
		}
	}

	/**
	 * Prints interfaces with a leading 'on' keyword.
	 */
	function genInterface()
	{
		$this->genItems('interface', 'on');
	}

	/**
	 * Prints inline comment with a leading # char.
	 */
	function genComment()
	{
		if (isset($this->rule['comment'])) {
			$this->str.= ' # ' . trim(stripslashes($this->rule['comment']));
		}
	}

	/**
	 * Prints log definition of the rule.
	 */
	function genLog()
	{
		if (isset($this->rule['log'])) {
			if (is_array($this->rule['log'])) {
				$s= ' log ( ';
				foreach ($this->rule['log'] as $k => $v) {
					$s.= (is_bool($v) ? "$k" : "$k $v") . ', ';
				}
				$this->str.= rtrim($s, ', ') . ' )';
			} else {
				$this->str.= ' log';
			}
		}
	}
}
?>
