<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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

/** @file
 * IM proxy.
 */

require_once($MODEL_PATH.'/model.php');

class Imspector extends Model
{
	public $Name= 'imspector';
	public $User= '_imspect\w*';
	
	public $ConfFile= '/etc/imspector/imspector.conf';
	public $LogFile= '/var/log/imspector.log';
	
	private $badwordsFile= '/etc/imspector/badwords.txt';
	private $aclFile= '/etc/imspector/acl.txt';

	public $PidFile= UTMFWDIR.'/run/imspector.pid';
	
	function __construct()
	{
		parent::__construct();
		
		$this->StartCmd= "/usr/local/sbin/imspector -c $this->ConfFile";

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetBadwords'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get badwords'),
					),

				'AddBadword'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Add badword'),
					),

				'DelBadword'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Delete badword'),
					),

				'GetAcl'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get ACL'),
					),

				'AddAcl'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Add ACL'),
					),

				'DelAcl'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Delete ACL'),
					),

				'MoveAclUp'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Move ACL Up'),
					),

				'MoveAclDown'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Move ACL Down'),
					),
				)
			);
	}

	function GetVersion()
	{
		return Output('IMSpector 0.9');
	}

	function SetConfig($confname)
	{
		global $basicConfig, $badwordsConfig, $aclConfig;

		$this->Config= ${$confname};
	}

	function GetBadwords()
	{
		return Output($this->GetFile($this->badwordsFile));
	}

	function AddBadword($badword)
	{
		$this->DelBadword($badword);
		return $this->AppendToFile($this->badwordsFile, $badword);
	}

	function DelBadword($badword)
	{
		$badword= Escape($badword, '/.');
		return $this->ReplaceRegexp($this->badwordsFile, "/^($badword\s)/m", '');
	}

	function GetAcl()
	{
		return Output($this->SearchFileAll($this->aclFile, "/^\h*((allow|deny)\h+[^\n]+)$/m", 1));
	}

	function AddAcl($acl)
	{
		$this->DelAcl($acl);
		return $this->AppendToFile($this->aclFile, $acl);
	}

	function DelAcl($acl)
	{
		$acl= Escape($acl, '/.');
		return $this->ReplaceRegexp($this->aclFile, "/^($acl\n)/m", '');
	}

	function MoveAclUp($acl)
	{
		$acl= Escape($acl, '/.');
		return $this->ReplaceRegexp($this->aclFile, "/^\h*((allow|deny)\h+[^\n]+)\n+\h*($acl)\n/m", '${3}'."\n".'${1}'."\n");
	}

	function MoveAclDown($acl)
	{
		$acl= Escape($acl, '/.');
		return $this->ReplaceRegexp($this->aclFile, "/^\h*($acl)\n+\h*((allow|deny)\h+[^\n]+)\n/m", '${2}'."\n".'${1}'."\n");
	}
}

/**
 * Basic configuration.
 */
$basicConfig = array(
	'icq_protocol' => array(
        'type' => STR_on_off,
		),
	'irc_protocol' => array(
        'type' => STR_on_off,
		),
	'msn_protocol' => array(
        'type' => STR_on_off,
		),
	'yahoo_protocol' => array(
        'type' => STR_on_off,
		),
	'gg_protocol' => array(
        'type' => STR_on_off,
		),
	'jabber_protocol' => array(
        'type' => STR_on_off,
		),
    'https_protocol' => array(
        'type' => STR_on_off,
		),
	'responder_filename=.*' => array(
        'type' => FALSE,
		),
    'notice_days' => array(
        'type' => UINT,
		),
    'filtered_mins' => array(
        'type' => UINT,
		),
    'response_prefix' => array(
		),
    'response_postfix' => array(
		),
    'notice_response' => array(
		),
    'filtered_response' => array(
		),
    'block_files' => array(
        'type' => STR_on_off,
		),
    'block_webcams' => array(
        'type' => STR_on_off,
		),
    'log_typing_events' => array(
        'type' => STR_on_off,
		),
	);

/**
 * Badwords configuration.
 */
$badwordsConfig = array(
	'badwords_filename=.*' => array(
        'type' => FALSE,
		),
    'badwords_replace_character' => array(
        'type' => CHAR,
		),
    'badwords_block_count' => array(
        'type' => UINT,
		),
	);

/**
 * Access Control List configuration.
 */
$aclConfig = array(
	'acl_filename=.*' => array(
        'type' => FALSE,
		),
	);
?>
