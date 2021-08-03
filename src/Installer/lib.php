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

/** @file
 * Installer library.
 */

/**
 * Satisfies Controller.
 * 
 * This is due to common code used by all. Otherwise, the Controller cannot display help windows.
 */
function PrintHelpWindow($msg, $width= 'auto', $type= 'INFO')
{
	$msg= preg_replace('?<br\s*(|/)>?', ' ', $msg);
	echo "$type: $msg\n";
}

/**
 * Applies configuration.
 * 
 * @return bool TRUE on success, FALSE on fail.
 */
function ApplyConfig()
{
	global $Config, $View, $Re_Ip;

	try {
		$myname= $Config['Myname'];
		$mygate= $Config['Mygate'];
		
		$lanif= $Config['IntIf'];
		$wanif= $Config['ExtIf'];

		$lanip= $Config['Ifs'][$lanif][1];
		$lanmask= $Config['Ifs'][$lanif][2];

		ComputeIfDefs($lanip, $lanmask, $lannet, $lanbc, $lancidr);

		$View->Model= 'pf';
		if (!$View->Controller($output, 'SetIfs', $lanif, $wanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pf interfaces: $lanif, $wanif");
		}

		if (!$View->Controller($output, 'SetIntnet', $lancidr)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pf internal net: $lancidr");
		}

		if (!$View->Controller($output, 'SetAfterhoursIf', $lanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pf afterhours interface: $lanif");
		}

		if (!$View->Controller($output, 'AddAllowedIp', $lancidr)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pf restricted ip: $lancidr");
		}

		$View->Model= 'dnsmasq';
		if (! $View->Controller($output, 'SetListenOn', $lanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting listen-on: $lanif");
		}

		$View->Model= 'system';
		$host= "$lanip	$myname ".explode(".", $myname)[0];
		if (!$View->Controller($output, 'AddHost', $host)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting hosts: $host");
		}

		if (!$View->Controller($output, 'SetManCgiHome', $lanip)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting man.cgi home: $lanip");
		}

		$View->Model= 'sslproxy';
		if (!$View->Controller($output, 'SetUserAuthURL', $lanip)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting userauth url: $lanip");
		}

		$View->Model= 'dhcpd';
		ComputeDhcpdIpRange($lanip, $lannet, $lanbc, $min, $max);
		if (!$View->Controller($output, 'SetDhcpdConf', $lanip, $lanmask, $lannet, $lanbc, $min, $max)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting dhcpd configuration: $lanip, $lanmask, $lannet, $lanbc, $min, $max");
		}

		if (!$View->Controller($output, 'AddIf', $lanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting dhcpd interface: $lanif");
		}

		$View->Model= 'snort';
		if (!$View->Controller($output, 'SetStartupIfs', $lanif, $wanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting snort interfaces: $lanif, $wanif");
		}

		$View->Model= 'spamd';
		if (!$View->Controller($output, 'SetStartupIf', $wanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting spamlogd interfaces: $wanif");
		}

		$View->Model= 'dante';
		if (!$View->Controller($output, 'SetIfs', $lanif, $wanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting dante interfaces: $lanif, $wanif");
		}

		if (!$View->Controller($output, 'SetIntnet', $lancidr)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting dante internal net: $lancidr");
		}

		$View->Model= 'smtp-gated';
		if (!$View->Controller($output, 'SetConfValue', 'proxy_name', $myname, '', '')) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting smtp-gated proxy_name: $myname");
		}

		$View->Model= 'e2guardian';
		if (!$View->Controller($output, 'SetTemplateIps', $lanip)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting e2guardian template ips: $lanip");
		}

		if (!$View->Controller($output, 'SetUserFilterGrp', 'utmfw', '1')) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed adding e2guardian network user utmfw to first group');
		}

		$View->Model= 'snortips';
		if (!$View->Controller($output, 'AddAllowedIp', $lanip)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting snortips whitelist: $lanip");
		}

		if (preg_match("/^$Re_Ip$/", $wanip)) {
			if (!$View->Controller($output, 'AddAllowedIp', $wanip)) {
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting snortips whitelist: $wanip");
			}
		}

		if (preg_match("/^$Re_Ip$/", $mygate)) {
			if (!$View->Controller($output, 'AddAllowedIp', $mygate)) {
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting snortips whitelist: $mygate");
			}
		}

		$View->Model= 'pmacct';
		if (!$View->Controller($output, 'SetIf', $lanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pmacct if: $lanif");
		}

		if (!$View->Controller($output, 'SetNet', $lancidr)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pmacct network: $lancidr");
		}

		$View->Model= 'symon';
		if (!$View->Controller($output, 'SetCpus')) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed setting symon cpus');
		}

		if (!$View->Controller($output, 'SetIfs', $lanif, $wanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting symon ifs: $lanif, $wanif");
		}

		if (!$View->Controller($output, 'SetPartitions')) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed setting symon partitions');
		}

		if (!$View->Controller($output, 'SetSensors')) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed setting symon sensors');
		}

		if (!$View->Controller($output, 'SetConf', $lanif, $wanif)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed setting symon conf');
		}

		$View->Model= 'collectd';
		if (!$View->Controller($output, 'SetGatewayPingHost', $mygate)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting gateway ping host: $mygate");
		}

		return TRUE;
	}
	catch (Exception $e) {
		echo 'Caught exception: ', $e->getMessage(), "\n";
		return FALSE;
	}
}

/**
 * Computes network, broadcast, and CIDR net addresses, given ip and netmask.
 *
 * Quoting from nice explanations here:
 * http://downloads.openwrt.org/people/mbm/network
 *
 * if we take the ip and netmask and do an AND: (hint, AND the columns)
 *
 *       11000000 10101000 00000001 00001011 = 192.168.1.11 (some ip address)
 *       11111111 11111111 11111111 11110000 = 255.255.255.240 (netmask)
 *  AND: 11000000 10101000 00000001 00000000 = 192.168.1.0 (network address)
 *
 *  This gives our network address, the lowest address in the subnet
 *  Now, flip the netmask: (hint, NOT the columns)
 *
 *       11111111 11111111 11111111 11110000 = 255.255.255.240 (netmask)
 *  NOT: 00000000 00000000 00000000 00001111 = 0.0.0.15 (NOT 255.255.255.240)
 *
 *  then OR this with the network address: (hint, OR the columns)
 *
 *       11000000 10101000 00000001 00000000 = 192.168.1.0 (network address)
 *       00000000 00000000 00000000 00001111 = 0.0.0.15 (NOT 255.255.255.240)
 *  OR:  11000000 10101000 00000001 00001111 = 192.168.1.15 (broadcast address)
 *
 * @param string $ip IPv4 address.
 * @param string $mask Netmask.
 * @param string $net Network address.
 * @param string $bc Broadcast address.
 * @param string $cidr CIDR.
 */
function ComputeIfDefs($ip, $mask, &$net, &$bc, &$cidr)
{
	global $Re_Ip;
	
	if (preg_match("/^$Re_Ip$/", $ip) && preg_match("/^$Re_Ip$/", $mask)) {
		$net= long2ip(ip2long($ip) & ip2long($mask));
		$bc= long2ip(ip2long($net) | ~ip2long($mask));
		$cidr= $net.'/'.(32 - round(log(sprintf("%u", ip2long('255.255.255.255')) - sprintf("%u", ip2long($mask)), 2)));
	}
}

/**
 * Computes a DHCP IP range.
 *
 * This function provides a guess only.
 *
 * @param string $ip System internal ip.
 * @param string $net System local network.
 * @param string $bc System broadcast address to guess max range.
 * @param string $min DHCP IP range min.
 * @param string $max DHCP IP range max.
 * @return bool TRUE on success, FALSE on fail.
 */
function ComputeDhcpdIpRange($ip, $net, $bc, &$min, &$max)
{
	if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3})\.(\d{1,3})/', $net, $match)) {
		$minnet= $match[1];
		$minoct= $match[2];
		$min= $minnet.'.'.($minoct + 1);

		// Avoid clash with system internal IP
		if ($ip === $min) {
			$min= $minnet.'.'.($minoct + 2);
		}
	
		if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3})\.(\d{1,3})/', $bc, $match)) {
			$maxnet= $match[1];
			$maxoct= $match[2];
			$max= $maxnet.'.'.($maxoct - 1);

			// Avoid clash with system internal IP
			if ($ip === $max) {
				$max= $maxnet.'.'.($maxoct - 2);
			}
			return TRUE;
		}
	}
	return FALSE;
}

