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
 * Contains Pf class to run pf tasks.
 */

use Model\RuleSet;

require_once($MODEL_PATH.'/model.php');

class Pf extends Model
{
	public $Name= 'pf';

	public $LogFile= '/var/log/pflog';
	
	private $pftopCmd= '/usr/local/sbin/pftop -b -a -o pkt -w 120';

	// PR  DIR SRC             DEST          STATE                   AGE      EXP      PKTS BYTES
	// tcp In  10.0.0.11:55802 10.0.0.254:22 ESTABLISHED:ESTABLISHED 00:48:37 24:00:00 4198 584448
	private $re_Pftop= "/^\s*(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\d+)\s+(\d+)$/";

	public $NVPS= '=';
	public $ConfFile= '/etc/pf.restrictedips';

	public $AfterHoursFile= '/etc/pfre/pf.conf.afterhours';

	private $PNRG_IMG_DIR;

	private $tmpCrontab= '/var/tmp/utmfw/root';

	private $PF_PATH= '';

	private $re_BusinessDays;
	private $re_DisabledBusinessDays;

	private $re_Flush;
	private $re_DisabledFlush;

	private $re_Holidays;
	private $re_DisabledHolidays;

	function __construct()
	{
		global $VIEW_PATH, $TCPDUMP;

		parent::__construct();

		$this->PNRG_IMG_DIR= $VIEW_PATH.'/pmacct/pnrg/spool/';

		$this->PF_PATH= $VIEW_PATH.'/pf';

		$re_days= '(\*|[0-7,]*)';
		$re_hour= '(\d+)';
		$re_min= '(\d+)';

		$re= "$re_min\s+$re_hour\s+\*\s+\*\s+$re_days\s+\/sbin\/pfctl -a AfterHours -f \/etc\/pfre\/pf\.conf\.afterhours";
		$this->re_BusinessDays= "^$re$";
		$this->re_DisabledBusinessDays= "^#$re$";

		$re= "$re_min\s+$re_hour\s+\*\s+\*\s+$re_days\s+\/sbin\/pfctl -a AfterHours -Fr";
		$this->re_Flush= "^$re$";
		$this->re_DisabledFlush= "^#$re$";

		$re= "\*\s+\*\s+\*\s+\*\s+$re_days\s+\/sbin\/pfctl -a AfterHours -f \/etc\/pfre\/pf\.conf\.afterhours";
		$this->re_Holidays= "^$re$";
		$this->re_DisabledHolidays= "^#$re$";
		
		$this->CmdLogStart= $TCPDUMP.' <LF> | /usr/bin/head -1';

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SetIfs'=>	array(
					'argv'	=>	array(NAME, NAME),
					'desc'	=>	_('Set interfaces'),
					),
				
				'SetIntnet'=>	array(
					'argv'	=>	array(IPRANGE),
					'desc'	=>	_('Set internal net'),
					),
				
