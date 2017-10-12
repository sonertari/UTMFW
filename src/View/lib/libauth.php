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
			$log= "$LOG_PRIOS[$prio] $useratip $file: $func ($line): $msg\n";
			if (!syslog($prio, $log)) {
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
 * - mcrypt_*() while saving as a cookie var.
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

	$cryptKey = pack('H*', $random);

	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

	$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $cryptKey, $passwd, MCRYPT_MODE_CBC, $iv);
	$ciphertext = $iv . $ciphertext;

	$ciphertext_base64 = base64_encode($ciphertext);

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