/**
 * Initializes interfaces.
 * 
 * @return bool TRUE on success, FALSE on fail.
 */
function InitIfs()
{
	global $Config;

	if (!isset($Config['Ifs'])) {
		$Config['Ifs']= array();
	}
	$Ifs= array_keys($Config['Ifs']);
	
	if (count($Ifs) > 0) {
		// Necessary during first install with lan0/wan0 in pf.conf
		if (!in_array($Config['IntIf'], $Ifs)) {
			$Config['IntIf']= $Ifs[0];
		}
		
		if (count($Ifs) > 1) {
			if (!in_array($Config['ExtIf'], $Ifs)) {
				$Config['ExtIf']= $Ifs[1];
			}
		}
		else {
			$Config['ExtIf']= $Config['IntIf'];
			wui_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, 'WARNING: Found only one interface, assigned internal to external if');
		}
		return TRUE;
	}
	wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'ERROR: Expected at least one interface, found: '.count($Ifs));
	return FALSE;
}

/**
 * Asks user for internal and external interface selection.
 */
function GetIfSelection()
{
	global $Config;

	$ifs= array_keys($Config['Ifs']);
	$iflist= implode(', ', $ifs);
	$ifcount= count($ifs);
	
	while (TRUE) {
		// Reset to system values
		$lanif= $Config['IntIf'];
		$wanif= $Config['ExtIf'];
		
		if (!isset($lanif) || (($ifcount > 1) && ($lanif === $wanif))) {
			$lanif= $ifs[0] === $wanif ? $ifs[1] : $ifs[0];
		}
		PrintIfConfig($lanif, $wanif);
		
		$selection= ReadSelection("Internal interface ($iflist or enter) [$lanif] ", $ifs);
		if ($selection !== '') {
			$lanif= $selection;
		}

		// Fix wan if necessary
		if (!isset($wanif) || (($ifcount > 1) && ($lanif === $wanif))) {
			$wanif= $ifs[0] === $lanif ? $ifs[1] : $ifs[0];
		}
		PrintIfConfig($lanif, $wanif);
		
		$selection= ReadSelection("External interface ($iflist or enter) [$wanif] ", $ifs);
		if ($selection !== '') {
			$wanif= $selection;
		}

		$warn= PrintIfConfig($lanif, $wanif);
		
		$selection= readline2("Type 'done' to accept or press enter to try again: ");
		if ($selection === 'done') {
			break;
		}
		echo "\n";
	}
	
	if ($warn) {
		echo "\nProceeding with warnings...\n";
	}
	
	$Config['IntIf']= $lanif;
	$Config['ExtIf']= $wanif;
}

