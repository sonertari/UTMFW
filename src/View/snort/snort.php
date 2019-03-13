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

require_once('include.php');

$LogConf = array(
	'snort' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'REs' => array(
				'red' => array('failed', 'ERROR:'),
				'yellow' => array('WARNING:'),
				),
			),
		),
	);

class Snort extends View
{
	public $Model= 'snort';
	public $Layout= 'snort';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('Intrusion Detection');
		$this->LogsHelpMsg= _HELPWINDOW('These logs contain messages from all IDS processes. Alerts are duplicated here as well.');
	}

	/**
	 * Wrapper for InterfaceSelectForm().
	 */
	function PrintInterfaceSelectForm()
	{
		global $ADMIN;
		/// Only admin can start/stop the processes
		if (in_array($_SESSION['USER'], $ADMIN)) {
			?>
			<table id="ifselect">
				<tr>
					<td class="title">
						<?php echo _TITLE2('Interfaces').':' ?>
					</td>
					<td>
						<?php
						$this->InterfaceSelectForm();
						?>
					</td>
					<td class="help">
						<?php PrintHelpBox(_HELPBOX2('Here you should select the interface(s) the IDS listens to. Instance with the selected interface is restarted if it is already running.')) ?>
					</td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * General form for selecting a physical interface in the system.
	 */
	function InterfaceSelectForm()
	{
		$startconfirm= _NOTICE('Are you sure you want to start the <NAME>?');
		$startconfirm= preg_replace('/<NAME>/', $this->Caption, $startconfirm);

		$stopconfirm= _NOTICE('Are you sure you want to stop the <NAME>?');
		$stopconfirm= preg_replace('/<NAME>/', $this->Caption, $stopconfirm);
		?>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<select name="Interfaces[]" multiple style="width: 100px; height: 50px;">
			<?php
			if ($this->Controller($output, 'GetPhyIfs')) {
				foreach ($output as $if) {
					?>
					<option value="<?php echo $if ?>"><?php echo $if ?></option>
					<?php
				}
			}
			?>
			</select>
			<br />
			<input type="submit" name="Start" value="<?php echo _CONTROL('Start') ?>" onclick="return confirm('<?php echo $startconfirm ?>')"/>
			<input type="submit" name="Stop" value="<?php echo _CONTROL('Stop') ?>" onclick="return confirm('<?php echo $stopconfirm ?>')"/>
			<input type="hidden" name="Model" value=<?php echo $this->Model ?> />
		</form>
		<?php
	}

	/**
	 * Sets the conf for the current session.
	 */
	function SetSessionConfOpt()
	{
		if (filter_has_var(INPUT_POST, 'ConfOpt')) {
			$conf= filter_input(INPUT_POST, 'ConfOpt');
		}
		else if ($_SESSION[$this->Model]['ConfOpt']) {
			$conf= $_SESSION[$this->Model]['ConfOpt'];
		}
		else {
			$conf= 0;
		}

		$_SESSION[$this->Model]['ConfOpt']= $conf;
	}

	/**
	 * Displays an edit box and a button to change current conf file.
	 */
	function PrintConfOptForm()
	{
		$conf= $_SESSION[$this->Model]['ConfOpt'];
		?>
		<table>
			<tr>
				<td>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<?php echo _TITLE('Configuration').':' ?>
						<select name="ConfOpt">
							<option <?php echo $conf == 0 ? 'selected':''; ?> value="0"><?php echo _TITLE('IDS') ?></option>
							<option <?php echo $conf == 1 ? 'selected':''; ?> value="1"><?php echo _TITLE('Inline IPS') ?></option>
						</select>
						<input type="submit" name="ApplyConf" value="<?php echo _CONTROL('Apply') ?>"/>
						<input type="hidden" name="Model" value=<?php echo $this->Model ?> />
					</form>
				</td>
				<td>
					<?php
					PrintHelpBox(_HELPBOX2('This shows the active configuration for this page. Use this form to change the active configuration.'), 400);
					?>
				</td>
			</tr>
		</table>
		<?php
	}
}

$View= new Snort();

/**
 * Basic configuration.
 */
$basicConfig = array(
	'ipvar HOME_NET' => array(
		'title' => _TITLE2('HOME_NET'),
		'info' => _HELPBOX2('You must change the following variables to reflect your local network. The variable is currently setup for an RFC 1918 address space.'),
		),
	'ipvar EXTERNAL_NET' => array(
		'title' => _TITLE2('EXTERNAL_NET'),
		'info' => _HELPBOX2('Set up the external network addresses as well.  A good start may be "any"'),
		),
	'ipvar DNS_SERVERS' => array(
		'title' => _TITLE2('DNS_SERVERS'),
		'info' => _HELPBOX2('Configure your server lists.  This allows snort to only look for attacks to systems that have a service up.  Why look for HTTP attacks if you are not running a web server?  This allows quick filtering based on IP addresses. These configurations MUST follow the same configuration scheme as defined above for $HOME_NET.
List of DNS servers on your network'),
		),
	'ipvar SMTP_SERVERS' => array(
		'title' => _TITLE2('SMTP_SERVERS'),
		'info' => _HELPBOX2('List of SMTP servers on your network'),
		),
	'ipvar HTTP_SERVERS' => array(
		'title' => _TITLE2('HTTP_SERVERS'),
		'info' => _HELPBOX2('List of web servers on your network'),
		),
	'ipvar SQL_SERVERS' => array(
		'title' => _TITLE2('SQL_SERVERS'),
		'info' => _HELPBOX2('List of sql servers on your network'),
		),
	'ipvar TELNET_SERVERS' => array(
		'title' => _TITLE2('TELNET_SERVERS'),
		'info' => _HELPBOX2('List of telnet servers on your network'),
		),
//	'ipvar SNMP_SERVERS' => array(
//		'title' => _TITLE2('SNMP_SERVERS'),
//		'info' => _HELPBOX2('List of snmp servers on your network'),
//		),
	'portvar SSH_PORTS' => array(
		'title' => _TITLE2('SSH_PORTS'),
		'info' => _HELPBOX2('Ports you run secure shell on.'),
		),
	'portvar HTTP_PORTS' => array(
		'title' => _TITLE2('HTTP_PORTS'),
		'info' => _HELPBOX2('Ports you run web servers on.

Please note:  [80,8080] does not work.
If you wish to define multiple HTTP ports, port lists must either be continuous [eg 80:8080], or a single port [eg 80].
We will add support for a real list of ports in the future.'),
		),
	'portvar SHELLCODE_PORTS' => array(
		'title' => _TITLE2('SHELLCODE_PORTS'),
		'info' => _HELPBOX2('Ports you want to look for SHELLCODE on.'),
		),
	);

/**
 * Advanced configuration.
 */
$advancedConfig = array(
	'config disable_decode_alerts' => array(
		'title' => _TITLE2('Disable decode alerts'),
		'info' => _HELPBOX2('Snort\'s decoder will alert on lots of things such as header truncation or options of unusual length or infrequently used tcp options.
Stop generic decode events:'),
		),
	'config disable_tcpopt_experimental_alerts' => array(
		'title' => _TITLE2('Disable tcpopt experimental alerts'),
		'info' => _HELPBOX2('Stop Alerts on experimental TCP options'),
		),
	'config disable_tcpopt_obsolete_alerts' => array(
		'title' => _TITLE2('Disable tcpopt obsolete alerts'),
		'info' => _HELPBOX2('Stop Alerts on obsolete TCP options'),
		),
	'config disable_tcpopt_ttcp_alerts' => array(
		'title' => _TITLE2('Disable tcpopt ttcp alerts'),
		'info' => _HELPBOX2('Stop Alerts on T/TCP alerts

In snort 2.0.1 and above, this only alerts when a TCP option is detected that shows T/TCP being actively used on the network.  If this is normal behavior for your network, disable the next option.'),
		),
	'config disable_tcpopt_alerts' => array(
		'title' => _TITLE2('Disable tcpopt alerts'),
		'info' => _HELPBOX2('Stop Alerts on all other TCPOption type events'),
		),
	'config disable_ipopt_alerts' => array(
		'title' => _TITLE2('Disable ipopt alerts'),
		'info' => _HELPBOX2('Stop Alerts on invalid ip options'),
		),
	'preprocessor frag3_global: max_frags' => array(
		'title' => _TITLE2('IP defragmentation support'),
		'info' => _HELPBOX2('This preprocessor performs IP defragmentation.  This plugin will also detect people launching fragmentation attacks (usually DoS) against hosts. Maximum number of frag trackers that may be active at once.'),
		),
	'preprocessor bo' => array(
		'title' => _TITLE2('Back Orifice detector'),
		'info' => _HELPBOX2('Detects Back Orifice traffic on the network.'),
		),
//	'preprocessor telnet_decode' => array(
//		'title' => _TITLE2('Telnet negotiation string normalizer'),
//		'info' => _HELPBOX2('This preprocessor "normalizes" telnet negotiation strings from telnet and ftp traffic.  It works in much the same way as the http_decode preprocessor, searching for traffic that breaks up the normal data stream of a protocol and replacing it with a normalized representation of that traffic so that the "content" pattern matching keyword can work without requiring modifications.'),
//		),
	'include classification.config' => array(
		'title' => _TITLE2('Include classification & priority settings'),
		),
	'include reference.config' => array(
		'title' => _TITLE2('Include reference systems'),
		),
	);
?>