				'GetPfInfo'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pf info'),
					),
				
				'GetPfTimeoutInfo'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pf timeout info'),
					),

				'GetPfMemInfo'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pf memory info'),
					),
				
				'GetPfQueueInfo'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pf queue info'),
					),

				'GetPfRulesInfo'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pf rules info'),
					),

				'GetPfIfsInfo'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pf interface info'),
					),

				'GetPfRules'=>	array(
					'argv'	=>	array(FILEPATH, BOOL|NONE, BOOL|NONE),
					'desc'	=>	_('Get pf rules'),
					),
				
				'GetPfRuleFiles'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pf rule files'),
					),
				
				'DeletePfRuleFile'=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Delete pf rule file'),
					),

				'InstallPfRules'=>	array(
					'argv'	=>	array(JSON, SAVEFILEPATH|NONE, BOOL|NONE, BOOL|NONE),
					'desc'	=>	_('Install pf rules'),
					),
				
				'GeneratePfRule'=>	array(
					'argv'	=>	array(JSON, NUM, BOOL|NONE),
					'desc'	=>	_('Generate pf rule'),
					),

				'GeneratePfRules'=>	array(
					'argv'	=>	array(JSON, BOOL|NONE, BOOL|NONE),
					'desc'	=>	_('Generate pf rules'),
					),

				'TestPfRules'=>	array(
					'argv'	=>	array(JSON),
					'desc'	=>	_('Test pf rules'),
					),

				'GetStateCount'	=>	array(
					'argv'	=>	array(REGEXP|NONE),
					'desc'	=>	_('Get pf state count'),
					),

				'GetStateList'	=>	array(
					'argv'	=>	array(NUM, TAIL, REGEXP|NONE),
					'desc'	=>	_('Get pf states'),
					),

				'SetAfterhoursIf'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set afterhours if'),
					),
				
				'ApplyPfRules'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Apply pf rules'),
					),

				'GetHostGraphsList'	=>	array(
					'argv'	=>	array(IPADR|EMPTYSTR),
					'desc'	=>	_('Get host graphs'),
					),

				'EnableAfterHours'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Enable AfterHours'),
					),

				'DisableAfterHours'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Disable AfterHours'),
					),

				'GetAfterHoursPfRules'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get AfterHour pf rules'),
					),

				'GetAfterHours'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get AfterHours'),
					),

				'DisableAfterHoursBusinessDays'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Disable AfterHours business days'),
					),

				'EnableAfterHoursBusinessDays'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Enable AfterHours business days'),
					),

				'DisableAfterHoursHolidays'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Disable AfterHours holidays'),
					),

				'EnableAfterHoursHolidays'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Enable AfterHours holidays'),
					),

				'ApplyAfterHours'=>	array(
					'argv'	=>	array(NUM|EMPTYSTR, NUM|EMPTYSTR, NUM|EMPTYSTR, NUM|EMPTYSTR, AFTERHOURS, AFTERHOURS),
					'desc'	=>	_('Apply AfterHours'),
					),
				)
			);
	}

	/**
	 * Sets interface names in pf.conf.
	 * 
	 * @param string $lanif Name of the internal interface.
	 * @param string $wanif Name of the external interface.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetIfs($lanif, $wanif)
	{
		$retval= $this->SetNVP($this->PfRulesFile, 'int_if', '"'.$lanif.'"');
		$retval&= $this->SetNVP($this->PfRulesFile, 'ext_if', '"'.$wanif.'"');
		return $retval;
	}

	/**
	 * Sets int_net.
	 * 
	 * @param string $net Internal network address.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetIntnet($net)
	{
		return $this->SetNVP($this->PfRulesFile, 'int_net', '"'.$net.'"');
	}

	/**
	 * Checks if pf is enabled or not.
	 * 
	 * @return bool TRUE if enabled, FALSE otherwise.
	 */
	function IsRunning($proc= '')
	{
		$output= $this->RunShellCommand('/sbin/pfctl -s info');
		if (preg_match('/Status:\s*(Enabled|Disabled)\s*/', $output, $match)) {
			return ($match[1] == 'Enabled');
		}
		return FALSE;
	}

	/**
	 * Enable pf.
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Start()
	{
		if (!$this->IsRunning()) {
			exec('/sbin/pfctl -e 2>&1', $output, $retval);
			if ($retval !== 0) {
				Error(implode("\n", $output));
			}
			return $this->IsRunning();
		} else {
			return TRUE;
		}
	}

	/**
	 * Disable pf.
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Stop()
	{
		if ($this->IsRunning()) {
			exec('/sbin/pfctl -d 2>&1', $output, $retval);
			if ($retval !== 0) {
				Error(implode("\n", $output));
			}
			return !$this->IsRunning();
		} else {
			return TRUE;
		}
	}

	function _getModuleStatus($start, $generate_info= FALSE)
	{
		/// @todo Should we get more status info from pfctl -s info output?
		// For example, the memory counter seems important: 'memory could not be allocated'
		$status= parent::_getModuleStatus($start, $generate_info);

		if ($generate_info) {
			$status['info']['states']= $this->_getStateCount();
		}
		return $status;
	}

	/**
	 * Runs pfctl info commands.
	 * 
	 * @param string $cmd Command to execute.
	 * @return mixed Output of the command on success, FALSE on fail.
	 */
	function RunPfInfoCmd($cmd)
	{
		if (!$this->RunPfctlCmd($cmd, $output, $retval)) {
			Error(_('Failed running pfctl command') . ': ' . $cmd);
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed running pfctl stats command: $cmd");
			return FALSE;
		}

		if ($retval === 0) {
			return Output(implode("\n", $output));
		}
		return FALSE;
	}

	/**
	 * Gets general pf info.
	 * 
	 * @bug We cannot use RunPfctlCmd() here, because pfctl info output gives:
	 * msg_send(): msgsnd failed: Invalid argument
	 * This issue still persists on OpenBSD 6.4 with PHP 7.0.32.
	 * 
	 * @attention So, do not use the following line: return $this->RunPfInfoCmd('/sbin/pfctl -s info');
	 * 
	 * @return mixed Output of the command on success, FALSE on fail.
	 */
	function GetPfInfo()
	{
		$info= $this->_getPfInfo();
		if ($info !== FALSE) {
			return Output(implode("\n", $info));
		}
		return FALSE;
	}

	function _getPfInfo()
	{
		exec('/sbin/pfctl -vs info', $output, $retval);
		if ($retval === 0) {
			return $output;
		}
		return FALSE;
	}

	/**
	 * Gets pf memory info.
	 * 
	 * @return mixed Output of the command on success, FALSE on fail.
	 */
	function GetPfMemInfo()
	{
		return $this->RunPfInfoCmd('/sbin/pfctl -s memory');
	}

	/**
	 * Gets pf timeouts.
	 * 
	 * @return mixed Output of the command on success, FALSE on fail.
	 */
	function GetPfTimeoutInfo()
	{
		return $this->RunPfInfoCmd('/sbin/pfctl -s timeouts');
	}

	/**
	 * Gets pf queue info.
	 * 
	 * @return mixed Parsed output of the command on success, FALSE on fail.
	 */
	function GetPfQueueInfo()
	{
		exec('/sbin/pfctl -s queue -v', $output, $retval);
		if ($retval === 0) {
			return Output(json_encode($this->parsePfQueueInfo($output)));
		}
		return FALSE;
	}

	function parsePfQueueInfo($lines)
	{
		$queues= array();
		$q= array();

		//queue std on em1 bandwidth 100M qlimit 50
		//  [ pkts:          0  bytes:          0  dropped pkts:      0 bytes:      0 ]
		//  [ qlength:   0/ 50 ]
		foreach ($lines as $line) {
			if (preg_match('/^queue\s+(\S+)/', $line, $match)) {
				if (!isset($q['name'])) {
					$q['name']= '';
				}
				$queues[]= $q;

				$q= array('name' => $match[1]);
			} elseif (preg_match('/^\s*\[\s*pkts:\s*(\d+)\s*bytes:\s*(\d+)\s*dropped pkts:\s*(\d+)\s*bytes:\s*(\d+)\s*\]/', $line, $match)) {
				$q['pkts']= convertDecimal($match[1]);
				$q['bytes']= convertBinary($match[2]);
				$q['droppedPkts']= convertDecimal($match[3]);
				$q['droppedBytes']= convertBinary($match[4]);
			} elseif (preg_match('/^\s*\[\s*qlength:\s*(\d+)\s*\/\s*(\d+)\s*/', $line, $match)) {
				$q['length']= $match[1] . '/' . $match[2];
			} else {
				ctlr_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Failed parsing queue line: $line");
			}
		}

		if (count($q) > 0) {
			$queues[]= $q;
		}
		return $queues;
	}
	
	/**
	 * Gets pf interfaces info.
	 * 
	 * @attention Do not use the following line: return $this->RunPfInfoCmd('/sbin/pfctl -s Interfaces -vv');
	 * See GetPfInfo() for the details of this bug.
	 * 
	 * @return mixed Parsed output of the command on success, FALSE on fail.
	 */
	function GetPfIfsInfo()
	{
		exec('/sbin/pfctl -s Interfaces -vv', $output, $retval);
		if ($retval === 0) {
			return Output(json_encode($this->parsePfIfsInfo($output)));
		}
		return FALSE;
	}

	function parsePfIfsInfo($lines)
	{
		$ifs= array();
		$i= array();

		//all
		//	Cleared:     Thu Jan  1 02:00:01 1970
		//	References:  [ States:  13                 Rules: 1                  ]
		//	In4/Pass:    [ Packets: 0                  Bytes: 0                  ]
		//	In4/Block:   [ Packets: 0                  Bytes: 0                  ]
		//	Out4/Pass:   [ Packets: 0                  Bytes: 0                  ]
		//	Out4/Block:  [ Packets: 0                  Bytes: 0                  ]
		//	In6/Pass:    [ Packets: 0                  Bytes: 0                  ]
		//	In6/Block:   [ Packets: 0                  Bytes: 0                  ]
		//	Out6/Pass:   [ Packets: 0                  Bytes: 0                  ]
		//	Out6/Block:  [ Packets: 0                  Bytes: 0                  ]
		foreach ($lines as $line) {
			if (preg_match('/^([\w\s\(\)]+)$/', $line, $match)) {
				if (count($i) > 0) {
					if (!isset($i['name'])) {
						$i['name']= '';
					}
					$ifs[]= $i;
				}

				$i= array('name' => $match[1]);
			} elseif (preg_match('/^\s*Cleared:\s*(.*)\s*$/', $line, $match)) {
				$i['cleared']= $match[1];
			} elseif (preg_match('/^\s*References:\s*\[\s*States:\s*(\d+)\s*Rules:\s*(\d+)\s*\]/', $line, $match)) {
				$i['states']= convertDecimal($match[1]);
				$i['rules']= convertDecimal($match[2]);
			} elseif (preg_match('/^\s*In4\/Pass:\s*\[\s*Packets:\s*(\d+)\s*Bytes:\s*(\d+)\s*\]/', $line, $match)) {
				$i['in4PassPackets']= convertDecimal($match[1]);
				$i['in4PassBytes']= convertBinary($match[2]);
			} elseif (preg_match('/^\s*In4\/Block:\s*\[\s*Packets:\s*(\d+)\s*Bytes:\s*(\d+)\s*\]/', $line, $match)) {
				$i['in4BlockPackets']= convertDecimal($match[1]);
				$i['in4BlockBytes']= convertBinary($match[2]);
			} elseif (preg_match('/^\s*Out4\/Pass:\s*\[\s*Packets:\s*(\d+)\s*Bytes:\s*(\d+)\s*\]/', $line, $match)) {
				$i['out4PassPackets']= convertDecimal($match[1]);
				$i['out4PassBytes']= convertBinary($match[2]);
			} elseif (preg_match('/^\s*Out4\/Block:\s*\[\s*Packets:\s*(\d+)\s*Bytes:\s*(\d+)\s*\]/', $line, $match)) {
				$i['out4BlockPackets']= convertDecimal($match[1]);
				$i['out4BlockBytes']= convertBinary($match[2]);
			} elseif (preg_match('/^\s*In6\/Pass:\s*\[\s*Packets:\s*(\d+)\s*Bytes:\s*(\d+)\s*\]/', $line, $match)) {
				$i['in6PassPackets']= convertDecimal($match[1]);
				$i['in6PassBytes']= convertBinary($match[2]);
			} elseif (preg_match('/^\s*In6\/Block:\s*\[\s*Packets:\s*(\d+)\s*Bytes:\s*(\d+)\s*\]/', $line, $match)) {
				$i['in6BlockPackets']= convertDecimal($match[1]);
				$i['in6BlockBytes']= convertBinary($match[2]);
			} elseif (preg_match('/^\s*Out6\/Pass:\s*\[\s*Packets:\s*(\d+)\s*Bytes:\s*(\d+)\s*\]/', $line, $match)) {
				$i['out6PassPackets']= convertDecimal($match[1]);
				$i['out6PassBytes']= convertBinary($match[2]);
			} elseif (preg_match('/^\s*Out6\/Block:\s*\[\s*Packets:\s*(\d+)\s*Bytes:\s*(\d+)\s*\]/', $line, $match)) {
				$i['out6BlockPackets']= convertDecimal($match[1]);
				$i['out6BlockBytes']= convertBinary($match[2]);
			} else {
				ctlr_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Failed parsing interface line: $line");
			}
		}

		if (count($i) > 0) {
			$ifs[]= $i;
		}

		return $ifs;
	}

	/**
	 * Gets pf rules info.
	 * 
	 * @attention Do not use the following line: return $this->RunPfInfoCmd('/sbin/pfctl -s rules -vv');
	 * See GetPfInfo() for the details of this bug.
	 * 
	 * @return mixed Parsed output of the command on success, FALSE on fail.
	 */
	function GetPfRulesInfo()
	{
		exec('/sbin/pfctl -s rules -vv', $output, $retval);
		if ($retval === 0) {
			return Output(json_encode($this->parsePfRulesInfo($output)));
		}
		return FALSE;
	}

	function parsePfRulesInfo($lines)
	{
		$rules= array();
		$r= array();

		//@0 match in all scrub (no-df)
		//  [ Evaluations: 1558      Packets: 16048     Bytes: 7312376     States: 2     ]
		//  [ Inserted: uid 0 pid 7529 State Creations: 0     ]
		foreach ($lines as $line) {
			if (preg_match('/^@(\d+)\s+(.*)$/', $line, $match)) {
				if (count($r) > 0) {
					if (!isset($r['number'])) {
						$r['number']= '';
					}
					$rules[]= $r;
				}

				$r= array(
					'number' => $match[1],
					'rule' => $match[2],
					);
			} elseif (preg_match('/^\s*\[\s*Evaluations:\s*(\d+)\s*Packets:\s*(\d+)\s*Bytes:\s*(\d+)\s*States:\s*(\d+)\s*\]/', $line, $match)) {
				$r['evaluations']= convertDecimal($match[1]);
				$r['packets']= convertDecimal($match[2]);
				$r['bytes']= convertBinary($match[3]);
				$r['states']= convertDecimal($match[4]);
			} elseif (preg_match('/^\s*\[\s*Inserted:\s*(.*)\s*State Creations:\s*(\d+)\s*\]/', $line, $match)) {
				$r['inserted']= $match[1];
				$r['stateCreations']= convertDecimal($match[2]);
			} else {
				ctlr_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Failed parsing rule line: $line");
			}
		}

		if (count($r) > 0) {
			$rules[]= $r;
		}

		return $rules;
	}

	/**
	 * Reads, parses, and validates the rules in the given file.
	 *
	 * @param string $file Rules file.
	 * @param bool $tmp Whether the given rule file is a temporary uploaded file or not.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GetPfRules($file, $tmp= FALSE, $force= FALSE)
	{
		global $PF_CONFIG_PATH, $TMP_PATH, $TEST_DIR_PATH;

		if ($file !== '/etc/pf.conf') {
			if (!$this->ValidateFilename($file)) {
				return FALSE;
			}
			if ($tmp == FALSE) {
				$file= "$PF_CONFIG_PATH/$file";
			} else {
				$file= "$TMP_PATH/$file";
			}
		}

		$ruleStr= $this->GetFile("$TEST_DIR_PATH$file");

		if ($ruleStr !== FALSE) {
			/// @todo Check if we need to unlink tmp file
			//if ($tmp !== FALSE) {
			//	unlink($file);
			//}

			$ruleSet= new RuleSet();
			$retval= $ruleSet->parse($ruleStr, $force);

			// Output ruleset, success or fail
			Output(json_encode($ruleSet));
		} else {
			$retval= FALSE;
		}

		return $retval;
	}

	/**
	 * Returns the file list under $PF_CONFIG_PATH.
	 * 
	 * @todo Should we return success or fail status, instead of TRUE?
	 *
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GetPfRuleFiles()
	{
		global $PF_CONFIG_PATH, $TEST_DIR_PATH;

		return Output($this->GetFiles("$TEST_DIR_PATH$PF_CONFIG_PATH"));
	}
	
	/**
	 * Deletes the given file under $PF_CONFIG_PATH.
	 * 
	 * Makes sure the file name is valid.
	 * Deletes only files under $PF_CONFIG_PATH. ValidateFilename() strips other file paths.
	 *
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DeletePfRuleFile($file)
	{
		global $PF_CONFIG_PATH, $TEST_DIR_PATH;

		$result= $this->ValidateFilename($file);

		if ($result) {
			$result= $this->DeleteFile("$TEST_DIR_PATH$PF_CONFIG_PATH/$file");
		}

		return $result;
	}
	
	/**
	 * Reads, parses, and validates the rules in the given file.
	 * 
	 * @attention We never run pfctl if the rules fail validation. Hence $force can only
	 * force loading the rules, not running pfctl.
	 *
	 * @param string $json JSON encoded rules array.
	 * @param string $file File name to save to.
	 * @param bool $load Whether to load the rules using pfctl after saving.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function InstallPfRules($json, $file= NULL, $load= TRUE, $force= FALSE)
	{
		global $PF_CONFIG_PATH, $INSTALL_USER, $TEST_DIR_PATH;

		if ($file == NULL) {
			$file= '/etc/pf.conf';
		} else {
			if (!$this->ValidateFilename($file)) {
				return FALSE;
			}
			$file= "$PF_CONFIG_PATH/$file";
		}
				
		/// @todo Check if $rulesArray is in correct format
		$rulesArray= json_decode($json, TRUE);

		$ruleSet= new RuleSet();
		$loadResult= $ruleSet->load($rulesArray, $force);

		if (!$loadResult && !$force) {
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rules with errors');
			return FALSE;
		}

		$rules= $ruleSet->generate();

		$output= array();
		$return= TRUE;
		
		$tmpFile= tempnam("$TEST_DIR_PATH/tmp", 'pf.conf.');
		if ($this->PutFile($tmpFile, $rules) !== FALSE) {
			$SUFFIX_OPT= '-B';
			if (posix_uname()['sysname'] === 'Linux') {
				$SUFFIX_OPT= '-S';
			}

			exec("/usr/bin/install -o $INSTALL_USER -m 0600 -D -b $SUFFIX_OPT '.orig' '$tmpFile' $TEST_DIR_PATH$file 2>&1", $output, $retval);
			if ($retval === 0) {
				if ($load === TRUE) {
					if ($loadResult) {
						$cmd= "/sbin/pfctl -f $TEST_DIR_PATH$file 2>&1";

						if (!$this->RunPfctlCmd($cmd, $output, $retval)) {
							Error(_('Failed loading pf rules') . ": $file");
							ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed loading pf rules: $file");
							$return= FALSE;
						}

						if ($retval !== 0) {
							Error(_('Cannot load pf rules') . "\n" . implode("\n", $output));
							ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot load pf rules');
							$return= FALSE;
						}
					} else {
						// Install button on the View is disabled if the ruleset has errors, so we should never reach here
						// But this method can be called on the command line too, that's why we check $loadResult
						Error(_('Will not load rules with errors') . ": $file");
						ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Will not load rules with errors: $file");
						$return= FALSE;
					}
				}
			} else {
				Error(_('Cannot install pf rule file') . ": $file\n" . implode("\n", $output));
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot install pf rule file: $file");
				$return= FALSE;
			}

			// Clean up after ourselves, even if there are errors
			exec("/bin/rm '$tmpFile' 2>&1", $output, $retval);
			if ($retval !== 0) {
				Error(_('Cannot remove tmp pf file') . ": $tmpFile\n" . implode("\n", $output));
				ctlr_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Cannot remove tmp pf file: $tmpFile");
				$return= FALSE;
			}
		} else {
			Error(_('Cannot write to tmp pf file') . ": $tmpFile\n" . implode("\n", $output));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot write to tmp pf file: $tmpFile");
			$return= FALSE;
		}
		
		return $return;
	}

	/**
	 * Validates the given file name.
	 * 
	 * Strips the file path, because we work with files under $PF_CONFIG_PATH only.
	 *
	 * @param string $file File name to validate [out].
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ValidateFilename(&$file)
	{
		$file= basename($file);
		if (preg_match('/^[\w._\-]+$/', $file)) {
			return TRUE;
		}

		Error(_('Filename not accepted') . ": $file");
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Filename not accepted: $file");
		return FALSE;
	}

	/**
	 * Loads and generates the given JSON encoded rule array.
	 * 
	 * @param string $json JSON encoded rule array.
	 * @param int $ruleNumber Rule number.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GeneratePfRule($json, $ruleNumber, $force= FALSE)
	{
		$ruleDef= json_decode($json, TRUE);

		$cat= 'Model\\' . $ruleDef['cat'];
		$ruleObj= new $cat('');
		$retval= $ruleObj->load($ruleDef['rule'], $ruleNumber, $force);

		if ($retval || $force) {
			Output($ruleObj->generate());
		} else {
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rule with errors');
		}

		return $retval;
	}

	/**
	 * Loads and generates the given JSON encoded rules array.
	 * 
	 * @param string $json JSON encoded rules array.
	 * @param bool $lines Whether to print line numbers in front of each line.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GeneratePfRules($json, $lines= FALSE, $force= FALSE)
	{
		$rulesArray= json_decode($json, TRUE);

		$ruleSet= new RuleSet();
		$retval= $ruleSet->load($rulesArray, $force);

		if ($retval || $force) {
			Output($ruleSet->generate($lines));
		} else {
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rules with errors');
		}

		return $retval;
	}

	/**
	 * Tests the given JSON encoded rules array.
	 * 
	 * Note that testing involves running pfctl, so there is no $force param here,
	 * because we never run pfctl with rules failed validation.
	 * 
	 * @param string $json JSON encoded rules array.
	 * @return bool Test result, TRUE on success, FALSE on fail.
	 */
	function TestPfRules($json)
	{
		$rulesArray= json_decode($json, TRUE);

		$ruleSet= new RuleSet();
		if (!$ruleSet->load($rulesArray)) {
			Error(_('Will not test rules with errors'));
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not test rules with errors');
			return FALSE;
		}

		/// @attention pfctl reports line numbers, not rule numbers, so do not reduce multi-line rules into single-line
		$rulesStr= $ruleSet->generate();

		$cmd= "/bin/echo '$rulesStr' | /sbin/pfctl -nf - 2>&1";

		if (!$this->RunPfctlCmd($cmd, $output, $retval)) {
			Error(_('Failed testing pf rules'));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed testing pf rules');
			return FALSE;
		}

		if ($retval === 0) {
			return TRUE;
		}

		$rules= explode("\n", $rulesStr);

		foreach ($output as $o) {
			if (preg_match('/^([^:]+):(\d+):\s*(.*)$/', $o, $match)) {
				$src= $match[1];
				$line= $match[2];
				$err= $match[3];
				
				// Rule numbers are 0 based, hence decrement once
				$line--;
				
				if ($src == 'stdin') {
					$rule= $rules[$line];
					Error(_('Line') . " $line: $err:\n<pre>" . htmlentities($rule) . '</pre>');
				} else {
					// Rule numbers in include files need an extra decrement
					$line--;
					Error(_('Error in include file') . ": $src\n" . _('Line') . " $line: $err");
				}
			} else {
				Error($o);
			}
		}
		return FALSE;
	}

	/**
	 * Daemonizes to run the given pfctl command.
	 * 
	 * We create a sysv message queue before forking the child process. The parent process
	 * waits for a message from the child. The child process runs the pfctl command, packs
	 * its output and return value in an array, and returns it in a message.
	 * 
	 * The parent loops waiting for a message from the child. In the loop we use a sleep interval
	 * obtained by an equation involving $PfctlTimeout, instead of a constant like 0.1,
	 * so that if $PfctlTimeout is set to 0, the interval becomes 0 too.
	 * 
	 * However, note that disabling the sleep interval may fail pfctl calls, because the parent
	 * exits without waiting for a message from the child.
	 * 
	 * pfctl takes a long time to return in certain cases. The WUI should not wait for too long,
	 * but exit upon timeout. In fact, all such external calls should timeout, instead of
	 * waiting indefinitely.
	 * 
	 * @bug pfctl gets stuck, or takes a long time to return on some errors.
	 * 
	 * Example 1: A macro using an unknown interface: int_if = "a1",
	 * pfctl tries to look up for its IP address, which takes a long time before failing with:
	 * > no IP address found for a1,
	 * > could not parse host specification
	 * 
	 * Example 2: A table with an entry (e.g. "test") for which no DNS record can be found,
	 * pfctl waits for name service lookup, which takes too long:
	 * > no IP address found for test,
	 * > could not parse host specification
	 * Therefore, we need to use a function which returns upon timeout, hence this method.
	 * 
	 * @param string $cmd pfctl command to run.
	 * @param array $output Output of pfctl.
	 * @param int $retval Return value of pfctl.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function RunPfctlCmd($cmd, &$output, &$retval)
	{
		global $PfctlTimeout;

		$retval= 0;
		$output= array();

		/// @todo Check why using 0 as mqid eventually (30-50 accesses later) fails creating or attaching to the queue.
		$mqid= 1;

		// Create or attach to the queue before forking
		$queue= msg_get_queue($mqid);
		
		if (!msg_queue_exists($mqid)) {
			Error(_('Failed creating or attaching to message queue'));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed creating or attaching to message queue');
			return FALSE;
		}
		
		$sendtype= 1;

		$pid= pcntl_fork();

		if ($pid == -1) {
			Error(_('Cannot fork pfctl process'));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot fork pfctl process');
		} elseif ($pid) {
			// This is the parent!

			$return= FALSE;

			// Parent should wait for output for $PfctlTimeout seconds
			// Wait count starts from 1 due to do..while loop
			$count= 1;

			// We use this $interval var instead of a constant like .1, because
			// if $PfctlTimeout is set to 0, $interval becomes 0 too, effectively disabling sleep
			// Add 1 to prevent division by zero ($PfctlTimeout cannot be set to -1 on the WUI)
			$interval= $PfctlTimeout/($PfctlTimeout + 1)/10;

			do {
				exec("/bin/sleep $interval");
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Receive message wait count: $count, sleep interval: $interval");

				/// @attention Do not wait for a message, loop instead: MSG_IPC_NOWAIT
				$received= msg_receive($queue, 0, $recvtype, 10000, $msg, TRUE, MSG_NOERROR|MSG_IPC_NOWAIT, $error);

				if ($received && $sendtype == $recvtype) {
					if (is_array($msg) && array_key_exists('retval', $msg) && array_key_exists('output', $msg)) {
						$retval= $msg['retval'];
						$output= $msg['output'];

						ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Received pfctl output: ' . print_r($msg, TRUE));

						$return= TRUE;
						break;
					} else {
						Error(_('Output not in correct format') . ': ' . print_r($msg, TRUE));
						ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Output not in correct format: ' . print_r($msg, TRUE));
						break;
					}
				} else {
					ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Failed receiving pfctl output: ' . posix_strerror($error));
				}

			} while ($count++ < $PfctlTimeout * 10);

			if (!$return) {
				Error(_('Timed out running pfctl command'));
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Timed out running pfctl command');
			}

			// Parent removes the queue
			if (!msg_remove_queue($queue)) {
				Error(_('Failed removing message queue'));
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed removing message queue');
			}

			/// @attention Make sure the child is terminated, otherwise the parent gets stuck too.
			if (posix_getpgid($pid)) {
				exec("/bin/kill -KILL $pid");
			}

			// Parent survives
			return $return;
		} else {
			// This is the child!

			// Child should run the command and send the result in a message
			ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Running pfctl command');
			exec($cmd, $output, $retval);

			$msg= array(
				'retval' => $retval,
				'output' => $output
				);

			if (!msg_send($queue, $sendtype, $msg, TRUE, TRUE, $error)) {
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed sending pfctl output: ' . print_r($msg, TRUE) . ', error: ' . posix_strerror($error));
			} else {
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Sent pfctl output: ' . print_r($msg, TRUE));
			}

			// Child exits
			exit;
		}
	}

	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;
	
		// Sep 28 03:50:22.683986 rule 39/(match) pass in on em1: 10.0.0.11.40284 > 10.0.0.13.443: S 3537547021:3537547021(0) win 5840 <mss 1460,sackOK,timestamp 6440374[|tcp]> (DF)
		// Sep 27 14:32:21.715363 rule 37/(match) pass in on em1: 10.0.0.11.40546 > 10.0.0.13.22: S 2853072521:2853072521(0) win 5840 <mss 1460,sackOK,timestamp 3609332[|tcp]> (DF)
		// Sep 28 02:57:36.888668 rule 47/(match) pass out on em1: 10.0.0.13.16227 > 194.27.110.130.123: v4 client strat 0 poll 0 prec 0 [tos 0x10]
		// 
		// Sep 26 14:26:28.605638 rule 11/(match) block in on em1: 10.0.0.11.59299 > 239.255.255.250.1900: udp 132 (DF) [ttl 1]
		// 
		// Sep 28 03:50:20.124951 rule 11/(match) block in on em1: 10.0.0.11 > 224.0.0.22: igmp-2 [v2] (DF) [tos 0xc0] [ttl 1]
		// 
		// Sep 28 03:50:16.900084 rule 11/(match) block in on em1: 10.0.0.11.5353 > 224.0.0.251.5353: 0*- [0q] 7/0/0[|domain] (DF)
		// Sep 28 03:30:02.676705 rule 47/(match) pass out on em1: 10.0.0.13.41578 > 10.0.0.2.53: 61952+% [1au][|domain]
		// 
		// Sep 28 03:50:03.858844 rule 11/(match) block in on em0: 10.0.0.2.67 > 255.255.255.255.68: xid:0xf2ed6079 flags:0x8000 [|bootp] (DF)
		// Sep 28 03:50:03.858828 rule 11/(match) block in on em0: 0.0.0.0.68 > 255.255.255.255.67: xid:0xf2ed6079 [|bootp] [tos 0x10]
		// 
		// Sep 28 03:50:03.749086 rule 11/(match) block in on em1: fe80::21f:e2ff:fe61:969a > ff02::2: icmp6: router solicitation
		// Sep 28 03:49:54.705294 rule 11/(match) block in on em0: :: > ff02::1:ff61:969a: [|icmp6]
		// Sep 26 17:46:27.631418 rule 11/(match) block in on em0: :: > ff02::16: HBH icmp6: type-#143 [hlim 1]

		// Oct 18 05:16:52.230791 rule 11/(match) block in on em1: fe80::a00:27ff:fe4f:2af9.546 > ff02::1:2.547:dhcp6 solicit [hlim 1]

		$re_datetime= '(\w+\s+\d+)\s+(\d+:\d+:\d+)\.\d+';
		$re_rule= 'rule\s+([\w\.]+)\/\S+';
		$re_action= '(block|pass|match)';
		$re_direction= '(in|out)';
		$re_if= 'on\s+(\w+\d+):';
		$re_srcipport= "($Re_Ip|[\w:]+)(.(\d+)|)";
		$re_dstipport= "($Re_Ip|[\w:]+)(.(\d+)|)";
		$re_rest= '(.*)';
		
		$re= "/^$re_datetime\s+$re_rule\s+$re_action\s+$re_direction\s+$re_if\s+$re_srcipport\s+>\s+$re_dstipport:\s*$re_rest$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['Rule']= $match[3];
			$cols['Act']= $match[4];
			$cols['Dir']= $match[5];
			$cols['If']= $match[6];
			$cols['SrcIP']= $match[7];
			$cols['SPort']= $match[9];
			$cols['DstIP']= $match[10];
			$cols['DPort']= $match[12];
			$rest= $match[13];

			$re= '/(tcp|udp|domain|icmp6|icmp\b|igmp\-2|igmp\b|bootp|\back\b|v4|dhcp\d*)/';
			if (preg_match($re, $rest, $match)) {
				$cols['Type']= $match[1];
			}
			elseif (preg_match('/\s+win\s+\d+/', $rest)) {
				$cols['Type']= 'tcp';
			}
			else {
				$cols['Type']= 'other';
			}
			$cols['Log']= $rest;
			return TRUE;
		}
		return FALSE;
	}
	
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
				$re.= sprintf('%02d', $date['Day']);
			}
		}
		return $re;
	}

	function _getFileLineCount($file, $re= '', $needle= '', $month='', $day='', $hour='', $minute='')
	{
		global $TCPDUMP;
		
		if (!$this->ValidateFile($file)) {
			return FALSE;
		}

		$cmd= "$TCPDUMP $file";

		if ($month != '' || $day != '' || $hour != '' || $minute != '') {
			$cmd.= ' | /usr/bin/grep -a -E "' . $this->formatDateHourRegexp($month, $day, $hour, $minute) . '"';
		}

		if ($needle != '') {
			$needle= escapeshellarg($needle);
			$cmd.= " | /usr/bin/grep -a -E $needle";
		}

		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}

		$cmd.= ' | /usr/bin/wc -l';
		
		// OpenBSD wc returns with leading blanks
		return trim($this->RunShellCommand($cmd));
	}
	
	function GetLogs($file, $end, $count, $re= '', $needle= '', $month='', $day='', $hour='', $minute='')
	{
		global $TCPDUMP;

		if (!$this->ValidateFile($file)) {
			return FALSE;
		}

		$cmd= "$TCPDUMP $file";

		if ($month != '' || $day != '' || $hour != '' || $minute != '') {
			$cmd.= ' | /usr/bin/grep -a -E "' . $this->formatDateHourRegexp($month, $day, $hour, $minute) . '"';
		}

		if ($needle != '') {
			$needle= escapeshellarg($needle);
			$cmd.= " | /usr/bin/grep -a -E $needle";
		}

		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}

		$cmd.= " | /usr/bin/head -$end | /usr/bin/tail -$count";
		
		$lines= explode("\n", $this->RunShellCommand($cmd));
		
		$logs= array();
		foreach ($lines as $line) {
			unset($Cols);
			if ($this->ParseLogLine($line, $Cols)) {
				$logs[]= $Cols;
			} else {
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed parsing log line: ' . $line);
			}
		}
		return Output(json_encode($logs));
	}

	function formatDateHourRegexp($month, $day, $hour, $minute)
	{
		return $this->formatDateHourRegexpDayLeadingZero($month, $day, $hour, $minute);
	}

	function _getLiveLogs($file, $count, $re= '', $needle= '', $reportFileExistResult= TRUE)
	{
		global $TCPDUMP;
		
		if (!$this->ValidateFile($file, $reportFileExistResult)) {
			return FALSE;
		}

		$cmd= "$TCPDUMP $file";
		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}

		$cmd.= " | /usr/bin/tail -$count";
		
		$lines= explode("\n", $this->RunShellCommand($cmd));
		
		$logs= array();
		foreach ($lines as $line) {
			if ($this->ParseLogLine($line, $Cols)) {
				$logs[]= $Cols;
			}
		}
		return $logs;
	}

	/**
	 * Gets pf state count.
	 *
	 * @param string $re Regexp to get count of a restricted result set
	 * @return int Line count
	 */
	function GetStateCount($re= '')
	{
		return Output($this->_getStateCount($re));
	}

	function _getStateCount($re= '')
	{
		/// @todo pfctl may take too long to return
//		if ($re == '') {
//			$ce= exec('/sbin/pfctl -s info | grep "current entries"', $output, $retval);
//			if ($retval === 0) {
//				if (preg_match('/current entries\s+(\d+)/', $ce, $match)) {
//					return $match[1];
//				}
//			}
//		} else {
			// Skip header lines by grepping for In or Out
			// Empty $re is not an issue for grep, greps all
			$cmd= "$this->pftopCmd | /usr/bin/egrep -a 'In|Out'";
			if ($re !== '') {
				$re= escapeshellarg($re);
				$cmd.= " | /usr/bin/grep -a -E $re";
			}
			$cmd.= ' | /usr/bin/wc -l';
			// OpenBSD wc returns with leading blanks
			return trim($this->RunShellCommand($cmd));
//		}
	}

	/**
	 * Gets the pftop output.
	 *
	 * @param int $end Head option, start line
	 * @param int $count Tail option, page line count
	 * @param string $re Regexp to restrict the result set
	 * @return serialized Lines
	 */
	function GetStateList($end, $count, $re= '')
	{
		// Skip header lines by grepping for In or Out
		// Empty $re is not an issue for grep, greps all
		$re= escapeshellarg($re);

		$cmd= "$this->pftopCmd | /usr/bin/egrep -a 'In|Out' | /usr/bin/grep -a -E $re | /usr/bin/head -$end | /usr/bin/tail -$count";
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return Output(json_encode($this->ParsePftop($output)));
		}

		Error(implode("\n", $output));
		ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Failed running pftop');
		return FALSE;
	}

	/**
	 * Parses pftop output.
	 *
	 * @param array $pftopout pftop output
	 * @return array States
	 */
	function ParsePftop($pftopout)
	{
		$states= array();
		foreach ($pftopout as $line) {
			if (preg_match($this->re_Pftop, $line, $match)) {
				$states[]= array(
					$match[1],
					$match[2],
					$match[3],
					$match[4],
					$match[5],
					$match[6],
					$match[7],
					$match[8],
					$match[9],
					);
			}
		}
		return $states;
	}

	/**
	 * Sets afterhours interface name.
	 */
	function SetAfterhoursIf($if)
	{
		return $this->SetNVP($this->AfterHoursFile, 'int_if', '"'.$if.'"');
	}

	/**
	 * Applies pf rules.
	 */
	function ApplyPfRules()
	{
		return $this->RunPfInfoCmd("/sbin/pfctl -f $this->PfRulesFile");
	}

	/**
	 * Generates host graphs.
	 */
	function GetHostGraphsList($ip)
	{
		global $Re_Ip;

		$iplist= array();
		
		$dh= opendir($this->PNRG_IMG_DIR);
		while (FALSE !== ($filename= readdir($dh))) {
			if (preg_match("/^($Re_Ip)\.cgi$/", $filename, $match)) {
				$iplist[]= $match[1];
			}
		}
		closedir($dh);
		
		sort($iplist);
		
		/// @attention /dev/null is necessary, otherwise rrdcgi does not exit
		if (in_array($ip, $iplist)) {
			$this->RunShellCommand($this->PNRG_IMG_DIR.$ip.'.cgi < /dev/null');
		}
		else if (count($iplist) > 0) {
			$this->RunShellCommand($this->PNRG_IMG_DIR.$iplist[0].'.cgi < /dev/null');
		}
		
		return Output(implode("\n", $iplist));
	}

	/**
	 * Enables AfterHours.
	 */
	function EnableAfterHours()
	{
		return $this->RunPfInfoCmd("/sbin/pfctl -a AfterHours -f $this->AfterHoursFile");
	}

	/**
	 * Disables AfterHours.
	 */
	function DisableAfterHours()
	{
		$this->RunPfctlCmd('/sbin/pfctl -a AfterHours -Fr 2>&1', $output, $retval);
		$strOutput= implode("\n", $output);
		if (($retval != 0) || ($strOutput != 'rules cleared')) {
			Error($strOutput);
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed disabling AfterHours rules: $strOutput");
		}
	}

	/**
	 * Gets pf AfterHour anchor rules.
	 */
	function GetAfterHoursPfRules()
	{
		/// @attention Do not use RunPfInfoCmd() here, sysv msg queue issue
		//return $this->RunPfInfoCmd('/sbin/pfctl -a AfterHours -sr');
		return Output($this->RunShellCommand('/sbin/pfctl -a AfterHours -sr'));
	}

	/**
	 * Gets pf AfterHour definitions from cron file.
	 */
	function GetAfterHours()
	{
		if (($contents= $this->GetCrontab()) !== FALSE) {
			if (preg_match("/$this->re_BusinessDays/ms", $contents, $match)) {
				$businessdaysdisabled= FALSE;
			}
			else if (preg_match("/$this->re_DisabledBusinessDays/ms", $contents, $match)) {
				$businessdaysdisabled= TRUE;
			}
			$endmin= sprintf('%02d', $match[1]);
			$endhour= sprintf('%02d', $match[2]);
			$businessdays= explode(',', $match[3]);

			if (preg_match("/$this->re_Holidays/ms", $contents, $match)) {
				$holidaysdisabled= FALSE;
			}
			else if (preg_match("/$this->re_DisabledHolidays/ms", $contents, $match)) {
				$holidaysdisabled= TRUE;
			}
			$holidays= explode(',', $match[1]);

			if (preg_match("/$this->re_Flush/ms", $contents, $match)) {
				$flushdisabled= FALSE;
			}
			else if (preg_match("/$this->re_DisabledFlush/ms", $contents, $match)) {
				$flushdisabled= TRUE;
			}
			$startmin= sprintf('%02d', $match[1]);
			$starthour= sprintf('%02d', $match[2]);
			$flushdays= explode(',', $match[3]);
			
			return Output(json_encode(array($businessdaysdisabled, $holidaysdisabled, $flushdisabled,
				$startmin, $starthour, $endmin, $endhour, $businessdays, $holidays, $flushdays)));
		}
		return FALSE;
	}

	/**
	 * Gets active crontab.
	 *
	 * Do not read root's crontab file directly, it has header comment lines.
	 */
	function GetCrontab()
	{
		exec('/usr/bin/crontab -l', $output, $retval);
		if ($retval === 0) {
			return implode("\n", $output);
		}
		Error(implode("\n", $output));
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed listing crontab');
		return FALSE;
	}

	function DisableAfterHoursBusinessDays()
	{
		if ($this->CommentAfterHoursLine($this->re_BusinessDays)) {
			/// Flush line should be in sync with business hours line.
			return $this->CommentAfterHoursLine($this->re_Flush);
		}
		return FALSE;
	}

	function EnableAfterHoursBusinessDays()
	{
		if ($this->UncommentAfterHoursLine($this->re_DisabledBusinessDays)) {
			/// Flush line should be in sync with business hours line.
			return $this->UncommentAfterHoursLine($this->re_DisabledFlush);
		}
		return FALSE;
	}

	function DisableAfterHoursHolidays()
	{
		return $this->CommentAfterHoursLine($this->re_Holidays);
	}

	function EnableAfterHoursHolidays()
	{
		return $this->UncommentAfterHoursLine($this->re_DisabledHolidays);
	}

	/**
	 * Sets after hours definitions in cron file.
	 *
	 * @param string $sh Start hour.
	 * @param string $sm Start minute.
	 * @param string $eh End hour.
	 * @param string $em End minute.
	 * @param string $businessdayslist Comma separated business days.
	 * @param string $holidayslist Comma separated holidays.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ApplyAfterHours($sh, $sm, $eh, $em, $businessdayslist, $holidayslist)
	{
		if (($contents= $this->GetCrontab()) !== FALSE) {
			// Remove leading zeros
			$starthour= $sh + 0;
			$startmin= $sm + 0;
			$endhour= $eh + 0;
			$endmin= $em + 0;

			$re_replace= "$endmin	$endhour	*	*	$businessdayslist	/sbin/pfctl -a AfterHours -f $this->AfterHoursFile";
			if ($newcontents= preg_replace("/$this->re_BusinessDays/ms", $re_replace, $contents)) {
				$contents= $newcontents;
			}

			$re_replace= "*	*	*	*	$holidayslist	/sbin/pfctl -a AfterHours -f $this->AfterHoursFile";
			if ($newcontents= preg_replace("/$this->re_Holidays/ms", $re_replace, $contents)) {
				$contents= $newcontents;
			}

			$re_replace= "$startmin	$starthour	*	*	$businessdayslist	/sbin/pfctl -a AfterHours -Fr";
			if ($newcontents= preg_replace("/$this->re_Flush/ms", $re_replace, $contents)) {
				$contents= $newcontents;
			}

			if ($this->CheckAfterHoursOverlap($contents)) {
				return $this->InstallNewCrontab($contents);
			}
		}
		return FALSE;
	}

	/**
	 * Comments the line that matches the give regexp.
	 *
	 * @param string $re RE of the line to comment.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function CommentAfterHoursLine($re)
	{
		if (($contents= $this->GetCrontab()) !== FALSE) {
			if (preg_match("/$re/ms", $contents, $match)) {
				if ($contents= preg_replace("/$re/ms", '#'.$match[0], $contents)) {
					return $this->InstallNewCrontab($contents);
				}
			}
		}
		return FALSE;
	}

	/**
	 * Uncomments the line that matches the give regexp.
	 *
	 * @param string $re RE of the line to uncomment.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function UncommentAfterHoursLine($re)
	{
		if (($contents= $this->GetCrontab()) !== FALSE) {
			if (preg_match("/$re/ms", $contents, $match)) {
				if ($contents= preg_replace("/$re/ms", substr($match[0], 1), $contents)) {
					if ($this->CheckAfterHoursOverlap($contents)) {
						return $this->InstallNewCrontab($contents);
					}
				}
			}
		}
		return FALSE;
	}

	/**
	 * Installs given crontab contents.
	 *
	 * Crontab contents should be installed by running crontab,
	 * otherwise they are not activated just by directly editing crontab files.
	 *
	 * @param string $contents Crontab contents.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function InstallNewCrontab($contents)
	{
		// PutFile() does not create the file if it does not exist already
		exec("/usr/bin/touch $this->tmpCrontab");
		// crontab complains without newline at the end of last line
		if ($this->PutFile($this->tmpCrontab, $contents.PHP_EOL)) {
			exec("/usr/bin/crontab $this->tmpCrontab", $output, $retval);
			if ($retval === 0) {
				return TRUE;
			}
			Error(implode("\n", $output));
		}
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed installing new crontab');
		return FALSE;
	}

	/**
	 * Checks if there is any overlap between business days and holidays in
	 * the given file contents.
	 *
	 * @param string $contents Cron file contents to check.
	 * @return bool TRUE if no overlap.
	 */
	function CheckAfterHoursOverlap($contents)
	{
		$businessdays= array();
		if (preg_match("/$this->re_BusinessDays/ms", $contents, $match)) {
			$businessdays= explode(',', $match[3]);
		}
		$holidays= array();
		if (preg_match("/$this->re_Holidays/ms", $contents, $match)) {
			$holidays= explode(',', $match[1]);
		}

		if (count(array_intersect($businessdays, $holidays)) > 0) {
			Error(_('Business days and holidays cannot overlap.'));
			return FALSE;
		}
		return TRUE;
	}
}
?>
