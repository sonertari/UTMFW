<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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
 * System-wide.
 */

require_once($MODEL_PATH.'/model.php');

class System extends Model
{
	public $Name= 'system';

	private $confDir= '/etc/';

	public $User= '\S+';
	
	private $rcLocalServices= array();
	private $rcConfLocalServices= array();

	public $LogFile= '/var/log/messages';

	private $rootCronTab= '/var/cron/tabs/root';
	private $cronDir= '/var/cron/';

	function __construct()
	{
		parent::__construct();
	
		$this->Proc= '.';
		
		/**
		 * rc.local module search strings and descriptions
		 *
		 * rc.local file should have lines like the following:
		 *
		 * <pre>
		 * if [ -x /usr/local/libexec/symon ]; then
		 * 	echo -n ' symon';
		 * 	/usr/local/libexec/symon
		 * fi
		 * </pre>
		 *
		 * Indeces of this array are used to comment or uncomment
		 * the lines like the 3rd one above.
		 */
		$this->rcLocalServices= array(
			'/usr/local/sbin/php-fpm-7.0',
			'/usr/local/sbin/named',
			'/usr/local/bin/sslproxy',
			'/usr/local/sbin/e2guardian',
			'/usr/local/sbin/squid',
			'/usr/local/bin/snort',
			'/usr/local/sbin/snortips',
			'/usr/local/sbin/clamd',
			'/usr/local/bin/freshclam',
			'/usr/local/bin/spamd',
			'/usr/local/sbin/p3scan',
			'/usr/local/sbin/smtp-gated',
			'/usr/local/sbin/imspector',
			'/usr/local/sbin/sockd',
			'/usr/local/libexec/symux',
			'/usr/local/libexec/symon',
			'/usr/local/sbin/pmacctd',
			);

		/// rc.conf.local module search strings and descriptions
		$this->rcConfLocalServices= array(
			'pf',
			'httpd_flags',
			'slowcgi_flags',
			'dhcpd_flags',
			'relayd_flags',
			'ftpproxy_flags',
			'ntpd_flags',
			'spamd_flags',
			'spamd_grey',
			'spamlogd_flags',
			'apmd_flags',
			);
				
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetMyName'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Read system hostname'),
					),

				'GetRootEmail'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get system admin e-mail address'),
					),

				'GetIfConfig'		=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get if config'),
					),

				'GetStaticGateway'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Read system gateway'),
					),

				'GetHosts'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('List hosts'),
					),

				'GetNameServer'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Read system nameserver'),
					),

				'GetConfig'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get configuration'),
					),

				'SetMyName'		=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set system hostname'),
					),

				'SetRootEmail'	=>	array(
					'argv'	=>	array(EMAIL),
					'desc'	=>	_('Set e-mail address'),
					),

				'SystemMakeStaticGateway'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Make system gateway static'),
					),

				'SystemMakeDynamicGateway'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Make system gateway dynamic'),
					),

				'GetDynamicGateway'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get system gateway'),
					),

				'SetMyGate'		=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set system gateway'),
					),

				'SetIf'		=>	array(
					/// @todo Is there any pattern or size for options, 6th param?
					'argv'	=>	array(NAME, NAME, IPADR|NAME|EMPTYSTR, IPADR|NAME|EMPTYSTR, IPADR|NAME|EMPTYSTR, STR|EMPTYSTR),
					'desc'	=>	_('Configure an interface'),
					),

				'DeleteIf'	=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Unconfigure an interface'),
					),

				'SetNameServer'	=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set system nameserver'),
					),

				'AddHost'		=>	array(
					'argv'	=>	array(HOST),
					'desc'	=>	_('Add host'),
					),

				'DelHost'		=>	array(
					'argv'	=>	array(HOST),
					'desc'	=>	_('Delete host'),
					),

				'SetDateTime'		=>	array(
					'argv'	=>	array(DATETIME),
					'desc'	=>	_('Set system clock'),
					),

				'UpdateMailAliases'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Update mail aliases'),
					),

				'DisplayRemoteTime'	=>	array(
					'argv'	=>	array(URL|IPADR),
					'desc'	=>	_('Display remote time'),
					),
				
				'SetRemoteTime'	=>	array(
					'argv'	=>	array(URL|IPADR),
					'desc'	=>	_('Set remote time'),
					),

				'GetRemoteTime'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get remote time'),
					),

				'AutoConfig'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Automatic configuration'),
					),

				'InitGraphs'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Init graphs'),
					),

				'DeleteStats'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Erase statistics files'),
					),
				
				'Shutdown'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('System shutdown'),
					),

				'Restart'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('System restart'),
					),

				'NetStart'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Restart network'),
					),

				'GetServiceStartStatus'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get service start status'),
					),

				'DisableService'=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Turn off service'),
					),

				'EnableService'=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Turn on service'),
					),

				'GetPartitions'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get partition list'),
					),

				'GetSystemInfo'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get system information'),
					),

				'GetLogsConfig'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get logs configuration'),
					),

				'SetLogsConfig'=>	array(
					'argv'	=>	array(NAME, FILEPATH, NUM, NUM|ASTERISK, NUM|ASTERISK),
					'desc'	=>	_('Set logs configuration'),
					),

				'RotateLogFile'=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Rotate log file'),
					),

				'RotateAllLogFiles'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Rotate all log files'),
					),

				'SetManCgiHome'=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set man.cgi home'),
					),

				'IsNotifierEnabled'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Is notifier enabled'),
					),

				'EnableNotifier'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Enable notifier'),
					),

				'DisableNotifier'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Disable notifier'),
					),

				'SetNotifyLevel'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set notify level'),
					),

				'SetNotifierHost'	=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Set notifier host'),
					),

				'DisableNotifierSSLVerifyPeer'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Disable notifier SSL verify peer'),
					),

				'EnableNotifierSSLVerifyPeer'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Enable notifier SSL verify peer'),
					),

				'SetNotifierAPIKey'	=>	array(
					// API key type is similar to URL
					'argv'	=>	array(URL),
					'desc'	=>	_('Set notifier API key'),
					),

				'AddNotifierUser'	=>	array(
					'argv'	=>	array(JSON),
					'desc'	=>	_('Add notifier user'),
					),

				'DelNotifierUser'	=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Delete notifier user'),
					),

				'AddFilter'		=>	array(
					'argv'	=>	array(REGEXP),
					'desc'	=>	_('Add filter'),
					),

				'DelFilter'		=>	array(
					'argv'	=>	array(REGEXP),
					'desc'	=>	_('Delete filter'),
					),

				'SetNotifierTimeout'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set notifier timeout'),
					),
				)
			);
	}

	/**
	 * Adds host line to hosts file.
	 *
	 * @param string $host Host definition line.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function AddHost($host)
	{
		$this->DelHost($host);
		return $this->AppendToFile($this->confDir.'hosts', $host);
	}

	/**
	 * Deletes host line from hosts file.
	 *
	 * @param string $host Host definition line.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DelHost($host)
	{
		return $this->ReplaceRegexp($this->confDir.'hosts', "/^(\h*$host(\s|))/m", '');
	}

	/**
	 * Reads hosts file contents.
	 *
	 * @return string Uncommented lines of hosts file.
	 */
	function GetHosts()
	{
		global $Re_Ip;

		return Output($this->SearchFileAll($this->confDir.'hosts', "/^\h*(($Re_Ip|[:\d]+)\b.*)\h*$/m"));
	}

	/**
	 * Reads hostname.
	 *
	 * @return string System name, output of hostname too.
	 */
	function GetMyName()
	{
		return Output($this->_getMyName());
	}

	function _getMyName()
	{
		return $this->GetFile($this->confDir.'myname');
	}

	/**
	 * Reads nameserver setting.
	 *
	 * @return string System-wide nameserver.
	 */
	function GetNameServer()
	{
		return Output($this->SearchFile($this->confDir.'resolv.conf', "/^\h*nameserver\h*([^#]*)\h*$/m"));
	}

	/**
	 * Reads root e-mail address.
	 *
	 * @return string Root e-mail address.
	 */
	function GetRootEmail()
	{
		return Output($this->SearchFile($this->confDir.'mail/aliases', "/^\h*root:\h*([^#]*)\h*$/m"));
	}

	/**
	 * Reads interface configuration.
	 *
	 * @param string $if Interface to get configuration of.
	 * @return string Root e-mail address.
	 */
	function GetIfConfig($if)
	{
		return Output($this->_getIfConfig($if));
	}

	function _getIfConfig($if)
	{
		$file= $this->confDir."hostname.".$if;
		if (file_exists($file)) {
			if (($contents= $this->GetFile($file)) !== FALSE) {
				$re= '^\s*(inet|dhcp)\s*(\S*)\s*(\S*)\s*(\S*)\s*(\S*)\s*$';
				if (preg_match("/$re/m", $contents, $match)) {
					return json_encode(array_slice($match, 1));
				}
			}
		}
		return FALSE;
	}

	/**
	 * Reads static gateway address from mygate file.
	 *
	 * @return string IP address of the gateway.
	 */
	function GetStaticGateway()
	{
		return Output($this->_getStaticGateway());
	}

	function _getStaticGateway()
	{
		return $this->GetFile($this->confDir.'mygate');
	}

	/**
	 * Reads the default gateway on the routing table.
	 * 
	 * @return string IP address of the gateway.
	 */
	function GetDynamicGateway()
	{
		return Output($this->_getDynamicGateway());
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
	 * Reads hostname, gateway, and interface configuration.
	 * 
	 * @return array Configuration.
	 */
	function GetConfig()
	{
		$config= array();
		
		if (($myname= $this->_getMyName()) !== FALSE) {
			$config['Myname']= trim($myname);
		}
	
		if (($mygate= $this->_getStaticGateway()) !== FALSE) {
			$config['Mygate']= trim($mygate);
			$config['StaticGateway']= TRUE;
		}
		else if (($mygate= $this->_getDynamicGateway()) !== FALSE) {
			$config['Mygate']= trim($mygate);
			$config['StaticGateway']= FALSE;
		}

		if (($intif= $this->_getIntIf()) !== FALSE) {
			$config['IntIf']= trim($intif, '"');
		}
		
		if (($extif= $this->_getExtIf()) !== FALSE) {
			$config['ExtIf']= trim($extif, '"');
		}
		
		if (($ifs= $this->_getPhyIfs()) !== FALSE) {
			$ifs= explode("\n", $ifs);
			foreach ($ifs as $if) {
				$config['Ifs'][$if]= array();
				if (($output= $this->_getIfConfig($if)) !== FALSE) {
					$config['Ifs'][$if]= json_decode($output, TRUE);
				}
			}
		}
		
		return Output(json_encode($config));
	}

	/**
	 * Converts dynamic gateway address to static address.
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SystemMakeStaticGateway()
	{
		if (($gateway= $this->_getDynamicGateway()) !== FALSE) {
			return $this->SetMyGate($gateway);
		}
		return FALSE;
	}

	/**
	 * Converts static gateway address to dynamic address.
	 * 
	 * Simply deletes mygate file.
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SystemMakeDynamicGateway()
	{
		return $this->DeleteFile($this->confDir.'mygate');
	}

	/**
	 * Sets system static gateway.
	 * 
	 * Writes the given IP address to mygate file.
	 *
	 * @param string $mygate Gateway IP address.
	 * @return int Return value of file_put_contents().
	 */
	function SetMyGate($mygate)
	{
		// File is created, if does not exist. Otherwise, overwrites the existing file.
		return file_put_contents($this->confDir.'mygate', $mygate.PHP_EOL);
	}

	/**
	 * Sets system hostname.
	 *
	 * Writes the given name to myname file.
	 * 
	 * @param string $myname Hostname.
	 * @return int Return value of file_put_contents().
	 */
	function SetMyName($myname)
	{
		/// @attention Do not change the active hostname here by running /bin/hostname, otherwise UI login over SSH fails.
		// The user should run auto config and then reboot the system to activate the new hostname.
		return file_put_contents($this->confDir.'myname', $myname.PHP_EOL);
	}

	/**
	 * Sets system interface configuration.
	 *
	 * @param string $if Interface name.
	 * @param string $type inet or dhcp only.
	 * @param string $ip IP address.
	 * @param string $mask Netmask.
	 * @param string $bc Broadcast address.
	 * @param string $opt Options.
	 * @return mixed Return value of file_put_contents() or FALSE on fail.
	 */
	function SetIf($if, $type, $ip, $mask, $bc, $opt)
	{
		global $Re_Ip;
		
		// Trim whitespace caused by empty strings
		$ifconf= trim("$type $ip $mask $bc $opt");
		// UTMFW supports only these configuration
		if (preg_match("/^inet\s*$Re_Ip\s*$Re_Ip\s*($Re_Ip|).*$/", $ifconf)
			|| preg_match('/^dhcp\s*NONE\s*NONE\s*NONE.*$/', $ifconf)
			|| preg_match('/^dhcp$/', $ifconf)) {
			/// @warning Need a new line char at the end of hostname.if, otherwise /etc/netstart fails
			/// Since file_put_contents() removes the last new line char, we append a PHP_EOL.
			return file_put_contents($this->confDir.'hostname.'.$if, $ifconf.PHP_EOL);
		}
		else {
			Error(_('Unsupported interface configuration').": $ifconf");
		}
		return FALSE;
	}

	/**
	 * Deconfigures an interface by deleting its hostname file.
	 *
	 * @param string $if Interface name.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DeleteIf($if)
	{
		exec("/sbin/ifconfig $if down");
		exec("/sbin/ifconfig $if delete");
		return $this->DeleteFile($this->confDir.'hostname.'.$if);
	}

	/**
	 * Changes nameserver.
	 *
	 * @param string $nameserver System nameserver IP.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetNameServer($nameserver)
	{
		global $Re_Ip;
		
		return $this->ReplaceRegexp($this->confDir.'resolv.conf', "/^(\h*nameserver\h*)($Re_Ip)(\b.*)$/m", '${1}'.$nameserver.'${3}');
	}

	/**
	 * Changes root e-mail address.
	 *
	 * @param string $emailaddr E-mail address.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetRootEmail($emailaddr)
	{
		return $this->ReplaceRegexp($this->confDir.'mail/aliases', "/^(\h*root:\h*)([^#\s]*)(.*)$/m", '${1}'.$emailaddr.'${3}');
	}

	/**
	 * Sets system clock.
	 *
	 * @param string $datetime Datetime.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetDateTime($datetime)
	{
		exec("/bin/date $datetime 2>&1", $output, $retval);
		/// /bin/date returns 0 on success on OpenBSD 5.9 now.
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		Error($errout);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Set date failed: $errout");
		return FALSE;
	}

	/**
	 * Updates mail aliases.
	 * 
	 * This is necessary after modifying the aliases file.
	 * 
	 * @return string Output of newaliases.
	 */
	function UpdateMailAliases()
	{
		return Output($this->RunShellCommand('/usr/bin/newaliases'));
	}

	/**
	 * Runs installer with the automatic configuration option.
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function AutoConfig()
	{
		global $SRC_ROOT;
		
		exec("$SRC_ROOT/Installer/install.php -a 2>&1", $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		Error($errout);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Auto configuration failed: $errout");
		return FALSE;
	}

	/**
	 * Deletes all graph files and recreates them if necessary.
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function InitGraphs()
	{
		global $VIEW_PATH;

		$result= TRUE;
		// symon
		exec("/bin/rm -f ${VIEW_PATH}/symon/cache/* 2>&1", $output, $retval);
		// Failing to clear the cache dir is not fatal
		exec("/bin/rm -f ${VIEW_PATH}/symon/rrds/localhost/*.rrd 2>&1", $output, $retval);
		if ($retval === 0) {
			exec('/bin/sh /usr/local/share/examples/symon/c_smrrds.sh all 2>&1', $output, $retval);
			if ($retval !== 0) {
				$result= FALSE;
			}
		}
		else {
			$result= FALSE;
		}
		
		// pnrg
		exec("/bin/rm -f ${VIEW_PATH}/pmacct/protograph/utmfw.rrd 2>&1", $output, $retval);
		if ($retval === 0) {
			exec("/bin/sh ${VIEW_PATH}/pmacct/protograph/createrrd.sh 2>&1", $output, $retval);
			if ($retval !== 0) {
				$result= FALSE;
			}
		}
		else {
			$result= FALSE;
		}
		
		// protograph
		exec("/bin/rm -f ${VIEW_PATH}/pmacct/pnrg/spool/*.{gif,cgi,rrd,desc} 2>&1", $output, $retval);
		if ($retval !== 0) {
			$result= FALSE;
		}
				
		if (!$result) {
			$errout= implode("\n", $output);
			Error($errout);
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed initializing graphs: $errout");
		}
		return $result;
	}

	/**
	 * Deletes temporary logs, statistics, and output files created by the WUI.
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DeleteStats()
	{
		exec('/bin/rm -rf /var/tmp/utmfw/* 2>&1', $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		Error($errout);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed erasing statistics files: $errout");
		return FALSE;
	}
	
	/**
	 * Halts and powers the system down.
	 */
	function Shutdown()
	{
		global $TmpFile;
		
		$this->RunShellCommand("/sbin/shutdown -h -p now > $TmpFile 2>&1 &");
		return TRUE;
	}

	/**
	 * Restarts the system.
	 */
	function Restart()
	{
		global $TmpFile;
		
		$this->RunShellCommand("/sbin/shutdown -r now > $TmpFile 2>&1 &");
		return TRUE;
	}

	/**
	 * Displays datetime from the given time server.
	 * 
	 * Writes its output to a temporary file.
	 */
	function DisplayRemoteTime($timeserver)
	{
		global $TmpFile;
		
		// First make sure there are no running rdate processes.
		$this->Pkill('rdate');
		$this->RunShellCommand("/usr/sbin/rdate -p $timeserver > $TmpFile 2>&1 &");
		return TRUE;
	}

	/**
	 * Sets datetime from the given time server.
	 * 
	 * Writes its output to a temporary file.
	 */
	function SetRemoteTime($timeserver)
	{
		global $TmpFile;

		// First make sure there are no running rdate processes.
		$this->Pkill('rdate');
		/// @todo All such networking calls should run on a separate thread, similar to pfctl tests.
		// Note that this command runs on the background.
		$this->RunShellCommand("/usr/sbin/rdate $timeserver > $TmpFile 2>&1 &");
		return TRUE;
	}

	/**
	 * Reads datetime temporary output file.
	 * 
	 * DisplayRemoteTime() and SetRemoteTime() functions write their output to a tmp file.
	 * We wait for their output in a loop.
	 * 
	 * @attention PHP sleep() function is affected by changes to the system clock,
	 * so we cannot use it in the while loop. Note that we change the system time by calling SetRemoteTime().
	 * But, the shell sleep command seems not affected by changes, to the clock, so we use it instead.
	 * 
	 * @return string Datetime.
	 */
	function GetRemoteTime()
	{
		global $TmpFile;
		
		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			// Wait until rdate exits
			if (!$this->IsRunning('rdate')) {
				break;
			}
			// Shell sleep command seems not affected by changes to clock
			exec('/bin/sleep ' . self::PROC_STAT_SLEEP_TIME);
		}
		
		if ($count < self::PROC_STAT_TIMEOUT) {
			if (($output= $this->GetFile($TmpFile)) !== FALSE) {
				$retval= $output;
			}
		}
		else {
			$retval= _('The process is taking too long, thus will run in the background.');
		}
		return Output($retval);
	}
	
	/**
	 * Restarts the network.
	 * 
	 * This method should be called after changing the network configuration of the system.
	 * 
	 * @attention We have to reload pf rules too, otherwise all access to the system may be blocked.
	 * For example, if you change the IP address of int_if, you need to reload the rules;
	 * otherwise, pf would still be running with rules using the old IP address of int_if.
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function NetStart()
	{
		// Refresh pf rules too
		$cmd= "/bin/sh /etc/netstart 2>&1 && /sbin/pfctl -f $this->PfRulesFile 2>&1";
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		Error($errout);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Netstart failed: $errout");
		return FALSE;
	}
	
	/**
	 * Reads all processes from ps output.
	 *
	 * Used to list all processes.
	 *
	 * @param array $psout ps output obtained elsewhere.
	 * @return array Parsed ps output.
	 */
	function SelectProcesses($psout)
	{
		//   PID STARTED  %CPU      TIME %MEM   RSS   VSZ STAT  PRI  NI TTY      USER     GROUP    COMMAND
		//     1  5:10PM   0.0   0:00.03  0.0   388   412 Is     10   0 ??       root     wheel    /sbin/init
		$re= '/^\s*(\d+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\d+)\s+(\d+)\s+(\S+)\s+(\d+)\s+(\d+)\s+\S+\s+(\S+)\s+(\S+)\s+(.+)$/';
		
		$processes= array();
		foreach ($psout as $line) {
			if (preg_match($re, $line, $match)) {
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
		return $processes;
	}
	
	/**
	 * Set int_net.
	 */
	function SetManCgiHome($ip)
	{
		$re= '|^(\s*\$www\{\'home\'\}\h*=\h*\')(.*)(\'\h*;\h*)$|m';
		return $this->ReplaceRegexp('/var/www/htdocs/utmfw/View/cgi-bin/man.cgi', $re, '${1}'."https://$ip".'${3}');
	}

	/**
	 * Gets partition list.
	 */
	function GetPartitions()
	{
		return Output($this->_getPartitions());
	}

	function _getPartitions()
	{
		return $this->RunShellCommand('/bin/df -h | /usr/bin/egrep "^\/dev"');
	}

	/**
	 * Gets system information.
	 */
	function GetSystemInfo()
	{
		return Output($this->RunShellCommand('/usr/bin/uptime') . "\n" .
			$this->RunShellCommand('/bin/date') . "\n" .
			$this->RunShellCommand('/sbin/sysctl -n kern.securelevel') . "\n" .
			$this->RunShellCommand('/sbin/sysctl -n net.inet.ip.forwarding') . "\n" .
			$this->RunShellCommand('/sbin/sysctl -n net.inet6.ip6.forwarding || echo "n/a"') . "\n" .
			$this->RunShellCommand('/sbin/sysctl -n kern.hostname') . "\n" .
			$this->RunShellCommand('/usr/bin/uname -msr') . "\n" .
			$this->RunShellCommand('/usr/bin/uname -p'));
	}

	/**
	 * Gets service start stati.
	 */
	function GetServiceStartStatus()
	{
		$output= array();
		foreach ($this->rcConfLocalServices as $service) {
			$stat= $this->GetServiceStatRcConfLocal($this->rcConfLocal, $service);
			if ($stat === '') {
				$output[$service]= TRUE;
			}
			else if ($stat === '#') {
				$output[$service]= FALSE;
			}
		}
		foreach ($this->rcLocalServices as $service) {
			$stat= $this->GetServiceStatRcLocal($this->confDir.'rc.local', $service);
			if ($stat === '') {
				$output[$service]= TRUE;
			}
			else if ($stat === '#') {
				$output[$service]= FALSE;
			}
		}
		return Output(json_encode($output));
	}
	
	/**
	 * Gets service startup status in rc.conf.local.
	 *
	 * @param string $file Config file.
	 * @param string $service Service name in rc.conf.local.
	 * @return string Empty if on, # if off.
	 */
	function GetServiceStatRcConfLocal($file, $service)
	{
		return $this->SearchFile($file, "/^\h*(#|)\h*$service\h*=.*$/m");
	}

	/**
	 * Gets service startup status in rc.local.
	 *
	 * @param string $file Config file.
	 * @param string $service Service name in rc.local.
	 * @return string Empty if on, # if off.
	 */
	function GetServiceStatRcLocal($file, $service)
	{
		$service= Escape($service, '/');
		return $this->SearchFile($file, "/^\h*(#|)\h*$service\b.*$/m");
	}
	
	/**
	 * Turn off (disable) service startup.
	 *
	 * @param string $service Service name.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DisableService($service)
	{
		if (in_array($service, $this->rcConfLocalServices)) {
			return $this->DisableServiceRcConfLocal($service);
		}
		else if (in_array($service, $this->rcLocalServices)) {
			return $this->DisableServiceRcLocal($service);
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Turn on (enable) service startup.
	 *
	 * @param string $service Service name.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function EnableService($service)
	{
		if (in_array($service, $this->rcConfLocalServices)) {
			return $this->EnableServiceRcConfLocal($service);
		}
		else if (in_array($service, $this->rcLocalServices)) {
			return $this->EnableServiceRcLocal($service);
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Turn off (disable) service startup in rc.conf.local.
	 *
	 * @param string $service Service name in rc.conf.local.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DisableServiceRcConfLocal($service)
	{
		return $this->ReplaceRegexp($this->rcConfLocal, "/^(\h*$service\h*=.*)$/m", '#${1}');
	}

	/**
	 * Turn off (disable) service startup in rc.local.
	 *
	 * @param string $service Service name in rc.local.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DisableServiceRcLocal($service)
	{
		$service= Escape($service, '/');

		// Prevent infinite loop in case ReplaceRegexp() always returns TRUE
		// We don't expect more than 10 lines
		$count= 10;
		$retval= FALSE;

		/// @attention snort and pmacct have multiple lines in rc.local, hence the while loop
		/// @attention $count should come first in the if condition to be decremented on each iteration of the loop
		while ($count-- && $this->ReplaceRegexp($this->confDir.'rc.local', "/^(\h*$service\b.*)$/m", '#${1}')) {
			$retval= TRUE;
		}

		return $retval;
	}

	/**
	 * Turn on (enable) service startup in rc.conf.local.
	 *
	 * @param string $service Service name in rc.conf.local.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function EnableServiceRcConfLocal($service)
	{
		return $this->ReplaceRegexp($this->rcConfLocal, "/^\h*#(\h*$service\h*=.*)$/m", '${1}');
	}

	/**
	 * Turn on (enable) service startup in rc.local.
	 * 
	 * See the attention notes in DisableServiceRcLocal()
	 *
	 * @param string $service Service name in rc.local.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function EnableServiceRcLocal($service)
	{
		$service= Escape($service, '/');

		$count= 10;
		$retval= FALSE;

		while ($count-- && $this->ReplaceRegexp($this->confDir.'rc.local', "/^\h*#(\h*$service\b.*)$/m", '${1}')) {
			$retval= TRUE;
		}

		return $retval;
	}

	/**
	 * Get logs configuration.
	 */
	function GetLogsConfig()
	{
		global $MODEL_PATH, $ModelFiles, $Models, $ModelsToLogConfig;

		$output= array();
		foreach ($ModelsToLogConfig as $m) {
			if (array_key_exists($m, $ModelFiles)) {
				require_once($MODEL_PATH.'/'.$ModelFiles[$m]);

				if (class_exists($Models[$m])) {
					$model= new $Models[$m]();
					if (($config= $model->GetNewsyslogConfig($m)) !== FALSE) {
						$output= array_merge($output, $config);
					}
				}
				else {
					ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in Models: $m");
				}
			}
			else {
				ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in ModelFiles: $m");
			}
		}
		return Output(json_encode($output));
	}

	/**
	 * Set logs configuration.
	 */
	function SetLogsConfig($model, $file, $count, $size, $when)
	{
		global $MODEL_PATH, $ModelFiles, $Models, $ModelsToLogConfig;

		if (array_key_exists($model, $ModelFiles)) {
			require_once($MODEL_PATH.'/'.$ModelFiles[$model]);

			if (class_exists($Models[$model])) {
				$model= new $Models[$model]();
				return $model->SetNewsyslogConfig($file, $count, $size, $when);
			}
			else {
				ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in Models: $model");
			}
		}
		else {
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in ModelFiles: $model");
		}
		return FALSE;
	}

	/**
	 * Calls forked rotation function.
	 */
	function RotateLogFile($file)
	{
		return $this->DaemonizeFunc('DaemonizedRotateLogFile', $file);
	}

	/**
	 * Rotate log file via newsyslog.
	 *
	 * Daemonized, because newsyslog may kill httpd, hence parent.
	 */
	function DaemonizedRotateLogFile($file)
	{
		global $TmpFile;

		if (($contents= $this->GetFile($this->newSyslogConf)) !== FALSE) {
			$re_filepath= Escape($file, '/');
			$re_owner= '([\w:]+|)';
			$re_mode= '(\d+)';
			$re_count= '(\d+)';
			$re_size= '(\d+|\*)';
			$re_when= '(\d+|\*)';

			$re= "/^(\s*$re_filepath\s+$re_owner\s*$re_mode\s+$re_count\s+$re_size\s+$re_when\s+.*)$/m";
			if (preg_match($re, $contents, $match)) {
				$line= $match[1];
				/// @attention Do not use & at the end to send the command to background
				/// Otherwise, gzip fails to compress the rotated file sometimes
				/// This daemonized child process should not exit before newsyslog exits
				$cmd= "/bin/echo '$line' | /usr/bin/newsyslog -vF -f -  > $TmpFile 2>&1";
				exec($cmd, $output, $retval);
				if ($retval === 0) {
					return TRUE;
				}
				$errout= implode("\n", $output);
				Error($errout);
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Log rotation failed: $errout");
			}
		}
		return FALSE;
	}

	/**
	 * Calls forked full rotation function.
	 */
	function RotateAllLogFiles()
	{
		return $this->DaemonizeFunc('DaemonizedRotateAllLogFiles');
	}

	/**
	 * Rotate all log files via newsyslog.
	 *
	 * Daemonized, because newsyslog kills httpd, hence parent,
	 * stopping rotation in the middle, e.g. before compressing files.
	 */
	function DaemonizedRotateAllLogFiles()
	{
		global $TmpFile;

		$cmd= "/usr/bin/newsyslog -vF -f $this->newSyslogConf > $TmpFile 2>&1 &";
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		Error($errout);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Log rotation failed: $errout");
		return FALSE;
	}
	
	/**
	 * Daemonizes to run the given function.
	 */
	function DaemonizeFunc($func, $param= '')
	{
		$pid= pcntl_fork();
		if ($pid == -1) {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot fork');
		}
		else if ($pid) {
			/// @attention Parent should exit, not return TRUE
			/// Otherwise the Controller returns twice confusing the View
			exit;
		}
		else {
			// Make the child process a session leader
	        $sid= posix_setsid();
		    if ($sid < 0) {
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot make the child a session leader');
				// Exit if cannot daemonize completely
				return FALSE;
			}
			// The child is daemonized now, hence survives even if its process group is killed.
			// This is necessary when rotating httpd logs.

			$argv= array();
			if ($param !== '') {
				$argv= array($param);
			}
			return call_user_func_array(array($this, $func), $argv);
		}
	}

	function IsNotifierEnabled()
	{
		$stat= $this->SearchFile($this->rootCronTab, "?^\h*(#|)\h*\*/1\h+\*\h+\*\h+\*\h+\*\h+/usr/local/bin/php\h+/var/www/htdocs/utmfw/Notifier/notify.php\h+.*$?m");
		return ($stat === '');
	}

	function DisableNotifier()
	{
		$tmp= $this->cronDir.'root';
		$this->PutFile($tmp, $this->getRootCronTab());

		$retval= $this->ReplaceRegexp($tmp, "?^(\h*\*/1\h+\*\h+\*\h+\*\h+\*\h+/usr/local/bin/php\h+/var/www/htdocs/utmfw/Notifier/notify.php\h+.*)$?m", '#${1}');
		if (file_exists($tmp.'.bak')) {
			unlink($tmp.'.bak');
		}

		if ($retval) {
			exec("/usr/bin/crontab -u root $tmp");
			return !$this->IsNotifierEnabled();
		}
		/// @todo Should we delete the tmp file too?
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed disabling notifier");
		return FALSE;
	}

	function EnableNotifier()
	{
		$tmp= $this->cronDir.'root';
		$this->PutFile($tmp, $this->getRootCronTab());

		$retval= $this->ReplaceRegexp($tmp, "?^\h*#(\h*\*/1\h+\*\h+\*\h+\*\h+\*\h+/usr/local/bin/php\h+/var/www/htdocs/utmfw/Notifier/notify.php\h+.*$)?m", '${1}');
		if (file_exists($tmp.'.bak')) {
			unlink($tmp.'.bak');
		}

		if ($retval) {
			exec("/usr/bin/crontab -u root $tmp");
			return $this->IsNotifierEnabled();
		}
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed enabling notifier");
		return FALSE;
	}

	function getRootCronTab()
	{
		/// @attention Remove the comments cron adds at the beginning of table, otherwise they are multiplied
		//# DO NOT EDIT THIS FILE - edit the master and reinstall.
		//# (/var/cron/root installed on Thu Aug  9 18:03:44 2018)
		//# (Cron version V5.0)

		$contents= explode("\n", $this->GetFile($this->rootCronTab));

		// Match cron comments, otherwise there are other lines at the beginning
		// Remove in reverse order
		if (preg_match('/^#\h*\(Cron\h*version\h*.*$/', $contents[2])) {
			unset($contents[2]);
		}
		if (preg_match('|^#\h*\(/var/cron/root\h*installed\h*on\h*.*$|', $contents[1])) {
			unset($contents[1]);
		}
		if (preg_match('/^#\h*DO\h*NOT\h*EDIT\h*THIS\h*FILE\h*.*$/', $contents[0])) {
			unset($contents[0]);
		}
		return implode("\n", $contents);
	}

	function SetNotifyLevel($level)
	{
		global $ROOT, $TEST_DIR_SRC;

		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifyLevel', $level.';');
	}

	function SetNotifierHost($host)
	{
		global $ROOT, $TEST_DIR_SRC;

		// Append semi-colon to new value, this setting is a PHP line
		/// @attention $host string should be in quotes
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifierHost', "'$host';");
	}

	function DisableNotifierSSLVerifyPeer($bool)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifierSSLVerifyPeer', $bool.';');
	}

	function EnableNotifierSSLVerifyPeer($bool)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifierSSLVerifyPeer', $bool.';');
	}

	function SetNotifierAPIKey($apikey)
	{
		global $ROOT, $TEST_DIR_SRC;

		// Append semi-colon to new value, this setting is a PHP line
		/// @attention $apikey string should be in quotes
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifierAPIKey', "'$apikey';");
	}

	function AddNotifierUser($userJson)
	{
		global $ROOT, $TEST_DIR_SRC, $NotifierUsers;

		$users= json_decode($NotifierUsers, TRUE);
		if ($users === NULL) {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot json_decode NotifierUsers, starting with an empty list: $NotifierUsers");
			$users= array();
		}

		$user= json_decode($userJson, TRUE);
		$token= key($user);
		$users[$token]= $user[$token];
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifierUsers', "'".json_encode($users)."';");
	}

	function DelNotifierUser($token)
	{
		global $ROOT, $TEST_DIR_SRC, $NotifierUsers;

		$users= json_decode($NotifierUsers, TRUE);
		if ($users === NULL) {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot json_decode NotifierUsers, starting with an empty list: $NotifierUsers");
			$users= array();
		}

		if (array_key_exists($token, $users)) {
			unset($users[$token]);
		}
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifierUsers', "'".json_encode($users)."';");
	}

	function AddFilter($filter)
	{
		global $ROOT, $TEST_DIR_SRC, $NotifierFilters;

		$filters= json_decode($NotifierFilters, TRUE);
		if ($filters === NULL) {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot json_decode NotifierFilters, starting with an empty list: $NotifierFilters");
			$filters= array();
		}

		$filters= $this->_delFilter($filters, $filter);
		$filters[]= $filter;
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifierFilters', "'".json_encode($filters)."';");
	}

	function DelFilter($filter)
	{
		global $ROOT, $TEST_DIR_SRC, $NotifierFilters;

		$filters= json_decode($NotifierFilters, TRUE);
		if ($filters !== NULL) {
			$filters= $this->_delFilter($filters, $filter);
			return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifierFilters', "'".json_encode($filters)."';");
		}
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot json_decode NotifierFilters: $NotifierFilters");
		return FALSE;
	}

	function _delFilter($tokens, $token)
	{
		$key= FALSE;
		try {
			$key= array_search($token, $tokens, TRUE);
		} catch (Exception $ignored) {}
		
		if ($key !== FALSE) {
			unset($tokens[$key]);
			/// @attention Fake slice to update the keys, otherwise json_encode() saves the numerical keys too, i.e. returns a json object not array.
			$tokens= array_slice($tokens, 0);
		}
		return $tokens;
	}

	function SetNotifierTimeout($timeout)
	{
		global $ROOT, $TEST_DIR_SRC;

		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$NotifierTimeout', $timeout.';');
	}
}
?>
