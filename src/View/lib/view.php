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

/** @file
 * View base class.
 */

require_once $VIEW_PATH.'/lib/phpseclib/Net/SSH2.php';

class View
{
	public $Model= 'model';
	public $Module= 'model';
	public $Caption= 'Model';

	public $LogsHelpMsg= '';
	public $GraphHelpMsg= '';
	public $ConfHelpMsg= '';

	public $Layout= '';
	public $LogsPage= 'logs.php';
	public $StatsPage= 'stats.php';

	/**
	 * Configuration.
	 *
	 * If title field is missing, index string is used as title.
	 *
	 * @param	title	Configuration title displayed.
	 * @param	info	Info text displayed in help box on the right.
	 */
	public $Config= array();

	private $prioClasses= array(
		'EMERGENCY' => 'red',
		'emergency' => 'red',
		'ALERT' => 'red',
		'alert' => 'red',
		'CRITICAL' => 'red',
		'critical' => 'red',
		'ERROR' => 'red',
		'error' => 'red',
		'WARNING' => 'yellow',
		'warning' => 'yellow',
		'warn' => 'yellow',
		);
	
	/**
	 * Calls the controller.
	 *
	 * Both command and arguments are passed as variable arguments.
	 *
	 * @param array $output Output of the command.
	 * @param mixed Variable_Args Elements of the command line in variable arguments.
	 * @return bool Return value of shell command (adjusted for PHP).
	 */
	function Controller(&$output)
	{
		global $SRC_ROOT, $UseSSH;

		$return= FALSE;
		try {
			$ctlr= $SRC_ROOT . '/Controller/ctlr.php';

			$argv= func_get_args();
			// Arg 0 is $output, skip it
			$argv= array_slice($argv, 1);
			// Prepend locale and model
			$argv= array_merge(array($_SESSION['Locale'], $this->Model), $argv);

			$encoded_args= json_encode($argv, JSON_UNESCAPED_SLASHES);
			if ($encoded_args !== NULL) {
				// Init command output
				$outputArray= array();

				$executed= TRUE;
				if ($UseSSH) {
					// Subsequent calls use the encrypted password in the cookie, so we should decrypt it first
					$ciphertext_base64= $_COOKIE['passwd'];
					$ciphertext= base64_decode($ciphertext_base64);

					$iv_size= openssl_cipher_iv_length('AES-256-CBC');
					$iv= substr($ciphertext, 0, $iv_size);

					$ciphertext= substr($ciphertext, $iv_size);

					$passwd= openssl_decrypt($ciphertext, 'AES-256-CBC', $_SESSION['cryptKey'], OPENSSL_RAW_DATA, $iv);

					$ssh= new Net_SSH2('127.0.0.1');

					// Give more time to all requests, the default timeout is 10 seconds
					$ssh->setTimeout(30);

					if ($ssh->login($_SESSION['USER'], $passwd)) {
						// The login shells of admin and user users are set to sh.php, so we just pass down the args
						$outputArray[0]= $ssh->exec($encoded_args);
						if ($ssh->isTimeout()) {
							$msg= 'SSH exec timed out';
							wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($encoded_args)");
							PrintHelpWindow($msg, 'auto', 'ERROR');
							$executed= FALSE;
						}
					} else {
						$msg= 'SSH login failed';
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($encoded_args)");
						PrintHelpWindow($msg, 'auto', 'ERROR');
						$executed= FALSE;
					}
				} else {
					// Runs the command as the www user
					// Escape args to avoid shell expansion
					/// @bug http://bugs.php.net/bug.php?id=49847, fixed/closed in SVN on 141009
					exec("/usr/bin/doas $ctlr ".escapeshellarg($encoded_args), $outputArray);
				}
 
				if ($executed) {
					$output= array();
					$errorStr= '';
					$retval= 1;

					$decoded= json_decode($outputArray[0], TRUE);
					if ($decoded !== NULL && is_array($decoded)) {
						$output= explode("\n", $decoded[0]);
						$errorStr= $decoded[1];
						// FALSE returned by the Model function is json_encoded/decoded as string 'false'
						if ($decoded[0] == 'false') {
							$retval= 1;
						} else {
							$retval= $decoded[2];
						}
					} else {
						$msg= 'Failed decoding output: '.print_r($outputArray[0], TRUE);
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($encoded_args)");
						PrintHelpWindow($msg, 'auto', 'ERROR');
					}

					// Show errors, if any
					if ($errorStr !== '') {
						$error= explode("\n", $errorStr);

						wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: (" . implode(', ', $error) . "), ($encoded_args)");
						PrintHelpWindow(implode('<br>', $error), 'auto', 'ERROR');
					}

					// (exit status 0 in shell) == (TRUE in php)
					if ($retval === 0) {
						$return= TRUE;
					} else {
						wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: ($encoded_args)");
					}
				}
			} else {
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed encoding args: ' . print_r($argv, TRUE));
			}
		}
		catch (Exception $e) {
			echo 'Exception: '.__FILE__.' '.__FUNCTION__.' ('.__LINE__.'): '.$e->getMessage()."\n";
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Exception: '.$e->getMessage());
		}
		return $return;
	}

