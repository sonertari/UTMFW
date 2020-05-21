<?php
/*
 * Copyright (C) 2004-2020 Soner Tari
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

require_once($MODEL_PATH.'/model.php');

class Httpd extends Model
{
	public $Name= 'httpd';
	public $User= 'root|www';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/httpd.conf';
	public $LogFile= '/var/www/logs/error.log';

	private $phpVersionCmd= '/usr/local/bin/php -v';

	function __construct()
	{
		parent::__construct();

		$this->StartCmd= '/usr/sbin/httpd';
	}

	function GetVersion()
	{
		return Output("OpenBSD/httpd\n".$this->RunShellCommand($this->phpVersionCmd.' | /usr/bin/head -1'));
	}
	
	function ParseLogLine($logline, &$cols)
	{
		if ($this->ParseSyslogLine($logline, $cols)) {
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
		}
		else {
			// There are very simple log lines too, e.g. "man: Formatting manual page..."
			// So parser never fails
			$cols['Log']= $logline;
		}
		return TRUE;
	}
}

/**
 * Configuration.
 *
 * If type field is missing, default type, STR, is assumed.
 *
 * If type field is FALSE, the configuration does not have a Value, it may just
 * be an enable/disable configuration.
 *
 * @param string type Configuration Value type, regexp definition, defaults to STR.
 */
$ModelConfig = array(
    'prefork' => array(
        'type' => UINT,
		),
	);
?>
