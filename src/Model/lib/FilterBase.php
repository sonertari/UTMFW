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

namespace Model;

class FilterBase extends State
{
	protected $keyDirection= array(
		'in' => array(
			'method' => 'parseNVP',
			'params' => array('direction'),
			),
		'out' => array(
			'method' => 'parseNVP',
			'params' => array('direction'),
			),
		);

	protected $keyProto= array(
		'proto' => array(
			'method' => 'parseItems',
			'params' => array('proto'),
			),
		);

	protected $keySrcDest= array(
		'any' => array(
			'method' => 'parseAny',
			'params' => array(),
			),
		'all' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'from' => array(
			'method' => 'parseSrcDest',
			'params' => array('fromport'),
			),
		'to' => array(
			'method' => 'parseSrcDest',
			'params' => array('toport'),
			),
		);

	protected $keyFilterOpts= array(
		'user' => array(
			'method' => 'parseItems',
			'params' => array('user'),
			),
		'group' => array(
			'method' => 'parseItems',
			'params' => array('group'),
			),
		'flags' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'icmp-type' => array(
			'method' => 'parseICMPType',
			'params' => array(),
			),
		'icmp6-type' => array(
			'method' => 'parseICMPType',
			'params' => array(),
			),
		'tos' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'no' => array(
			'method' => 'parseNVPInc',
			'params' => array('state-filter'),
			),
		'keep' => array(
			'method' => 'parseNVPInc',
			'params' => array('state-filter'),
			),
		'modulate' => array(
			'method' => 'parseNVPInc',
			'params' => array('state-filter'),
			),
		'synproxy' => array(
			'method' => 'parseNVPInc',
			'params' => array('state-filter'),
			),
		'fragment' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'allow-opts' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'once' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'divert-reply' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'label' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('label'),
			),
		'tag' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('tag'),
			),
		'tagged' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('tagged'),
			),
		'!tagged' => array(
			'method' => 'parseNotTagged',
			'params' => array(),
			),
		// "set prio", "set tos", and "set delay"
		'set' => array(
			'method' => 'parseSet',
			'params' => array(),
			),
		'queue' => array(
			'method' => 'parseItems',
			'params' => array('queue', '(', ')'),
			),
		'rtable' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-pkt-rate' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'probability' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'prio' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'received-on' => array(
			'method' => 'parseItems',
			'params' => array('received-on', '(', ')'),
			),
		'!received-on' => array(
			'method' => 'parseNotReceivedOn',
			'params' => array(),
			),
		'os' => array(
			'method' => 'parseOS',
			'params' => array(),
			),
		);

	protected $typeDirection= array(
		'direction' => array(
			'regex' => RE_DIRECTION,
			),
		);

	protected $typeProto= array(
		'proto' => array(
			'multi' => TRUE,
			'regex' => RE_PROTOSPEC,
			),
		);

	protected $typeSrcDest= array(
		'all' => array(
			'regex' => RE_BOOL,
			),
		'from' => array(
			'multi' => TRUE,
			'regex' => RE_HOST,
			),
		'fromroute' => array(
			'regex' => RE_ID,
			),
		'fromport' => array(
			'multi' => TRUE,
			'regex' => RE_PORT,
			),
		'to' => array(
			'multi' => TRUE,
			'regex' => RE_HOST,
			),
		'toroute' => array(
			'regex' => RE_ID,
			),
		'toport' => array(
			'multi' => TRUE,
			'regex' => RE_PORT,
			),
		);

	protected $typeFilterOpts= array(
		'user' => array(
			'multi' => TRUE,
			'regex' => RE_NAME,
			),
		'group' => array(
			'multi' => TRUE,
			'regex' => RE_NAME,
			),
		'flags' => array(
			'regex' => RE_FLAGS,
			),
		'icmp-type' => array(
			'multi' => TRUE,
			'regex' => RE_ICMPTYPE,
			),
		'icmp6-type' => array(
			'multi' => TRUE,
			'regex' => RE_ICMPTYPE,
			),
		'tos' => array(
			'regex' => RE_W_1_10,
			),
		'state-filter' => array(
			'regex' => RE_STATE,
			),
		'fragment' => array(
			'regex' => RE_BOOL,
			),
		'allow-opts' => array(
			'regex' => RE_BOOL,
			),
		'once' => array(
			'regex' => RE_BOOL,
			),
		'divert-reply' => array(
			'regex' => RE_BOOL,
			),
		'label' => array(
			'regex' => RE_NAME,
			),
		'tag' => array(
			'regex' => RE_NAME,
			),
		'tagged' => array(
			'regex' => RE_NAME,
			),
		'not-tagged' => array(
			'regex' => RE_BOOL,
			),
		'set-prio' => array(
			'multi' => TRUE,
			'regex' => RE_W_1_10,
			),
		'set-tos' => array(
			'regex' => RE_W_1_10,
			),
		'set-delay' => array(
			'regex' => RE_NUM,
			),
		'queue' => array(
			'multi' => TRUE,
			'regex' => RE_NAME,
			),
		'rtable' => array(
			'regex' => RE_NUM,
			),
		'max-pkt-rate' => array(
			'regex' => RE_MAXPKTRATE,
			),
		'probability' => array(
			'regex' => RE_PROBABILITY,
			),
		'prio' => array(
			'regex' => RE_W_1_10,
			),
		'received-on' => array(
			'regex' => RE_IF,
			),
		'not-received-on' => array(
			'regex' => RE_BOOL,
			),
		'os' => array(
			'multi' => TRUE,
			'regex' => RE_OS,
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keyDirection,
			$this->keyInterface,
			$this->keyAf,
			$this->keyProto,
			$this->keySrcDest,
			$this->keyFilterOpts,
			$this->keywords
			);

		$this->typedef= array_merge(
			$this->typedef,
			$this->typeDirection,
			$this->typeInterface,
			$this->typeAf,
			$this->typeProto,
			$this->typeSrcDest,
			$this->typeFilterOpts
			);

		parent::__construct($str);
	}

	/**
	 * Gets icmp or icmp6 type and code in the rule string.
	 * 
	 * This method is called if the parser finds an 'icmp-type' keyword in the rule string.
	 * There may be multiple icmp types listed.
	 * 
	 * ICMP code comes after a 'code' keyword, if any.
	 */
	function parseICMPType()
	{
		$this->parseItems($this->words[$this->index]);
	}

	/**
	 * Parses prio or tos settings of the rule.
	 */
	function parseSet()
	{
		if ($this->words[$this->index + 1] === 'prio') {
			$this->index++;
			$this->parseItems('set-prio', '(', ')');
		} elseif ($this->words[$this->index + 1] === 'tos') {
			$this->index++;
			$this->parseNextNVP('set-tos');
		} elseif ($this->words[$this->index + 1] === 'delay') {
			$this->index++;
			$this->parseNextNVP('set-delay');
		}
	}

	/**
	 * Parses !tagged.
	 * 
	 * This method is called when !tagged is found in the rule string.
	 * Sanitization step removes any spaces between ! and tagged, so we always find a !tagged.
	 */
	function parseNotTagged()
	{
		$this->parseDelimitedStr('tagged');
		$this->rule['not-tagged']= TRUE;
	}

	/**
	 * Parses !received-on.
	 * 
	 * This method is called when !received-on is found in the rule string.
	 * Sanitization step removes any spaces between ! and received-on, so we always find a !received-on.
	 */
	function parseNotReceivedOn()
	{
		$this->parseItems('received-on', '(', ')');
		$this->rule['not-received-on']= TRUE;
	}

	function genFilterHead()
	{
		$this->genValue('direction');
		$this->genLog();
		$this->genKey('quick');
		$this->genInterface();
		$this->genValue('af');
		$this->genItems('proto', 'proto');
		$this->genSrcDest();
	}
	
	function genFilterOpts()
	{
		$this->genItems('user', 'user');
		$this->genItems('group', 'group');
		$this->genValue('flags', 'flags ');
		$this->genIcmpType('icmp', 'inet');
		$this->genIcmpType('icmp6', 'inet6');
		$this->genValue('tos', 'tos ');
		$this->genKey('fragment');
		$this->genKey('allow-opts');
		$this->genKey('once');
		$this->genKey('divert-reply');
		$this->genValue('label', 'label "', '"');
		$this->genValue('tag', 'tag "', '"');
		$this->genTagged();
		$this->genValue('set-delay', 'set delay ');
		$this->genItems('set-prio', 'set prio', '(', ')');
		$this->genQueue();
		$this->genValue('rtable', 'rtable ');
		$this->genValue('max-pkt-rate', 'max-pkt-rate ');
		$this->genValue('probability', 'probability ');
		$this->genValue('prio', 'prio ');
		$this->genValue('set-tos', 'set tos ');
		$this->genReceivedOn();
		$this->genValue('state-filter', NULL, ' state');
		$this->genState();
	}
	
	function genSrcDest()
	{
		if (isset($this->rule['all'])) {
			$this->str.= ' all';
		} else {
			if (isset($this->rule['from']) || isset($this->rule['fromroute']) || isset($this->rule['fromport'])) {
				$this->str.= ' from';
				$this->genItems('from');
				$this->genValue('fromroute', 'route ');
				$this->genItems('fromport', 'port');
			}

			if (isset($this->rule['os'])) {
				$this->genItems('os', 'os');
			}
			
			if (isset($this->rule['to']) || isset($this->rule['toroute']) || isset($this->rule['toport'])) {
				$this->str.= ' to';
				$this->genItems('to');
				$this->genValue('toroute', 'route ');
				$this->genItems('toport', 'port');
			}
		}
	}

	/**
	 * Prints state options.
	 * 
	 * genStateOpts() calls genTimeoutOpts() which populates the arr var.
	 */
	function genState()
	{
		if (isset($this->rule['state-filter'])) {
			$this->arr= array();
			$this->genStateOpts();
			if (count($this->arr)) {
				$this->str.= ' ( ';
				$this->str.= implode(', ', $this->arr);
				$this->str.= ' )';
			}
		}
	}

	/**
	 * Prints ICMP type and code.
	 * 
	 * Used to print both icmp and icmp6, hence we pass af too.
	 * 
	 * @param string $icmp icmp or icmp6
	 * @param string $af inet or inet6
	 */
	function genIcmpType($icmp, $af)
	{
		if ((isset($this->rule['af']) && $this->rule['af'] === $af) &&
			(isset($this->rule['proto']) && ($this->rule['proto'] === $icmp || (is_array($this->rule['proto']) && in_array($icmp, $this->rule['proto']))))) {
			if (isset($this->rule[$icmp . '-type'])) {
				$this->str.= $this->generateItem($this->rule[$icmp . '-type'], $icmp . '-type');
			}
		}
	}

	function genQueue()
	{
		if (isset($this->rule['queue'])) {
			if (!is_array($this->rule['queue'])) {
				$this->str.= ' set queue ' . $this->rule['queue'];
			} else {
				$this->str.= ' set queue (' . $this->rule['queue'][0] . ', ' . $this->rule['queue'][1] . ')';
			}
		}
	}

	function genTagged()
	{
		if (isset($this->rule['tagged'])) {
			$not= '';
			if (isset($this->rule['not-tagged']) && $this->rule['not-tagged'] === TRUE) {
				$not= '!';
			}
			$this->str.= " ${not}tagged \"" . $this->rule['tagged'] . '"';
		}
	}
	
	function genReceivedOn()
	{
		if (isset($this->rule['received-on'])) {
			$not= '';
			if (isset($this->rule['not-received-on']) && $this->rule['not-received-on'] === TRUE) {
				$not= '!';
			}
			$this->str.= " ${not}received-on " . $this->rule['received-on'];
		}
	}
}
?>
