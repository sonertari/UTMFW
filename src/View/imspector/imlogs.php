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

require_once('include.php');

$LogConf = array(
	'imlogs' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'IPPort' => _TITLE('IPPort'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'REs' => array(
				/// v0.7 log format confuses old format parser
				/// Incrementing by 1 in im parser elsewhere for v0.7
				//'intext'		=> array('0'),
				'intext' => array('1'),
				'outtext' => array('2'),
				'infilexfer' => array('3'),
				'outfilexfer' => array('4'),
				),
			),
		),
	);

class Imlogs extends View
{
	public $Model= 'imlogs';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
	}

	function PrintLogLine($cols, $linenum, $lastlinenum)
	{
		$class= $this->getLogLineClass($cols['User'], $cols);
		PrintLogCols($linenum, $cols, $lastlinenum, $class);
	}
}

$View= new Imlogs();

if (filter_has_var(INPUT_POST, 'Proto')) {
	$_SESSION[$View->Model]['Proto']= filter_input(INPUT_POST, 'Proto');
	unset($_SESSION[$View->Model]['LocalUser'],
		$_SESSION[$View->Model]['RemoteUser'],
		$_SESSION[$View->Model]['Session']);
}
if (filter_has_var(INPUT_POST, 'LocalUser')) {
	$_SESSION[$View->Model]['LocalUser']= filter_input(INPUT_POST, 'LocalUser');
	unset($_SESSION[$View->Model]['RemoteUser'],
		$_SESSION[$View->Model]['Session']);
}
if (filter_has_var(INPUT_POST, 'RemoteUser')) {
	$_SESSION[$View->Model]['RemoteUser']= filter_input(INPUT_POST, 'RemoteUser');
	unset($_SESSION[$View->Model]['Session']);
}
if (filter_has_var(INPUT_POST, 'Session')) {
	$_SESSION[$View->Model]['Session']= filter_input(INPUT_POST, 'Session');
}

if ($_SESSION[$View->Model]['Proto']) {
	$Proto= $_SESSION[$View->Model]['Proto'];
}
if ($_SESSION[$View->Model]['LocalUser']) {
	$LocalUser= $_SESSION[$View->Model]['LocalUser'];
}
if ($_SESSION[$View->Model]['RemoteUser']) {
	$RemoteUser= $_SESSION[$View->Model]['RemoteUser'];
}
if ($_SESSION[$View->Model]['Session']) {
	$Session= $_SESSION[$View->Model]['Session'];
}

