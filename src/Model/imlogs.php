<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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
 * IMSpector message logs.
 */

require_once($MODEL_PATH.'/model.php');

class Imlogs extends Model
{
	private $logsDir= '/var/log/imspector';
	
	function __construct()
	{
		parent::__construct();
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetProtocols'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get protocols'),
					),

				'GetLocalUsers'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get local users'),
					),

				'GetRemoteUsers'=>	array(
					/// @todo Is there any pattern or size for localuser, 2nd param?
					'argv'	=>	array(NAME, STR),
					'desc'	=>	_('Get remote users'),
					),

				'GetSessions'=>	array(
					/// @todo Is there any pattern or size for localuser and remoteuser, 2nd and 3rd param?
					'argv'	=>	array(NAME, STR, STR),
					'desc'	=>	_('Get sessions'),
					),

				'GetImLogFile'=>	array(
					/// @todo Is there any pattern or size for localuser and remoteuser, 2nd and 3rd param?
					'argv'	=>	array(NAME, STR, STR, NAME),
					'desc'	=>	_('Get im log file'),
					),
				)
			);
	}

	/// @todo Name clash with the Model method GetFiles(),
	/// but PHP overloading does not allow redeclaration with different signature
	function ImGetFiles($proto= '', $localuser= '', $remoteuser= '')
	{
		/// @attention Double quotes is necessary for group chats, which contain curly braces in the path names
		return $this->GetFiles('"'.$this->logsDir.$proto.$localuser.$remoteuser.'"');
	}

	function GetDirs($proto= '', $localuser= '')
	{
		$path= $proto.$localuser;
		/// @attention Double quotes is necessary for group chats, which contain curly braces in the path names
		$files= $this->GetFiles('"'.$this->logsDir.$path.'"');
		$files= explode("\n", $files);

		$dirs= array();
		foreach ($files as $file) {
			if (is_dir($this->logsDir."$path/$file")) {
				$dirs[]= $file;
			}
		}
		return implode("\n", $dirs);
	}

	function GetProtocols()
	{
		return Output($this->GetDirs());
	}

	function GetLocalUsers($proto)
	{
		return Output($this->GetDirs("/$proto"));
	}

	function GetRemoteUsers($proto, $localuser)
	{
		return Output($this->GetDirs("/$proto", "/$localuser"));
	}

	function GetSessions($proto, $localuser, $remoteuser)
	{
		return Output($this->ImGetFiles("/$proto", "/$localuser", "/$remoteuser"));
	}
	
	function GetImLogFile($proto, $localuser, $remoteuser, $session)
	{
		return Output($this->logsDir."/$proto/$localuser/$remoteuser/$session");
	}
	
	function ParseLogLine($logline, &$cols)
	{
		$re_ip= '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
		$re_port= '\d{1,5}';
		$re_ipport= "($re_ip:$re_port)";
		$re_datetime= '(\d+)';
		$re_num= '(\d+|)';
		$re_rest= '(.*)';
		
		$re= "/^$re_ipport,$re_datetime,$re_num,$re_num,$re_num,$re_num,$re_rest$/";

		if (preg_match($re, $logline, $match)) {
			$cols['IPPort']= $match[1];
			$cols['Date']= date("Y.m.d", $match[2]);
			$cols['Time']= date("H:i:s", $match[2]);
			/// v0.7 log format confuses old format parser, converting to old User
			//$cols['User']= $match[3];
			$cols['User']= $match[3] + 1;
			$cols['Log']= $match[7];
			return TRUE;
		}
		else {
			/// @attention For old log format (< v0.4)
			$re= "/^$re_ipport,$re_datetime,$re_num,$re_num,$re_rest$/";

			if (preg_match($re, $logline, $match)) {
				$cols['IPPort']= $match[1];
				$cols['Date']= date("Y.m.d", $match[2]);
				$cols['Time']= date("H:i:s", $match[2]);
				$cols['User']= $match[3];
				$cols['Log']= $match[5];
				return TRUE;
			}
		}
		return FALSE;
	}
}
?>