/**
 * Prints current internal/external interface selections.
 *
 * @param string $lanif Internal if.
 * @param string $wanif External if.
 * @return bool Whether the user should be warned or not.
 */
function PrintIfConfig($lanif, $wanif)
{
	global $Config;

	$warn= FALSE;
	
	echo "\nInterface assignment:\n";
	$lanconfig= trim(implode(' ', array_slice($Config['Ifs'][$lanif], 0, 5)));
	$lanconfig= $lanconfig === '' ? 'not configured':$lanconfig;
	echo "  internal= $lanif ($lanconfig)\n";
	$wanconfig= trim(implode(' ', array_slice($Config['Ifs'][$wanif], 0, 5)));
	$wanconfig= $wanconfig === '' ? 'not configured':$wanconfig;
	echo "  external= $wanif ($wanconfig)\n\n";
	
	if (($lanconfig == 'not configured') || ($wanconfig == 'not configured')) {
		echo "WARNING: There are unconfigured interfaces\n";
		$warn= TRUE;
	}
	
	if ($lanif === $wanif) {
		echo "WARNING: Internal and external interfaces are the same\n";
		$warn= TRUE;
	}

	if (isset($Config['Ifs'][$lanif][0]) && $Config['Ifs'][$lanif][0] === 'dhcp') {
		echo "WARNING: Internal interface is configured as dhcp\n";
		$warn= TRUE;
	}
	return $warn;
}

