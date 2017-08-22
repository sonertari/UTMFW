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

class Sslproxy extends Model
{
	public $Name= 'sslproxy';
	public $User= 'root|_sslprox\w*';

	public $NVPS= '\h';
	public $ConfFile= '/etc/sslproxy/sslproxy.conf';
	public $LogFile= '/var/log/sslproxy.log';
	
	public $VersionCmd= '/usr/local/bin/sslproxy -V 2>&1';
	
	public $PidFile= '/var/run/sslproxy.pid';
	
	function __construct()
	{
		global $TmpFile;
		
		parent::__construct();
		
		$this->Proc= 'sslproxy';
		$this->StartCmd= "/usr/local/bin/sslproxy -d -f $this->ConfFile > $TmpFile 2>&1 &";

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetSpecs'	=> array(
					'argv'	=>	array(),
					'desc'	=>	_('Get proxy specs'),
					),

				'AddSpecs'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Add proxy specs'),
					),

				'DelSpecs'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Del proxy specs'),
					),

				'GetMaxStats'	=> array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Get max stats'),
					),

				'GetIdleConns'	=> array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Get idle conns'),
					),

				'GetCriticalErrors'	=> array(
					'argv'	=>	array(),
					'desc'	=>	_('Get critical errors'),
					),

				'GetCACertFileName'	=> array(
					'argv'	=>	array(),
					'desc'	=>	_('Get CA cert filename'),
					),
				)
			);
	}

	function GetVersion()
	{
		$version= explode("\n", $this->RunShellCommand($this->VersionCmd));
		return Output($version[0]."\n".$version[12]."\n".$version[22]);
	}

	function Stop()
	{
		return $this->Kill();
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
		global $Re_Ip, $Re_Net;

		if ($this->ParseSyslogLine($logline, $cols)) {
			// CONN: https 192.168.3.24 60512 172.217.17.206 443 safebrowsing-cache.google.com GET /safebrowsing/rd/xxx 200 - sni:safebrowsing-cache.google.com names:- sproto:TLSv1.2:ECDHE-RSA-AES128-GCM-SHA256 dproto:TLSv1.2:ECDHE-ECDSA-AES128-GCM-SHA256 origcrt:- usedcrt:-
			// CONN: pop3s 192.168.3.24 46790 66.102.1.108 995 sni:pop.gmail.com names:- sproto:TLSv1.2:ECDHE-RSA-AES128-GCM-SHA256 dproto:TLSv1.2:ECDHE-RSA-AES128-GCM-SHA256 origcrt:- usedcrt:-
			$re= "/^CONN:\s+(\S+)\s+(\S+)\s+(\d+)\s+(\S+)\s+(\d+)(\s+.*sproto:(\S+)\s+dproto:(\S+).*|)$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['Proto']= $match[1];
				$cols['SrcAddr']= $match[2];
				$cols['SrcPort']= $match[3];
				$cols['DstAddr']= $match[4];
				$cols['DstPort']= $match[5];
				$cols['SProto']= $match[7];
				$cols['DProto']= $match[8];
			} else {
				// STATS: thr=1, mld=0, mfd=0, mat=0, mct=0, iib=0, iob=0, eib=0, eob=0, swm=0, uwm=0, to=0, err=0
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
				} else {
					// ERROR: Error from bufferevent: 0:- 336151570:1042:sslv3 alert bad certificate:20:SSL routines:148:SSL3_READ_BYTES
					$re= "/^(CRITICAL|ERROR): (.*)$/";
					if (preg_match($re, $cols['Log'], $match)) {
						$cols['Error']= $match[2];
					} else {
						// WARNING: Received SIGPIPE; ignoring.
						$re= "/^WARNING: (.*)$/";
						if (preg_match($re, $cols['Log'], $match)) {
							$cols['Warning']= $match[1];
						} else {
							// IDLE: thr=0, id=1, ce=1 cc=1, at=0 ct=0, src_addr=192.168.3.24:56530, dst_addr=192.168.111.130:443
							$re= "/^IDLE: thr=(\d+), id=(\d+),.*, at=(\d+) ct=(\d+)(, src_addr=((\S+):\d+)|)(, dst_addr=((\S+):\d+)|)$/";
							if (preg_match($re, $cols['Log'], $match)) {
								$cols['ThreadIdx']= $match[1];
								$cols['ConnIdx']= $match[2];
								$cols['IdleTime']= $match[3];
								$cols['Duration']= $match[4];
								$cols['SrcAddr']= $match[7];
								$cols['DstAddr']= $match[10];
							} else {
								// EXPIRED: thr=1, time=0, src_addr=192.168.3.24:56530, dst_addr=192.168.111.130:443
								$re= "/^EXPIRED: thr=\d+, time=(\d+)(, src_addr=((\S+):\d+)|)(, dst_addr=((\S+):\d+)|)$/";
								if (preg_match($re, $cols['Log'], $match)) {
									$cols['IdleTime']= $match[1];
									$cols['ExpiredSrcAddr']= $match[4];
									$cols['ExpiredDstAddr']= $match[7];
								} else {
									// INFO:
									$re= "/^INFO: (.*)$/";
									if (preg_match($re, $cols['Log'], $match)) {
										$cols['Info']= $match[1];
									}
								}
							}
						}
					}
				}
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
	
	function AddSpecs($specs)
	{
		$this->DelSpecs($specs);
		return $this->AppendToFile($this->ConfFile, "ProxySpec $specs");
	}

	function DelSpecs($specs)
	{
		$specs= Escape($specs, '/.');
		return $this->ReplaceRegexp($this->ConfFile, "/^(ProxySpec\h+$specs\b.*(\s|))/m", '');
	}
	
	function GetLastLogs($needle, $interval= 60)
	{
		$lastLogs= array();

		$logs= $this->_getLiveLogs($this->LogFile, 1);
		if (count($logs) == 1) {
			$lastLine= $logs[0];
			$lastTs= DateTime::createFromFormat('M d H:i:s', $lastLine['Date'].' '.$lastLine['Time'])->getTimestamp();
			$firstTs= $lastTs;
			$lineCount= 32;

			$logs= array();
			// Make sure we cover the requested interval, but limit the number of lines too
			while ($lastTs - $firstTs < $interval) {
				if ($lineCount > 1024) {
					// @todo Should we clear the logs?
					//$logs= array();
					break;
				}

				$logs= $this->_getLiveLogs($this->LogFile, $lineCount, $needle);
				if (count($logs)) {
					$firstLine= $logs[0];
					$firstTs= DateTime::createFromFormat('M d H:i:s', $firstLine['Date'].' '.$firstLine['Time'])->getTimestamp();
				}

				$lineCount*= 2;
			}

			foreach ($logs as $l) {
				$ts= DateTime::createFromFormat('M d H:i:s', $l['Date'].' '.$l['Time'])->getTimestamp();
				if ($lastTs - $ts <= $interval) {
					$lastLogs[]= $l;
				}
			}
		}
		return $lastLogs;
	}

	function GetMaxStats($interval)
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
			$maxStats['Fd']= max(array($maxStats['Fd'], $l['MaxFd']));
			$maxStats['AccessTime']= max(array($maxStats['AccessTime'], $l['MaxAccessTime']));
			$maxStats['CreateTime']= max(array($maxStats['CreateTime'], $l['MaxCreateTime']));

			if ($statsIdx != $l['StatsIdx']) {
				$statsIdx= $l['StatsIdx'];
				$load= 0;
				$upload= 0;
				$download= 0;
			}

			$load+= $l['MaxLoad'];
			$upload+= $l['IntifInBytes'];
			$download+= $l['IntifOutBytes'];

			$maxStats['Load']= max(array($maxStats['Load'], $load));
			$maxStats['UploadKB']= max(array($maxStats['UploadKB'], $upload));
			$maxStats['DownloadKB']= max(array($maxStats['DownloadKB'], $download));
 		}

		$maxStats['UploadKB']= round($maxStats['UploadKB'] / 1000);
		$maxStats['DownloadKB']= round($maxStats['DownloadKB'] / 1000);

		return Output(json_encode($maxStats));
	}

	function GetIdleConns($interval)
	{
		return Output(json_encode($this->GetLastLogs('IDLE:', $interval)));
	}

	function GetCriticalErrors()
	{
		$logs= $this->_getCriticalErrors();
		if (count($logs) > 0) {
			$errorStr= '';
			foreach ($logs as $l) {
				$errorStr.= "\n" . $l['Log'];
			}
			Error(_('There are CRITICAL errors.')."$errorStr");
		}
		return TRUE;
	}

	function hasCriticalErrors()
	{
		return count($this->_getCriticalErrors()) > 0;
	}

	function _getCriticalErrors()
	{
		global $CriticalErrorCheckInterval;

		return $this->GetLastLogs('CRITICAL:', $CriticalErrorCheckInterval);
	}

	function GetCACertFileName()
	{
		return Output($this->GetNVP($this->ConfFile, 'CACert'));
	}
}

$ModelConfig = array(
    'CACert' => array(
		),
    'CAKey' => array(
		),
    'ConnIdleTimeout' => array(
        'type' => UINT,
		),
    'ExpiredConnCheckPeriod' => array(
        'type' => UINT,
		),
    'SSLShutdownRetryDelay' => array(
        'type' => UINT,
		),
    'LogStats' => array(
        'type' => STR_yes_no,
		),
    'StatsPeriod' => array(
        'type' => UINT,
		),
	);
?>
