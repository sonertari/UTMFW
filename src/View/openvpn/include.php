<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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

require_once('../lib/vars.php');

$Menu = array(
	'info' => array(
		'Name' => _MENU('Info'),
		'Perms' => $ALL_USERS,
		),
	'graphs' => array(
		'Name' => _MENU('Graphs'),
		'Perms' => $ALL_USERS,
		),
	'logs' => array(
		'Name' => _MENU('Logs'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'archives' => _MENU('Archives'),
			'live' => _MENU('Live'),
			),
		),
	'conf' => array(
		'Name' => _MENU('Config'),
		'Perms' => $ADMIN,
		),
	);

$LogConf = array(
	'openvpn' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'REs' => array(
				'red' => array('Failed'),
				'yellow' => array('WARNING'),
				'green' => array('Accepted'),
				),
			),
		),
	);

class Openvpn extends View
{
	public $Model= 'openvpn';
	public $Layout= 'openvpn';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('OpenVPN');

		$this->LogsHelpMsg= _HELPWINDOW('Messages from all OpenVPN processes are recorded in the same log file.');
		$this->ConfHelpMsg= _HELPWINDOW('Sample client and server configuration are provided to get you started. You may want to replace the default SSL certificates with yours.');
	}

	/**
	 * Sets session config file, ModelConfig and ModelConfigName params.
	 *
	 * @param string $file Config file
	 */
	function SetConfig($file)
	{
		global $ViewConfigName, $ClientConfig, $ServerConfig;

		if (isset($file)) {
			$_SESSION[$this->Model][basename($_SERVER['PHP_SELF'])]['ConfFile']= $file;
			
			if ($this->Controller($output, 'IsClientConf', $file)) {
				$this->Config= $ClientConfig;
			}
			else {
				$this->Config= $ServerConfig;
			}
			$ViewConfigName= $file;
		}
	}

	/**
	 * General form for selecting, deleting, and copying an OpenVPN conf file.
	 *
	 * @param string $module Module name, the caller
	 */
	function ConfSelectForm($module)
	{
		global $ConfigFile;

		$deleteconfirm= _NOTICE('Are you sure you want to delete the configuration?');

		if ($this->Controller($conffiles, 'GetConfs')) {
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="ConfFile">
				<?php
				$conffileexists= FALSE;
				foreach ($conffiles as $file) {
					$selected= '';
					if ($ConfigFile === basename($file)) {
						$selected= ' selected';
						$conffileexists= TRUE;
					}
					?>
					<option value="<?php echo basename($file) ?>"<?php echo $selected ?>><?php echo basename($file) ?></option>
					<?php
				}

				if (!$conffileexists && (count($conffiles) > 0)) {
					$ConfigFile= basename($conffiles[0]);
					$this->SetConfig($ConfigFile);
				}
				?>
				</select>
				<input type="submit" name="Select" value="<?php echo _CONTROL('Select') ?>"/>
				<br />
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo $deleteconfirm ?>')"/>
				<br />
				<input type="text" name="CopyTo" style="width: 150px;" maxlength="100" value="<?php echo basename($ConfigFile) ?>"/>
				<input type="submit" name="Copy" value="<?php echo _CONTROL('Copy') ?>"/>
			</form>
			<?php
		}
	}

	/**
	 * General form for selecting an OpenVPN conf file to run.
	 */
	function ConfStartStopForm()
	{
		$startconfirm= _NOTICE('Are you sure you want to start the <NAME>?');
		$startconfirm= preg_replace('/<NAME>/', $this->Caption, $startconfirm);

		$stopconfirm= _NOTICE('Are you sure you want to stop the <NAME>?');
		$stopconfirm= preg_replace('/<NAME>/', $this->Caption, $stopconfirm);

		if ($this->Controller($conffiles, 'GetConfs')) {
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="ConfFiles[]" multiple style="width: 150px; height: 100px;">
					<?php
					foreach ($conffiles as $file) {
						?>
						<option value="<?php echo basename($file) ?>"><?php echo basename($file) ?></option>
						<?php
					}
					?>
				</select>
				<br />
				<input type="submit" name="Start" value="<?php echo _CONTROL('Start') ?>" onclick="return confirm('<?php echo $startconfirm ?>')"/>
				<input type="submit" name="Stop" value="<?php echo _CONTROL('Stop') ?>" onclick="return confirm('<?php echo $stopconfirm ?>')"/>
			</form>
			<?php
		}
	}
}

