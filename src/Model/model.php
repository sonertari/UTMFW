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

/** @file
 * Contains base class which runs basic Model tasks.
 */

require_once($MODEL_PATH.'/include.php');

class Model
{
	public $Name= '';

	/// @attention Should be updated in constructors of children
	public $Proc= '';
	public $User= '';

	// @attention On OpenBSD 5.9 ps limits the user name string to 7 chars, hence _e2guardian becomes _e2guard
	// @todo Find a way to increase the terminal COLUMNS size, using "env COLUMNS=10000" does not work
	protected $psCmd= '/bin/ps arwwx -o pid,start,%cpu,time,%mem,rss,vsz,stat,pri,nice,tty,user,group,command | /usr/bin/grep -v -e grep | /usr/bin/grep -E <PROC>';

	public $StartCmd= '';

	/// Max number of iterations to try while starting or stopping processes.
	const PROC_STAT_TIMEOUT= 100;
	const PROC_STAT_SLEEP_TIME= .3;
	
	/**
	 * Argument lists and descriptions of commands.
	 *
	 * @todo Should we implement $Commands using Interfaces in OOP?
	 *
	 * @param array argv Array of arg types in order.
	 * @param string desc Description of the shell function.
	 */
	public $Commands= array();

	public $NVPS= '=';
	public $COMC= '#';

	public $LogFile= '';
	public $TmpLogsDir= '';

	protected $rcConfLocal= '/etc/rc.conf.local';

	public $PfRulesFile= '/etc/pf.conf';

	public $ConfFile= '';
	public $Config= '';

	public $CmdLogStart= '/usr/bin/head -1 <LF>';
	
	public $VersionCmd= '';

	public $PidFile= '';

	protected $newSyslogConf= '/etc/newsyslog.conf';

	/// This datetime format is for error logs, not access logs.
	/// Certain modules use the default syslog format.
	protected $dateTimeFormat= 'M j H:i:s';

	protected $prios= array();

	function __construct()
	{
		global $ModelConfig;
		
		$this->Proc= $this->Name;

		$this->TmpLogsDir= '/var/tmp/utmfw/logs/'.get_class($this).'/';

		$this->Config= $ModelConfig;

		$this->prios= array(
			'CRITICAL|ALERT|EMERGENCY' => _TITLE('<MODEL> has CRITICAL errors'),
			'ERROR' => _TITLE('<MODEL> has ERRORs'),
			'WARNING' => _TITLE('<MODEL> has WARNINGs')
			);

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'IsRunning'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Check if process running'),
					),

