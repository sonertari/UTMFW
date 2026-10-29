<?php
/*
 * Copyright (C) 2004-2026 Soner Tari
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

class Suricata extends Model
{
	public $Name= 'suricata';
	/// @attention Do not use the full user name '_suricata', ps reports the first 8 chars of user names
	public $User= '_suricat';

	public $NVPS= '\h';
	public $ConfFile= '/etc/suricata/suricata-ids.yaml';
	public $LogFile= '/var/log/suricata/suricata.log';

	public $VersionCmd= '/opt/local/bin/suricata -V 2>&1';

	private $re_RulePrefix= 'include\h*\$(RULE_PATH|PREPROC_RULE_PATH)\/';

	// Add spaces around '-i' to differentiate the interface option from 'c-icap'
	protected $psCmd= '/bin/ps arwwx -o pid,start,%cpu,time,%mem,rss,vsz,stat,pri,nice,tty,user,group,command | /usr/bin/grep " \-i " | /usr/bin/grep -v -e grep | /usr/bin/grep -E <PROC>';

	function __construct()
	{
		parent::__construct();

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'StartInstance'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Start instance'),
					),

				'StopInstance'=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Stop instance'),
					),

				'GetRules'		=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Get rules'),
					),

				'GetDisabledRules'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Get disabled rules'),
					),

				'DisableRule'	=>	array(
					'argv'	=>	array(NAME, NUM),
					'desc'	=>	_('Disable rule'),
					),

				'EnableRule'	=>	array(
					'argv'	=>	array(NAME, NUM),
					'desc'	=>	_('Enable rule'),
					),

				'MoveRuleUp'=>	array(
					'argv'	=>	array(NAME, NUM),
					'desc'	=>	_('Move rule up'),
					),

				'MoveRuleDown'=>	array(
					'argv'	=>	array(NAME, NUM),
					'desc'	=>	_('Move rule down'),
					),

				'SetStartupIfs'	=>	array(
					'argv'	=>	array(NAME, NAME),
					'desc'	=>	_('Set startup ifs'),
					),
				)
			);
	}

	function GetVersion()
	{
		return Output($this->RunShellCommand($this->VersionCmd));
	}

	function StartInstance($if)
	{
		global $TmpFile, $RetvalFile;

		$startCmd= "/opt/local/bin/suricata -i $if -D -c $this->ConfFile --pidfile {$this->UTMFWDIR}/run/suricata/suricata-$if.pid";
		exec("$startCmd > $TmpFile 2>&1 && echo -n '0' > $RetvalFile || echo -n '1' > $RetvalFile &", $output);
		$retval= file_get_contents($RetvalFile);

		$running= FALSE;
		if ($retval === '0') {
			$count= 0;
			while (!($running= $this->IsInstanceRunning($if)) && $count++ < self::PROC_STAT_TIMEOUT) {
				/// @todo Check $TmpFile for error messages, if so break out instead
				exec('/bin/sleep ' . self::PROC_STAT_SLEEP_TIME);
			}
		}

		// Start command is redirected to tmp file, report its contents, success or failure
		$output= file_get_contents($TmpFile);
		Error($output);
		if (!$running) {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Start failed with: $output");
		}
		return $running;
	}

	/**
	 * Stops suricata process started with the given interface.
	 *
	 * @param string $if Interface name.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function StopInstance($if)
	{
		$pid= $this->FindInstancePid($if);
		if ($pid > -1) {
			return $this->KillPid($pid);
		}
		return TRUE;
	}

	/**
	 * Finds the pid of suricata process started with the given inf.
	 *
	 * @param string $if Interface name.
	 * @return int Pid or -1 if not running
	 */
	function FindInstancePid($if)
	{
		$pidcmd= "/bin/ps arwwx | /usr/bin/grep suricata | /usr/bin/grep '$if' | /usr/bin/grep -v -e ctlr.php -e grep";
		exec($pidcmd, $output, $retval);

		foreach ($output as $psline) {
			$re= '/\h+-i\h+(\w+)\b/';
			if (preg_match($re, $psline)) {
				$re= '/^\s*(\d+)\s+/';
				if (preg_match($re, $psline, $match)) {
					if ($match[1] !== '') {
						return $match[1];
					}
				}
			}
		}
		return -1;
	}

	/**
	 * Checks if the process(es) is running.
	 *
	 * Uses ps with -U option.
	 *
	 * @param string $if Interface name
	 * @return bool TRUE if running
	 */
	function IsInstanceRunning($if)
	{
		$re= "\/opt\/local\/bin\/suricata\s+[^\n]*-i\s+$if\s+[^\n]*";

		$output= $this->RunShellCommand('/bin/ps arwwx -U_suricata');
		if (preg_match("/$re/m", $output)) {
			return TRUE;
		}
		return FALSE;
	}

	function _getModuleInfo($start)
	{
		return array('alerts'	=>	$this->getRrdValue('derive-alert.rrd', $start, $result));
	}

	function SetConfig($confname)
	{
		global $basicConfig, $advancedConfig;

		$this->Config= ${$confname};
	}

	function GetConfFile($confname, $group)
	{
		if ($group == 1) {
			return '/etc/suricata/suricata.yaml';
		}
		else {
			return $this->ConfFile;
		}
	}

	/**
	 * Get list of enabled/uncommented rules.
	 *
	 * @return Rule or list of rules.
	 */
	function GetRules($group)
	{
		$file= $this->GetConfFile('', $group);
		return Output($this->SearchFileAll($file, "/^\h*$this->re_RulePrefix([^#\s]+)\h*$/m", 2));
	}

	/**
	 * Gets list of disabled/commented rules.
	 *
	 * @return Rule or list of rules.
	 */
	function GetDisabledRules($group)
	{
		$file= $this->GetConfFile('', $group);
		return Output($this->SearchFileAll($file, "/^\h*$this->COMC\h*$this->re_RulePrefix([^#\s]+)\b\h*$/m", 2));
	}

	function EnableRule($rule, $group)
	{
		$file= $this->GetConfFile('', $group);
		return $this->EnableName($file, "$this->re_RulePrefix$rule");
	}

	function DisableRule($rule, $group)
	{
		$file= $this->GetConfFile('', $group);
		return $this->DisableName($file, "$this->re_RulePrefix$rule");
	}

	function MoveRuleUp($rule, $group)
	{
		$file= $this->GetConfFile('', $group);
		$rule= Escape($rule, '/.');
		return $this->ReplaceRegexp($file, "/^\h*(($this->re_RulePrefix[^\n]+)\n+($this->COMC\h*$this->re_RulePrefix[^\n]+\n+)*)\h*($this->re_RulePrefix$rule)\n/m", '${6}'."\n".'${1}');
	}

	function MoveRuleDown($rule, $group)
	{
		$file= $this->GetConfFile('', $group);
		$rule= Escape($rule, '/.');
		return $this->ReplaceRegexp($file, "/^\h*($this->re_RulePrefix$rule)\n+\h*(($this->COMC\h*$this->re_RulePrefix[^\n]+\n+)*($this->re_RulePrefix[^\n]+)\n+)/m", '${3}'.'${1}'."\n");
	}

	function SetStartupIfs($lanif, $wanif)
	{
		// /opt/local/bin/suricata -i lan0 -D -c /etc/suricata/suricata-ids.yaml --pidfile /var/log/utmfw/run/suricata/suricata-lan0.pid
        $re= "|(\h*/opt/local/bin/suricata\h+.*-i\h+)(\w+\d+)(.*--pidfile\h+{$this->UTMFWDIR}/run/suricata/suricata-)(\w+\d+)(\.pid\h+.*\h+/opt/local/bin/suricata\h+.*-i\h+)(\w+\d+)(.*--pidfile\h+{$this->UTMFWDIR}/run/suricata/suricata-)(\w+\d+)(\.pid\h+.*)|ms";
		return $this->ReplaceRegexp('/etc/rc.local', $re, '${1}'.$lanif.'${3}'.$lanif.'${5}'.$wanif.'${7}'.$wanif.'${9}');
	}

	function isPrio($log, $prio)
	{
		$p= strtoupper($prio);
		return $log['Prio'] == $p || preg_match("/$p:/", $log['Log']);
	}
}

