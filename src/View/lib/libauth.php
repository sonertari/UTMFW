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
 * Authentication and related library functions.
 */

require_once($VIEW_PATH.'/lib/setup.php');

if (!isset($_SESSION)) {
	session_name('utmfw');
	session_start();
}

if (filter_has_var(INPUT_GET, 'logout')) {
	LogUserOut();
}

if (filter_has_var(INPUT_GET, 'locale')) {
	$_SESSION['Locale'] = filter_input(INPUT_GET, 'locale');
	// To refresh the page after language change
	// @attention Remove the trailing locale, otherwise the page goes into a redirection loop
	header('Location: '.preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI'], -1));
	exit;
}

if (!isset($_SESSION['Locale'])) {
	$_SESSION['Locale']= $DefaultLocale;
}
putenv('LC_ALL='.$_SESSION['Locale']);
putenv('LANG='.$_SESSION['Locale']);

$Domain= 'utmfw';
bindtextdomain($Domain, $VIEW_PATH.'/locale');
bind_textdomain_codeset($Domain, $LOCALES[$_SESSION['Locale']]['Codeset']);
textdomain($Domain);

/**
 * Wrapper for syslog().
 *
 * Web interface related syslog messages.
 * A global $LOG_LEVEL is set in setup.php.
 *
 * @param int $prio Log priority checked against $LOG_LEVEL.
 * @param string $file Source file the function is in.
 * @param string $func Function where the log is taken.
 * @param int $line Line number within the function.
 * @param string $msg Log message.
 */
function wui_syslog($prio, $file, $func, $line, $msg)
{
	global $LOG_LEVEL, $LOG_PRIOS;

	try {
		openlog('wui', LOG_PID, LOG_LOCAL0);
		
		if ($prio <= $LOG_LEVEL) {
			$useratip= $_SESSION['USER'].'@'.filter_input(INPUT_SERVER, 'REMOTE_ADDR');
			$func= $func == '' ? 'NA' : $func;
			$log= "$LOG_PRIOS[$prio] $useratip $file: $func ($line): $msg";
			if (!syslog($prio, $log)) {
				$log.= "\n";
				if (!fwrite(STDERR, $log)) {
					echo $log;
				}
			}
		}
		closelog();
	}
	catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
		echo "wui_syslog() failed: $prio, $file, $func, $line, $msg\n";
		// No need to closelog(), it is optional
	}
}

/**
 * Logs user out by setting session USER var to loggedout.
 *
 * Redirects to the main index page, which asks for re-authentication.
 *
 * @param string $reason Reason for log message.
 */
function LogUserOut($reason= 'User logged out')
{
	wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, $reason);

	// Save USER to check if the user changes in the next session
	$_SESSION['PREVIOUS_USER']= $_SESSION['USER'];

	$_SESSION['USER']= 'loggedout';
	/// @warning Relogin page should not time out
	$_SESSION['Timeout']= -1;
	session_write_close();

	header('Location: /index.php');
	exit;
}

/**
 * Authenticates session user with the password supplied.
 *
 * Passwords are never passed around plaintext, but are always encrypted.
 * 
 * In fact, passwords are double encrypted using:
 * - sha1() right after the user types her password in
 * - encrypt(1) while saving to the password file using chpass(1).
 * - openssl_*() while saving as a cookie var.
 * 
 * Also, we always use SSH connections to execute all Controller commands.
 *
 * @param string $passwd SHA encrypted password.
 */
