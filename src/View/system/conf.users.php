<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
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
	$SelectedUser= '';
	$SelectedUserName= '';
	if (filter_has_var(INPUT_POST, 'SelectedUser') && (filter_has_var(INPUT_POST, 'Delete') || filter_has_var(INPUT_POST, 'Select'))) {
		$Selected= filter_input(INPUT_POST, 'SelectedUser');
		$SelectedArray= explode(':', filter_input(INPUT_POST, 'SelectedUser'));
		$SelectedUser= $SelectedArray[0];
		$SelectedUserName= $SelectedArray[1];
	}
	if (filter_has_var(INPUT_POST, 'Change')) {
		$User= filter_input(INPUT_POST, 'User');
		$Password= filter_input(INPUT_POST, 'Password');
		if (CheckUserDbUser($User, TRUE)) {
			if (CheckPasswordsMatch($User, $Password, filter_input(INPUT_POST, 'PasswordAgain'))) {
				if (ValidatePassword($User, $Password)) {
					// Encrypt passwords before passing down, plaintext passwords should never be visible, not even in the doas logs
					if ($View->Controller($Output, 'SetPassword', $User, sha1($Password))) {
						PrintHelpWindow(_NOTICE('User password changed') . ': ' . $User);
						wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "User password changed: $User");
					}
					else {
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Password change failed: $User");
					}
				}
			}
		} else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('User does not exist'), 'auto', 'ERROR');
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "User does not exist: $User");
		}
	}
	else if (filter_has_var(INPUT_POST, 'Add')) {
		$User= filter_input(INPUT_POST, 'User');
		$Password= filter_input(INPUT_POST, 'Password');
		if ($User !== '') {
			if (!CheckUserDbUser($User, TRUE)) {
				if (CheckPasswordsMatch($User, $Password, filter_input(INPUT_POST, 'PasswordAgain'))) {
					if (ValidatePassword($User, $Password)) {
						// Encrypt passwords before passing down, plaintext passwords should never be visible, not even in the doas logs
						if ($View->Controller($Output, 'AddUser', $User, sha1($Password), filter_input(INPUT_POST, 'UserName'))) {
							PrintHelpWindow(_NOTICE('User added').': '.$User);
							wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "User added: $User");
						}
						else {
							wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "User add failed: $User");
						}
					}
				}
			} else {
				PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('User already exists'), 'auto', 'ERROR');
				wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "User already exists: $User");
			}
		} else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Need a login name for user'), 'auto', 'ERROR');
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Need a login name for user');
		}
	}
	else if (filter_has_var(INPUT_POST, 'Delete')) {
		if ($View->Controller($Output, 'DelUser', $SelectedUser)) {
			PrintHelpWindow(_NOTICE('User deleted') . ': ' . $SelectedUser);
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "User deleted: $SelectedUser");
			// Blank out selected user, otherwise the deleted user's info is printed in edit boxes
			$SelectedUser= '';
			$SelectedUserName= '';
		}
		else {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "User delete failed: $SelectedUser");
		}
	}
	else if (filter_has_var(INPUT_POST, 'DeleteClient')) {
		foreach ($_POST['ClientsToDelete'] as $Client) {
			list($ip, $user, $ether, $atime, $desc)= explode(' | ', $Client);
			if ($View->Controller($Output, 'DelClient', $ip)) {
				PrintHelpWindow(_NOTICE('Client deleted') . ': ' . $Client);
				wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Client deleted: $Client");
			}
			else {
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Client delete failed: $Client");
			}
		}
	}
}

if ($View->Controller($Output, 'GetUsers')) {
	$users= json_decode($Output[0]);
}
if ($View->Controller($Output, 'GetClients')) {
	/// @attention Need $assoc param set to TRUE here, otherwise we get an object instead
	$clients= json_decode($Output[0], TRUE);
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
		<tr class="evenline">
			<td class="title">
				<?php echo _TITLE('Users').':' ?>
			</td>
			<td>
				<select name="SelectedUser" style="width: 300px;">
					<?php
					foreach ($users as $user => $userName) {
						$value= "$user:$userName";
						?>
						<option value="<?php echo $value ?>" <?php echo ($value == $Selected ? 'selected' : '') ?>><?php echo $value ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="Select" value="<?php echo _CONTROL('Select') ?>"/>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to delete the user?') ?>')"/>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegrouptop">
				<?php echo _TITLE('User').':' ?>
			</td>
			<td class="valuegrouptop">
				<input type="text" name="User" style="width: 100px;" maxlength="31" value="<?php echo $SelectedUser ?>"/>
			</td>
			<td class="none" rowspan="4">
				<?php
				PrintHelpBox(_HELPBOX('Passwords should have at least 8 alphanumeric characters.'));
				?>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegroupmiddle">
				<?php echo _TITLE('Password').':' ?>
			</td>
			<td class="valuegroupmiddle">
				<input type="password" name="Password" style="width: 100px;" maxlength="20"/>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegroupmiddle">
				<?php echo _TITLE('Password Again').':' ?>
			</td>
			<td class="valuegroupmiddle">
				<input type="password" name="PasswordAgain" style="width: 100px;" maxlength="20"/>
				<input type="submit" name="Change" value="<?php echo _CONTROL('Change Password') ?>"/>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegroupbottom">
				<?php echo _TITLE('User Name').':' ?>
			</td>
			<td class="valuegroupbottom">
				<input type="text" name="UserName" style="width: 200px;" maxlength="50" value="<?php echo $SelectedUserName ?>"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</td>
		</tr>
		<tr class="evenline">
			<td class="title">
				<?php echo _TITLE('Clients').':' ?>
			</td>
			<td>
				<select name="ClientsToDelete[]" multiple style="width: 400px; height: 100px;">
					<?php
					$now= time();
					foreach ($clients as $client) {
						$value= $client['IP'].' | '.$client['USER'].' | '.$client['ETHER'].' | '.($now - $client['ATIME']).' | '.$client['DESC'];
						?>
						<option value="<?php echo $value ?>"><?php echo $value ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="DeleteClient" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to delete the selected client(s)?') ?>')"/>
			</td>
			<td class="none">
				<?php
				PrintHelpBox(_HELPBOX('This is the list of clients. The fields on each line are the IP address, user name, ethernet address, idle time in seconds, and description of the client.'));
				?>
			</td>
		</tr>
	</form>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('This page allows you to add and delete firewall users, change their passwords, list and delete active clients. You can setup user authentication on the configuration page for SSLproxy.'));
require_once($VIEW_PATH.'/footer.php');
?>
