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

namespace SSLproxy;

/**
 * Keeps the count of nested proxyspecs in inline rules.
 */
$Nesting= 0;

/**
 * Class for ProxySpecStruct rules.
 */
class ProxySpecStruct extends Rule
{	
	/**
	 * Keywords for proxyspec rules.
	 * 
	 * 'inline' keyword is inserted by the proxyspec parser.
	 */
	protected $keyInline= array(
		'inline' => array(
			'method' => 'parseNextNVP',
			'params' => array('inline'),
			),
		);

	/**
	 * Type definition for proxyspec rules.
	 * 
	 * IsStructuredProxySpec() validates structured proxyspec.
	 */
	protected $typeInline= array(
		'inline' => array(
			'func' => 'SSLproxy\\IsStructuredProxySpec',
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyInline;

		$this->typedef= $this->typeInline;

		parent::__construct($str);
	}

	/**
	 * Sanitizes structured proxyspec sting.
	 * 
	 * We should not sanitize structured proxyspecs, because they will be parsed by a newly created
	 * RuleSet. So we remove the structured proxyspecs, sanitize the rest of the string as usual, and
	 * reinsert the structured proxyspecs back.
	 * 
	 * Note that inline comments are parsed and removed before sanitization, hence removal
	 * and reinsertion of structured proxyspecs does not cause a problem in parsing inline comments.
	 */
	function sanitize()
	{
		$inline= '';
		$pos= strpos($this->str, 'inline');
		if ($pos) {
			// Do not sanitize structured proxyspecs
			$inline= trim(substr($this->str, $pos));
			$this->str= substr($this->str, 0, $pos);
		}

		parent::sanitize();

		if ($inline !== '') {
			$this->str.= $inline;
		}
	}

	/**
	 * Splits structured proxyspec string into words.
	 * 
	 * Similarly to sanitize(), we should not split structured proxyspecs, because they will be parsed
	 * by the newly created RuleSet. However, the difference now is that we remove the 'inline'
	 * keyword and insert the rest as the value of that keyword in the rules array.
	 */
	function split()
	{
		$inline= '';
		$pos= strpos($this->str, 'inline');
		if ($pos) {
			// Do not split structured proxyspecs
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

	function parse($str)
	{
		$this->str= $str;
		$this->init();
		// Do not parse comments in ProxySpec
		//$this->parseComment();
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

	/**
	 * Generates proxyspec rule.
	 * 
	 * @return string String rule.
	 */
	function generate()
	{
		$this->str= 'ProxySpec';

		$this->genInline();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	/**
	 * Generates structured proxyspecs.
	 * 
	 * Structured proxyspecs should start on a new line.
	 * Ending brace (proxyspec-close) should be at the start of a new line.
	 * 
	 * @attention Note that structured proxyspecs are parsed and untainted in the Model before passing to sslproxy.
	 */
	function genInline()
	{
		if (isset($this->rule['inline'])) {
			$this->str.= " {\n" . $this->rule['inline'] . "\n}";
		}
	}
}

/**
 * Checks and validates structured proxyspecs.
 * 
 * @param string $str List of rule definitions in an array.
 * @return bool TRUE if $str has a structured proxyspec.
 */
function IsStructuredProxySpec($str)
{
	global $LOG_LEVEL;

	$ruleSet= new RuleSet();
	$result= $ruleSet->parse($str);
	if (!$result) {
		if (LOG_DEBUG <= $LOG_LEVEL) {
			Error(_('Validation Error') . ': ' . _('Invalid structured proxyspec, parser output') . ': <pre>' . htmlentities(print_r(json_decode(json_encode($ruleSet), TRUE), TRUE)) . '</pre>');
		}
		ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Validation Error: Invalid structured proxyspec: ' . print_r(json_decode(json_encode($ruleSet), TRUE), TRUE));
	}
	return $result;
}
?>
