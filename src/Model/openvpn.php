<?php
/*
 * Copyright (C) 2004-2017 Soner Tari
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

class Openvpn extends Model
{
	public $Name= 'openvpn';
	public $User= '\S+';
	
	public $NVPS= '\h';
	
	private $confDir= '/etc/openvpn/';
	public $LogFile= '/var/log/openvpn.log';
	
	public $VersionCmd= '/usr/local/sbin/openvpn --version 2>&1';
	
	function __construct()
	{
		parent::__construct();
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'Restart'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Start OpenVPN process'),
					),

				'StopProcess'=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Stop OpenVPN instance'),
					),

				'IsClientConf'=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('If client conf'),
					),
				
				'CopyConf'	=> array(
					'argv'	=> array(NAME, NAME),
					'desc'	=> _('Copy conf'),
					),
				
				'GetConfs'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get confs'),
					),
				
				'DeleteConf'=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Delete conf'),
					),
				)
			);
	}

	/// @attention PHP does not allow parameter overriding, method signature should be the same, hence this redundant $group arg
	function GetConfFile($conf, $group)
	{
		return '/etc/openvpn/'.$conf;
	}

	function SetConfig($confname)
	{
		global $ClientConfig, $ServerConfig;
		
		if ($this->IsClientConf($confname)) {
			$this->Config= $ClientConfig;
		}
		else {
			$this->Config= $ServerConfig;
		}
	}
	
	/**
	 * Stops openvpn process started with the given conf file.
	 *
	 * @param string $conffile Conf file name.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function StopProcess($conffile)
	{
		$pid= $this->FindPid($conffile);
		if ($pid > -1) {
			return $this->KillPid($pid);
		}
		return TRUE;
	}

	/**
	 * Starts module process(es).
	 *
	 * Tries PROC_STAT_TIMEOUT times.
	 *
	 * @todo Actually should stop retying on some error conditions?
	 */
	function Restart($conffile)
	{
		global $TmpFile;

		if ($this->StopProcess($conffile)) {
			$count= 0;
			while ($count++ < self::PROC_STAT_TIMEOUT) {
				if ($this->FindPid($conffile) > -1) {
					return TRUE;
				}

				$cmd= "/usr/local/sbin/openvpn --config /etc/openvpn/$conffile --daemon --status /var/log/openvpn-status.log 5 > $TmpFile";
				$this->RunShellCommand($cmd);
				exec('/bin/sleep .1');
			}

			/// Start command is redirected to tmp file
			$output= file_get_contents($TmpFile);
			Error($output);
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Restart failed with: $output");
			
			// Check one last time due to the last sleep in the loop
			return $this->FindPid($conffile) > -1;
		}
		return FALSE;
	}

	/**
	 * Determines if the given conf file contains Client configuration.
	 *
	 * Used by config page to use the right configuration array
	 *
	 * @param string $file Conf file name.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function IsClientConf($file)
	{
		if (($this->GetName($this->confDir.$file, 'client') !== FALSE) ||
			($this->GetName($this->confDir.$file, 'tls-client') !== FALSE)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Finds the pid of openvpn process started with the given conf file.
	 *
	 * @param string $conffile Conf file name.
	 * @return int Pid on success or -1 on failure
	 */
	function FindPid($conffile)
	{
		$re= '/^(.*)\.conf$/';
		if (preg_match($re, $conffile, $match)) {
			$file= $match[1].'.conf';

			$pidcmd= "/bin/ps arwwx | /usr/bin/grep openvpn | /usr/bin/grep '$file' | /usr/bin/grep -v -e ctlr.php -e grep";
			$output= $this->RunShellCommand($pidcmd);
			
			$re= '/^\s*(\d+)\s+/m';
			if (preg_match($re, $output, $match)) {
				if ($match[1] !== '') {
					return $match[1];
				}
			}
		}
		return -1;
	}

	/**
	 * Gets files with conf ext.
	 */
	function GetConfs()
	{
		return Output($this->GetFiles($this->confDir.'*.conf'));
	}

	function DeleteConf($Conf)
	{
		return $this->DeleteFile($this->confDir.$Conf);
	}

	/**
	 * Copies file.
	 *
	 * @param string $file File.
	 * @param string $newfile New file.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function CopyConf($file, $newfile)
	{
		if (!file_exists($this->confDir.$newfile)) {
			// copy() returns TRUE on success or FALSE on failure
			return copy($this->confDir.$file, $this->confDir.$newfile);
		}
		ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Dest file already exists '.$this->confDir.$newfile);
		return FALSE;
	}
}

/**
 * Server configuration.
 */
$ServerConfig = array(
    'local' => array(
        'type' => IP,
		),
    'ifconfig' => array(
		),
    'route' => array(
		),
    'port' => array(
        'type' => PORT,
		),
    'proto' => array(
		),
    'dev' => array(
		),
    'ca' => array(
		),
    'cert' => array(
		),
    'key' => array(
		),
    'dh' => array(
		),
    'cipher' => array(
		),
    'server' => array(
		),
    'tls-server' => array(
        'type' => FALSE,
		),
    'tls-auth' => array(
		),
    'keepalive' => array(
		),
    'comp-lzo' => array(
        'type' => FALSE,
		),
    'persist-key' => array(
        'type' => FALSE,
		),
    'persist-tun' => array(
        'type' => FALSE,
		),
    'max-clients' => array(
        'type' => UINT,
		),
    'verb' => array(
        'type' => UINT,
		),
    'ping' => array(
        'type' => UINT,
		),
	);

/**
 * Client configuration.
 */
$ClientConfig = array(
    'remote' => array(
		),
    'ifconfig' => array(
		),
    'route' => array(
		),
    'proto' => array(
		),
    'nobind' => array(
        'type' => FALSE,
		),
    'dev' => array(
		),
    'ca' => array(
		),
    'cert' => array(
		),
    'key' => array(
		),
    'cipher' => array(
		),
    'tls-client' => array(
        'type' => FALSE,
		),
    'tls-auth' => array(
		),
    'comp-lzo' => array(
        'type' => FALSE,
		),
    'persist-key' => array(
        'type' => FALSE,
		),
    'persist-tun' => array(
        'type' => FALSE,
		),
    'verb' => array(
        'type' => UINT,
		),
    'ping' => array(
        'type' => UINT,
		),
	);
?>