function Authentication($passwd)
{
	global $ALL_USERS, $SessionTimeout, $View;

	wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Login attempt');

	if (!in_array($_SESSION['USER'], $ALL_USERS)) {
		wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Not a valid user');
		// Throttle authentication failures
		exec('/bin/sleep 5');
		LogUserOut('Authentication failed');
	}

	if (!$View->CheckAuthentication($_SESSION['USER'], $passwd)) {
		wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Password mismatch');
		// Throttle authentication failures
		exec('/bin/sleep 5');
		LogUserOut('Authentication failed');
	}
	wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Authentication succeeded');

	// Encrypt the password to save as a cookie var.
	$random= exec('(dmesg; sysctl; route -n show; df; ifconfig -A; hostname) | cksum -q -a sha256 -');

	$cryptKey= pack('H*', $random);

	$iv_size= openssl_cipher_iv_length('AES-256-CBC');
	$iv= openssl_random_pseudo_bytes($iv_size);

	$ciphertext= openssl_encrypt($passwd, 'AES-256-CBC', $cryptKey, OPENSSL_RAW_DATA, $iv);
	if (!$ciphertext) {
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'openssl_encrypt failed: '.openssl_error_string());
		exit;
	}
	$ciphertext= $iv.$ciphertext;

	$ciphertext_base64= base64_encode($ciphertext);

	// Save password to the cookie
	setcookie('passwd', $ciphertext_base64);

	// Save key to the session, so we can decrypt the password from the cookie later on to log in to the SSH server
	$_SESSION['cryptKey']= $cryptKey;

	// Update session timeout now, otherwise in the worst case scenario, vars.php may log user out on very close session timeout
	$_SESSION['Timeout']= time() + $SessionTimeout;

	// Reset the pf session if the user changes
	if (isset($_SESSION['PREVIOUS_USER']) && $_SESSION['PREVIOUS_USER'] !== $_SESSION['USER']) {
		/// @todo Should we reset everything else, other than USER and Timeout?
		unset($_SESSION['pf']);
	}
	
	header('Location: /system/dashboard.php');
	exit;
}

/**
 * Authenticates firewall user with the password supplied.
 * 
 * Note that this function is used outside of the WUI, and
 * we cannot run Controller commands over SSH without logging
 * in as a WUI user (www is not a WUI user).
 * Therefore, we have to disable $UseSSH in this function.
 */
function UserDbAuth($user, $passwd, $desc)
{
	global $View, $UseSSH;

	wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Login attempt');

	$UseSSH= FALSE;

	if (!CheckUserDbUser($user)) {
		return FALSE;
	}

	if (!AuthUserDbUser($user, $passwd)) {
		return FALSE;
	}

	$ip= filter_input(INPUT_SERVER, 'REMOTE_ADDR');

	if ($View->Controller($output, 'GetEther', $ip) == FALSE) {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Cannot get ethernet address of IP'), 'auto', 'ERROR');
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot get ether of ip');
		return FALSE;
	}
	$ether= $output[0];

	// Remove any/all double quotes, because we use double quotes around desc arg in sql statements
	$desc= str_replace('"', '', $desc);
	if ($View->Controller($output, 'UpdateUser', $ip, $user, $ether, $desc) == FALSE) {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Cannot update user'), 'auto', 'ERROR');
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot update user');
		return FALSE;
	}

	// If redirection does not work, the help window is displayed
	PrintHelpWindow(_NOTICE('Authentication succeeded, now you can access the Internet'), 'auto', 'INFO');
	wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Authentication succeeded');
	return TRUE;
}

function UserDbLogout($user, $passwd)
{
	global $View, $UseSSH;

	wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Logout attempt');

	$UseSSH= FALSE;

	if (!CheckUserDbUser($user)) {
		return FALSE;
	}

	if (!AuthUserDbUser($user, $passwd)) {
		return FALSE;
	}

	$ip= filter_input(INPUT_SERVER, 'REMOTE_ADDR');

	if ($View->Controller($output, 'GetEther', $ip) == FALSE) {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Cannot get ethernet address of IP'), 'auto', 'ERROR');
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot get ether of ip');
		return FALSE;
	}
	$ether= $output[0];

	if ($View->Controller($output, 'LogUserOut', $ip, $user, $ether) == FALSE) {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Cannot log user out'), 'auto', 'ERROR');
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot log user out');
		return FALSE;
	}

	PrintHelpWindow(_NOTICE('User logged out'), 'auto', 'INFO');
	wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'User logged out');
	return TRUE;
}

function UserDbChangePasswd($user, $passwd, $newpasswd, $newpasswdagain)
{
	global $View, $UseSSH;

	wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Userdb password change');

	$UseSSH= FALSE;

	if (!CheckUserDbUser($user)) {
		return FALSE;
	}

	if (!AuthUserDbUser($user, $passwd)) {
		return FALSE;
	}

	if (!CheckPasswordsMatch($user, $newpasswd, $newpasswdagain)) {
		return FALSE;
	}

	if (!ValidatePassword($user, $newpasswd)) {
		return FALSE;
	}

	// Encrypt passwords before passing down, plaintext passwords should never be visible, not even in the doas logs
	if ($View->Controller($output, 'SetPassword', $user, sha1($newpasswd))) {
		PrintHelpWindow(_NOTICE('User password changed').': '.$user);
		wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "User password changed: $user");
	} else {
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Password change failed: $user");
		return FALSE;
	}
	return TRUE;
}