	/**
	 * Checks the given user:password pair by testing login.
	 * 
	 * @param string $user User name.
	 * @param string $passwd SHA encrypted password.
	 * @return bool TRUE if passwd matches, FALSE otherwise.
	 */
	function CheckAuthentication($user, $passwd)
	{
		$ssh = new Net_SSH2('127.0.0.1');
		if ($ssh->login($user, $passwd)) {
			$hostname= gethostname();

			$encoded_args= json_encode(array($_SESSION['Locale'], $this->Model, 'GetMyName'), JSON_UNESCAPED_SLASHES);
			$output= $ssh->exec($encoded_args);
			$decoded= json_decode($output, TRUE);
			if ($decoded !== NULL && is_array($decoded)) {
				$output= explode("\n", $decoded[0]);
			} else {
				$msg= 'Failed decoding output: '.print_r($output, TRUE);
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($encoded_args)");
				PrintHelpWindow($msg, 'auto', 'ERROR');
			}

			if ($hostname == $output[0]) {
				return TRUE;
			} else {
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "SSH test command failed: $hostname == $output[0]");
			}
		} else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Authentication failed'), 'auto', 'ERROR');
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Authentication failed');
		}
		return FALSE;
	}

	function CheckUserDbAuthentication($user, $passwd)
	{
		$ssh = new Net_SSH2('127.0.0.1');
		if ($ssh->login($user, $passwd)) {
			return TRUE;
		} else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Authentication failed'), 'auto', 'ERROR');
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Authentication failed');
		}
		return FALSE;
	}

	/**
	 * Stops and restarts module process(es).
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Restart()
	{
		if ($this->Stop()) {
			return $this->Start();
		}
		return FALSE;
	}

	/**
	 * Stops module process(es).
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Stop()
	{
		if ($this->Controller($output, 'Stop')) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Starts module process(es).
	 * 
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function Start()
	{
		if ($this->Controller($output, 'Start')) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Processes user posts for process start and stop.
	 *
	 * Used on info pages.
	 */
	function ProcessStartStopRequests()
	{
		$generate_status= 0;

		if (filter_has_var(INPUT_POST, 'Model')) {
			if (filter_input(INPUT_POST, 'Model') == $this->Model) {
				if (filter_has_var(INPUT_POST, 'Start')) {
					$this->Restart();
					$generate_status= 1;
				}
				else if (filter_has_var(INPUT_POST, 'Stop')) {
					$this->Stop();
					$generate_status= 1;
				}
			}
		}
		$this->Controller($Output, 'GetStatus');

		return $generate_status;
	}

	/**
	 * Displays module status, software version, Restart/Stop buttons, and process table.
	 *
	 * @param boolean $generate_status Force generate module status, do not use cached status, necessary after process start/stop
	 * @param boolean $printcount Whether to print number of running processes too
	 * @param boolean $showbuttons Show Start/Stop buttons
	 * @param boolean $printprocs Whether to print the process table
	 * @param boolean $showrestartbutton Show Restart button, for System Info page
	 */
	function PrintStatusForm($generate_status= 0, $printcount= FALSE, $showbuttons= TRUE, $printprocs= TRUE, $showrestartbutton= FALSE)
	{
		global $IMG_PATH, $ADMIN, $Status2Images, $StatusTitles;

		$this->Controller($output, 'GetModuleStatus', $generate_status);

		$status= json_decode($output[0], TRUE);

		$running= $status['Status'] == 'R';
		$imgfile= $Status2Images[$status['Status']];
		if ($running) {
			$name= 'Running';
			$info= _TITLE('is running');
			$confirm= _NOTICE('Are you sure you want to stop the <NAME>?');
			if ($showbuttons) {
				$button= 'Stop';
			}
		}
		else {
			$name= 'Stopped';
			$info= _TITLE('is not running');
			$confirm= _NOTICE('Are you sure you want to start the <NAME>?');
			if ($showbuttons) {
				$button= 'Start';
			}
		}

		$errorStatus= $status['ErrorStatus'];
		$errorImgfile= $Status2Images[$errorStatus];
		$errorInfo= $StatusTitles[$errorStatus];

		$confirm= preg_replace('/<NAME>/', $this->Caption, $confirm);
		?>
		<table id="status">
			<tr>
				<td class="image">
					<img src="<?php echo $IMG_PATH.$imgfile ?>" name="<?php echo $name ?>" alt="<?php echo $name ?>" border="0" title="<?php echo $name ?>">
				</td>
				<td class="image">
					<img src="<?php echo $IMG_PATH.$errorImgfile ?>" name="<?php echo $errorInfo ?>" alt="<?php echo $errorInfo ?>" border="0" title="<?php echo $errorInfo ?>">
				</td>
				<td>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<strong><?php echo $this->Caption . " $info " ?></strong>
						<?php
						/// Only admin can start/stop the processes
						if (in_array($_SESSION['USER'], $ADMIN)) {
							if (isset($button)) {
								?>
								<input type="submit" name="<?php echo $button ?>" value="<?php echo _($button) ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
								<?php
							}
							if ($showrestartbutton) {
								$restartConfirm= _NOTICE('Are you sure you want to restart the <NAME>?');
								$restartConfirm= preg_replace('/<NAME>/', $this->Caption, $restartConfirm);
								?>
								<input type="submit" name="Restart" value="<?php echo _CONTROL('Restart') ?>" onclick="return confirm('<?php echo $restartConfirm ?>')"/>
								<?php
							}
						}
						?>
						<input type="hidden" name="Model" value=<?php echo $this->Model ?> />
					</form>
				</td>
				<?php
				if ($this->Controller($output, 'GetVersion')) {
					?>
					<td class="version">
						<strong><?php echo _TITLE('Software Version') ?></strong>
						<?php
						foreach ($output as $line) {
							echo '<br />'.$line;
						}
						?>
					</td>
					<?php
				}
				?>
			</tr>
		</table>
		<?php
		if ($running && $printprocs) {
			if ($this->Controller($output, 'GetProcList')) {
				$this->PrintProcessTable(json_decode($output[0], TRUE), $printcount);
			}
		}
	}

	/**
	 * Gets and lists processes for daemons/services.
	 *
	 * Used on module status pages.
	 * Perl processes are hard to list.
	 * @warning Spaces in $output lines were replaced by |'s elsewhere.
	 *
	 * @param array $output ps output, modified by SelectProcesses().
	 * @param bool $printcount Whether to print process count at top.
	 */
	function PrintProcessTable($output, $printcount= FALSE)
	{
		$total= count($output);
		if ($total > 0) {
			if ($printcount) {
				echo _TITLE('Number of processes').': '.$total;
			}
			?>
			<table id="logline">
				<?php
				$this->PrintProcessTableHeader();
				$linenum= 0;
				foreach ($output as $cols) {
					$rowClass= ($linenum++ % 2 == 0) ? 'evenline' : 'oddline';
					$lastLine= $linenum == $total;
					?>
					<tr>
						<?php
						$totalCols= count($cols);
						$count= 1;
						foreach ($cols as $c) {
							$cellClass= $rowClass;

							if ($lastLine) {
								if ($count == 1) {
									$cellClass.= ' lastLineFirstCell';
								} else if ($count == $totalCols) {
									$cellClass.= ' lastLineLastCell';
								}
							}

							if (in_array($count, array(8, 11, 12, 13))) {
								// Left align stat, user, group, and command columns
								$cellClass.= ' left';
							}
							?>
							<td class="<?php echo $cellClass ?>">
								<?php echo $c ?>
							</td>
							<?php
							$count++;
						}
						?>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}
	}

	/**
	 * Prints headers for processes table.
	 *
	 * PID STAT  %CPU      TIME %MEM   RSS   VSZ STARTED  PRI  NI USER     COMMAND
	 */
	function PrintProcessTableHeader()
	{
		?>
		<tr id="logline">
			<th><?php echo _('PID') ?></th>
			<th><?php echo _TITLE2('STARTED') ?></th>
			<th><?php echo _('%CPU') ?></th>
			<th><?php echo _TITLE2('TIME') ?></th>
			<th><?php echo _TITLE2('%MEM') ?></th>
			<th><?php echo _('RSS') ?></th>
			<th><?php echo _('VSZ') ?></th>
			<th><?php echo _('STAT') ?></th>
			<th><?php echo _TITLE2('PRI') ?></th>
			<th><?php echo _('NI') ?></th>
			<th><?php echo _TITLE2('USER') ?></th>
			<th><?php echo _TITLE2('GROUP') ?></th>
			<th><?php echo _TITLE2('COMMAND') ?></th>
		</tr>
		<?php
	}

	/**
	 * Uploads selected log file.
	 */
	function UploadLogFile()
	{
		/// Do not send anything yet if download requested (header is modified below).
		if (filter_has_var(INPUT_POST, 'Download') && filter_has_var(INPUT_POST, 'LogFile')) {
			if ($this->Controller($output, 'PrepareFileForDownload', filter_input(INPUT_POST, 'LogFile'))) {
				$tmpfile= $output[0];
				/// @warning Clear the output buffer first
				ob_clean();

				if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
					// Without this header, IE cannot even download the file
					header("Pragma: public");
				}

				if (preg_match('/.*\.gz$/', $tmpfile)) {
					 header('Content-Type: application/x-gzip');
				}
				else if (preg_match('/.*\.pdf$/', $tmpfile)) {
					 header('Content-Type: application/pdf');
				}
				else {
					 header('Content-Type: text/plain');
				}
				header('Content-Disposition: attachment; filename="'.basename($tmpfile).'"');
				header('Content-Length: '.filesize($tmpfile));
				readfile($tmpfile);
				flush();
				/// @warning Do not send anything else, otherwise it is appended to the file
				exit;
			}
		}
	}

	/**
	 * Prints general text statistics.
	 *
	 * @param string $file Log file if different from the one in $LogConf
	 */
	function PrintStats($file= '')
	{
		if ($file !== FALSE && $this->Controller($output, 'GetProcStatLines', $file)) {
			$stats= json_decode($output[0], TRUE);
			if ($stats !== NULL && is_array($stats)) {
				PrintNVPs($stats, _STATS('General Statistics'), 50, FALSE, FALSE);
			}
		}
	}

	/**
	 * Checks if the date array contains a range, meaning an empty string.
	 *
	 * Assumes standard syslog date format for the output string.
	 *
	 * @param array $date Datetime struct.
	 * @return bool TRUE if a range.
	 */
	function IsDateRange($date)
	{
		return ($date['Month'] == '') || ($date['Day'] == '');
	}

	/**
	 * Generic date array to string formatter.
	 *
	 * Assumes standard syslog date format for the output string.
	 * The datetimes in log lines may be different for each module.
	 * Does the opposite of FormatDateArray().
	 *
	 * @param array $date Datetime struct.
	 * @return string Date string.
	 */
	function FormatDate($date)
	{
		global $MonthNames;

		return $MonthNames[$date['Month']].' '.sprintf('% 2d', $date['Day']);
	}

	/**
	 * Date string to array formatter.
	 *
	 * Assumes standard syslog date format for the output string.
	 * Does the opposite of FormatDate().
	 *
	 * @todo Check if empty Year is ok around new year's day
	 *
	 * @param string $datestr Date as string.
	 * @param array $date Datetime output.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function FormatDateArray($datestr, &$date)
	{
		global $MonthNumbers;
		
		if (preg_match('/(\d+)\s+(\d+)/', $datestr, $match)) {
			$date['Month']= $match[1];
			$date['Day']= sprintf('%02d', $match[2]);
			return TRUE;
		}
		else if (preg_match('/(\w+)\s+(\d+)/', $datestr, $match)) {
			if (array_key_exists($match[1], $MonthNumbers)) {
				$date['Month']= $MonthNumbers[$match[1]];
				$date['Day']= sprintf('%02d', $match[2]);
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Generic parser, highlighter, and printer for the log line.
	 *
	 * @param array $cols Parsed log line
	 * @param int $linenum Line number of the log line
	 * @param array $lastlinenum Last line number, used to detect the last line
	 */
	function PrintLogLine($cols, $linenum, $lastlinenum)
	{
		global $LogConf;

		$class= $this->getLogLineClass($cols);
		PrintLogCols($linenum, $cols, $lastlinenum, $class);
	}

	/**
	 * Determines log line color class.
	 *
	 * Keywords are obtained from arrays in $LogConf.
	 *
	 * @param array $cols Parsed log line
	 * @return string Log line color class.
	 */
	function getLogLineClass($cols)
	{
		global $LogConf;

		$class= '';
		if (array_key_exists('Prio', $cols) && array_key_exists($cols['Prio'], $this->prioClasses)) {
			$class= $this->prioClasses[$cols['Prio']];
		}

		if (isset($LogConf[$this->Model]['HighlightLogs']['Col']) && isset($LogConf[$this->Model]['HighlightLogs']['REs'])) {
			// Make sure the log column exists, log rotation lines do not have module specific columns
			if (isset($cols[$LogConf[$this->Model]['HighlightLogs']['Col']])) {
				foreach ($LogConf[$this->Model]['HighlightLogs']['REs'] as $color => $res) {
					foreach ($res as $re) {
						$r= Escape($re, '/');
						if (preg_match("/$r/", $cols[$LogConf[$this->Model]['HighlightLogs']['Col']])) {
							$class= $color;
							// Exit on first match, i.e. precedence: red, yellow, green
							goto out;
						}
					}
				}
			}
		}
out:
		return $class;
	}

	/**
	 * Post-processes log columns for display.
	 *
	 * @param array $cols Parsed log line in columns
	 */
	function FormatLogCols(&$cols)
	{
	}

	function SetSessionConfOpt()
	{
	}
}

/// For classifying gettext strings into files.
function _MENU($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _CONTROL($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _NOTICE($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _HELPBOX($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _HELPWINDOW($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _TITLE2($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _HELPBOX2($str)
{
	return _($str);
}
?>