function EnableHostap()
{
	global $View, $Config;

	// In case
	$View->Model= 'system';
	$intif= $Config['IntIf'];

	exec("ifconfig $intif 2>/dev/null | grep -q \"^[[:space:]]*ieee80211:\"", $output, $retval);

	if ($retval === 0) {
		echo "\nInternal interface $intif is a Wi-fi interface";

		$driver= rtrim($intif, '0..9');
		$selection= ReadSelection("\nEnable hostap on $intif (make sure $driver(4) supports Host AP mode)? [yes] ", array('yes', 'no'));
		if ($selection === '' || $selection === 'yes') {
			if (!$View->Controller($Output, 'EnableHostap', $intif)) {
				$msg= "Failed enabling hostap on $intif";
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, $msg);
				echo "\n$msg.\n";
			} else {
				echo "\nHostap enabled on $intif.\n";
			}
		}
	}
}

/**
 * Prompts for and reads user selection.
 *
 * @param string $prompt Message to display.
 * @param array $opts Valid options.
 * @return string User input.
 */
function ReadSelection($prompt, $opts)
{
	while (TRUE) {
		$selection= readline2($prompt);
		if (($selection === '') || in_array($selection, $opts)) {
			return $selection;
		}
		echo "\nInvalid choice\n";
	}
}

/**
 * Reads a line of input from stdin.
 *
 * @param string $prompt Message to display.
 * @return string User input, no newlines.
 */
function readline2($prompt= '')
{
    echo $prompt;
    return rtrim(fgets(STDIN), "\n");
}

/**
 * Create admin and user.
 *
 * Password should have at least 8 alphanumeric chars.
 */
function CreateUsers()
{
	global $View;
	
	// In case
	$View->Model= 'system';
	
	echo "\nPassword for WUI users 'admin' and 'user', and SSLproxy user 'utmfw'\n";
	echo "To use the WUI, log in as 'admin' or 'user' with this password\n";
	echo "To access the Internet, log in as 'utmfw' with the same password\n";
	echo "You can change user passwords and add/delete network users on the WUI:\n";
	
	while (TRUE) {
		echo "Password? (will not echo) ";
		$passwd= AskPass();
		
		echo "\nPassword? (again) ";
		if ($passwd === AskPass()) {
			if (preg_match('/^\w{8,}$/', $passwd)) {
				echo "\n";

				$sha1Passwd= sha1($passwd);

				// Update admin password
				if ($View->Controller($output, 'CreateUser', 'admin', $sha1Passwd, 1000)) {
					wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'User created: admin');
					// Update user password
					if ($View->Controller($output, 'CreateUser', 'user', $sha1Passwd, 1001)) {
						echo "Successfully created WUI users: 'admin' and 'user'.\n\n";
						wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'User created: user');
					}
					else {
						echo "\nUser create failed: user.\n";
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'User create failed: user');
					}
				}
				else {
					echo "\nUser create failed: admin.\n";
					wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'User create failed: admin');
				}

				if ($View->Controller($output, 'AddUser', 'utmfw', $sha1Passwd, 'UTMFW network user')) {
					echo "Successfully created network user 'utmfw'.\n\n";
					wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'User created: utmfw');
				}
				else {
					echo "\nUser create failed: utmfw.\n";
					wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'User create failed: utmfw');
				}
				// Always break out if password is entered correctly
				break;
			}
			else {
				echo "\nERROR: Choose a password with at least 8 alphanumeric characters.\n";
			}
		}
		else {
			echo "\nERROR: Passwords do not match.\n";
		}
	}
}

