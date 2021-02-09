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

/// Force https
if ($ForceHTTPs) {
	if (!filter_has_var(INPUT_SERVER, 'HTTPS')) {
		header('Location: https://'.filter_input(INPUT_SERVER, 'SERVER_ADDR').filter_input(INPUT_SERVER, 'REQUEST_URI'));
		exit;
	}
}

require_once('include.php');

if (count($_POST)) {
	if (filter_has_var(INPUT_POST, 'DisableNotifier')) {
		if ($View->Controller($Output, 'DisableNotifier')) {
			wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Disable Notifier');
		}
	}
	else if (filter_has_var(INPUT_POST, 'EnableNotifier')) {
		if ($View->Controller($Output, 'EnableNotifier')) {
			wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Enable Notifier');
		}
	}
	else if (filter_has_var(INPUT_POST, 'NotifyLevel')) {
		if ($View->Controller($Output, 'SetNotifyLevel', filter_input(INPUT_POST, 'NotifyLevel'))) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'NotifyLevel set: '.filter_input(INPUT_POST, 'NotifyLevel'));
		}
	}
	else if (filter_has_var(INPUT_POST, 'NotifierHost')) {
		if ($View->Controller($Output, 'SetNotifierHost', filter_input(INPUT_POST, 'NotifierHost'))) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'NotifierHost set: '.filter_input(INPUT_POST, 'NotifierHost'));
		}
	}
	else if (filter_has_var(INPUT_POST, 'DisableNotifierSSLVerifyPeer')) {
		if ($View->Controller($Output, 'DisableNotifierSSLVerifyPeer', 'FALSE')) {
			wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Disable NotifierSSLVerifyPeer');
		}
	}
	else if (filter_has_var(INPUT_POST, 'EnableNotifierSSLVerifyPeer')) {
		if ($View->Controller($Output, 'EnableNotifierSSLVerifyPeer', 'TRUE')) {
			wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Enable NotifierSSLVerifyPeer');
		}
	}
	else if (filter_has_var(INPUT_POST, 'NotifierAPIKey')) {
		if ($View->Controller($Output, 'SetNotifierAPIKey', filter_input(INPUT_POST, 'NotifierAPIKey'))) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'NotifierAPIKey set: '.filter_input(INPUT_POST, 'NotifierAPIKey'));
		}
	}
	else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'TokenToAdd')) {
		$user = array(filter_input(INPUT_POST, 'TokenToAdd') => filter_input(INPUT_POST, 'UserToAdd'));
		$View->Controller($Output, 'AddNotifierUser', json_encode($user));
	}
	else if (filter_has_var(INPUT_POST, 'Delete')) {
		/// @todo Do not delete individually, send the list of tokens to delete
		foreach ($_POST['TokensToDelete'] as $Token) {
			$View->Controller($Output, 'DelNotifierUser', $Token);
		}
	}
	else if (filter_has_var(INPUT_POST, 'AddFilter') && filter_has_var(INPUT_POST, 'FilterToAdd')) {
		$View->Controller($Output, 'AddFilter', filter_input(INPUT_POST, 'FilterToAdd'));
	}
	else if (filter_has_var(INPUT_POST, 'DeleteFilter')) {
		/// @todo Do not delete individually, send the list of filters to delete
		foreach ($_POST['FiltersToDelete'] as $Filter) {
			$View->Controller($Output, 'DelFilter', $Filter);
		}
	}
	else if (filter_has_var(INPUT_POST, 'NotifierTimeout')) {
		if ($View->Controller($Output, 'SetNotifierTimeout', filter_input(INPUT_POST, 'NotifierTimeout'))) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'NotifierTimeout set: '.filter_input(INPUT_POST, 'NotifierTimeout'));
		}
	}
	// Reset defaults to their new values
	require($SRC_ROOT . '/lib/setup.php');
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Notifications').':' ?>
		</td>
		<td>
			<?php
			$NotifierEnabled= $View->Controller($Output, 'IsNotifierEnabled');
			$Button= $NotifierEnabled ? 'Disable' : 'Enable';
			$ButtonValue= $NotifierEnabled ? _CONTROL('Disable') : _CONTROL('Enable');
			?>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<input type="submit" name="<?php echo $Button ?>Notifier" value="<?php echo $ButtonValue ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Send notifications.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Notification Service').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="text" name="NotifierHost" style="width: 200px;" maxlength="100" value="<?php echo $NotifierHost ?>" />
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('The URL of the Firebase notification service.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('SSL Verify Peer').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<?php
				$Button= $NotifierSSLVerifyPeer ? 'Disable' : 'Enable';
				$ButtonValue= $NotifierSSLVerifyPeer ? _CONTROL('Disable') : _CONTROL('Enable');
				?>
				<input type="submit" name="<?php echo $Button ?>NotifierSSLVerifyPeer" value="<?php echo $ButtonValue ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Verify Firebase SSL notification server.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Notifier Timeout').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<input type="text" name="NotifierTimeout" style="width: 50px;" maxlength="4" value="<?php echo $NotifierTimeout ?>" />
				<input type="submit" id="ApplyNotifierTimeout" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Notifier times out trying to connect to the notification server after a period defined by this value. The unit is in seconds.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Notifier API Key').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="text" name="NotifierAPIKey" style="width: 300px;" maxlength="200" value="<?php echo $NotifierAPIKey ?>" />
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('The Firebase API key for the project, i.e. the Android application.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Registration IDs').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="TokensToDelete[]" multiple style="width: 300px; height: 100px;">
					<?php
					$users= json_decode($NotifierUsers, TRUE);
					if ($users !== NULL) {
						foreach ($users as $token => $user) {
							?>
							<option title="<?php echo $user ?>" value="<?php echo $token ?>"><?php echo $token ?></option>
							<?php
						}
					} else {
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot json_decode NotifierUsers: $NotifierUsers");
					}
					?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="TokenToAdd" style="width: 300px;" maxlength="200"/> <?php echo _TITLE('Token') ?><br />
				<input type="text" name="UserToAdd" style="width: 300px;" maxlength="200"/> <?php echo _TITLE('User') ?>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('The system will send notifications to these devices.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Notify Level').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<select name="NotifyLevel">
					<?php
					// We just have 3x notifier levels from $LOG_PRIOS
					$NOTIFIER_PRIOS= array(
						'LOG_CRIT',		// critical conditions
						'LOG_ERR',		// error conditions
						'LOG_WARNING',	// warning conditions
						);
					foreach ($NOTIFIER_PRIOS as $Prio) {
						$Selected= $Prio === $LOG_PRIOS[$NotifyLevel] ? 'selected' : '';
						?>
						<option <?php echo $Selected ?> value="<?php echo $Prio ?>"><?php echo $Prio ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" id="ApplyNotifierLevel" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This is the notifier level. The system will send push notifications for the logs at this level and up.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Filters').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="AddFilter" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="FiltersToDelete[]" multiple style="width: 300px; height: 100px;">
					<?php
					$filters= json_decode($NotifierFilters, TRUE);
					if ($filters !== NULL) {
						foreach ($filters as $filter) {
							?>
							<option value="<?php echo $filter ?>"><?php echo $filter ?></option>
							<?php
						}
					} else {
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot json_decode NotifierFilters: $NotifierFilters");
					}
					?>
				</select>
				<input type="submit" name="DeleteFilter" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="FilterToAdd" style="width: 300px;" maxlength="200"/>
				<input type="submit" name="AddFilter" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('The system will send service statuses containing one of these keywords only.'));
			?>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('Notifier runs every minute to check the system status and send Firebase push notifications based on the settings on this page. You can change the frequency of notifications on the crontab of root.

When the user logs in to the mobile application, the application automatically adds its device token to the registration ids displayed on this page. And it deletes its token when the user logs out. So the notifications are sent to that device only while the user is logged in to the application. You can manually add or delete tokens on this page.'));
require_once($VIEW_PATH.'/footer.php');
?>