$SelectHeight= '100px';
require_once($VIEW_PATH.'/header.php');
?>
<table>
	<tr>
		<td class="imselectbox">
		<?php
		$View->Controller($Output, 'GetProtocols');
		?>
		<?php echo _TITLE2('Protocol').':' ?>
		<form id="ProtoForm" name="ProtoForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<select name="Proto" onchange="document.ProtoForm.submit()" multiple style="width: 175px; height: <?php echo $SelectHeight ?>;">
				<?php
				if (!empty($Output)) {
					foreach ($Output as $Protocol) {
						$Selected= $Protocol === $Proto ? ' selected' : '';
						?>
						<option value="<?php echo $Protocol ?>"<?php echo $Selected ?>><?php echo $Protocol ?></option>
						<?php
					}
				}
				?>
			</select>
		</form>
		</td>
		<td class="imselectbox">
		<?php
		if (isset($Proto)) {
			$View->Controller($Output, 'GetLocalUsers', $Proto);
			?>
			<label class="imlocaluser"><?php echo _TITLE2('Local User').':' ?></label>
			<form id="LocalUserForm" name="LocalUserForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="LocalUser" onchange="document.LocalUserForm.submit()" multiple style="width: 175px; height: <?php echo $SelectHeight ?>;">
					<?php
					foreach ($Output as $User) {
						$Selected= $User === $LocalUser ? ' selected' : '';
						?>
						<option value="<?php echo $User ?>"<?php echo $Selected ?>><?php echo $User ?></option>
						<?php
					}
					?>
				</select>
				<input type="hidden" name="Proto" value="<?php echo $Proto ?>" />
			</form>
			<?php
		}
		?>
		</td>
		<td class="imselectbox">
		<?php
		if (isset($LocalUser)) {
			$View->Controller($Output, 'GetRemoteUsers', $Proto, $LocalUser);
			?>
			<?php echo _TITLE2('Remote User').':' ?>
			<form id="RemoteUserForm" name="RemoteUserForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="RemoteUser" onchange="document.RemoteUserForm.submit()" multiple style="width: 175px; height: <?php echo $SelectHeight ?>;">
					<?php
					foreach ($Output as $User) {
						$Selected= $User === $RemoteUser ? ' selected' : '';
						?>
						<option value="<?php echo $User ?>"<?php echo $Selected ?>><?php echo $User ?></option>
						<?php
					}
					?>
				</select>
				<input type="hidden" name="Proto" value="<?php echo $Proto ?>" />
				<input type="hidden" name="LocalUser" value="<?php echo $LocalUser ?>" />
			</form>
			<?php
		}
		?>
		</td>
		<td class="imselectbox">
		<?php
		if (isset($RemoteUser)) {
			$View->Controller($Output, 'GetSessions', $Proto, $LocalUser, $RemoteUser);
			?>
			<?php echo _TITLE2('Session').':' ?>
			<form id="SessionForm" name="SessionForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="Session" onchange="document.SessionForm.submit()" multiple style="width: 175px; height: <?php echo $SelectHeight ?>;">
					<?php
					foreach ($Output as $SessionDate) {
						$Selected= $SessionDate === $Session ? ' selected' : '';
						?>
						<option value="<?php echo $SessionDate ?>"<?php echo $Selected ?>><?php echo $SessionDate ?></option>
						<?php
					}
					?>
				</select>
				<input type="hidden" name="Proto" value="<?php echo $Proto ?>" />
				<input type="hidden" name="LocalUser" value="<?php echo $LocalUser ?>" />
				<input type="hidden" name="RemoteUser" value="<?php echo $RemoteUser ?>" />
			</form>
			<?php
		}
		?>
		</td>
	</tr>
</table>
<?php
if ($Session) {
	$View->Controller($Output, 'GetImLogFile', $Proto, $LocalUser, $RemoteUser, $Session);
	$LogFile= Escape($Output[0], ';{}');
	$_SESSION[$View->Model]['LogFile']= $LogFile;

	ProcessStartLine($StartLine);
	UpdateLogsPageSessionVars($LinesPerPage, $SearchRegExp, $SearchNeedle);

	$View->Controller($Output, 'GetFileLineCount', $LogFile, $SearchRegExp);
	$LogSize= $Output[0];

	ProcessNavigationButtons($LinesPerPage, $LogSize, $StartLine, $HeadStart);

	$CustomHiddenInputs= <<<EOF
<input type="hidden" name="Proto" value="$Proto" />
<input type="hidden" name="LocalUser" value="$LocalUser" />
<input type="hidden" name="RemoteUser" value="$RemoteUser" />
<input type="hidden" name="Session" value="$Session" />
EOF;
	PrintLogHeaderForm($StartLine, $LogSize, $LinesPerPage, $SearchRegExp, $CustomHiddenInputs);
	?>
	<table id="imlogs">
		<?php
		PrintTableHeaders($View->Model);
		?>
		<?php
		$View->Controller($Output, 'GetLogs', $LogFile, $HeadStart, $LinesPerPage, $SearchRegExp);
		$Logs= json_decode($Output[0], TRUE);

		$LineCount= $StartLine + 1;
		$LastLineNum= $StartLine + min(array(count($Logs), $LinesPerPage));
		foreach ($Logs as $Log) {
			$View->PrintLogLine($Log, $LineCount++, $LastLineNum);
		}
		?>
	</table>
	<?php
}

PrintHelpWindow(_HELPWINDOW('IM logs are categorized by protocols. After you select the protocol, you need to select the local user. Local user sessions are further categorized by remote users connected. Finally, sessions with remote users are categorized by date. IM proxy can log group chats as well.

You can find these logs under /var/log/imspector/.'));
require_once($VIEW_PATH.'/footer.php');
?>
