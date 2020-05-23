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

/// Force https
if ($ForceHTTPs) {
	if (!filter_has_var(INPUT_SERVER, 'HTTPS')) {
		header('Location: https://'.filter_input(INPUT_SERVER, 'SERVER_ADDR').filter_input(INPUT_SERVER, 'REQUEST_URI'));
		exit;
	}
}

require_once('include.php');

if (count($_POST)) {
	// Allow posting empty strings to display an error message
	if (filter_has_var(INPUT_POST, 'User') && filter_has_var(INPUT_POST, 'NewPassword') && filter_has_var(INPUT_POST, 'NewPasswordAgain')) {
		$User= filter_input(INPUT_POST, 'User');
		$NewPassword= filter_input(INPUT_POST, 'NewPassword');
		if (in_array($User, $ALL_USERS)) {
			if (CheckPasswordsMatch($User, $NewPassword, filter_input(INPUT_POST, 'NewPasswordAgain'))) {
				if (ValidatePassword($User, $NewPassword)) {
					/// @attention Admin can change other users' passwords without needing to know their current passwords
					if (($_SESSION['USER'] == 'admin' && $User != 'admin')
							|| $View->CheckAuthentication($User, sha1(filter_input(INPUT_POST, 'CurrentPassword')))) {
						// Encrypt passwords before passing down, plaintext passwords should never be visible, not even in the doas logs
						if ($View->Controller($Output, 'SetPassword', $User, sha1($NewPassword))) {
							PrintHelpWindow(_NOTICE('User password changed') . ': ' . $User);
							wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "User password changed: $User");
							if ($_SESSION['USER'] == $User) {
								// Log user out if she changes her own password, currently only admin can do that
								LogUserOut('User password changed');
							}
						}
						else {
							wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Password change failed: $User");
						}
					}
					else {
						wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Password mismatch');
						// Throttle authentication failures
						exec('/bin/sleep 5');
					}
				}
			}
		}
		else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('utmfw currently supports only admin and user usernames'), 'auto', 'ERROR');
			wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Invalid user: $User");
		}
	}
	else if (filter_has_var(INPUT_POST, 'LogLevel')) {
		if ($View->Controller($Output, 'SetLogLevel', filter_input(INPUT_POST, 'LogLevel'))) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'LogLevel set: '.filter_input(INPUT_POST, 'LogLevel'));
			// Reset $LOG_LEVEL to its new value
			require($SRC_ROOT . '/lib/setup.php');
		}
	}
	else if (filter_has_var(INPUT_POST, 'MaxAnchorNesting')) {
		if ($View->Controller($Output, 'SetMaxAnchorNesting', filter_input(INPUT_POST, 'MaxAnchorNesting'))) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'MaxAnchorNesting set: '.filter_input(INPUT_POST, 'MaxAnchorNesting'));
			// Reset $MaxAnchorNesting to its new value
			require($SRC_ROOT . '/lib/setup.php');
		}
	}
	else if (filter_has_var(INPUT_POST, 'PfctlTimeout')) {
		if ($View->Controller($Output, 'SetPfctlTimeout', filter_input(INPUT_POST, 'PfctlTimeout'))) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'PfctlTimeout set: '.filter_input(INPUT_POST, 'PfctlTimeout'));
			// Reset $PfctlTimeout to its new value
			require($SRC_ROOT . '/lib/setup.php');
		}
	}
	else if (filter_has_var(INPUT_POST, 'StatusCheckInterval')) {
		if ($View->Controller($Output, 'SetStatusCheckInterval', filter_input(INPUT_POST, 'StatusCheckInterval'))) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'StatusCheckInterval set: '.filter_input(INPUT_POST, 'StatusCheckInterval'));
			// Reset $StatusCheckInterval to its new value
			require($SRC_ROOT . '/lib/setup.php');
		}
	}
	else {
		if (filter_has_var(INPUT_POST, 'DisableHelpBoxes')) {
			if ($View->Controller($Output, 'SetHelpBox', 'FALSE')) {
				wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Disable HelpBoxes');
			}
		}
		else if (filter_has_var(INPUT_POST, 'EnableHelpBoxes')) {
			if ($View->Controller($Output, 'SetHelpBox', 'TRUE')) {
				wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Enable HelpBoxes');
			}
		}
		else if (filter_has_var(INPUT_POST, 'SessionTimeout')) {
			if ($View->Controller($Output, 'SetSessionTimeout', filter_input(INPUT_POST, 'SessionTimeout'))) {
				wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'SessionTimeout set: '.filter_input(INPUT_POST, 'SessionTimeout'));
			}
		}
		else if (filter_has_var(INPUT_POST, 'DefaultLocale')) {
			if ($View->Controller($Output, 'SetDefaultLocale', filter_input(INPUT_POST, 'DefaultLocale'))) {
				wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'DefaultLocale set: '.filter_input(INPUT_POST, 'DefaultLocale'));
				// Reset $DefaultLocale to its new value
				require($SRC_ROOT . '/lib/setup.php');
			}
		}
		else if (filter_has_var(INPUT_POST, 'ReloadRate')) {
			if ($View->Controller($Output, 'SetReloadRate', filter_input(INPUT_POST, 'ReloadRate'))) {
				wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'ReloadRate set: '.filter_input(INPUT_POST, 'ReloadRate'));
			}
		}
		else if (filter_input(INPUT_POST, 'DisableForceHTTPs') || filter_input(INPUT_POST, 'EnableForceHTTPs')) {
			if (filter_has_var(INPUT_POST, 'DisableForceHTTPs')) {
				if ($View->Controller($Output, 'SetForceHTTPs', 'FALSE')) {
					wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Disable ForceHTTPs');
				}
			}
			else if (filter_has_var(INPUT_POST, 'EnableForceHTTPs')) {
				if ($View->Controller($Output, 'SetForceHTTPs', 'TRUE')) {
					wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Enable ForceHTTPs');
				}
			}
			// Reload the page using plain HTTP to activate the change
			header('Location: http://'.filter_input(INPUT_SERVER, 'SERVER_ADDR').filter_input(INPUT_SERVER, 'REQUEST_URI'));
			exit;
		}
		else if (filter_has_var(INPUT_POST, 'DisableUseSSH')) {
			if ($View->Controller($Output, 'SetUseSSH', 'FALSE')) {
				wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Disable UseSSH');
			}
		}
		else if (filter_has_var(INPUT_POST, 'EnableUseSSH')) {
			if ($View->Controller($Output, 'SetUseSSH', 'TRUE')) {
				wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Enable UseSSH');
			}
		}
		// Reset defaults to their new values
		require($VIEW_PATH.'/lib/setup.php');
	}
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
		<tr class="oddline">
			<td class="titlegrouptop">
				<?php echo _TITLE('User').':' ?>
			</td>
			<td class="valuegrouptop">
				<input type="text" name="User" style="width: 100px;" maxlength="20" value="<?php echo $_SESSION['USER'] ?>"/>
			</td>
			<td class="none" rowspan="4">
				<?php
				PrintHelpBox(_HELPBOX('Here you can change the web administration interface passwords for admin and user. Passwords should have at least 8 alphanumeric characters.

Admin can change the user password without knowing the current user password. But if you forget the admin password, you should login to the system as root and run the following on the command line to set the password to soner123:
<code>/usr/bin/chpass -a "admin:$(/usr/bin/encrypt `/bin/echo -n soner123 | sha1 -`):1000:1000::0:0:UTMFW admin:/var/empty:/var/www/htdocs/utmfw/Controller/sh.php"</code>'));
				?>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegroupmiddle">
				<?php echo _TITLE('Current Password').':' ?>
			</td>
			<td class="valuegroupmiddle">
				<input type="password" name="CurrentPassword" style="width: 100px;" maxlength="20"/>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegroupmiddle">
				<?php echo _TITLE('New Password').':' ?>
			</td>
			<td class="valuegroupmiddle">
				<input type="password" name="NewPassword" style="width: 100px;" maxlength="20"/>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegroupbottom">
				<?php echo _TITLE('New Password Again').':' ?>
			</td>
			<td class="valuegroupbottom">
				<input type="password" name="NewPasswordAgain" style="width: 100px;" maxlength="20"/>
				<input type="submit" id="ApplyPassword" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</td>
		</tr>
	</form>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Force HTTPs').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<?php
				$Button= $ForceHTTPs ? 'Disable' : 'Enable';
				$ButtonValue= $ForceHTTPs ? _CONTROL('Disable') : _CONTROL('Enable');
				$confirmMsg= str_replace('<BUTTON_VALUE>', $ButtonValue, _CONTROL('Are you sure you want to <BUTTON_VALUE> secure HTTP?'));
				?>
				<input type="submit" name="<?php echo $Button ?>ForceHTTPs" value="<?php echo $ButtonValue ?>" onclick="return confirm('<?php echo $confirmMsg ?>')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('If enabled, authentication pages are forced to use secure connections. Make sure you have a working SSL setup in the web server configuration, otherwise you cannot even log in to the web user interface. It is advised to use secure HTTP.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Use SSH').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<?php
				$Button= $UseSSH ? 'Disable' : 'Enable';
				$ButtonValue= $UseSSH ? _CONTROL('Disable') : _CONTROL('Enable');
				?>
				<input type="submit" name="<?php echo $Button ?>UseSSH" value="<?php echo $ButtonValue ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This setting allows you to choose the method used while running the controller. Controller commands can be executed using either of the following two methods: (1) Executing the controller directly on the command line or (2) Openning an SSH connection to the system and calling the controller over this secure channel.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Log Level').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<select name="LogLevel">
					<?php
					foreach ($LOG_PRIOS as $Prio) {
						$Selected= $Prio === $LOG_PRIOS[$LOG_LEVEL] ? 'selected' : '';
						?>
						<option <?php echo $Selected ?> value="<?php echo $Prio ?>"><?php echo $Prio ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" id="ApplyLogLevel" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This is the log level for the utmfw web interface. Logs at this level and up will be recorded in WUI and Controller log files. This setting does not effect other services.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Help Boxes').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<?php
				$Button= $ShowHelpBox ? 'Disable' : 'Enable';
				$ButtonValue= $ShowHelpBox ? _CONTROL('Disable') : _CONTROL('Enable');
				?>
				<input type="submit" name="<?php echo $Button ?>HelpBoxes" value="<?php echo $ButtonValue ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This setting enables or disables help boxes, such as this one and the help window at the bottom. Disabling help boxes does not disable error or warning help windows.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Session Timeout').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<input type="text" name="SessionTimeout" style="width: 50px;" maxlength="4" value="<?php echo $SessionTimeout ?>" />
				<input type="submit" id="ApplySessionTimeout" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('User sessions expire after an idle period defined by this value. The unit is in seconds. You cannot set a value less than 10 seconds.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Reload Rate').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<input type="text" name="ReloadRate" style="width: 50px;" maxlength="4" value="<?php echo $DefaultReloadRate ?>" />
				<input type="submit" id="ApplyReloadRate" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This is the default refresh value in seconds for pages which reload periodically, such as Live pages for logs and statistics. The new value takes effect only for newly created sessions. Note that logging out of the web user interface does not erase your session values.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Language').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<select name="DefaultLocale">
					<?php
					foreach ($LOCALES as $Locale => $Conf) {
						$Selected= $Locale === $DefaultLocale ? 'selected' : '';
						?>
						<option <?php echo $Selected ?> value="<?php echo $Locale ?>"><?php echo _($Conf['Name']) ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" id="ApplyDefaultLocale" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This is the default language for the WUI.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Max Anchor Nesting').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<input type="text" name="MaxAnchorNesting" style="width: 50px;" maxlength="2" value="<?php echo $MaxAnchorNesting ?>" />
				<input type="submit" id="ApplyMaxAnchorNesting" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Inline anchor rules can be nested. It is advised to limit the number of nesting allowed. Parsing and validation stop at this many number of nesting.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Pfctl Timeout').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<input type="text" name="PfctlTimeout" style="width: 50px;" maxlength="2" value="<?php echo $PfctlTimeout ?>" />
				<input type="submit" id="ApplyPfctlTimeout" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Pfctl commands are executed in a separate process, which returns pfctl output in a message. Parent process times out waiting for an output message after this many seconds. This approach is necessary in case pfctl is stuck or taking too long (and it is in certain cases).

<b>Setting this timeout to 0 may fail the execution of all pfctl commands, effectively disabling rule tests.<b>'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Status Check Interval').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<input type="text" name="StatusCheckInterval" style="width: 50px;" maxlength="3" value="<?php echo $StatusCheckInterval ?>"/>
				<input type="submit" id="ApplyStatusCheckInterval" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This is the time interval in seconds to check module statuses for displaying.'));
			?>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('These defaults are permanently stored in web user interface settings, i.e. they are <em>not</em> specific to your current session only.'));
require_once($VIEW_PATH.'/footer.php');
?>
