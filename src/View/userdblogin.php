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
		header('Location: https://'.filter_input(INPUT_SERVER, 'SERVER_ADDR').'/userdblogin.php');
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

$Redirect= FALSE;
if (filter_has_var(INPUT_POST, 'SSLproxy')) {
	$Redirect= filter_input(INPUT_POST, 'SSLproxy');
} else if (filter_has_var(INPUT_GET, 'SSLproxy')) {
	$Redirect= filter_input(INPUT_GET, 'SSLproxy');
}

// Do not include vars.php for $IMG_PATH, vars.php redirects to login.php
$IMG_PATH= '/images/';
$Result= FALSE;
if (filter_has_var(INPUT_POST, 'Login')) {
	$Result= UserDbAuth(filter_input(INPUT_POST, 'UserName'), filter_input(INPUT_POST, 'Password'), filter_input(INPUT_POST, 'Description'));
	if ($Result && $Redirect !== FALSE) {
		header("Location: $Redirect");
		exit;
	}
} else if (filter_has_var(INPUT_POST, 'Logout')) {
	UserDbLogout(filter_input(INPUT_POST, 'UserName'), filter_input(INPUT_POST, 'Password'));
}

HTMLHeader('whitesmoke');

$InHelpRegion= TRUE;
PrintHelpWindow('', 'auto', 'PRINT');

if ($Result == FALSE) {
	?>
		<table style="height: 400px;">
			<tr>
				<td>
					<div align="center">
					<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
						<table id="window">
							<tr>
								<td class="titlebar">
									<?php echo _MENU('UTM FIREWALL User Login') ?>
								</td>
							</tr>
							<tr>
								<td class="authbox">
									<?php echo _TITLE('You must authenticate to access the Internet').'<br>'; ?>
									<table id="authbox">
											<tr>
												<td class="label">
													<?php echo _TITLE('User Name').':' ?>
												</td>
												<td class="textbox">
													<input class="textbox" type="text" name="UserName" maxlength="31"/>
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
											<tr>
												<td class="label">
													<?php echo _TITLE('Description').':' ?>
												</td>
												<td class="textbox">
													<input class="textbox" type="text" name="Description" maxlength="50"/>
												</td>
											</tr>
									</table>
									<?php echo str_replace('<SERVER_ADDR>', filter_input(INPUT_SERVER, 'SERVER_ADDR'), _TITLE('Click <a href="https://<SERVER_ADDR>/userdbpasswd.php">here</a> to change your password')); ?>
								</td>
							</tr>
							<tr>
								<td class="button">
									<input class="button" type="submit" name="Login" value="<?php echo _CONTROL('Log in') ?>"/>
									<input class="button" type="submit" name="Logout" value="<?php echo _CONTROL('Log out') ?>"/>
									<?php
									if ($Redirect !== FALSE) {
										?>
										<input type="hidden" name="SSLproxy" value=<?php echo $Redirect ?> />
										<?php
									}
									?>
								</td>
							</tr>
						</table>
					</form>
					</div>
				</td>
			</tr>
		</table>
	<?php
	}
	?>
	</body>
</html>