$View= new Openvpn();

/**
 * Sever configuration.
 */
$ServerConfig = array(
	'local' => array(
		'title' => _TITLE2('Local IP address'),
		'info' => _HELPBOX2('Which local IP address should OpenVPN listen on? (optional).'),
		),
	'ifconfig' => array(
		'title' => _TITLE2('Interface Configuration'),
		'info' => _HELPBOX2('Local and remote VPN endpoint IP addresses.'),
		),
	'route' => array(
		'title' => _TITLE2('Route'),
		'info' => _HELPBOX2('Rule to be added to the routing table.'),
		),
	'port' => array(
		'title' => _TITLE2('Port'),
		'info' => _HELPBOX2('Which TCP/UDP port should OpenVPN listen on? If you want to run multiple OpenVPN instances on the same machine, use a different port number for each one.  You will need to open up this port on your firewall.'),
		),
	'proto' => array(
		'title' => _TITLE2('Protocol'),
		'info' => _HELPBOX2('TCP or UDP server?'),
		),
	'dev' => array(
		'title' => _TITLE2('Device'),
		'info' => _HELPBOX2('"dev tun" will create a routed IP tunnel, "dev tap" will create an ethernet tunnel. Use "dev tap0" if you are ethernet bridging and have precreated a tap0 virtual interface and bridged it with your ethernet interface. If you want to control access policies over the VPN, you must create firewall rules for the the TUN/TAP interface.'),
		),
	'ca' => array(
		'title' => _TITLE2('Root Certificate'),
		'info' => _HELPBOX2('SSL/TLS root certificate (ca), certificate (cert), and private key (key).  Each client and the server must have their own cert and key file. The server and all clients will use the same ca file.
Any X509 key management system can be used. OpenVPN can also use a PKCS #12 formatted key file (see "pkcs12" directive in man page).'),
		),
	'cert' => array(
		'title' => _TITLE2('Certificate'),
		),
	'key' => array(
		'title' => _TITLE2('Private Key'),
		),
	'dh' => array(
		'title' => _TITLE2('Diffie-Hellman params'),
		'info' => _HELPBOX2('Generate your own with:
openssl dhparam -out dh1024.pem 1024
Substitute 2048 for 1024 if you are using 2048 bit keys.'),
		),
	'topology' => array(
		'title' => _TITLE2('Network Topology'),
		'info' => _HELPBOX2('Should be subnet (addressing via IP)
unless Windows clients v2.0.9 and lower have to
be supported (then net30, i.e. a /30 per client)
Defaults to net30 (not recommended).'),
		),
	'cipher' => array(
		'title' => _TITLE2('Cipher'),
		'info' => _HELPBOX2('Select a cryptographic cipher. This config item must be copied to the client config file as well.'),
		),
	'server' => array(
		'title' => _TITLE2('Server Mode'),
		'info' => _HELPBOX2('Configure server mode and supply a VPN subnet for OpenVPN to draw client addresses from. Comment this line out if you are ethernet bridging. See the man page for more info.'),
		),
	'tls-server' => array(
		'title' => _TITLE2('Enable SSL/TLS'),
		'info' => _HELPBOX2('Configure server to use SSL/TLS.'),
		),
	'tls-auth' => array(
		'title' => _TITLE2('TLS Auth'),
		'info' => _HELPBOX2('For extra security beyond that provided by SSL/TLS, create an "HMAC firewall" to help block DoS attacks and UDP port flooding.

Generate with:
  openvpn --genkey --secret ta.key

The server and each client must have a copy of this key. The second parameter should be 0 on the server and 1 on the clients. This file is secret.'),
		),
	'keepalive' => array(
		'title' => _TITLE2('Keep Alive'),
		'info' => _HELPBOX2('The keepalive directive causes ping-like messages to be sent back and forth over the link so that each side knows when the other side has gone down. If for example "10 120", ping every 10 seconds, assume that remote peer is down if no ping received during a 120 second time period.'),
		),
	'comp-lzo' => array(
		'title' => _TITLE2('Compression'),
		'info' => _HELPBOX2('Enable compression on the VPN link. If you enable it here, you must also enable it in the client config file.'),
		),
	'persist-key' => array(
		'title' => _TITLE2('Persist Key'),
		'info' => _HELPBOX2('The persist options will try to avoid accessing certain resources on restart that may no longer be accessible because of the privilege downgrade.'),
		),
	'persist-tun' => array(
		'title' => _TITLE2('Persist Tun'),
		),
	'max-clients' => array(
		'title' => _TITLE2('Max Clients'),
		'info' => _HELPBOX2('The maximum number of concurrently connected clients we want to allow.'),
		),
	'verb' => array(
		'title' => _TITLE2('Verbosity'),
		'info' => _HELPBOX2('Set the appropriate level of log file verbosity.
0 is silent, except for fatal errors
4 is reasonable for general usage
5 and 6 can help to debug connection problems
9 is extremely verbose'),
		),
	'ping' => array(
		'title' => _TITLE2('Ping'),
		'info' => _HELPBOX2('Send a UDP ping to remote once every given seconds to keep stateful firewall connection alive.'),
		),
	);

/**
 * Client configuration.
 */
$ClientConfig = array(
	'remote' => array(
		'title' => _TITLE2('Remote Server'),
		'info' => _HELPBOX2('Use the same setting as you are using on the server. On most systems, the VPN will not function unless you partially or fully disable the firewall for the TUN/TAP interface.'),
		),
	'ifconfig' => array(
		'title' => _TITLE2('Interface Configuration'),
		'info' => _HELPBOX2('Local and remote VPN endpoint IP addresses.'),
		),
	'route' => array(
		'title' => _TITLE2('Route'),
		'info' => _HELPBOX2('Rule to be added to the routing table.'),
		),
	'proto' => array(
		'title' => _TITLE2('Protocol'),
		'info' => _HELPBOX2('Are we connecting to a TCP or UDP server?  Use the same setting as on the server.'),
		),
	'nobind' => array(
		'title' => _TITLE2('No bind'),
		'info' => _HELPBOX2('Most clients don\'t need to bind to a specific local port number.'),
		),
	'dev' => array(
		'title' => _TITLE2('Device'),
		'info' => _HELPBOX2('Use the same setting as you are using on the server. On most systems, the VPN will not function unless you partially or fully disable the firewall for the TUN/TAP interface.'),
		),
	'ca' => array(
		'title' => _TITLE2('Root Certificate'),
		'info' => _HELPBOX2('SSL/TLS parms. See the server config file for more description.  It\'s best to use a separate .crt/.key file pair for each client.  A single ca file can be used for all clients.'),
		),
	'cert' => array(
		'title' => _TITLE2('Certificate'),
		),
	'key' => array(
		'title' => _TITLE2('Private Key'),
		),
	'cipher' => array(
		'title' => _TITLE2('Cipher'),
		'info' => _HELPBOX2('Select a cryptographic cipher. If the cipher option is used on the server then you must also specify it here.'),
		),
	'tls-client' => array(
		'title' => _TITLE2('Enable SSL/TLS'),
		'info' => _HELPBOX2('Configure client to use SSL/TLS.'),
		),
	'tls-auth' => array(
		'title' => _TITLE2('TLS Auth'),
		'info' => _HELPBOX2('If a tls-auth key is used on the server then every client must also have the key.'),
		),
	'comp-lzo' => array(
		'title' => _TITLE2('Compression'),
		'info' => _HELPBOX2('Enable compression on the VPN link. Don\'t enable this unless it is also enabled in the server config file.'),
		),
	'persist-key' => array(
		'title' => _TITLE2('Persist Key'),
		'info' => _HELPBOX2('Try to preserve some state across restarts.'),
		),
	'persist-tun' => array(
		'title' => _TITLE2('Persist Tun'),
		),
	'verb' => array(
		'title' => _TITLE2('Verbosity'),
		'info' => _HELPBOX2('Set the appropriate level of log file verbosity.
0 is silent, except for fatal errors
4 is reasonable for general usage
5 and 6 can help to debug connection problems
9 is extremely verbose'),
		),
	'ping' => array(
		'title' => _TITLE2('Ping'),
		'info' => _HELPBOX2('Send a UDP ping to remote once every given seconds to keep stateful firewall connection alive.'),
		),
	);
?>