/**
 * Basic configuration.
 */
$basicConfig = array(
    'ipvar HOME_NET' => array(
		),
    'ipvar EXTERNAL_NET' => array(
		),
    'ipvar DNS_SERVERS' => array(
		),
    'ipvar SMTP_SERVERS' => array(
		),
    'ipvar HTTP_SERVERS' => array(
		),
    'ipvar SQL_SERVERS' => array(
		),
    'ipvar TELNET_SERVERS' => array(
		),
//    'ipvar SNMP_SERVERS' => array(
//		),
    'portvar SSH_PORTS' => array(
		),
    'portvar HTTP_PORTS' => array(
		),
    'portvar SHELLCODE_PORTS' => array(
		),
    'var RULE_PATH' => array(
		),
	);

/**
 * Advanced configuration.
 */
$advancedConfig = array(
    'config disable_decode_alerts' => array(
        'type' => FALSE,
		),
    'config disable_tcpopt_experimental_alerts' => array(
        'type' => FALSE,
		),
    'config disable_tcpopt_obsolete_alerts' => array(
        'type' => FALSE,
		),
    'config disable_tcpopt_ttcp_alerts' => array(
        'type' => FALSE,
		),
    'config disable_tcpopt_alerts' => array(
        'type' => FALSE,
		),
    'config disable_ipopt_alerts' => array(
        'type' => FALSE,
		),
    'preprocessor frag3_global: max_frags' => array(
        'type' => UINT,
		),
    'preprocessor bo' => array(
        'type' => FALSE,
		),
//    'preprocessor telnet_decode' => array(
//        'type' => FALSE,
//		),
    'include classification.config' => array(
        'type' => FALSE,
		),
    'include reference.config' => array(
        'type' => FALSE,
		),
	);
?>
