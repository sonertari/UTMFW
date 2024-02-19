<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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

use SSLproxy\RuleSet;

require_once($MODEL_PATH.'/model.php');

class Sslproxy extends Model
{
	use Rules;

	public $Name= 'sslproxy';
	public $User= 'root|_sslprox\w*';

	public $NVPS= '\h';
	public $ConfPath= '/etc/sslproxy';
	public $ConfFile= '/etc/sslproxy/sslproxy.conf';

	public $LogFile= '/var/log/sslproxy/sslproxy.log';
	
	public $VersionCmd= '/usr/local/bin/sslproxy -V 2>&1';
	
	public $PidFile= UTMFWDIR.'/run/sslproxy.pid';
	
	function __construct()
	{
		parent::__construct();
		
		$this->Proc= 'sslproxy';
		$this->StartCmd= "/usr/local/bin/sslproxy -f $this->ConfFile";
		// sslproxy only restarts loggers upon receiving the HUP signal,
		// so we have to stop and restart it to reload its configuration
		$this->ReloadCmd= "/usr/bin/pkill sslproxy 2>&1; $this->StartCmd 2>&1";

		$this->Commands= array_merge(
			$this->Commands,
			array(
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
				)
			);

		$this->registerRulesCommands();
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

	function _getModuleInfo($start)
	{
		return array(
			'conns'	=> $this->getRrdValue('derive-mld.rrd', $start, $result),
			'fds'	=> $this->getRrdValue('gauge-mfd.rrd', $start, $result, 'gauge'),
			);
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

	function GetMaxStats($interval)
	{
		return Output(json_encode($this->_getMaxStats($interval)));
	}

	function _getMaxStats($start)
	{
		$maxStats['Load']= $this->getRrdValue('derive-mld.rrd', $start, $result);
		$maxStats['UploadKB']= round($this->getRrdValue('derive-iib.rrd', $start, $result) / 1000);
		$maxStats['DownloadKB']= round($this->getRrdValue('derive-iob.rrd', $start, $result) / 1000);
		$maxStats['Fd']= $this->getRrdValue('gauge-mfd.rrd', $start, $result, 'gauge');
		$maxStats['AccessTime']= $this->getRrdValue('gauge-mat.rrd', $start, $result, 'gauge');
		$maxStats['CreateTime']= $this->getRrdValue('gauge-mct.rrd', $start, $result, 'gauge');
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

	function getNamespace()
	{
		return 'SSLproxy\\';
	}

	function getTestRulesCmd($rulesStr, &$tmpFile)
	{
		global $TMP_PATH;

		$tmpFile= tempnam("$TMP_PATH", 'sslproxy.conf.');
		if ($this->PutFile($tmpFile, $rulesStr) === FALSE) {
			Error(_('Cannot write to tmp sslproxy file') . ": $tmpFile\n" . implode("\n", $output));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot write to tmp sslproxy file: $tmpFile");
			$return= FALSE;
		}

		return "/usr/local/bin/sslproxy -Q -f $tmpFile 2>&1";
	}

	function removeTmpTestFile($tmpFile)
	{
		exec("/bin/rm '$tmpFile' 2>&1", $output, $retval);
		if ($retval !== 0) {
			Error(_('Cannot remove tmp sslproxy file') . ": $tmpFile\n" . implode("\n", $output));
			ctlr_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Cannot remove tmp sslproxy file: $tmpFile");
			return FALSE;
		}
		return TRUE;
	}
}
?>