function CheckUserDbUser($user, $justcheck= FALSE)
{
	global $View;

	// $UseSSH must be set to FALSE by the caller
	$View->Controller($output, 'GetUsers');
	$users= json_decode($output[0], TRUE);
	if ($users === NULL || !is_array($users)) {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Users not a valid json or array'), 'auto', 'ERROR');
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Users not a valid json or array');
		return FALSE;
	}

	if (!in_array($user, array_keys($users))) {
		if (!$justcheck) {
			// Invalid user box should look similar to password mismatch one
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Authentication failed'), 'auto', 'ERROR');
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Not a valid user');
			// Throttle authentication failures
			exec('/bin/sleep 5');
		}
		return FALSE;
	}
	return TRUE;
}

function AuthUserDbUser($user, $passwd)
{
	global $View;

	// Encrypt passwords before passing down, plaintext passwords should never be visible, not even in the doas logs
	if (!$View->CheckUserDbAuthentication($user, sha1($passwd))) {
		wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Password mismatch');
		// Throttle authentication failures
		exec('/bin/sleep 5');
		return FALSE;
	}
	return TRUE;
}

function CheckPasswordsMatch($user, $passwd, $passwdagain)
{
	if ($passwd !== $passwdagain) {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Passwords do not match'), 'auto', 'ERROR');
		wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Passwords do not match: $user");
		return FALSE;
	}
	return TRUE;
}

function ValidatePassword($user, $passwd)
{
	if (!preg_match('/^\w{8,}$/', $passwd)) {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Not a valid password'), 'auto', 'ERROR');
		wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not a valid password: $user");
		return FALSE;
	}
	return TRUE;
}

/**
 * HTML Header.
 *
 * @param string $color Page background, Login page uses gray.
 * @param int $reloadrate Page reload rate, defaults to 0 (no reload)
 */
function HTMLHeader($color= 'white', $reloadRate= 0)
{
	global $LOCALES;
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo _MENU('UTM Firewall') ?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo $LOCALES[$_SESSION['Locale']]['Codeset'] ?>" />
		<meta name="description" content="UTM Firewall" />
		<meta name="author" content="Soner Tari"/>
		<meta name="keywords" content="UTM, Firewall, :)" />
		<link rel="stylesheet" href="../utmfw.css" type="text/css" media="screen" />
		<?php
		if ($reloadRate !== 0) {
			?>
			<meta http-equiv="refresh" content="<?php echo $reloadRate ?>" />
			<?php
		}
		?>
	</head>
	<body style="background: <?php echo $color ?>">
<?php
}

/**
 * Sets session submenu variable.
 *
 * @param string $default Default submenu selected
 * @return string Selected submenu
 */
function SetSubmenu($default)
{
	global $View, $Menu, $TopMenu;

	$page= basename($_SERVER['PHP_SELF']);

	if (filter_has_var(INPUT_GET, 'submenu')) {
		$submenu= filter_input(INPUT_GET, 'submenu');
		if (array_key_exists($submenu, $Menu[$TopMenu]['SubMenu'])) {
			$_SESSION[$View->Model][$TopMenu]['submenu']= $submenu;
		}
		else {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "No such submenu for $View->Model>$TopMenu: " . $submenu);
			echo _TITLE('Resource not available').": $TopMenu.php?submenu=" . $submenu;
			exit(1);
		}
	}

	if ($_SESSION[$View->Model][$TopMenu]['submenu']) {
		$submenu= $_SESSION[$View->Model][$TopMenu]['submenu'];
	}
	else {
		$submenu= $default;
	}

	$_SESSION[$View->Model][$TopMenu]['submenu']= $submenu;
	return $submenu;
}

/**
 * Sets session topmenu variable.
 *
 * View object does not exists yet when this function is called
 * in index.php files, hence the $view parameter.
 *
 * @param string $view Module name
 * @param string $default Default topmenu selected
 * @return string Selected topmenu
 */
function SetTopMenu($view, $default= 'info')
{
	if ($_SESSION[$view]['topmenu']) {
		$topmenu= $_SESSION[$view]['topmenu'];
	}
	else {
		$topmenu= $default;
	}

	$_SESSION[$view]['topmenu']= $topmenu;
	return $topmenu.'.php';
}
?>
