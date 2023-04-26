<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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

namespace Model;

/**
 * Keeps the count of nested anchors in inline rules.
 */
$Nesting= 0;

/**
 * Class for Anchor rules.
 */
class Anchor extends FilterBase
{	
	/**
	 * Keywords for anchor rules.
	 * 
	 * Identifier can be empty, but "anchors without explicit rules must specify a name".
	 * 
	 * 'inline' keyword is inserted by the anchor parser.
	 */
	protected $keyAnchor= array(
		'anchor' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('identifier'),
			),
		'inline' => array(
			'method' => 'parseNextNVP',
			'params' => array('inline'),
			),
		);

	/**
	 * Type definition for anchor rules.
	 * 
	 * IsInlineAnchor() validates inline rules.
	 * 
	 * 'force' element instructs type checker to pass the $force param to IsInlineAnchor().
	 * Otherwise, this does not mean that rule loading will be forced.
	 */
	protected $typeAnchor= array(
		'identifier' => array(
			'regex' => RE_ANCHOR_ID,
			),
		'inline' => array(
			'func' => 'Model\\IsInlineAnchor',
			'force' => TRUE,
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyAnchor;

		$this->typedef= $this->typeAnchor;

		parent::__construct($str);
	}

	/**
	 * Sanitizes anchor rule sting.
	 * 
	 * We should not sanitize inline rules, because they will be parsed by a newly created
	 * RuleSet. So we remove the inline rules, sanitize the rest of the string as usual, and
	 * reinsert the inline rules back.
	 * 
	 * Note that inline comments are parsed and removed before sanitization, hence removal
	 * and reinsertion of inline rules does not cause a problem in parsing inline comments.
	 */
	function sanitize()
	{
		$inline= '';
		$pos= strpos($this->str, 'inline');
		if ($pos) {
			// Do not sanitize inline rules
			$inline= trim(substr($this->str, $pos));
			$this->str= substr($this->str, 0, $pos);
		}

		parent::sanitize();

		if ($inline !== '') {
			$this->str.= $inline;
		}
	}

	/**
	 * Splits anchor rule string into words.
	 * 
	 * Similarly to sanitize(), we should not split inline rules, because they will be parsed
	 * by the newly created RuleSet. However, the difference now is that we remove the 'inline'
	 * keyword and insert the rest as the value of that keyword in the rules array.
	 */
	function split()
	{
		$inline= '';
		$pos= strpos($this->str, 'inline');
		if ($pos) {
			// Do not split inline rules
			// Skip inline keyword
			$inline= substr($this->str, $pos + strlen('inline') + 1);
			$this->str= substr($this->str, 0, $pos);
		}

		parent::split();

		if ($inline !== '') {
			$this->words[]= 'inline';
			$this->words[]= $inline;
		}
	}

	/**
	 * Generates anchor rule.
	 * 
	 * Inline rules are always appended to the end.
	 * 
	 * @return string String rule.
	 */
	function generate()
	{
		$this->str= 'anchor';
		$this->genValue('identifier', '"', '"');

		$this->genValue('direction');
		$this->genInterface();
		$this->genValue('af');
		$this->genItems('proto', 'proto');
		$this->genSrcDest();

		$this->genFilterOpts();

		$this->genInline();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	/**
	 * Generates inline rules.
	 * 
	 * Inline rules should start on a new line.
	 * Ending brace (anchor-close) should be at the start of a new line.
	 * 
	 * @attention Note that inline rules are parsed and untainted in the Model before passing to pfctl.
	 */
	function genInline()
	{
		if (isset($this->rule['inline'])) {
			$this->str.= " {\n" . $this->rule['inline'] . "\n}";
		}
	}
}

/**
 * Checks and validates any inline rules.
 * 
 * Since we create a new RuleSet object for each nested anchor, we limit the number of nesting.
 * 
 * @param string $str List of rule definitions in an array.
 * @param bool $force If set, continues checking and validating even if there are errors or $MaxAnchorNesting is reached.
 * @return bool TRUE if $str has inline anchor.
 */
function IsInlineAnchor($str, $force= FALSE)
{
	global $LOG_LEVEL, $Nesting, $MaxAnchorNesting;

	$result= FALSE;
	
	// Do not allow more than $MaxAnchorNesting count of nested inline rules
	$max= $Nesting + 1 > $MaxAnchorNesting;
	if ($max) {
		Error(_('Validation Error') . ': ' . _('Reached max nesting for inline anchors') . ': <pre>' . htmlentities(print_r($str, TRUE)) . '</pre>');
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Validation Error: Reached max nesting for inline anchors: $str");
	}

	if (!$max || $force) {
		$Nesting++;
		$ruleSet= new RuleSet();
		$result= $ruleSet->parse($str, $force);
		if (!$result) {
			if (LOG_DEBUG <= $LOG_LEVEL) {
				Error(_('Validation Error') . ': ' . _('Invalid inline rules, parser output') . ': <pre>' . htmlentities(print_r(json_decode(json_encode($ruleSet), TRUE), TRUE)) . '</pre>');
			}
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Validation Error: Invalid inline rules: ' . print_r(json_decode(json_encode($ruleSet), TRUE), TRUE));
		}
		$Nesting--;
	}
	return $result;
}
?>
