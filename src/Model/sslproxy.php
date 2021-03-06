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

require_once($MODEL_PATH.'/model.php');

class Sslproxy extends Model
{
	public $Name= 'sslproxy';
	public $User= 'root|_sslprox\w*';

	public $NVPS= '\h';
	public $ConfFile= '/etc/sslproxy/sslproxy.conf';
	public $LogFile= '/var/log/sslproxy/sslproxy.log';
	
	public $VersionCmd= '/usr/local/bin/sslproxy -V 2>&1';
	
	public $PidFile= '/var/run/sslproxy.pid';
	
	function __construct()
	{
		parent::__construct();
		
		$this->Proc= 'sslproxy';
		$this->StartCmd= "/usr/local/bin/sslproxy -f $this->ConfFile";

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetSpecs'	=> array(
					'argv'	=>	array(),
					'desc'	=>	_('Get proxy specs'),
					),

				'AddSpec'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Add proxy spec'),
					),

				'DelSpec'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Delete proxy spec'),
					),

				'GetMaxStats'	=> array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Get max stats'),
					),

				'GetCACertFileName'	=> array(
					'argv'	=>	array(),
					'desc'	=>	_('Get CA cert filename'),
					),

				'SetUserAuthURL'	=> array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set UserAuth URL'),
					),

				'GetPassSites'	=> array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pass sites'),
					),

				'AddPassSite'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Add pass site'),
					),

				'DelPassSite'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Delete pass site'),
					),
				)
			);
	}

	function GetVersion()
	{
		$version= explode("\n", $this->RunShellCommand($this->VersionCmd));
		return Output($version[0]."\n".$version[9]."\n".$version[20]);
	}

	function Stop()
	{
		return $this->Kill();
	}

	function _getModuleStatus($generate_info= FALSE, $start= 0)
	{
		$status= parent::_getModuleStatus($generate_info, $start);

		if ($generate_info) {
			$maxStats= $this->_getMaxStats($start != 0 ? $start : 60);
			$status['info']['conns']= $maxStats['Load'];
			$status['info']['fds']= $maxStats['Fd'];
		}
		return $status;
	}

	/**
	 * Parses SSLproxy logs.
	 *
	 * @param string $logline Log line to parse.
	 * @param array $cols Parser output, parsed fields.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ParseLogLine($logline, &$cols)
	{
		if ($this->ParseSyslogLine($logline, $cols)) {
			// STATS: thr=1, mld=0, mfd=0, mat=0, mct=0, iib=0, iob=0, eib=0, eob=0, swm=0, uwm=0, to=0, err=0, si=1
			$re= "/^STATS: thr=\d+, mld=(\d+), mfd=(\d+), mat=(\d+), mct=(\d+), iib=(\d+), iob=(\d+), eib=(\d+), eob=(\d+), swm=(\d+), uwm=(\d+), to=(\d+), err=(\d+), si=(\d+)$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['MaxLoad']= $match[1];
				$cols['MaxFd']= $match[2];
				$cols['MaxAccessTime']= $match[3];
				$cols['MaxCreateTime']= $match[4];
				$cols['IntifInBytes']= $match[5];
				$cols['IntifOutBytes']= $match[6];
				$cols['ExtifInBytes']= $match[7];
				$cols['ExtifOutBytes']= $match[8];
				$cols['SetWatermark']= $match[9];
				$cols['UnsetWatermark']= $match[10];
				$cols['Timeout']= $match[11];
				$cols['StatsError']= $match[12];
				$cols['StatsIdx']= $match[13];
			} else if ($cols['Prio'] == 'ERROR' || $cols['Prio'] == 'CRITICAL') {
				// ERROR: Error from bufferevent: 0:- 336151570:1042:sslv3 alert bad certificate:20:SSL routines:148:SSL3_READ_BYTES
				$cols['Error']= $cols['Log'];
			} else if ($cols['Prio'] == 'WARNING') {
				// WARNING: Received SIGPIPE; ignoring.
				$cols['Warning']= $cols['Log'];
			}
			return TRUE;
		}
		return FALSE;
	}

	function GetSpecs()
	{
		// ProxySpec https 127.0.0.1 8443 up:8080
		return Output($this->SearchFileAll($this->ConfFile, "/^\h*ProxySpec\h*([^\n]+)\h*$/m"));
	}

	function AddSpec($spec)
	{
		$this->DelSpec($spec);
		return $this->AppendToFile($this->ConfFile, "ProxySpec $spec");
	}

	function DelSpec($spec)
	{
		$spec= Escape($spec, '/.');
		return $this->ReplaceRegexp($this->ConfFile, "/^(ProxySpec\h+$spec\b.*(\s|))/m", '');
	}

	function GetMaxStats($interval)
	{
		return Output(json_encode($this->_getMaxStats($interval)));
	}

	function _getMaxStats($interval)
	{
		$logs= $this->GetLastLogs('STATS:', $interval);

		$maxStats= array(
			'Load' => 0,
			'Fd' => 0,
			'AccessTime' => 0,
			'CreateTime' => 0,
			'UploadKB' => 0,
			'DownloadKB' => 0,
			);
		
		$statsIdx= 0;
		$load= 0;
		$upload= 0;
		$download= 0;
		foreach ($logs as $l) {
			$maxStats['Fd']= max($maxStats['Fd'], $l['MaxFd']);
			$maxStats['AccessTime']= max($maxStats['AccessTime'], $l['MaxAccessTime']);
			$maxStats['CreateTime']= max($maxStats['CreateTime'], $l['MaxCreateTime']);

			if ($statsIdx != $l['StatsIdx']) {
				$maxStats['Load']= max($maxStats['Load'], $load);
				$maxStats['UploadKB']= max($maxStats['UploadKB'], $upload);
				$maxStats['DownloadKB']= max($maxStats['DownloadKB'], $download);

				$statsIdx= $l['StatsIdx'];
				$load= 0;
				$upload= 0;
				$download= 0;
			}

			$load+= $l['MaxLoad'];
			$upload+= $l['IntifInBytes'];
			$download+= $l['IntifOutBytes'];
 		}

		// Update one last time with the accumulated values of the last loop
		$maxStats['Load']= max($maxStats['Load'], $load);
		$maxStats['UploadKB']= max($maxStats['UploadKB'], $upload);
		$maxStats['DownloadKB']= max($maxStats['DownloadKB'], $download);

		$maxStats['UploadKB']= round($maxStats['UploadKB'] / 1000);
		$maxStats['DownloadKB']= round($maxStats['DownloadKB'] / 1000);

		return $maxStats;
	}

	function GetCACertFileName()
	{
		return Output($this->GetNVP($this->ConfFile, 'CACert'));
	}

	function SetUserAuthURL($ip)
	{
		return $this->SetNVP($this->ConfFile, 'UserAuthURL', "https://$ip/userdblogin.php");
	}

	function GetPassSites()
	{
		// PassSite example.com
		return Output($this->SearchFileAll($this->ConfFile, "/^\h*PassSite\h*([^\n]+)\h*$/m"));
	}

	function AddPassSite($site)
	{
		$this->DelPassSite($site);
		return $this->AppendToFile($this->ConfFile, "PassSite $site");
	}

	function DelPassSite($site)
	{
		$site= Escape($site, '/.*');
		return $this->ReplaceRegexp($this->ConfFile, "/^(PassSite\h+$site\b.*(\s|))/m", '');
	}
}

$ModelConfig = array(
    'CACert' => array(
		),
    'CAKey' => array(
		),
    'DenyOCSP' => array(
        'type' => STR_yes_no,
		),
    'SSLCompression' => array(
        'type' => STR_yes_no,
		),
    'ForceSSLProto' => array(
		),
    'DisableSSLProto' => array(
		),
    'MinSSLProto' => array(
		),
    'MaxSSLProto' => array(
		),
    'Ciphers' => array(
		),
    'CipherSuites' => array(
		),
    'LeafKeyRSABits' => array(
        'type' => UINT,
		),
    'ConnIdleTimeout' => array(
        'type' => UINT,
		),
    'ExpiredConnCheckPeriod' => array(
        'type' => UINT,
		),
    'LogStats' => array(
        'type' => STR_yes_no,
		),
    'StatsPeriod' => array(
        'type' => UINT,
		),
    'RemoveHTTPAcceptEncoding' => array(
        'type' => STR_yes_no,
		),
    'RemoveHTTPReferer' => array(
        'type' => STR_yes_no,
		),
    'VerifyPeer' => array(
        'type' => STR_yes_no,
		),
    'AllowWrongHost' => array(
        'type' => STR_yes_no,
		),
    'UserAuth' => array(
        'type' => STR_yes_no,
		),
    'UserTimeout' => array(
        'type' => UINT,
		),
    'ValidateProto' => array(
        'type' => STR_yes_no,
		),
    'OpenFilesLimit' => array(
        'type' => UINT,
		),
	);
?>
