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

class ProxySpecLine extends Rule
{
	protected $keyProto= array(
		'tcp' => array(
			'method' => 'parsePSLine',
			'params' => array(),
			),
		'ssl' => array(
			'method' => 'parsePSLine',
			'params' => array(),
			),
		'http' => array(
			'method' => 'parsePSLine',
			'params' => array(),
			),
		'https' => array(
			'method' => 'parsePSLine',
			'params' => array(),
			),
		'pop3' => array(
			'method' => 'parsePSLine',
			'params' => array(),
			),
		'pop3s' => array(
			'method' => 'parsePSLine',
			'params' => array(),
			),
		'smtp' => array(
			'method' => 'parsePSLine',
			'params' => array(),
			),
		'smtps' => array(
			'method' => 'parsePSLine',
			'params' => array(),
			),
		'autossl' => array(
			'method' => 'parsePSLine',
			'params' => array(),
			),
		);

	protected $typeSpec= array(
		'proto' => array(
			'regex' => SPRE_PROTO,
			),
		'addr' => array(
			'regex' => SPRE_IP,
			),
		'port' => array(
			'regex' => SPRE_PORT,
			),
		'divertport' => array(
			'regex' => SPRE_PORT,
			),
		'divertaddress' => array(
			'regex' => SPRE_IP,
			),
		'returnaddress' => array(
			'regex' => SPRE_IP,
			),
		'natengine' => array(
			'regex' => SPRE_NATENGINE,
			),
		'targetaddress' => array(
			'regex' => SPRE_IP,
			),
		'targetport' => array(
			'regex' => SPRE_PORT,
			),
		'sniport' => array(
			'regex' => SPRE_PORT,
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keywords,
			$this->keyProto,
			);

		$this->typedef= array_merge(
			$this->typedef,
			$this->typeSpec,
			$this->typeComment,
			);

		parent::__construct($str);
	}

	function parsePSLine()
	{
		$this->rule['proto']= $this->words[$this->index++];

		$finished= FALSE;
		$state= 1;
		while (!$this->isEndOfWords() && !$finished) {
			$word= $this->words[$this->index];

			switch ($state) {
				case 1:
					$this->rule['addr']= $word;
					$state++;
					$this->index++;
					break;
				case 2:
					$this->rule['port']= $word;
					$state++;
					$this->index++;
					break;
				case 3:
					$state++;
					if (preg_match('/^up:(.+)$/', $word, $match)) {
						$this->rule['divertport']= $match[1];

						$this->index++;
						if (!$this->isEndOfWords()) {
							if (preg_match('/^ua:(.+)$/', $this->words[$this->index], $match)) {
								$this->rule['divertaddress']= $match[1];
								$this->index++;
							}
						}
						if (!$this->isEndOfWords()) {
							if (preg_match('/^ra:(.+)$/', $this->words[$this->index], $match)) {
								$this->rule['returnaddress']= $match[1];
								$this->index++;
							}
						}
						break;
					}
					/* fall-through */
				case 4:
					if ($word == 'sni')
						$state= 6;
					else if (in_array($word, array('pf', 'ipfw', 'netfilter', 'tproxy'))) {
						$this->rule['natengine']= $word;
						$finished= TRUE;
					}
					else {
						$this->rule['targetaddress']= $word;
						$state++;
					}
					$this->index++;
					break;
				case 5:
					$this->rule['targetport']= $word;
					$finished= TRUE;
					$this->index++;
					break;
				case 6:
					$this->rule['sniport']= $word;
					$finished= TRUE;
					$this->index++;
					break;
			}
		}
	}

	/**
	 * Generates rule.
	 * 
	 * The output of this function is returned to the View on the command line,
	 * so we return the generated rule string.
	 * 
	 * @return string String rule.
	 */
	function generate()
	{
		$this->genProto();
		$this->genSpec();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genProto()
	{
		if (isset($this->rule['proto'])) {
			$this->str= 'ProxySpec '.$this->rule['proto'];
		}
	}

	function genSpec()
	{
		if (isset($this->rule['addr'])) {
			$this->str.= ' '.$this->rule['addr'];
		}
		if (isset($this->rule['port'])) {
			$this->str.= ' '.$this->rule['port'];
		}
		if (isset($this->rule['divertport'])) {
			$this->str.= ' up:'.$this->rule['divertport'];
		}
		if (isset($this->rule['divertaddress'])) {
			$this->str.= ' ua:'.$this->rule['divertaddress'];
		}
		if (isset($this->rule['returnaddress'])) {
			$this->str.= ' ra:'.$this->rule['returnaddress'];
		}
		if (isset($this->rule['natengine'])) {
			$this->str.= ' '.$this->rule['natengine'];
		}
		if (isset($this->rule['targetaddress'])) {
			$this->str.= ' '.$this->rule['targetaddress'];
		}
		if (isset($this->rule['targetport'])) {
			$this->str.= ' '.$this->rule['targetport'];
		}
		if (isset($this->rule['sniport'])) {
			$this->str.= ' sni '.$this->rule['sniport'];
		}
	}
}
?>
