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

	/**
	 * Configuration.
	 *
	 * If title field is missing, index string is used as title.
	 *
	 * @param	title	Configuration title displayed.
	 * @param	info	Info text displayed in help box on the right.
	 */
	public $Config= array();
	
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

			if ($this->EscapeArgs($argv, $cmdline)) {
				$locale= $_SESSION['Locale'];
				$cmdline= "/usr/bin/doas $ctlr $locale $this->Model $cmdline";
				
				// Init command output
				$outputArray= array();

				$executed= TRUE;
				if ($UseSSH) {
					// Subsequent calls use the encrypted password in the cookie, so we should decrypt it first.
					$ciphertext_base64= $_COOKIE['passwd'];
					$ciphertext_dec = base64_decode($ciphertext_base64);

					$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
					$iv_dec = substr($ciphertext_dec, 0, $iv_size);

					$ciphertext_dec = substr($ciphertext_dec, $iv_size);

					$passwd = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $_SESSION['cryptKey'], $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

					$ssh = new Net_SSH2(gethostname());

					if ($ssh->login($_SESSION['USER'], $passwd)) {
						$outputArray[0]= $ssh->exec($cmdline);
					} else {
						$msg= 'SSH login failed';
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($cmdline)");
						PrintHelpWindow(_NOTICE('FAILED') . ":<br>$msg", 'auto', 'ERROR');
						$executed= FALSE;
					}
				} else {
					/// @bug http://bugs.php.net/bug.php?id=49847, fixed/closed in SVN on 141009
					exec($cmdline, $outputArray);
				}
 
				if ($executed) {
					$output= array();
					$errorStr= '';
					$retval= 1;

					$decoded= json_decode($outputArray[0], TRUE);
					if ($decoded !== NULL && is_array($decoded)) {
						$output= explode("\n", $decoded[0]);
						$errorStr= $decoded[1];
						$retval= $decoded[2];
					} else {
						$msg= "Failed decoding output: $outputArray[0]";
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($cmdline)");
						PrintHelpWindow(_NOTICE('FAILED') . ":<br>$msg", 'auto', 'ERROR');
					}

					// Show error, if any
					if ($errorStr !== '') {
						$error= explode("\n", $errorStr);

						wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: (" . implode(', ', $error) . "), ($cmdline)");
						PrintHelpWindow(_NOTICE('FAILED') . ':<br>' . implode('<br>', $error), 'auto', 'ERROR');
					}

					// (exit status 0 in shell) == (TRUE in php)
					if ($retval === 0) {
						$return= TRUE;
					} else {
						wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: ($cmdline)");
					}
				}
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
		$hostname= gethostname();

		$ssh = new Net_SSH2($hostname);

		if ($ssh->login($user, $passwd)) {
			/// @attention Trim the newline
			$output= trim($ssh->exec('hostname'));
			if ($hostname == $output) {
				return TRUE;
			} else {
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "SSH test command failed: $hostname == $output");
			}
		} else {
			$msg= 'Authentication failed';
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, $msg);
			PrintHelpWindow(_NOTICE('FAILED') . ":<br>$msg", 'auto', 'ERROR');
		}
		return FALSE;
	}

	/**
	 * Escapes the arguments passed to Controller() and builds the command line.
	 *
	 * @param array $argv Command and arguments array.
	 * @param string $cmdline Actual command line to run.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function EscapeArgs($argv, &$cmdline)
	{
		if (count($argv) > 0) {
			$cmd= $argv[0];
			$argv= array_slice($argv, 1);
  	
			$cmdline= $cmd;
			foreach ($argv as $arg) {
				$cmdline.= ' '.escapeshellarg($arg);
			}
			return TRUE;
		}
		wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, '$argv is empty');
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
	 * Processes user posts for process restart and stop.
	 *
	 * Used on all info pages.
	 */
	function ProcessRestartStopRequests()
	{
		if (filter_has_var(INPUT_POST, 'Model')) {
			if (filter_input(INPUT_POST, 'Model') == $this->Model) {
				if (filter_has_var(INPUT_POST, 'Start')) {
					$this->Restart();
				}
				else if (filter_has_var(INPUT_POST, 'Stop')) {
					$this->Stop();
				}
			}
		}
	}

	/**
	 * Displays module status, software version, Restart/Stop buttons, and process table.
	 *
	 * @param boolean $printcount Whether to print number of running processes too
	 * @param boolean $showbuttons Show Start/Stop buttons
	 */
	function PrintStatusForm($printcount= FALSE, $showbuttons= TRUE)
	{
		global $IMG_PATH, $ADMIN;

		if ($running= $this->Controller($output, 'IsRunning')) {
			$imgfile= 'run.png';
			$name= 'Running';
			$info= _TITLE('is running');
			$confirm= _NOTICE('Are you sure you want to stop the <NAME>?');
			if ($showbuttons) {
				$button= 'Stop';
			}
		}
		else {
			$imgfile= 'stop.png';
			$name= 'Stopped';
			$info= _TITLE('is not running');
			$confirm= _NOTICE('Are you sure you want to start the <NAME>?');
			if ($showbuttons) {
				$button= 'Start';
			}
		}
		$confirm= preg_replace('/<NAME>/', $this->Caption, $confirm);
		?>
		<table id="status">
			<tr>
				<td class="image">
					<img src="<?php echo $IMG_PATH.$imgfile ?>" name="<?php echo $name ?>" alt="<?php echo $name ?>" border="0">
				</td>
				<td>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<strong><?php echo $this->Caption . " $info " ?></strong>
						<?php
						/// Only admin can start/stop the processes
						if ($button && in_array($_SESSION['USER'], $ADMIN)) {
							?>
							<input type="submit" name="<?php echo $button ?>" value="<?php echo _($button) ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
							<?php
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
		if ($running && $this->Model != 'pf') {
			$this->Controller($output, 'GetProcList');
			$this->PrintProcessTable(json_decode($output[0], TRUE), $printcount);
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
				$class= ($linenum++ % 2 == 0) ? 'evenline' : 'oddline';
				?>
				<tr class="<?php echo $class ?>">
				<?php
				$count= 1;
				foreach ($cols as $c) {
					if (in_array($count++, array(8, 11, 12, 13))) {
						// Left align stat, user, group, and command columns
						$class= 'class="left"';
					}
					else {
						$class= '';
					}
					?>
					<td <?php echo $class ?>>
						<?php echo $c ?>
					</td>
					<?php
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
		$this->Controller($output, 'GetProcStatLines', $file);
		$stats= json_decode($output[0], TRUE);
		PrintNVPs($stats, _STATS('General Statistics'));
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
	 * If there is no PrintLogLine() defined in module's include file,
	 * this one is used instead.
	 *
	 * @param array $cols Parsed log line
	 * @param int $linenum Line number of the log line
	 */
	function PrintLogLine($cols, $linenum)
	{
		global $LogConf;

		$logstr= isset($LogConf[$this->Model]['HighlightLogs']['Col']) ?
			$cols[$LogConf[$this->Model]['HighlightLogs']['Col']] :
			implode(' ', $cols);

		$this->PrintLogLineClass($logstr);

		PrintLogCols($linenum, $cols);
		echo '</tr>';
	}

	/**
	 * Prints log line color tr tag.
	 *
	 * Keywords are obtained from arrays in $LogConf.
	 *
	 * @param string $logstr Log string to search for keywords
	 */
	function PrintLogLineClass($logstr)
	{
		global $LogConf;

		$genericREs= array(
			'red' => array('\berror\b'),
			'yellow' => array('\bwarning\b'),
			'green' => array('\bsuccess'),
		);

		$logREs= isset($LogConf[$this->Model]['HighlightLogs']['REs']) ? $LogConf[$this->Model]['HighlightLogs']['REs'] : $genericREs;

		$done= FALSE;
		$class= '';
		foreach ($logREs as $color => $res) {
			foreach ($res as $re) {
				$r= Escape($re, '/');
				if (preg_match("/$r/", $logstr)) {
					$class= $color;
					/// Exit on first match, i.e. precedence: red, yellow, green
					$done= TRUE;
					break;
				}
			}
			if ($done) {
				// Exit on first match
				break;
			}
		}
		echo $class == '' ? '<tr>' : "<tr class=\"$class\">";
	}

	/**
	 * Post-processes log columns for display.
	 *
	 * @param array $cols Parsed log line in columns
	 */
	function FormatLogCols(&$cols)
	{
		if (isset($cols['Log'])) {
			$cols['Log']= wordwrap($cols['Log'], 80, '<br />', TRUE);
		}
	}

	function SetSessionFilterGroup()
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
function _TITLE($str)
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

/// @todo Check if we need _STATS() in the View
///// For classifying gettext strings into files.
//function _STATS($str)
//{
//	return _($str);
//}
?>