				'Start'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Start '.get_class($this)),
					),
				
				'Stop'	=>	array(
					'argv'	=>	array(),
					'desc'	=> _('Stop '.get_class($this)),
					),
				
				'GetProcList'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get process list'),
					),

				'GetIntIf'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get int_if'),
					),
				
				'GetExtIf'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get ext_if'),
					),
				
				'CreateUser'	=>	array(
					'argv'	=>	array(NAME, SHA1STR, NUM),
					'desc'	=>	_('Create user'),
					),

				'SetPassword'	=>	array(
					'argv'	=>	array(NAME, SHA1STR),
					'desc'	=>	_('Set user password'),
					),

				'SetLogLevel'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set log level'),
					),

				'SetHelpBox'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set help boxes'),
					),

				'SetSessionTimeout'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set session timeout'),
					),

				'SetDefaultLocale'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set default locale'),
					),

				'SetForceHTTPs'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set force HTTPs'),
					),

				'SetUseSSH'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set use SSH'),
					),

				'SetMaxAnchorNesting'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set max anchor nesting'),
					),

				'SetPfctlTimeout'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set pfctl timeout'),
					),

				'SetStatusCheckInterval'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set status check interval'),
					),

				'SetMaxLogFileSize'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set max log file size'),
					),

				'GetReloadRate'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get reload rate'),
					),

				'SetReloadRate'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set reload rate'),
					),

				'GetDateTime'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get datetime'),
					),

				'GetPhyIfs'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('List physical interfaces'),
					),

				'GetDefaultLogFile'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get log file'),
					),

				'SelectLogFile'	=>	array(
					'argv'	=>	array(FILEPATH|EMPTYSTR),
					'desc'	=>	_('Select log file'),
					),

				'GetLogFilesList'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get log files list'),
					),

				'GetLogStartDate'	=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Get log start date'),
					),

				'GetFileLineCount'	=>	array(
					'argv'	=>	array(FILEPATH, REGEXP|NONE, REGEXP|NONE, NUM|EMPTYSTR|NONE, NUM|EMPTYSTR|NONE, NUM|EMPTYSTR|NONE, NUM|EMPTYSTR|NONE),
					'desc'	=>	_('Get line count'),
					),

				'GetLogs'	=>	array(
					'argv'	=>	array(FILEPATH, NUM, TAIL, REGEXP|NONE, REGEXP|NONE, NUM|EMPTYSTR|NONE, NUM|EMPTYSTR|NONE, NUM|EMPTYSTR|NONE, NUM|EMPTYSTR|NONE),
					'desc'	=>	_('Get lines'),
					),

				'GetLiveLogs'	=>	array(
					'argv'	=>	array(FILEPATH, TAIL, REGEXP|NONE),
					'desc'	=>	_('Get tail'),
					),

				'GetAllStats'=>	array(
					'argv'	=>	array(FILEPATH, NAME|EMPTYSTR),
					'desc'	=>	_('Get all stats'),
					),

				'GetStats'=>	array(
					'argv'	=>	array(FILEPATH, SERIALARRAY, NAME|EMPTYSTR),
					'desc'	=>	_('Get stats'),
					),

				'GetProcStatLines'	=>	array(
					'argv'	=>	array(FILEPATH|NONE),
					'desc'	=>	_('Get stat lines'),
					),

				'PrepareFileForDownload'	=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Prepare file for download'),
					),
				
				'GetVersion'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get software version'),
					),

				'Reload'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Reload '.get_class($this)),
					),

				'GetServiceStatus'	=>	array(
					'argv'	=>	array(BOOL|NONE, STR|NONE),
					'desc'	=>	_('Get service status'),
					),
				
				'GetModuleStatus'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get module status'),
					),
				
				'GetSysCtl'	=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get sysctl values'),
					),

				'GetConfigValues'	=>	array(
					'argv'	=>	array(NAME|EMPTYSTR, NUM|EMPTYSTR),
					'desc'	=>	_('Get config values'),
					),

				'SetConfValue'	=>	array(
					/// @todo Is there any pattern or size for new value, 2nd param?
					'argv'	=>	array(CONFNAME, STR, NAME|EMPTYSTR, NUM|EMPTYSTR),
					'desc'	=>	_('Set name value pair'),
					),

				'EnableConf'	=>	array(
					'argv'	=>	array(CONFNAME, NAME|EMPTYSTR, NUM|EMPTYSTR),
					'desc'	=>	_('Enable config'),
					),

				'DisableConf'	=>	array(
					'argv'	=>	array(CONFNAME, NAME|EMPTYSTR, NUM|EMPTYSTR),
					'desc'	=>	_('Disable config'),
					),

				'GetAllowedIps'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get PF allowed'),
					),

				'AddAllowedIp'	=>	array(
					'argv'	=>	array(IPADR|IPRANGE),
					'desc'	=>	_('Set PF allowed'),
					),

				'DelAllowedIp'	=>	array(
					'argv'	=>	array(IPADR|IPRANGE),
					'desc'	=>	_('Delete PF allowed'),
					),

				'GetRestrictedIps'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get PF restricted'),
					),

				'AddRestrictedIp'=>	array(
					'argv'	=>	array(IPADR|IPRANGE),
					'desc'	=>	_('Set PF restricted'),
					),

				'DelRestrictedIp'=>	array(
					'argv'	=>	array(IPADR|IPRANGE),
					'desc'	=>	_('Delete PF restricted'),
					),

				'GetStatus'	=> array(
					'argv'	=>	array(),
					'desc'	=>	_('Get critical errors'),
					),
				)
			);
	}

	/**
	 * Checks if the given process(es) is running.
	 *
	 * Uses ps with grep.
	 *
	 * @param string $proc Module process name.
	 * @return bool TRUE if there is any process running, FALSE otherwise.
	 */
	function IsRunning($proc= '')
	{
		if ($proc == '') {
			$proc= $this->Proc;
		}
	
		/// @todo Should use pid files instead of ps, if possible at all
		$cmd= preg_replace('/<PROC>/', escapeshellarg($proc), $this->psCmd);
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return count($this->SelectProcesses($output)) > 0;
		}
		Error(implode("\n", $output));
		ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "No such process: $proc");
		return FALSE;
	}
	
	/**
	 * Gets the list of processes running.
	 * 
	 * @return mixed List of processes on success, FALSE on failure.
	 */
	function GetProcList()
	{
		$cmd= preg_replace('/<PROC>/', escapeshellarg($this->Proc), $this->psCmd);
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return Output(json_encode($this->SelectProcesses($output)));
		}
		Error(implode("\n", $output));
		ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Process list failed for $this->Proc");
		return FALSE;
	}

	/**
	 * Selects user processes from ps output.
	 *
	 * @param array $psout ps output obtained elsewhere.
	 * @return array Parsed ps output of user processes.
	 */
	function SelectProcesses($psout)
	{
		//   PID STARTED  %CPU      TIME %MEM   RSS   VSZ STAT  PRI  NI TTY      USER     GROUP    COMMAND
		//     1  5:10PM   0.0   0:00.03  0.0   388   412 Is     10   0 ??       root     wheel    /sbin/init
		// Skip processes running on terminals, e.g. vi, tail, man
		// Select based on daemon user
		$re= "/^\s*(\d+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\d+)\s+(\d+)\s+(\S+)\s+(\d+)\s+(\d+)\s+\?\?\s+($this->User)\s+(\S+)\s+(.+)$/";
		
		$processes= array();
		foreach ($psout as $line) {
			if (preg_match($re, $line, $match)) {
				// Skip processes initiated by this WUI
				if (!preg_match('/\b(ctlr\.php|grep|kill|pkill)\b/', $match[13])) {
					$processes[]= array(
						$match[1],
						$match[2],
						$match[3],
						$match[4],
						$match[5],
						$match[6],
						$match[7],
						$match[8],
						$match[9],
						$match[10],
						$match[11],
						$match[12],
						$match[13],
						);
				}
			}
		}
		return $processes;
	}

	/**
	 * Start module process(es).
	 *
	 * Waits PROC_STAT_TIMEOUT times.
	 *
	 * @todo Actually should stop retrying on error?
	 *
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Start()
	{
		global $TmpFile, $RetvalFile;
		
		exec($this->StartCmd." > $TmpFile 2>&1 && echo -n '0' > $RetvalFile || echo -n '1' > $RetvalFile &", $output);
		$retval= file_get_contents($RetvalFile);
		
		$running= FALSE;
		if ($retval === '0') {
			$count= 0;
			while (!($running= $this->IsRunning()) && $count++ < self::PROC_STAT_TIMEOUT) {
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
	 * Stops module process(es)
	 *
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Stop()
	{
		return $this->Pkill($this->Proc);
	}
		
	/**
	 * Kills the given process(es).
	 *
	 * Used to kill processes without a model definition, hence the $proc param.
	 * Tries PROC_STAT_TIMEOUT times.
	 *
	 * @todo Actually should stop retrying on error?
	 *
	 * @param string $proc Process name
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Pkill($proc)
	{
		global $TmpFile;
		
		$cmd= '/usr/bin/pkill -x '.$proc;
		
		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			if (!$this->IsRunning($proc)) {
				return TRUE;
			}
			$this->RunShellCommand("$cmd > $TmpFile 2>&1");
			/// @todo Check $TmpFile for error messages, if so break out instead
			exec('/bin/sleep ' . self::PROC_STAT_SLEEP_TIME);
		}

		// Check one last time due to the last sleep in the loop
		if (!$this->IsRunning($proc)) {
			return TRUE;
		}
		
		// Pkill command is redirected to the tmp file
		$output= file_get_contents($TmpFile);
		Error($output);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Pkill failed for $proc with: $output");
		return FALSE;
	}

	/**
	 * Get int_if.
	 * 
	 * @return string Internal interface name.
	 */
	function GetIntIf()
	{
		return Output($this->_getIntIf());
	}

	function _getIntIf()
	{
		return $this->GetNVP($this->PfRulesFile, 'int_if');
	}

	/**
	 * Get ext_if.
	 * 
	 * @return string External interface name.
	 */
	function GetExtIf()
	{
		return Output($this->_getExtIf());
	}

	function _getExtIf()
	{
		return $this->GetNVP($this->PfRulesFile, 'ext_if');
	}

	/**
	 * Creates a system user.
	 * 
	 * Note that passwords are double encrypted.
	 * 
	 * @param string $user User name.
	 * @param string $passwd SHA encrypted password.
	 * @param int $uid User id.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function CreateUser($user, $passwd, $uid)
	{
		exec("/bin/cat /etc/master.passwd | /usr/bin/grep -E '^$user:' 2>&1", $output, $retval);

		// If the user does not exist
		if ($retval !== 0 && count($output) == 0) {
			$uline= "$user:$(/usr/bin/encrypt $passwd):$uid:$uid::0:0:UTMFW $user:/var/empty:/var/www/htdocs/utmfw/Controller/sh.php";
			exec("/bin/echo $uline >>/etc/master.passwd 2>&1", $output, $retval);

			if ($retval === 0) {
				$gline= "$user:*:$uid:";
				exec("/bin/echo $gline >>/etc/group 2>&1", $output, $retval);

				if ($retval === 0) {
					exec("/usr/sbin/pwd_mkdb -p /etc/master.passwd 2>&1", $output, $retval);
					return $retval === 0;
				}
			}
		}

		$errout= implode("\n", $output);
		Error($errout);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Create user failed: $errout");
		return FALSE;
	}

	/**
	 * Sets user's password in the system password file.
	 * 
	 * Note that passwords are double encrypted.
	 * 
	 * @param string $user User name.
	 * @param string $passwd SHA encrypted password.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetPassword($user, $passwd)
	{
		exec("/bin/cat /etc/master.passwd | /usr/bin/grep -E '^$user:' 2>&1", $output, $retval);
		if ($retval === 0) {
			$line= $output[0];
			if (preg_match("/^$user:[^:]+(:.+)$/", $line, $match)) {
				unset($output);
				$cmdline= '/usr/bin/chpass -a "' . $user . ':$(/usr/bin/encrypt ' . $passwd . ')' . $match[1] . '"';
				exec($cmdline, $output, $retval);
				if ($retval === 0) {
					return TRUE;
				}
			}
		}

		$errout= implode("\n", $output);
		Error($errout);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Set password failed: $errout");
		return FALSE;
	}

	/**
	 * Sets global log level.
	 * 
	 * @param string $level Level to set to.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetLogLevel($level)
	{
		global $ROOT, $TEST_DIR_SRC;

		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$LOG_LEVEL', $level.';');
	}

	/**
	 * Enables or disables help boxes.
	 * 
	 * @param string $bool 'TRUE' to enable, 'FALSE' otherwise.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetHelpBox($bool)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/View/lib/setup.php', '\$ShowHelpBox', $bool.';');
	}
	
	/**
	 * Sets session timeout.
	 * 
	 * If the given values is less than 10, we set the timeout to 10 seconds.
	 * 
	 * @param int $timeout Timeout in seconds.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetSessionTimeout($timeout)
	{
		global $ROOT, $TEST_DIR_SRC;

		if ($timeout < 10) {
			$timeout= 10;
		}
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/View/lib/setup.php', '\$SessionTimeout', $timeout.';');
	}

	/**
	 * Sets default locale.
	 * 
	 * @param string $locale Locale.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetDefaultLocale($locale)
	{
		global $ROOT, $TEST_DIR_SRC;

		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$DefaultLocale', $locale.';');
	}

	/**
	 * Enables or disables HTTPs.
	 * 
	 * @param string $bool 'TRUE' to enable, 'FALSE' otherwise.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetForceHTTPs($bool)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$ForceHTTPs', $bool.';');
	}

	/**
	 * Enables or disables SSH.
	 * 
	 * @param string $bool 'TRUE' to enable, 'FALSE' otherwise.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetUseSSH($bool)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/View/lib/setup.php', '\$UseSSH', $bool.';');
	}
	
	/**
	 * Sets the max number of nested anchors allowed.
	 * 
	 * @param int $max Number of nested anchors allowed.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetMaxAnchorNesting($max)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$MaxAnchorNesting', $max.';');
	}

	/**
	 * Sets pfctl timeout.
	 * 
	 * Note that setting this value to 0 effectively fails all pfctl calls.
	 * 
	 * @param int $timeout Timeout waiting pfctl output in seconds.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetPfctlTimeout($timeout)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$PfctlTimeout', $timeout.';');
	}

	/**
	 * Sets status check interval.
	 * 
	 * @param int $interval Interval to check module statuses.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetStatusCheckInterval($interval)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		if ($interval < 10) {
			$interval= 10;
		}
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$StatusCheckInterval', $interval.';');
	}

	/**
	 * Sets max log file size.
	 *
	 * @param int $size Max size in MB.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetMaxLogFileSize($size)
	{
		global $ROOT, $TEST_DIR_SRC;

		if ($size < 1) {
			$size= 1;
		} else if ($size > 1000) {
			$size= 1000;
		}

		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$MaxLogFileSize', $size.';');
	}

	/**
	 * Gets default reload rate.
	 * 
	 * @return string Reload rate in seconds.
	 */
	function GetReloadRate()
	{
		global $VIEW_PATH;

		require($VIEW_PATH.'/lib/setup.php');
		
		return Output($DefaultReloadRate);
	}
	
	/**
	 * Sets default reload rate.
	 * 
	 * @param int $rate Reload rate in seconds.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetReloadRate($rate)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/View/lib/setup.php', '\$DefaultReloadRate', $rate.';');
	}
	
	/**
	 * Runs the given shell command and returns its output as string.
	 *
	 * @todo Fix return value checks in some references, RunShellCommand() does not return FALSE
	 *
	 * @param string $cmd Command string to run.
	 * @return string Command result in a string.
	 */
	function RunShellCommand($cmd)
	{
		/// @attention Do not use shell_exec() here, because it is disabled when PHP is running in safe_mode
		/// @warning Not all shell commands return 0 on success, such as grep, date...
		/// Hence, do not check return value
		exec($cmd, $output);
		if (is_array($output)) {
			return implode("\n", $output);
		}
		return '';
	}

	/**
	 * Returns files with the given filepath pattern.
	 *
	 * $filepath does not have to be just directory path, and may contain wildcards.
	 *
	 * @param string $filepath File pattern to match.
	 * @return string List of file names, without path.
	 */
	function GetFiles($filepath)
	{
		return $this->RunShellCommand("ls -1 $filepath");
	}

	/**
	 * Reads file contents.
	 *
	 * @param string $file Config file.
	 * @return mixed File contents in a string or FALSE on fail.
	 */
	function GetFile($file)
	{
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		return FALSE;
	}

	/**
	 * Deletes the given file or directory.
	 *
	 * @param string $path File or dir to delete.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DeleteFile($path)
	{
		if (file_exists($path)) {
			exec("/bin/rm -rf $path 2>&1", $output, $retval);
			if ($retval === 0) {
				return TRUE;
			}
			else {
				$errout= implode("\n", $output);
				Error($errout);
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Failed deleting: $path, $errout");
			}
		}
		else {
			ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "File path does not exist: $path");
		}
		return FALSE;
	}

	/**
	 * Writes contents to file.
	 *
	 * @param string $file Config filename.
	 * @param string $contents Contents to write.
	 * @return mixed Output of file_put_contents() or FALSE on fail.
	 */
	function PutFile($file, $contents)
	{
		if (file_exists($file)) {
			return file_put_contents($file, $contents, LOCK_EX);
		}
		return FALSE;
	}

	/**
	 * Changes value of NVP.
	 *
	 * @param string $file Config file.
	 * @param string $name Name of NVP.
	 * @param string $newvalue New value to set.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetNVP($file, $name, $newvalue)
	{
		if (copy($file, $file.'.bak')) {
			if (($value= $this->GetNVP($file, $name)) !== FALSE) {
				/// @warning Backslash should be escaped first, or causes double escapes
				$value= Escape($value, '\/$^*().-[]"');
				$re= "^(\h*$name\b\h*$this->NVPS\h*)($value)(\h*$this->COMC.*|\h*)$";

				/// @todo Put strings between single quotes, otherwise PHP conf files complain about certain chars, such as ':'
				$contents= preg_replace("/$re/m", '${1}'.$newvalue.'${3}', file_get_contents($file), 1, $count);
				if ($contents !== NULL && $count == 1) {
					file_put_contents($file, $contents);
					return TRUE;
				}
				else {
					ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot set new value $file, $name, new: $newvalue, old: $value, re: $re, $count");
				}
			}
			else {
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot find NVP: $file, $name");
			}
		}
		else {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file: $file");
		}
		return FALSE;
	}

	/**
	 * Reads value of NVP.
	 *
	 * @param string $file Config file.
	 * @param string $name Name of NVP.
	 * @param int $set There may be multiple parentheses in $re, which one to return.
	 * @param string $trimchars Chars to trim in the results.
	 * @return mixed Value of NVP or FALSE on failure.
	 */
	function GetNVP($file, $name, $set= 0, $trimchars= '')
	{
		return $this->SearchFile($file, "/^\h*$name\b\h*$this->NVPS\h*([^$this->COMC'\"\n]*|'[^'\n]*'|\"[^\"\n]*\"|[^$this->COMC\n]*)(\h*|\h*$this->COMC.*)$/m", $set, $trimchars);
	}

	/**
	 * Searches the given file with the given regex.
	 *
	 * @param string $file Config file.
	 * @param string $re Regex to search the file with, should have end markers.
	 * @param int $set There may be multiple parentheses in $re, which one to return.
	 * @param string $trimchars If given, these chars are trimmed on the left or right.
	 * @return mixed String found or FALSE if no match.
	 */
	function SearchFile($file, $re, $set= 0, $trimchars= '')
	{
		// There may be multiple matching NVPs
		if (preg_match_all($re, file_get_contents($file), $match)) {
			// Index 0 always gives full matches, so use index 1
			$retval= $match[1][$set];
			if ($trimchars !== '') {
				$retval= trim($retval, $trimchars);
			}
			return rtrim($retval);
		}
		return FALSE;
	}

	/**
	 * Multi-searches a given file with a given regexp.
	 *
	 * @param string $file Config file.
	 * @param string $re Regexp to search the file with, should have end markers.
	 * @param int $set There may be multiple parentheses in $re, which one to return.
	 * @return mixed String found or FALSE on fail.
	 */
	function SearchFileAll($file, $re, $set= 1)
	{
		/// @todo What to do multiple matching NVPs
		if (preg_match_all($re, file_get_contents($file), $match)) {
			return implode("\n", array_values($match[$set]));
		}
		return FALSE;
	}

	/**
	 * Searches a needle and replaces with a value in the given file.
	 *
	 * @param string $file Config file.
	 * @param string $matchre Match re.
	 * @param string $replacere Replace re.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ReplaceRegexp($file, $matchre, $replacere)
	{
		if (copy($file, $file.'.bak')) {
			$contents= preg_replace($matchre, $replacere, file_get_contents($file), 1, $count);
			if ($contents !== NULL && $count === 1) {
				file_put_contents($file, $contents);
				return TRUE;
			}
			else {
				// Replace failure may not be important, we sometimes search and try to replace possibly nonexistent needles
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Cannot replace in: $file");
			}
		}
		else {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file: $file");
		}
		return FALSE;
	}

	/**
	 * Appends a string to a file.
	 *
	 * @param string $file Config file pathname.
	 * @param string $line Line to add.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function AppendToFile($file, $line)
	{
		if (copy($file, $file.'.bak')) {
			$contents= file_get_contents($file).$line."\n";
			/// @todo Return the return value of file_put_contents()? Check and test all usages of AppendToFile() first.
			file_put_contents($file, $contents);
			return TRUE;
		}
		else {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file: $file");
		}
		return FALSE;
	}

	/**
	 * Gets system datetime.
	 *
	 * @return array Datetime.
	 */
	function GetDateTime()
	{
		$dateArray= array();
		$dateArray['Month']= exec('/bin/date +%m');
		$dateArray['Day']= exec('/bin/date +%d');
		$dateArray['Hour']= exec('/bin/date +%H');
		$dateArray['Minute']= exec('/bin/date +%M');

		return Output(json_encode($dateArray));
	}

	function _getStaticGateway()
	{
		return $this->GetFile($this->confDir.'mygate');
	}

	function _getDynamicGateway()
	{
		global $Re_Ip;

		$cmd= "/sbin/route -n get default | /usr/bin/grep gateway 2>&1";
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			if (count($output) > 0) {
				#    gateway: 10.0.0.2
				$re= "\s*gateway:\s*($Re_Ip)\s*";
				if (preg_match("/$re/m", $output[0], $match)) {
					return $match[1];
				}
			}
		}
		else {
			$errout= implode("\n", $output);
			Error($errout);
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Get dynamic gateway failed: $errout");
		}
		return FALSE;
	}

	/**
	 * Extracts physical interface names from ifconfig output.
	 *
	 * Removes non-physical interfaces from the output.
	 * 
	 * @return string Names of physical interfaces.
	 */
	function GetPhyIfs()
	{
		return Output($this->_getPhyIfs());
	}

	function _getPhyIfs()
	{
		return $this->RunShellCommand("/sbin/ifconfig -a | /usr/bin/grep ': flags=' | /usr/bin/sed 's/: flags=.*//g' | /usr/bin/grep -v -e lo -e pflog -e pfsync -e enc -e tun");
	}

	/**
	 * Gets the log file of the module.
	 * 
	 * @return string Name of log file.
	 */
	function GetDefaultLogFile()
	{
		return Output($this->LogFile);
	}

	/**
	 * Gets the log file under the tmp folder.
	 *
	 * Updates the tmp file if the original file is modified.
	 * Updates the stat info of the file in the tmp statistics file, which is used to check file modificaton.
	 *
	 * @param string $file Original file name.
	 * @return string Pathname of the log file.
	 */
	function SelectLogFile($file)
	{
		if ($file === '') {
			$file= $this->LogFile;
		}

		if (!$this->ValidateLogFile($file)) {
			return FALSE;
		}

		$file= $this->GetTmpLogFileName($file);

		if (!file_exists($file) || $this->IsLogFileModified($file)) {
			if ($this->UpdateTmpLogFile($file)) {
				// Update stats to update file stat info only
				$this->UpdateStats($file, $stats, $briefstats);
			}
			else {
				$file= $this->LogFile;
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Logfile tmp copy update failed, defaulting to: $file");
			}
		}
		return Output($file);
	}

	/**
	 * Checks if the given log file has been updated.
	 *
	 * Compares the full stat info of the orig and tmp files. 
	 * We unset the last access time before the diff, because it is updated by the stat() call itself too.
	 *
	 * @param string $logfile Log file.
	 * @return bool TRUE if modified, FALSE otherwise.
	 */
	function IsLogFileModified($logfile)
	{
		$origfile= $this->GetOrigFileName($logfile);
		
		if ($this->GetStatsFileInfo($logfile, $linecount, $filestat)) {
			if (file_exists($origfile)) {
				$newfilestat= stat($origfile);

				$diff= array_diff($newfilestat, $filestat);
				unset($diff['8']);
				unset($diff['atime']);
				if (count($diff) === 0) {
					ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Logfile not modified: $logfile, linecount $linecount");
					return FALSE;
				}
				
				// Reset the accumulated stats if the log file turned over
				if ($newfilestat['ino'] != $filestat['ino']) {
					$statsfile= $this->GetStatsFileName($logfile);
					exec("/bin/rm -f $statsfile 2>&1", $output, $retval);
					ctlr_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, "Logfile turned over: $logfile, deleted stats file: $statsfile");
				}
			}
		}
		return TRUE;
	}

	/**
	 * Gets the name of the file in the tmp folder.
	 *
	 * @param string $file File pathname.
	 * @return string Pathname of the tmp file.
	 */
	function GetTmpLogFileName($file)
	{
		if (preg_match('/(.*)\.gz$/', $file, $match)) {
			$file= $this->TmpLogsDir.basename($match[1]);
		}
		else {
			$file= $this->TmpLogsDir.basename($file);
		}
		return $file;
	}

	/**
	 * Copies the original log file to tmp folder.
	 *
	 * @param string $file File pathname.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function UpdateTmpLogFile($file)
	{
		$origfile= $this->GetOrigFileName($file);
		
		if ($this->CopyLogFileToTmp($origfile, $this->TmpLogsDir)) {
			return TRUE;
		}
		ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Copy failed: $file");
		return FALSE;
	}

	/**
	 * Generates the module specific datetime regexp for filtering logs.
	 *
	 * @param string $month Two-digit month, or empty string for match all.
	 * @param string $day Two-digit day, or empty string for match all.
	 * @param string $hour Two-digit hour, or empty string for match all.
	 * @param string $minute Two-digit minute, or empty string for match all.
	 * @return string Regexp to use as datetime filter.
	 */
	function formatDateHourRegexp($month, $day, $hour, $minute)
	{
		global $MonthNames, $Re_MonthNames;

		// Sep  7 13:49:06
		if ($month != '') {
			$reMonth= $MonthNames[$month];
		} else {
			$reMonth= '('.$Re_MonthNames.')';
		}

		if ($day != '') {
			$reDay= sprintf('% 2d', $day);
		} else {
			$reDay= '([[:digit:][:blank:]][[:digit:]])';
		}

		if ($hour != '') {
			$reHour= $hour;
		} else {
			$reHour= '([[:digit:]][[:digit:]])';
		}

		if ($minute != '') {
			$reMinute= $minute;
		} else {
			$reMinute= '([[:digit:]][[:digit:]])';
		}

		return "^$reMonth $reDay $reHour:$reMinute:";
	}

	function formatDateHourRegexpDayLeadingZero($month, $day, $hour, $minute)
	{
		global $MonthNames, $Re_MonthNames;

		// Sep 07 13:49:06
		if ($month != '') {
			$reMonth= $MonthNames[$month];
		} else {
			$reMonth= '('.$Re_MonthNames.')';
		}

		if ($day != '') {
			$reDay= $day;
		} else {
			$reDay= '([[:digit:]][[:digit:]])';
		}

		if ($hour != '') {
			$reHour= $hour;
		} else {
			$reHour= '([[:digit:]][[:digit:]])';
		}

		if ($minute != '') {
			$reMinute= $minute;
		} else {
			$reMinute= '([[:digit:]][[:digit:]])';
		}
		
		return "^$reMonth $reDay $reHour:$reMinute:";
	}

	function formatDateHourRegexpWeekDays($month, $day, $hour, $minute)
	{
		global $MonthNames, $Re_MonthNames, $Re_WeekDays;

		// Mon Sep  4 23:51:31
		if ($month != '') {
			$reMonth= $MonthNames[$month];
		} else {
			$reMonth= '('.$Re_MonthNames.')';
		}

		if ($day != '') {
			$reDay= sprintf('% 2d', $day);
		} else {
			$reDay= '([[:digit:][:blank:]][[:digit:]])';
		}

		if ($hour != '') {
			$reHour= $hour;
		} else {
			$reHour= '([[:digit:]][[:digit:]])';
		}

		if ($minute != '') {
			$reMinute= $minute;
		} else {
			$reMinute= '([[:digit:]][[:digit:]])';
		}

		$reWeekDays= '('.$Re_WeekDays.')';

		return "^$reWeekDays $reMonth $reDay $reHour:$reMinute:";
	}

	/**
	 * Gets line count of the given log file.
	 *
	 * @param string $file Log file pathname.
	 * @param string $re Regexp to get count of a restricted result set.
	 * @param string $needle Optional regexp to use with a second grep over logs, used by Stats pages.
	 * @param string $month Two-digit month, or empty string for match all.
	 * @param string $day Two-digit day, or empty string for match all.
	 * @param string $hour Two-digit hour, or empty string for match all.
	 * @param string $minute Two-digit minute, or empty string for match all.
	 * @return int Line count.
	 */
	function GetFileLineCount($file, $re= '', $needle= '', $month='', $day='', $hour='', $minute='')
	{
		return Output($this->_getFileLineCount($file, $re, $needle, $month, $day, $hour, $minute));
	}

	function _getFileLineCount($file, $re= '', $needle= '', $month='', $day='', $hour='', $minute='')
	{
		if (!$this->ValidateLogFile($file)) {
			return FALSE;
		}

		if ($re == '' && $needle == '' && $month == '' && $day == '' && $hour == '' && $minute == '') {
			/// @warning Input redirection is necessary, otherwise wc adds file name to its output too
			$cmd= "/usr/bin/wc -l < $file";
		}
		else {
			// Skip for speed, otherwise we could use datetime regexp for empty strings too
			if ($month == '' && $day == '' && $hour == '' && $minute == '') {
				$re= escapeshellarg($re);
				if ($needle == '') {
					$cmd= "/usr/bin/grep -a -E $re $file";
				}
				else {
					$needle= escapeshellarg($needle);
					$cmd= "/usr/bin/grep -a -E $needle $file | /usr/bin/grep -a -E $re";
				}
			}
			else {
				$cmd= '/usr/bin/grep -a -E "' . $this->formatDateHourRegexp($month, $day, $hour, $minute) . '" ' . $file;

				$re= escapeshellarg($re);
				if ($needle == '') {
					$cmd.= " | /usr/bin/grep -a -E $re";
				}
				else {
					$needle= escapeshellarg($needle);
					$cmd.= " | /usr/bin/grep -a -E $needle | /usr/bin/grep -a -E $re";
				}
			}

			$cmd.= ' | /usr/bin/wc -l';
		}

		// OpenBSD wc returns with leading blanks
		return trim($this->RunShellCommand($cmd));
	}

	/**
	 * Gets lines in log file.
	 *
	 * @param string $file Log file pathname.
	 * @param int $end Head option, start line.
	 * @param int $count Tail option, page line count.
	 * @param string $re Regexp to restrict the result set.
	 * @param string $needle Optional regexp to use with a second grep over logs, used by Stats pages.
	 * @param string $month Two-digit month, or empty string for match all.
	 * @param string $day Two-digit day, or empty string for match all.
	 * @param string $hour Two-digit hour, or empty string for match all.
	 * @param string $minute Two-digit minute, or empty string for match all.
	 * @return array Log lines.
	 */
	function GetLogs($file, $end, $count, $re= '', $needle= '', $month='', $day='', $hour='', $minute='')
	{
		if (!$this->ValidateLogFile($file)) {
			return FALSE;
		}

		// Empty $re is not an issue for grep, greps all
		// Skip for speed, otherwise we could use datetime regexp for empty strings too
		if ($month == '' && $day == '' && $hour == '' && $minute == '') {
			$re= escapeshellarg($re);
			if ($needle == '') {
				$cmd= "/usr/bin/grep -a -E $re $file";
			}
			else {
				$needle= escapeshellarg($needle);
				$cmd= "/usr/bin/grep -a -E $needle $file | /usr/bin/grep -a -E $re";
			}
		}
		else {
			$cmd= '/usr/bin/grep -a -E "' . $this->formatDateHourRegexp($month, $day, $hour, $minute) . '" ' . $file;

			$re= escapeshellarg($re);
			if ($needle == '') {
				$cmd.= " | /usr/bin/grep -a -E $re";
			}
			else {
				$needle= escapeshellarg($needle);
				$cmd.= " | /usr/bin/grep -a -E $needle | /usr/bin/grep -a -E $re";
			}
		}

		$cmd.= " | /usr/bin/head -$end | /usr/bin/tail -$count";

		exec($cmd, $output, $retval);
		
		$logs= array();
		foreach ($output as $line) {
			unset($cols);
			if ($this->ParseLogLine($line, $cols)) {
				$logs[]= $cols;
			}
		}
		return Output(json_encode($logs));
	}

	/**
	 * Gets logs for live logs pages.
	 *
	 * Used to extract lines in last section of the log file or
	 * of the lines grep'd.
	 *
	 * Difference from the archives method is that this one always gets
	 * the tail of the log or grep'd lines.
	 *
	 * @param string $file Log file.
	 * @param int $count Tail length, page line count.
	 * @param string $re Regexp to restrict the result set.
	 * @return array Log lines.
	 */
	function GetLiveLogs($file, $count, $re= '')
	{
		return Output(json_encode($this->_getLiveLogs($file, $count, $re)));
	}

	/**
	 * Gets logs for live logs pages, the actual method.
	 * 
	 * A few modules share their log files with other processes.
	 * So the $needle param is used to filter module log lines.
	 * 
	 * @param string $needle Second regexp to further restrict the result set.
	 */
	function _getLiveLogs($file, $count, $re= '', $needle= '')
	{
		if (!$this->ValidateLogFile($file)) {
			return FALSE;
		}

		// Empty $re is not an issue for grep, greps all
		$re= escapeshellarg($re);
		if ($needle == '') {
			$cmd= "/usr/bin/grep -a -E $re $file";
		}
		else {
			$needle= escapeshellarg($needle);
			$cmd= "/usr/bin/grep -a -E $needle $file | /usr/bin/grep -a -E $re";
		}

		$cmd.= " | /usr/bin/tail -$count";

		exec($cmd, $output, $retval);
		
		$logs= array();
		foreach ($output as $line) {
			unset($cols);
			if ($this->ParseLogLine($line, $cols)) {
				$logs[]= $cols;
			}
		}
		return $logs;
	}

	/**
	 * Gets log files list with start dates.
	 *
	 * Searches the logs directory for all possible archives according to
	 * the default file name.
	 * 
	 * @return array File names with start dates.
	 */
	function GetLogFilesList()
	{
		$file= $this->LogFile;
		$filelist= explode("\n", $this->GetFiles("$file*"));
		asort($filelist);

		$result= array();
		foreach ($filelist as $filepath) {
			$result[$filepath]= $this->_getLogStartDate($filepath);
		}
		return Output(json_encode($result));
	}

	/**
	 * Extracts the datetime of the first line in the log file.
	 *
	 * Works only on uncompressed log files.
	 *
	 * @param string $file Log file pathname.
	 * @return string DateTime or a message if the archive is compressed.
	 */
	function GetLogStartDate($file)
	{
		return Output($this->_getLogStartDate($file));
	}

	function _getLogStartDate($file)
	{
		if (preg_match('/.*\.gz$/', $file)) {
			$tmpfile= $this->GetTmpLogFileName($file);
			// Log file may have been rotated, shifting compressed archive file numbers,
			// hence modification check
			if (file_exists($tmpfile) && !$this->IsLogFileModified($tmpfile)) {
				$file= $tmpfile;
			}
		}
		
		if (!preg_match('/.*\.gz$/', $file)) {
			$logline= $this->GetFileFirstLine($file);
			
			$this->ParseLogLine($logline, $cols);
			return $cols['Date'].' '.$cols['Time'];
		}
		return _('Compressed');
	}

	/**
	 * Gets first line of file.
	 *
	 * Used to get the start date of log files.
	 *
	 * @param string $file Log file pathname.
	 * @return string First line in file.
	 */
	function GetFileFirstLine($file)
	{
		$cmd= preg_replace('/<LF>/', $file, $this->CmdLogStart);
		return $this->RunShellCommand($cmd);
	}

	/**
	 * Parses the given log line.
	 *
	 * @param string $logline Log line.
	 * @param array $cols Parsed fields.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ParseLogLine($logline, &$cols)
	{
		return $this->ParseSyslogLine($logline, $cols);
	}
	
	/**
	 * Further processes parser output fields.
	 *
	 * Used by statistics collector functions.
	 *
	 * @attention This cannot be done in the parser. Because details of the Link
	 * field is lost, which are needed on log pages.
	 *
	 * @param array $cols Updated parser output.
	 */
	function PostProcessCols(&$cols)
	{
	}

	/**
	 * Prepares file for download over WUI.
	 * 
	 * @return mixed File name or FALSE on failure.
	 */
	function PrepareFileForDownload($file)
	{
		if (!$this->ValidateLogFile($file)) {
			return FALSE;
		}

		$tmpdir= '/var/tmp/utmfw/downloads';
		$retval= 0;
		if (!file_exists($tmpdir)) {
			exec("/bin/mkdir -p $tmpdir 2>&1", $output, $retval);
		}
		
		if ($retval === 0) {
			exec("/bin/rm -f $tmpdir/* 2>&1", $output, $retval);
			if ($retval === 0) {
				$tmpfile= "$tmpdir/".basename($file);
				exec("/bin/cp $file $tmpfile 2>&1", $output, $retval);
				if ($retval === 0) {
					exec("/sbin/chown www:www $tmpfile 2>&1", $output, $retval);
					if ($retval === 0) {
						return Output($tmpfile);
					}
				}
			}
		}
		$errout= implode("\n", $output);
		Error($errout);
		ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "FAILED: $errout");
		return FALSE;
	}

	/**
	 * Gets the original file name for the given log file.
	 *
	 * @param string $logfile Log file.
	 * @return string Log file name.
	 */
	function GetOrigFileName($logfile)
	{
		$origfilename= basename($logfile);
		if (basename($this->LogFile) !== $origfilename) {
			$origfilename.= '.gz';
		}
		$origfile= dirname($this->LogFile).'/'.$origfilename;
		
		return $origfile;
	}

	/**
	 * Collects text statistics for the proc stats general page.
	 *
	 * Builds the shell command to count with grep first.
	 * Counts the number of lines in the grep output.
	 * 
	 * @param string $logfile Log file.
	 * @return array Statistics.
	 */
	function GetProcStatLines($logfile)
	{
		global $StatsConf;

		if (!$this->ValidateLogFile($logfile)) {
			return FALSE;
		}

		$stats= array();
		foreach ($StatsConf[$this->Name] as $stat => $conf) {
			if (isset($conf['Title'])) {
				if (isset($conf['Cmd'])) {
					$cmd= $conf['Cmd'];
					if (isset($conf['Needle'])) {
						$cmd.= ' | /usr/bin/grep -a -E <NDL>';
						$cmd= preg_replace('/<NDL>/', escapeshellarg($conf['Needle']), $cmd);
					}
					$cmd.= ' | /usr/bin/wc -l';
				}
				else if (isset($conf['Needle'])) {
					$cmd= '/usr/bin/grep -a -E <NDL> <LF> | /usr/bin/wc -l';
					$cmd= preg_replace('/<NDL>/', escapeshellarg($conf['Needle']), $cmd);
				}
				if ($logfile == '') {
					$logfile= $this->LogFile;
				}
				$cmd= preg_replace('/<LF>/', $logfile, $cmd);

				$stats[$conf['Title']]= trim($this->RunShellCommand($cmd));
			}
		}
		return Output(json_encode($stats));
	}

	/**
	 * Uncompresses gzipped log file to tmp dir.
	 * 
	 * @param string $file Log file.
	 * @param string $tmpdir Tmp folder to copy to.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function CopyLogFileToTmp($file, $tmpdir)
	{
		if (!$this->ValidateLogFile($file)) {
			return FALSE;
		}

		exec("/bin/mkdir -p $tmpdir 2>&1", $output, $retval);
		if ($retval === 0) {
			exec("/bin/cp $file $tmpdir 2>&1", $output, $retval);
			if ($retval === 0) {
				$tmpfile= $tmpdir.basename($file);
				if (preg_match('/(.*)\.gz$/', $tmpfile, $match)) {
					// Delete the old uncompressed file, otherwise gunzip fails
					$this->DeleteFile($match[1]);
					
					exec("/usr/bin/gunzip $tmpfile 2>&1", $output, $retval);
					if ($retval === 0) {
						return TRUE;
					}
					else {
						ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'gunzip failed: '.$tmpdir.basename($file));
					}
				}
				else {
					return TRUE;
				}
			}
			else {
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'cp failed: '.$file);
			}
		}
		else {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'mkdir failed: '.$tmpdir);
		}
		Error(implode("\n", $output));
		return FALSE;
	}

	/**
	 * Builds generic grep command and extracts log lines.
	 *
	 * @param string $logfile Log file pathname.
	 * @param int $tail Tail len to get the new log lines to update the stats with.
	 * @return string Log lines.
	 */
	function GetStatsLogLines($logfile, $tail= -1)
	{
		global $StatsConf;

		if (!$this->ValidateLogFile($logfile)) {
			return FALSE;
		}

		$statsdefs= $StatsConf[$this->Name];

		$needle= '';
		if (isset($statsdefs['Total']['Needle'])) {
			$needle= $statsdefs['Total']['Needle'];
		}
		
		$cmd= $statsdefs['Total']['Cmd'];
		if ($needle != '') {
			$needle= escapeshellarg($needle);
			$cmd.= " | /usr/bin/grep -a -E $needle";
		}

		if ($tail > -1) {
			/// @attention Normally would never allow large $tail numbers here, but this $tail is computed in the code.
			$cmd.= " | /usr/bin/tail -$tail";
		}

		$cmd= preg_replace('/<LF>/', $logfile, $cmd);

		return $this->RunShellCommand($cmd);
	}
	
	/**
	 * Gets both brief and full statistics.
	 *
	 * @param string $logfile Log file pathname.
	 * @param bool $collecthours Flag to get hour statistics also.
	 * @return array Statistics in serialized arrays.
	 */
	function GetAllStats($logfile, $collecthours= '')
	{
		$date= json_encode(array('Month' => '', 'Day' => ''));
		/// @attention We need $stats return value of GetStats() because of $collecthours constraint
		$stats= $this->_getStats($logfile, $date, $collecthours);
		if ($stats === FALSE) {
			return FALSE;
		}

		// Do not get $stats here, just $briefstats
		$this->GetSavedStats($logfile, $dummy, $briefstats);
		$briefstats= json_encode($briefstats);

		// Use serialized stats as array elements to prevent otherwise extra json_decode() for $stats,
		// which is already serialized by GetStat() above.
		// They are ordinary strings now, this json_encode() should be quite fast
		return Output(json_encode(
				array(
					'stats' 	=> $stats,
					'briefstats'=> $briefstats,
					)
				)
			);
	}
	
	/**
	 * Main statistics collector, as module data set.
	 *
	 * @param string $logfile Log file pathname.
	 * @param array $date Datetime struct.
	 * @param bool $collecthours Flag to collect hour statistics also.
	 * @return array Statistics data set collected.
	 */
	function GetStats($logfile, $date, $collecthours= '')
	{
		return Output($this->_getStats($logfile, $date, $collecthours));
	}

	function _getStats($logfile, $date, $collecthours= '')
	{
		if (!$this->ValidateLogFile($logfile)) {
			return FALSE;
		}

		$date= json_decode($date, TRUE);

		$stats= array();
		$briefstats= array();
		$uptodate= FALSE;

		if ($this->IsLogFileModified($logfile)) {
			$this->UpdateTmpLogFile($logfile);
		}
		else {
			$uptodate= $this->GetSavedStats($logfile, $stats, $briefstats);
		}

		if (!$uptodate) {
			$this->UpdateStats($logfile, $stats, $briefstats);
		}

		if (isset($stats['Date'])) {
			if ($collecthours === '') {
				foreach ($stats['Date'] as $day => $daystats) {
					unset($stats['Date'][$day]['Hours']);
				}
			}

			$re= $this->GetDateRegexp($date);
			foreach ($stats['Date'] as $day => $daystats) {
				if (!preg_match("/$re/", $day)) {
					unset($stats['Date'][$day]);
				}
			}

			$re= $this->GetHourRegexp($date);
			foreach ($stats['Date'] as $day => $daystats) {
				if (isset($daystats['Hours'])) {
					foreach ($daystats['Hours'] as $hour => $hourstats) {
						if (!preg_match("/$re/", $hour)) {
							unset($stats['Date'][$day]['Hours'][$hour]);
						}
					}
				}
			}
		}
		return json_encode($stats);
	}

	function ValidateLogFile($logfile)
	{
		global $MaxLogFileSize;

		/// @todo Should we validate $logfile too?
		$origfile= $this->GetOrigFileName($logfile);
		if (!file_exists($origfile)) {
			Error(_('Log file does not exit').': '.$origfile);
			return FALSE;
		}

		$filestat= stat($origfile);
		if ($filestat['size'] > $MaxLogFileSize*1000000) {
			$error_msg= preg_replace('/<SIZE>/', $MaxLogFileSize, _('File too large, will not process files larger than <SIZE> MB'));
			Error("$error_msg: $origfile = ".$filestat['size']);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Gets the number of lines added to log files since last tmp file update.
	 *
	 * @param string $logfile Log file pathname.
	 * @param int $count Number of new log lines.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function CountDiffLogLines($logfile, &$count)
	{
		global $StatsConf;

		$count= -1;

		if ($this->GetStatsFileInfo($logfile, $oldlinecount, $oldfilestat)) {
			$needle= '';
			$statsdefs= $StatsConf[$this->Name];
			if (isset($statsdefs) && isset($statsdefs['Total']['Needle'])) {
				$needle= $statsdefs['Total']['Needle'];
			}

			$newlinecount= $this->_getFileLineCount($logfile, $needle);
			if ($newlinecount === FALSE) {
				return FALSE;
			}

			$origfile= $this->GetOrigFileName($logfile);

			if (($newlinecount >= $oldlinecount) && !preg_match('/\.gz$/', $origfile)) {
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Logfile modified: $logfile, linecount $oldlinecount->$newlinecount");

				$count= $newlinecount - $oldlinecount;
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Logfile has grown by $count lines: $logfile");
				return TRUE;
			}
			else {
				// Logs probably rotated, recollect the stats
				// Also stats for compressed files are always recollected on rotation, otherwise stats would be merged with the old stats
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Assuming log file rotation: $logfile");
			}
		}
		else {
			ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Cannot get file info: $logfile");
		}
		return FALSE;
	}

	/**
	 * Updates statistics incrementally, both brief and full.
	 *
	 * @param string $logfile Log file pathname.
	 * @param array $stats Full stats.
	 * @param array $briefstats Brief stats.
	 */
	function UpdateStats($logfile, &$stats, &$briefstats)
	{
		global $StatsConf;

		$stats= array();
		$briefstats= array();
		$linecount= 0;

		$statsdefs= $StatsConf[$this->Name];

		if (isset($statsdefs)) {
			$needle= '';
			if (isset($statsdefs['Total']['Needle'])) {
				$needle= $statsdefs['Total']['Needle'];
			}

			// Line count should be obtained here, see SaveStats() for explanation
			$linecount= $this->_getFileLineCount($logfile, $needle);

			if ($this->CountDiffLogLines($logfile, $tail)) {
				$this->GetSavedStats($logfile, $stats, $briefstats);
			}

			$lines= $this->GetStatsLogLines($logfile, $tail);
			if ($lines === FALSE) {
				return FALSE;
			}

			if ($lines !== '') {
				$lines= explode("\n", $lines);

				foreach ($lines as $line) {
					unset($values);
					$this->ParseLogLine($line, $values);
	 				// Post-processing modifies link and/or datetime values.
					$this->PostProcessCols($values);

					$this->CollectDayStats($statsdefs, $values, $line, $stats);

					$briefstatsdefs= $statsdefs['Total']['BriefStats'];

					if (isset($briefstatsdefs)) {
						if (!isset($briefstatsdefs['Date'])) {
							// Always collect Date field
							$briefstatsdefs['Date'] = _('Requests by date');
						}

						// Collect the fields listed under BriefStats
						foreach ($briefstatsdefs as $name => $title) {
							$value= $values[$name];
							if (isset($value)) {
								$briefstats[$name][$value]+= 1;
							}
						}
					}
				}
			}
		}

		$this->SaveStats($logfile, $stats, $briefstats, $linecount);
	}

	/**
	 * Generates date regexp to be used by statistics functions.
	 *
	 * Used to match date indeces of stats array to get stats for date ranges.
	 *
	 * @param array $date Date struct.
	 * @return string Regexp.
	 */
	function GetDateRegexp($date)
	{
		global $MonthNames;

		if ($date['Month'] == '') {
			$re= '.*';
		}
		else {
			$re= $MonthNames[$date['Month']].'\s+';
			if ($date['Day'] == '') {
				$re.= '.*';
			}
			else {
				$re.= sprintf('% 2d', $date['Day']);
			}
		}
		return $re;
	}

	/**
	 * Generates hour regexp to be used by statistics functions.
	 *
	 * @param array $date Date struct.
	 * @return string Regexp.
	 */
	function GetHourRegexp($date)
	{
		if ($date['Hour'] == '') {
			$re= '.*';
		}
		else {
			$re= $date['Hour'];
		}
		return $re;
	}

	/**
	 * Gets saved statistics for the given log file.
	 *
	 * @param string $logfile Log file.
	 * @param array $stats Statistics.
	 * @param array $briefstats Brief statistics.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GetSavedStats($logfile, &$stats, &$briefstats)
	{
		$statsfile= $this->GetStatsFileName($logfile);
		if (($filecontents= $this->GetFile($statsfile)) !== FALSE) {
			if ($serialized_stats= preg_replace("|^(<filestat>.*</filestat>\s)|m", '', $filecontents)) {
				$allstats= json_decode($serialized_stats, TRUE);
				if (isset($allstats['stats']) && isset($allstats['briefstats'])) {
					$stats= $allstats['stats'];
					$briefstats= $allstats['briefstats'];
					return TRUE;
				}
				else {
					ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Missing stats in file: $statsfile");
				}
			}
			else {
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "filestat removal failed in file: $statsfile");
			}
		}
		return FALSE;
	}

	/**
	 * Gets previous line count and stat() from statistics file.
	 *
	 * @param string $logfile Log file.
	 * @param int $linecount Previous line count.
	 * @param array $filestat Previous stat() output.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GetStatsFileInfo($logfile, &$linecount, &$filestat)
	{
		/// @todo Should check file format too, and delete the stats file if corrupted

		$linecount= 0;
		$filestat= array();

		$statsfile= $this->GetStatsFileName($logfile);
		if (file_exists($statsfile)) {
			$filestatline= $this->RunShellCommand("/usr/bin/head -1 $statsfile");
			if (preg_match('|^<filestat>(.*)</filestat>$|', $filestatline, $match)) {
				$fileinfo= json_decode($match[1], TRUE);

				$linecount= $fileinfo['linecount'];
				$filestat= $fileinfo['stat'];
				return TRUE;
			}
			else {
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "filestat missing in: $statsfile");
			}
		}
		else {
			ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "No such file: $statsfile");
		}
		return FALSE;
	}

	/**
	 * Gets name of the tmp statistics file for the given log file.
	 *
	 * @param string $logfile Log file.
	 * @return string Statistics file name.
	 */
	function GetStatsFileName($logfile)
	{
		$origfilename= basename($this->GetOrigFileName($logfile));

		$statsdir= '/var/tmp/utmfw/stats/'.get_class($this);
		$statsfile= "$statsdir/$origfilename";

		return $statsfile;
	}

	/**
	 * Saves collected statistics with the current line count and stat() output.
	 *
	 * @attention Line count should be obtained before statistics collection, otherwise
	 * new lines appended during stats processing may be skipped, hence the $linecount param.
	 *
	 * @param string $logfile Log file.
	 * @param array $stats Statistics.
	 * @param array $briefstats Brief statistics.
	 * @param int $linecount Line count.
	 */
	function SaveStats($logfile, $stats, $briefstats, $linecount)
	{
		$origfile= $this->GetOrigFileName($logfile);
		$statsfile= $this->GetStatsFileName($logfile);

		$savestats=
			'<filestat>'.
			json_encode(
				array(
					'linecount'	=> $linecount,
					'stat'		=> stat($origfile),
					)
			).
			'</filestat>'."\n".
			json_encode(
				array(
					'stats' 	=> $stats,
					'briefstats'=> $briefstats,
					)
			);
		
		$statsdir= dirname($statsfile);
		if (!file_exists($statsdir)) {
			exec('/bin/mkdir -p '.$statsdir);
		}
		
		exec('/usr/bin/touch '.$statsfile);
		$this->PutFile($statsfile, $savestats);
		ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Saved stats to: $statsfile");
	}
	
	/**
	 * Day statistics collector.
	 *
	 * $statsdefs has all the information to collect what data.
	 *
	 * If parsed log time does not have an appropriate hour/min, then 12:00 is assumed.
	 *
	 * @todo How is it possible that Time does not have hour/min? Should have a module
	 * Time field processor as well?
	 * 
	 * @param array $statsdefs Module stats section of $StatsConf.
	 * @param array $values Log fields parsed by caller function.
	 * @param string $line Current log line needed to search for keywords.
	 * @param array $stats Statistics data set collected.
	 *
	 */
	function CollectDayStats($statsdefs, $values, $line, &$stats)
	{
		$re= '/^(\d+):(\d+):(\d+)$/';
		if (preg_match($re, $values['Time'], $match)) {
			$hour= $match[1];
			$min= $match[2];
		}
		else {
			// Should be unreachable
			$hour= '12';
			$min= '00';
			ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'There was no Time in log values, defaulting to 12:00');
		}

		$daystats= &$stats['Date'][$values['Date']];
		$this->IncStats($line, $values, $statsdefs, $daystats);

		$this->CollectHourStats($statsdefs, $hour, $min, $values, $line, $daystats);
	}

	/**
	 * Hour statistics collector.
	 *
	 * $statsdefs has all the information to collect what data.
	 *
	 * $daystats is the subsection of the main $stats array for the current date.
	 *
	 * @param array $statsdefs Module stats section of $StatsConf.
	 * @param string $hour Hour to collect stats for.
	 * @param string $min Min to collect stats for, passed to CollectMinuteStats().
	 * @param array $values Log fields parsed by caller function.
	 * @param string $line Current log line needed to search for keywords.
	 * @param array $daystats Statistics data set collected.
	 */
	function CollectHourStats($statsdefs, $hour, $min, $values, $line, &$daystats)
	{
		$hourstats= &$daystats['Hours'][$hour];
		$this->IncStats($line, $values, $statsdefs, $hourstats);

		$this->CollectMinuteStats($statsdefs, $min, $values, $line, $hourstats);
	}
	
	/**
	 * Increments stats for the given values.
	 * 
	 * @param string $line Current log line needed to search for keywords.
	 * @param array $values Log fields parsed by caller function.
	 * @param array $statsdefs Module stats section of $StatsConf.
	 * @param array $stats Statistics data set collected.
	 */
	function IncStats($line, $values, $statsdefs, &$stats)
	{
		$stats['Sum']+= 1;

		foreach ($statsdefs as $stat => $statconf) {
			if (isset($statconf['Counters'])) {
				foreach ($statconf['Counters'] as $counter => $conf) {
					$value= $values[$conf['Field']];
					if (isset($value)) {
						$stats[$counter]['Sum']+= $value;

						if (isset($conf['NVPs'])) {
							foreach ($conf['NVPs'] as $name => $title) {
								if (isset($values[$name])) {
									$stats[$counter][$name][$values[$name]]+= $value;
								}
							}
						}
					}
				}
			}
		}

		foreach ($statsdefs as $stat => $conf) {
			if (isset($conf['Needle'])) {
				if (preg_match('/'.$conf['Needle'].'/', $line)) {
					$stats[$stat]['Sum']+= 1;

					if (isset($conf['NVPs'])) {
						foreach ($conf['NVPs'] as $name => $title) {
							if (isset($values[$name])) {
								$stats[$stat][$name][$values[$name]]+= 1;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Minute statistics collector.
	 *
	 * $statsdefs has all the information to collect what data.
	 *
	 * $hourstats is the subsection of the $stats array for the current hour.
	 * 
	 * @param array $statsdefs Module stats section of $StatsConf.
	 * @param string $min Min to collect stats for, passed to CollectMinuteStats().
	 * @param array $values Log fields parsed by caller function.
	 * @param string $line Current log line needed to search for keywords.
	 * @param array $hourstats Statistics data set collected.
	 */
	function CollectMinuteStats($statsdefs, $min, $values, $line, &$hourstats)
	{
		$minstats= &$hourstats['Mins'][$min];
		$minstats['Sum']+= 1;

		foreach ($statsdefs as $stat => $statconf) {
			if (isset($statconf['Counters'])) {
				foreach ($statconf['Counters'] as $counter => $conf) {
					if (isset($values[$conf['Field']])) {
						$minstats[$counter]+= $values[$conf['Field']];
					}
				}
			}
		}

		foreach ($statsdefs as $stat => $conf) {
			if (isset($conf['Needle'])) {
				if (preg_match('/'.$conf['Needle'].'/', $line)) {
					$minstats[$stat]+= 1;
				}
			}
		}
	}

	/**
	 * Gets all configuration for a given configuration type.
	 *
	 * @param string $conf Config type
	 * @param int $group E2guardian group
	 * @return array Array of config items
	 */
	function GetConfigValues($conf, $group)
	{
		$this->SetConfig($conf);

		$values= array();
		foreach ($this->Config as $name => $config) {
			if (($output= $this->GetValue($name, $conf, $group)) !== FALSE) {
				$values[$name]= array(
					'Value' => $output,
					'Type' => $this->GetConfValueType($name),
					'Enabled' => TRUE,
					);
			}
			else if (($output= $this->GetDisabledValue($name, $conf, $group)) !== FALSE) {
				$values[$name]= array(
					'Value' => $output,
					'Type' => $this->GetConfValueType($name),
					'Enabled' => FALSE,
					);
			}
		}
		return Output(json_encode($values));
	}

	/**
	 * Returns all enabled configuration for a given configuration type.
	 *
	 * @param string $name Config name
	 * @param string $conf Config type
	 * @param int $group E2guardian group
	 * @return array Array of config items
	 */
	function GetValue($name, $conf, $group)
	{
		$file= $this->GetConfFile($conf, $group);
		
		if ((isset($this->Config[$name]['type'])) && ($this->Config[$name]['type'] === FALSE)) {
			return $this->GetName($file, $name);
		}

		$validValues= $this->getValidValues($name);
		$value= FALSE;
		$set= 0;
		// Try max 5 possible values
		while ($set < 5) {
			$value= $this->GetNVP($file, $name, $set);
			if ($value === FALSE) {
				return FALSE;
			}
			if (!count($validValues) || in_array($value, $validValues)) {
				return "$name=$value";
			}
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Failed validating value: $name: $value");
			$set++;
		}
		return $value;
	}

	/**
	 * Returns all disabled configuration for a given configuration type.
	 */
	function GetDisabledValue($name, $conf, $group)
	{
		$file= $this->GetConfFile($conf, $group);
		
		if ((isset($this->Config[$name]['type'])) && ($this->Config[$name]['type'] === FALSE)) {
			return $this->GetDisabledName($file, $name);
		}

		$validValues= $this->getValidValues($name);
		$value= FALSE;
		$set= 0;
		// Try max 5 possible values
		while ($set < 5) {
			$value= $this->GetDisabledNVP($file, $name, $set);
			if ($value === FALSE) {
				return FALSE;
			}
			if (!count($validValues) || in_array($value, $validValues)) {
				return "$name=$value";
			}
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Failed validating value: $name: $value");
			$set++;
		}
		return $value;
	}

	function getValidValues($name)
	{
		$validValues= array();
		$type= $this->Config[$name]['type'];
		if ($type == STR_on_off) {
			$validValues= array('on', 'off');
		} elseif ($type == STR_On_Off) {
			$validValues= array('On', 'Off');
		} elseif ($type == STR_yes_no) {
			$validValues= array('yes', 'no');
		}
		return $validValues;
	}

	/**
	 * Reads value of commented-out NVP.
	 *
	 * @param string $file Config file
	 * @param string $name Name of NVP
	 * @param int $set There may be multiple parentheses in $re, which one to return
	 * @return string Value of commented NVP or NULL on failure
	 */
	function GetDisabledNVP($file, $name, $set= 0)
	{
		return $this->SearchFile($file, "/^\h*$this->COMC\h*$name\b\h*$this->NVPS\h*([^$this->COMC'\"\n]*|'[^'\n]*'|\"[^\"\n]*\"|[^$this->COMC\n]*)(\h*|\h*$this->COMC.*)$/m", $set);
	}

	/**
	 * Checks if Name exists.
	 *
	 * @param string $file Config file
	 * @param string $name Name of NVP
	 * @return mixed Name or FALSE on failure
	 */
	function GetName($file, $name)
	{
		return $this->SearchFile($file, "/^\h*($name)(\h*$this->COMC.*|\h*)$/m");
	}

	/**
	 * Checks if commented-out Name exists.
	 *
	 * @param string $file Config file
	 * @param string $name Name of NVP
	 * @return mixed Commented Name or FALSE on failure
	 */
	function GetDisabledName($file, $name)
	{
		return $this->SearchFile($file, "/^\h*$this->COMC\h*($name)(\h*$this->COMC.*|\h*)$/m");
	}

	/**
	 * Sets the value of NVP configuration.
	 *
	 * @param string $name Config name
	 * @param string $newvalue New Config value
	 * @param string $conf Config type
	 * @param int $group E2guardian group
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetConfValue($name, $newvalue, $conf, $group)
	{
		$this->SetConfig($conf);
		if (isset($this->Config[$name]['type'])) {
			$re= $this->Config[$name]['type'];
		}
		else {
			$re= '.*';
		}

		if (preg_match("/^($re)$/", $newvalue)) {
			$file= $this->GetConfFile($conf, $group);
			return $this->SetNVP($file, $name, $newvalue);
		}
		Error(_('Invalid value').": $name: $newvalue");
		ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Configuration change failed, invalid value: $name: $newvalue");
		return FALSE;
	}

	function GetConfValueType($name)
	{
		if (isset($this->Config[$name]['type'])) {
			return $this->Config[$name]['type'];
		} else {
			return '.*';
		}
	}

	/**
	 * Enables a configuration item in conf file.
	 *
	 * Certain modules have multiple configuration files,
	 * hence they override this method.
	 *
	 * @param string $name Config name
	 * @param string $conf Config type
	 * @param int $group E2guardian group
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function EnableConf($name, $conf, $group)
	{
		$file= $this->GetConfFile($conf, $group);

		$this->SetConfig($conf);
		if ((isset($this->Config[$name]['type'])) && ($this->Config[$name]['type'] === FALSE)) {
			return $this->EnableName($file, $name);
		}
		return $this->EnableNVP($file, $name, $this->GetConfValueType($name));
	}

	/**
	 * Disables a configuration item in conf file.
	 */
	function DisableConf($name, $conf, $group)
	{
		$file= $this->GetConfFile($conf, $group);

		$this->SetConfig($conf);
		if ((isset($this->Config[$name]['type'])) && ($this->Config[$name]['type'] === FALSE)) {
			return $this->DisableName($file, $name);
		}
		return $this->DisableNVP($file, $name, $this->GetConfValueType($name));
	}

	/**
	 * Enables an NVP configuration item with value.
	 *
	 * @param string $file Config file
	 * @param string $name Config name
	 * @param string $type Type regexp of value
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function EnableNVP($file, $name, $type)
	{
		return $this->ReplaceRegexp($file, "/^\h*$this->COMC(\s*$name\b\s*$this->NVPS\s*$type)$/m", '${1}');
	}

	/**
	 * Enables a Name: configuration item without value.
	 */
	function EnableName($file, $name)
	{
		return $this->ReplaceRegexp($file, "/^\h*$this->COMC(\h*$name(\h*$this->COMC.*|\h*))$/m", '${1}');
	}

	/**
	 * Disables an NVP.
	 */
	function DisableNVP($file, $name, $type)
	{
		return $this->ReplaceRegexp($file, "/^(\h*$name\b\s*$this->NVPS\s*$type)$/m", $this->COMC.'${1}');
	}

	/**
	 * Disables a Name.
	 */
	function DisableName($file, $name)
	{
		return $this->ReplaceRegexp($file, "/^(\h*$name(\h*$this->COMC.*|\h*))$/m", $this->COMC.'${1}');
	}

	/**
	 * Returns configuration file of the module.
	 *
	 * Certain modules have configuration divided into multiple pages/files,
	 * hence they override this method.
	 *
	 * @param string $conf Config type
	 * @param int $group E2guardian group
	 * @return string Config file pathname
	 */
	function GetConfFile($conf, $group)
	{
		return $this->ConfFile;
	}

	/**
	 * Sets configuration file based on config type provided.
	 *
	 * Certain modules have configuration divided into multiple pages/files,
	 * hence they override this method.
	 *
	 * @param string $confname Config type
	 */
	function SetConfig($confname)
	{
	}
	
	/**
	 * Extracts IP address assigned to an interface.
	 *
	 * @param string $if Interface name.
	 * @return mixed IP of the interface or FALSE.
	 */
	function GetIpAddr($if)
	{
		global $Re_Ip;

		if (file_exists($this->confDir."hostname.$if")) {
			return $this->SearchFile($this->confDir."hostname.$if", "/^\h*inet\h*($Re_Ip)\h*$Re_Ip\b.*$/m");
		}
		return FALSE;
	}

	/**
	 * Sends HUP to the module pid.
	 */
	function Reload()
	{
		if ($this->PidFile !== '') {
			if (($pid= $this->GetFile($this->PidFile)) !== FALSE) {
				$this->RunShellCommand("/bin/kill -HUP $pid");
				return TRUE;
			}
		}
		Error(_('Cannot get pid'));
		return FALSE;
	}

	/**
	 * Stops module's parent process with its pid.
	 */
	function Kill()
	{
		global $TmpFile;
		
		if (($pid= $this->GetFile($this->PidFile)) !== FALSE) {
			$cmd= "/bin/kill $pid";

			$count= 0;
			while ($count++ < self::PROC_STAT_TIMEOUT) {
				if (!$this->IsRunning()) {
					return TRUE;
				}
				$this->RunShellCommand("$cmd > $TmpFile 2>&1");
				/// @todo Check $TmpFile for error messages, if so break out instead
				exec('/bin/sleep ' . self::PROC_STAT_SLEEP_TIME);
			}

			// Check one last time due to the last sleep in the loop
			if (!$this->IsRunning()) {
				return TRUE;
			}

			// Kill command is redirected to tmp file
			$output= file_get_contents($TmpFile);
			Error($output);
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Kill failed with: $output");
			return FALSE;
		}
		/// @attention Missing pid file means success, proc is not running anyway
		return TRUE;
	}

	/**
	 * Kills process with the given pid
	 *
	 * Tries PROC_STAT_TIMEOUT times.
	 * @todo Actually should stop retrying on some error conditions?
	 *
	 * @param int $pid Pid
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function KillPid($pid)
	{
		global $TmpFile;

		$cmd= '/bin/kill '.$pid;

		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			if (!$this->IsModulePidRunning($pid)) {
				return TRUE;
			}
			$this->RunShellCommand("$cmd > $TmpFile 2>&1");
			/// @todo Check $TmpFile for error messages, if so break out instead
			exec('/bin/sleep ' . self::PROC_STAT_SLEEP_TIME);
		}

		/// Kill command is redirected to tmp file
		$output= file_get_contents($TmpFile);
		Error($output);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Kill failed with: $output");

		// Check one last time due to the last sleep in the loop
		return !$this->IsModulePidRunning($pid);
	}

	/**
	 * Checks if the pid is running.
	 *
	 * @param int $pid Pid
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function IsModulePidRunning($pid)
	{
		$pidcmd= "/bin/ps -o pid -p $pid | /usr/bin/grep '$pid'";

		$output= $this->RunShellCommand($pidcmd);

		return ($output !== '');
	}

	/**
	 * Gets software version string.
	 */
	function GetVersion()
	{
		if ($this->VersionCmd !== '') {
			return Output($this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -1'));
		}
		return FALSE;
	}

	/**
	 * Gets service statuses.
	 */
	function GetServiceStatus($generate_info= FALSE, $start= '10min')
	{
		global $MODEL_PATH, $ModelFiles, $Models, $ModelsToStat;

		$DashboardIntervals2Seconds= array(
			'1min' => 60,
			'5min' => 300,
			'10min' => 600,
			'30min' => 1800,
			'1hour' => 3600,
			'3hour' => 10800,
			'6hour' => 21600,
			'12hour' => 43200,
			'1day' => 86400,
			'3day' => 259200,
			'1week' => 604800,
			'1month' => 2592000,
			'3month' => 7776000,
			'6month' => 15552000,
			'1year' => 31104000,
			);

		if ($generate_info) {
			require_once($MODEL_PATH.'/'.$ModelFiles['collectd']);

			$model= new $Models['collectd']();
			$gateway= $model->getGatewayPingHost();
			$remote_target= $model->getRemotePingHost();

			exec("doas sh $MODEL_PATH/rrdgraph.sh -$start $gateway $remote_target");
		}

		$status= array();
		$info= array();
		foreach ($ModelsToStat as $name => $caption) {
			if (array_key_exists($name, $ModelFiles)) {
				require_once($MODEL_PATH.'/'.$ModelFiles[$name]);

				if (class_exists($Models[$name])) {
					$model= new $Models[$name]();
					// Pass interval down to _getModuleStatus()
					$module_status= $model->_getModuleStatus($generate_info, $DashboardIntervals2Seconds[$start]);
					if ($module_status !== FALSE) {
						$status[$name]= $module_status['status'];
						if ($generate_info) {
							$info[$name]= $module_status['info'];
						}
					}
				}
				else {
					ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in Models: $name");
				}
			}
			else {
				ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in ModelFiles: $name");
			}
		}

		$output= array();
		$output['status']= $status;
		if ($generate_info) {
			$output['info']= $info;
		}

		return Output(json_encode($output));
	}

	function GetModuleStatus()
	{
		$module_status= $this->_getModuleStatus();
		if ($module_status === FALSE) {
			return FALSE;
		}
		return Output(json_encode($module_status['status']));
	}

	function _getModuleStatus($generate_info= FALSE, $start= 0)
	{
		// @attention Don't use long extended regexps with grep, grep takes too long
		//$logs= $model->_getStatus('(EMERGENCY|emergency|ALERT|alert|CRITICAL|critical|ERROR|error|WARNING|warning):');
		$logs= $this->_getStatus('', $start);
		if ($logs === FALSE) {
			return FALSE;
		}

		$crits= array();
		$errs= array();
		$warns= array();
		foreach ($logs as $l) {
			// Warnings and errors must be more frequent, so check them first
			if ($this->isPrio($l, 'WARNING')) {
				$warns[]= $l;
			}
			else if ($this->isPrio($l, 'ERROR')) {
				$errs[]= $l;
			}
			else if ($this->isPrio($l, 'CRITICAL') || $this->isPrio($l, 'ALERT') || $this->isPrio($l, 'EMERGENCY')) {
				$crits[]= $l;
			}
		}

		$errorStatus= 'N';
		if (count($crits)) {
			$errorStatus= 'C';
		}
		else if (count($errs)) {
			$errorStatus= 'E';
		}
		else if (count($warns)) {
			$errorStatus= 'W';
		}

		$prioLogs= array_merge($crits, $errs, $warns);

		return array(
			'status' => array(
				'Status' => $this->IsRunning()? 'R':'S',
				'ErrorStatus' => $errorStatus,
				'Critical' => count($crits),
				'Error' => count($errs),
				'Warning' => count($warns),
				'Logs' => $prioLogs,
				),
			'info' => array()
			);
	}

	function GetLastLogs($needle, $interval= 60)
	{
		if (!$this->ValidateLogFile($this->LogFile)) {
			return FALSE;
		}

		// @attention Get the last datetime in the logs, so do not use the $needle
		$logs= $this->getStatusLogs($this->LogFile, 1);
		if ($logs === FALSE) {
			return FALSE;
		}

		$lastLogs= array();
		if (count($logs) == 1) {
			$lastLine= $logs[0];
			// @attention Always check the retval of createFromFormat(), it may fail due to format mismatch, e.g. log rotation lines
			$dt= DateTime::createFromFormat($this->dateTimeFormat, $lastLine['Date'].' '.$lastLine['Time']);
			if ($dt) {
				$lastTs= $dt->getTimestamp();

				// @attention Don't get the logs in the last 60 seconds from now instead, otherwise the errors still important cannot be reported after 60 seconds.
				// XXX: 10000 is too large, but the Dashboard may pass down 1 year as the $interval var
				$logs= $this->getStatusLogs($this->LogFile, 10000, $needle);

				$count= count($logs);
				// Loop in reverse order to break out asap
				while (--$count >= 0) {
					$l= $logs[$count];
					$dt= DateTime::createFromFormat($this->dateTimeFormat, $l['Date'].' '.$l['Time']);
					if ($dt) {
						$ts= $dt->getTimestamp();
						if ($lastTs - $ts <= $interval) {
							$lastLogs[]= $l;
						} else {
							break;
						}
					}
				}
			}
		}
		return $lastLogs;
	}

	function getStatusLogs($file, $count, $re= '', $needle= '')
	{
		return $this->_getLiveLogs($file, $count, $re, $needle);		
	}

	function isPrio($log, $prio)
	{
		return strtoupper($log['Prio']) == $prio;
	}

	function GetStatus()
	{
		global $ModelsToStat;

		// Do not use needles with this _getStatus() call, it (grep) takes too long
		$logs= $this->_getStatus('');
		if ($logs === FALSE) {
			return FALSE;
		}

		if (count($logs)) {
			foreach ($this->prios as $p => $msg) {
				$keys= explode('|', $p);

				$errorStr= '';
				$shown= 0;
				$total= 0;
				foreach ($logs as $l) {
					foreach ($keys as $n) {
						if ($this->isPrio($l, $n)) {
							if ($shown < 5) {
								$errorStr.= "\n" . $l['Log'];
								$shown++;
							}
							$total++;
							break;
						}
					}
				}
				if ($shown) {
					if ($total > $shown) {
						$errorStr.= "\n" . str_replace('<COUNT>', $total - $shown, _TITLE('And <COUNT> others not shown.'));
					}
					Error(str_replace('<MODEL>', _($ModelsToStat[$this->Name]), _($msg)) . ':' . $errorStr);
				}
			}
		}
		return TRUE;
	}

	function _getStatus($needle, $start= 0)
	{
		global $StatusCheckInterval;

		return $this->GetLastLogs($needle, $start != 0 ? $start : $StatusCheckInterval);
	}

	/**
	 * Gets sysctl output for the given arg.
	 *
	 * @param string $option sysctl arg, such as hw.sensors.
	 * @return string sysctl output lines.
	 */
	function GetSysCtl($option)
	{
		return Output($this->_getSysCtl($option));
	}

	function _getSysCtl($option)
	{
		return $this->RunShellCommand("/sbin/sysctl $option");
	}

	/**
	 * Restart module processes.
	 */
	function Restart()
	{
		return TRUE;
	}

	/**
	 * Parses standard syslog line.
	 *
	 * @param string $logline Log line
	 * @param array $cols Parsed fields
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ParseSyslogLine($logline, &$cols)
	{
		$re_datetime= '(\w+\s+\d+)\s+(\d+:\d+:\d+)';
		$re_proc= '((\S+(\[\d+\]|)):|)';
		$re_prio= '((EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG|emergency|alert|critical|error|warning|notice|info|debug):|)';
		
		$re= "/^$re_datetime\s+(\S+|)\s+$re_proc\s*$re_prio\s*(.*|)$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['Process']= $match[5];
			$cols['Prio']= $match[8];
			$cols['Log']= $match[9];
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Provides the list of allowed IPs.
	 *
	 * @return List of IPs.
	 */
	function GetAllowedIps()
	{
		global $Re_Ip, $Re_Net;
		
		return Output($this->SearchFileAll($this->ConfFile, "/^\h*!($Re_Ip|$Re_Net)\h*$/m"));
	}

	/**
	 * Provides a list of restricted IPs.
	 *
	 * @return List of IPs.
	 */
	function GetRestrictedIps()
	{
		global $Re_Ip, $Re_Net;
		
		return Output($this->SearchFileAll($this->ConfFile, "/^\h*($Re_Ip|$Re_Net)\h*$/m"));
	}

	/**
	 * Adds an IP or IP range to allowed list.
	 *
	 * @param string $ip IP or IP range.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function AddAllowedIp($ip)
	{
		$this->DelAllowedIp($ip);
		return $this->AppendToFile($this->ConfFile, "!$ip");
	}

	/**
	 * Deletes an IP or IP range from allowed list.
	 *
	 * @param string $ip IP or IP range.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DelAllowedIp($ip)
	{
		$ip= Escape($ip, '/.');
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*!$ip\b.*(\s|))/m", '');
	}

	/**
	 * Adds an IP or IP range to restricted list.
	 *
	 * @param string $ip IP or IP range.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function AddRestrictedIp($ip)
	{
		$this->DelRestrictedIp($ip);
		return $this->AppendToFile($this->ConfFile, $ip);
	}

	/**
	 * Deletes an IP or IP range from restricted list.
	 *
	 * @param string $ip IP or IP range.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DelRestrictedIp($ip)
	{
		$ip= Escape($ip, '/.');
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*$ip\b.*(\s|))/m", '');
	}

	/**
	 * Gets newsyslog configuration for log file.
	 *
	 * Certain log files do not have model classes, hence the $model param.
	 *
	 * @param string $model Index to $ModelsToLogConfig
	 * @return mixed Configuration array or FALSE on failure
	 */
	function GetNewsyslogConfig($model)
	{
		$output= FALSE;
		if (($contents= $this->GetFile($this->newSyslogConf)) !== FALSE) {
			$re_filepath= Escape($this->LogFile, '/');
			$re_owner= '([\w:]+|)';
			$re_mode= '(\d+)';
			$re_count= '(\d+)';
			$re_size= '(\d+|\*)';
			$re_when= '(\d+|\*)';

			$re= "/^\s*$re_filepath\s+$re_owner\s*$re_mode\s+$re_count\s+$re_size\s+$re_when\s+.*$/m";
			if (preg_match($re, $contents, $match)) {
				$output= array(
					$this->LogFile => array(
						'Model' => $model,
						'Count' => $match[3],
						'Size' => $match[4],
						'When' => $match[5],
						),
					);
			}
		}
		return $output;
	}

	/**
	 * Sets newsyslog configuration for log file.
	 *
	 * @param string $file Log file pathname
	 * @param int $count How many archives to keep
	 * @param int/* $size Max site to rotate, or don't care
	 * @param int/* $when Interval to rotate in hours, or don't care
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetNewsyslogConfig($file, $count, $size, $when)
	{
		if (copy($this->newSyslogConf, $this->newSyslogConf.'.bak')) {
			if (($contents= $this->GetFile($this->newSyslogConf)) !== FALSE) {
				$re_filepath= Escape($file, '/');
				$re_owner= '([\w:]+|)';
				$re_mode= '(\d+)';
				$re_count= '(\d+)';
				$re_size= '(\d+|\*)';
				$re_when= '(\d+|\*)';

				$re= "/^(\s*$re_filepath\s+$re_owner\s*$re_mode\s+)$re_count(\s+)$re_size(\s+)$re_when(\s+.*)$/m";
				$re_replace= '${1}'.$count.'${5}'.$size.'${7}'.$when.'${9}';
				if (($newcontents= preg_replace($re, $re_replace, $contents)) !== FALSE) {
					$this->PutFile($this->newSyslogConf, $newcontents);
					return TRUE;
				}
				else {
					ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot set new value: $file, $count, $size, $when");
				}
			}
		}
		else {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file $this->newSyslogConf");
		}
		return FALSE;
	}

	/**
	 * Returns all partitions mounted.
	 */
	function _getPartitions()
	{
		if (($contents= $this->RunShellCommand("/sbin/mount")) !== '') {
			$contents= explode("\n", $contents);
			
			$partitions= array();
			foreach ($contents as $line) {
				if (preg_match('/^(\S+)\s+on\s+(\S+)/', $line, $match)) {
					$partitions[$match[1]]= $match[2];
				}
			}
			return $partitions;
		}
		return FALSE;
	}
	
	/**
	 * Finds sysctl temp and fan sensors.
	 *
	 * There may be multiple sensors. And we don't know which is CPU sensor.
	 *
	 * @return array Sensors extracted from sysctl output, FALSE if error or no sensors.
	 */
	function GetSensors()
	{
		if (($hwsensors= $this->_getSysCtl('hw.sensors')) !== FALSE) {
			$hwsensors= explode("\n", $hwsensors);

			if (count($hwsensors) > 0) {
				$tempsensors= array();
				$fansensors= array();
				foreach ($hwsensors as $sensor) {
					if (preg_match("/^hw\.sensors\.(\w+\d+\.temp\d+)/", $sensor, $match)) {
						if (!in_array($match[1], $tempsensors)) {
							$tempsensors[]= $match[1];
						}
					}
					else if (preg_match("/^hw\.sensors\.(\w+\d+\.fan\d+)/", $sensor, $match)) {
						if (!in_array($match[1], $fansensors)) {
							$fansensors[]= $match[1];
						}
					}
				}
				
				return array(
					'temp'	=> $tempsensors,
					'fan'	=> $fansensors,
					);
			}
		}
		return FALSE;
	}
}

$ModelsToLogConfig= array(
	'system',
	'pf',
	'sslproxy',
	'sslproxyconns',
	'e2guardian',
	'e2guardianlogs',
	'snort',
	'snortalerts',
	'snortips',
	'clamd',
	'freshclam',
	'spamassassin',
	'p3scan',
	'smtp-gated',
	'imspector',
	'dhcpd',
	'named',
	'openvpn',
	'openssh',
	'ftp-proxy',
	'dante',
	'spamd',
	'httpd',
	'httpdlogs',
	'wui_syslog',
	'ctlr_syslog',
	'monitoring',
	);
?>