function GenerateSSLKeyPairs()
{
	global $View;

	// In case
	$View->Model= 'system';

	echo "Generating the SSL key pairs for httpd, openvpn, and sslproxy\n";
	$serial= '1';
	$n= readline2("Set serial to? [1] ");
	if (preg_match('/^\d{1,4}$/', $n)) {
		$serial= $n;
	} else if ($n !== '') {
		echo "\nInvalid serial\n";
	}
	echo "Setting serial to $serial\n";

	if (!$View->Controller($Output, 'GenerateSSLKeyPairs', $serial)) {
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed generating ssl key pairs');
	}
	echo "\nYou can generate the SSL key pairs on the WUI too.\n";
}

function ConfigMFS()
{
	global $View;

	// In case
	$View->Model= 'system';

	echo "\nIf the system has enough memory, you can mount /var/log as MFS";
	$selection= ReadSelection("\nEnable MFS? [yes] ", array('yes', 'no'));

	if ($selection === '') {
		$selection= 'yes';
	}

	if (!$View->Controller($Output, 'SetMFS', $selection)) {
		$msg= "Failed setting MFS to $selection";
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, $msg);
		echo "\n$msg.\n";
	} else {
		if ($selection == 'yes') {
			echo "\nMFS /var/log enabled.\n";
		} else {
			echo "\nMFS /var/log disabled.\n";
		}
	}

	$MFSConfig= FALSE;
	if ($View->Controller($Output, 'GetMFSConfig')) {
		$MFSConfig= json_decode($Output[0], TRUE);
	}

	if (!$MFSConfig) {
		echo "\nCannot configure MFS\n";
		return;
	}

	$mfs_enabled= $MFSConfig['enable'] == 'yes';

	if ($mfs_enabled) {
		$size= $MFSConfig['size'];

		echo "\nMFS size of 1024m or more is recommended\n";

		$s= readline2("Set MFS size to (you can use k/m/g or K/M/G suffix)? [$size] ");
		if (preg_match('/^\d+[kKmMgG]*$/', $s)) {
			$size= $s;
		} else if ($s !== '') {
			echo "\nInvalid MFS size\n";
		}
		echo "\nSetting MFS size to $size\n";

		if (!$View->Controller($Output, 'SetMFSSize', $size)) {
			$msg= "Failed setting MFS size to $size";
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, $msg);
			echo "\n$msg.\n";
		}

		echo "\nMFS /var/log can be set to persist, so its contents are not lost on shutdown\n";
		echo "Note that syncing /var/log to disk can take some time";
		$selection= ReadSelection("\nEnable persistent MFS? [yes] ", array('yes', 'no'));

		if ($selection === '') {
			$selection= 'yes';
		}

		if (!$View->Controller($Output, 'SetSyncMFS', $selection)) {
			$msg= "Failed setting sync MFS to $selection";
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, $msg);
			echo "\n$msg.\n";
		} else {
			if ($selection == 'yes') {
				echo "\nPersistent MFS enabled.\n";
			} else {
				echo "\nPersistent MFS disabled.\n";
			}
		}
	}
	echo "\n";
}

/**
 * Reads typed chars without echo.
 *
 * @return string exec() return value is the last line of shell cmd output, i.e. user input
 */
function AskPass()
{
	return exec('set -o noglob; stty -echo; read resp; stty echo; set +o noglob; echo $resp');
}
?>
