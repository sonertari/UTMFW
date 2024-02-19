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
 * Login page.
 */

/// @warning Do not include vars.php here, it checks for logged in user, otherwise would loop back here
// Init minimal setup for login page
$ROOT= dirname(dirname(dirname(__FILE__)));
$SRC_ROOT= dirname(dirname(__FILE__));
require_once($SRC_ROOT . '/lib/setup.php');

/// Force https
if ($ForceHTTPs) {
	if (!filter_has_var(INPUT_SERVER, 'HTTPS')) {
		header('Location: https://'.filter_input(INPUT_SERVER, 'SERVER_ADDR').'/index.php');
		exit;
	}
}

require_once($SRC_ROOT . '/lib/defs.php');

// Session is started in libauth.php
// Need a session everywhere below, after successful Authentication() too
require_once('lib/libauth.php');

// This include is necessary, because $View->Controller() tries to print a helpbox
// Such help boxes are never shown on login page (there is no HelpRegion),
// but not including this lib causes a fatal PHP error: white screen
require_once('lib/libwui.php');

require_once('lib/view.php');
$View= new View();
$View->Model= 'system';

if (filter_has_var(INPUT_POST, 'Login')) {
	$_SESSION['USER']= filter_input(INPUT_POST, 'UserName');

	// The user has just typed in her password to log in to the WUI, so we use it.
	// Note that we cannot use the password stored in the cookie here, so unset it.
	setcookie('passwd', 'unset');

	// Encrypt the password immediately
	Authentication(sha1(filter_input(INPUT_POST, 'Password')));
} elseif (isset($_SESSION['Timeout'])) {
	// If user was already logged out, do not check timeout, LogUserOut() sets timeout to -1
	// Otherwise results in a loop
	wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Session timeout: ' . $_SESSION['Timeout'] . ', time: ' . time());

	if ($_SESSION['Timeout'] > 0) {
		if ($_SESSION['Timeout'] <= time()) {
			LogUserOut('Session timed out');
		} else {
			header("Location: /system/index.php");
			exit;
		}
	}
}

HTMLHeader('whitesmoke');
?>
		<table style="height: 400px;">
			<tr>
				<td>
					<div align="center">
					<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
						<table id="window">
							<tr>
								<td class="titlebar">
									<?php echo _MENU('UTM FIREWALL') ?>
								</td>
							</tr>
							<tr>
								<td class="authbox">
									<table id="authbox">
											<tr>
												<td class="label">
													<?php echo _TITLE('User name').':' ?>
												</td>
												<td class="textbox">
													<input class="textbox" type="text" name="UserName" maxlength="20"/>
												</td>
											</tr>
											<tr>
												<td class="label">
													<?php echo _TITLE('Password').':' ?>
												</td>
												<td class="textbox">
													<input class="textbox" type="password" name="Password" maxlength="20"/>
												</td>
											</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="button">
									<input class="button" type="submit" name="Login" value="<?php echo _CONTROL('Log in') ?>"/>
								</td>
							</tr>
						</table>
					</form>
					</div>
				</td>
			</tr>
		</table>
	</body>
</html>
