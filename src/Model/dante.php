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
 * Socks proxy.
 */

require_once($MODEL_PATH.'/model.php');

class Dante extends Model
{
	public $Name= 'dante';
	public $User= '_sockd';
	
	public $ConfFile= '/etc/sockd.conf';
	public $LogFile= '/var/log/sockd.log';
	public $VersionCmd= '/usr/local/sbin/sockd -v';
	
	public $PidFile= '/var/run/sockd.pid';
	
	function __construct()
	{
		parent::__construct();
		
		$this->Proc= 'sockd';
		
		$this->StartCmd= '/usr/local/sbin/sockd -D';
	
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SetIfs'	=> array(
					'argv'	=> array(NAME, NAME),
					'desc'	=> _('Set ifs'),
					),
				
				'SetIntnet'	=> array(
					'argv'	=> array(IPRANGE),
					'desc'	=> _('Set ifs'),
					),
				)
			);
	}
	
	function SetIfs($lanif, $wanif)
	{
        $re= '/(\h*internal:\h*)(\w+\d+)(\h+.*external:\h*)(\w+\d+)(\s+)/ms';
		return $this->ReplaceRegexp($this->ConfFile, $re, '${1}'.$lanif.'${3}'.$wanif.'${5}');
	}

	function SetIntnet($net)
	{
		global $Re_Ip, $Re_Net;

        $re= "/(\h*client\h+pass\h*\{\s*\h+from:\h+)($Re_Ip|$Re_Net)(\h+.*)/ms";
		return $this->ReplaceRegexp($this->ConfFile, $re, '${1}'.$net.'${3}');
	}
}
?>
