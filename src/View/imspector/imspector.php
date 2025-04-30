<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('include.php');

$LogConf = array(
	'imspector' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		),
	);

class Imspector extends View
{
	public $Model= 'imspector';
	public $Layout= 'imspector';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('IM Proxy');
		$this->LogsHelpMsg= _HELPWINDOW('IM proxy logs contain process errors and notices.');
	}
}

$View= new Imspector();

$basicConfig = array(
	'icq_protocol' => array(
		'title' => _TITLE2('ICQ'),
		'info' => _HELPBOX2('Enable protocols'),
		),
	'irc_protocol' => array(
		'title' => _TITLE2('IRC'),
		),
	'msn_protocol' => array(
		'title' => _TITLE2('MSN'),
		),
	'yahoo_protocol' => array(
		'title' => _TITLE2('Yahoo'),
		),
	'gg_protocol' => array(
		'title' => _TITLE2('GG'),
		),
	'jabber_protocol' => array(
		'title' => _TITLE2('Jabber'),
		),
	'https_protocol' => array(
		'title' => _TITLE2('HTTPs'),
		'info' => _HELPBOX2('MSN via HTTP proxy needs https'),
		),
	'responder_filename=.*' => array(
		'title' => _TITLE2('Notice responses'),
		'info' => _HELPBOX2('Enable or disable automated responses configured below. You need to enable either Notice days or Filtered minutes too.'),
		),
	'notice_days' => array(
		'title' => _TITLE2('Notice days'),
		'info' => _HELPBOX2('Inform parties that chats are monitored every N days (default is never)'),
		),
	'filtered_mins' => array(
		'title' => _TITLE2('Filtered minutes'),
		'info' => _HELPBOX2('Inform of a blocked event, but upto a max of every N mins (default is never)'),
		),
	'response_prefix' => array(
		'title' => _TITLE2('Response prefix'),
		'info' => _HELPBOX2('Prefix to all responses using all responder plugins'),
		),
	'response_postfix' => array(
		'title' => _TITLE2('Response postfix'),
		'info' => _HELPBOX2('Postfix to all responses using all responder plugins'),
		),
	'notice_response' => array(
		'title' => _TITLE2('Notice response'),
		'info' => _HELPBOX2('Customised notice text'),
		),
	'filtered_response' => array(
		'title' => _TITLE2('Filtered response'),
		'info' => _HELPBOX2('Customised filtered text (message text or filename follows in response)'),
		),
	'block_files' => array(
		'title' => _TITLE2('Block all filetransfers'),
		),
	'block_webcams' => array(
		'title' => _TITLE2('Block webcams'),
		'info' => _HELPBOX2('Only webcam sessions on Yahoo are recognized and blocked'),
		),
	'log_typing_events' => array(
		'title' => _TITLE2('Log typing events'),
		),
	);

$badwordsConfig = array(
	'badwords_filename=.*' => array(
		'title' => _TITLE2('Badwords filtering'),
		'info' => _HELPBOX2('Enable or disable badwords filtering'),
		),
	'badwords_replace_character' => array(
		'title' => _TITLE2('Badwords replace character'),
		'info' => _HELPBOX2('Badwords found are replaced with this single character'),
		),
	'badwords_block_count' => array(
		'title' => _TITLE2('Badwords block count'),
		'info' => _HELPBOX2('If a message contains more then this many bad words then the message will be completely blocked, not just replaced'),
		),
	);

$aclConfig = array(
	'acl_filename=.*' => array(
		'title' => _TITLE2('Access control list'),
		'info' => _HELPBOX2('Enable or disable ACL'),
		),
	);
?>
