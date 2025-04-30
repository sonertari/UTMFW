<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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

// XXX: Store the system vars
$SavedMenu= $Menu;
$SavedLogConf= $LogConf;

require_once($VIEW_PATH.'/httpd/httpd.php');
require_once($VIEW_PATH.'/pf/pf.php');
require_once($VIEW_PATH.'/sslproxy/sslproxy.php');
require_once($VIEW_PATH.'/dnsmasq/include.php');
require_once($VIEW_PATH.'/dhcpd/include.php');
require_once($VIEW_PATH.'/ftp-proxy/include.php');
require_once($VIEW_PATH.'/e2guardian/e2guardian.php');
require_once($VIEW_PATH.'/clamav/clamd.php');
require_once($VIEW_PATH.'/clamav/freshclam.php');
require_once($VIEW_PATH.'/spamassassin/include.php');
require_once($VIEW_PATH.'/imspector/imspector.php');
require_once($VIEW_PATH.'/dante/include.php');
require_once($VIEW_PATH.'/openvpn/include.php');
// require_once($VIEW_PATH.'/spamd/include.php');
require_once($VIEW_PATH.'/snort/snort.php');
require_once($VIEW_PATH.'/snortips/include.php');
require_once($VIEW_PATH.'/p3scan/include.php');
require_once($VIEW_PATH.'/smtp-gated/include.php');
require_once($VIEW_PATH.'/openssh/include.php');

// Restore the system vars
$View= new System();
$Menu= $SavedMenu;
$LogConf= $SavedLogConf;

$TopMenu= 'info';
$Submenu= 'dashboard';

$LastDashboardInterval= '10min';
if (filter_has_var(INPUT_GET, 'interval')) {
	$LastDashboardInterval= filter_input(INPUT_GET, 'interval');
} else if (isset($_SESSION['system']['DashboardInterval'])) {
	$LastDashboardInterval= $_SESSION['system']['DashboardInterval'];
}
$_SESSION['system']['DashboardInterval']= $LastDashboardInterval;

$IntervalChanged= 0;
if ($StatusCheckInterval !== $DashboardIntervals2Seconds[$LastDashboardInterval]) {
	$IntervalChanged= 1;
}

$ServiceStatus= array();
if ($View->Controller($Output, 'GetServiceStatus', 1, $LastDashboardInterval, $IntervalChanged)) {
	$Output= json_decode($Output[0], TRUE);
	$ServiceInfo= $Output['info'];
	$ServiceStatus= $Output['status'];
}

$ModuleNames= array(
	'snortinline' => 'snort',
	'clamd' => 'clamav',
	'freshclam' => 'clamav',
	'symon' => 'monitoring',
	'symux' => 'monitoring',
	'pmacct' => 'monitoring',
	'collectd' => 'monitoring',
	);

$SubmenuNames= array(
	'system' => 'system',
	'pf' => 'pf',
	'dhcpd' => 'dhcpd',
	);

$ShowDataRangeSelector= TRUE;

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');

$Critical= 0;
$Error= 0;
$Warning= 0;
foreach ($ServiceStatus as $Module => $StatusArray) {
	$Critical+= $ServiceStatus[$Module]['Critical'];
	$Error+= $ServiceStatus[$Module]['Error'];
	$Warning+= $ServiceStatus[$Module]['Warning'];
}

function DisplayModuleStatus($Module, $DisplayDashboardExtrasFunc= FALSE)
{
	global $ModelsToStat, $ServiceStatus, $ModuleNames, $SubmenuNames, $Status2Images, $StatusTitles, $IMG_PATH;

	if (array_key_exists($Module, $ModuleNames)) {
		$ModuleName= $ModuleNames[$Module];
	} else {
		$ModuleName= $Module;
	}

	if (array_key_exists($Module, $SubmenuNames)) {
		$SubmenuName= $SubmenuNames[$Module];
	} else {
		$SubmenuName= 'info';
	}

	$Caption = $ModelsToStat[$Module];

	$RunStatus= $ServiceStatus[$Module]['Status'];
	$ErrorStatus= $ServiceStatus[$Module]['ErrorStatus'];

	$StatusImage= $Status2Images[$RunStatus];
	$ErrorStatusImage= $Status2Images[$ErrorStatus];

	$Critical= $ServiceStatus[$Module]['Critical'];
	$Error= $ServiceStatus[$Module]['Error'];
	$Warning= $ServiceStatus[$Module]['Warning'];

	$ErrorCounts= array();
	if ($Critical) {
		$ErrorCounts[]= _TITLE('Critical').': '.$Critical;
	}
	if ($Error) {
		$ErrorCounts[]= _TITLE('Error').': '.$Error;
	}
	if ($Warning) {
		$ErrorCounts[]= _TITLE('Warning').': '.$Warning;
	}
	$ErrorCountsStr= implode(', ', $ErrorCounts);
	?>
	<tr>
		<td class="module">
			<a class="transparent" href="<?php echo "/$ModuleName/info.php?submenu=$SubmenuName" ?>">
			<div>
				<table>
					<tr>
						<td class="moduleimage">
							<img src="<?php echo $IMG_PATH.$StatusImage ?>" name="<?php echo $RunStatus ?>" alt="<?php echo $RunStatus ?>" title="<?php echo $StatusTitles[$RunStatus] ?>" align="absmiddle">
						</td>
						<td class="moduleimage">
							<img src="<?php echo $IMG_PATH.$ErrorStatusImage ?>" name="<?php echo $ErrorStatus ?>" alt="<?php echo $ErrorStatus ?>" title="<?php echo $StatusTitles[$ErrorStatus] ?>" align="absmiddle">
						</td>
						<td class="modulecaption">
							<strong><?php echo _($Caption) ?></strong>
						</td>
						<td class="moduleerrorcounts">
							<?php echo $ErrorCountsStr ?>
						</td>
					</tr>
					<?php
					if ($DisplayDashboardExtrasFunc) {
						$DisplayDashboardExtrasFunc();
					}
					?>
				</table>
			</div>
			</a>
		</td>
	</tr>
	<?php
}

?>
<table id="dashboard">
	<tr>
		<td title="<?php echo _TITLE2('Total number of critical errors reported by the system and services') ?>">
			<div class="critical">
				<table>
					<tr>
						<td class="count">
							<?php echo $Critical ?>
						</td>
					</tr>
					<tr>
						<td class="prio">
							<?php echo _TITLE('CRITICAL') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td title="<?php echo _TITLE2('Total number of errors reported by the system and services') ?>">
			<div class="error">
				<table>
					<tr>
						<td class="count">
							<?php echo $Error ?>
						</td>
					</tr>
					<tr>
						<td class="prio">
							<?php echo _TITLE('ERROR') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td title="<?php echo _TITLE2('Total number of warnings reported by the system and services') ?>">
			<div class="warning">
				<table>
					<tr>
						<td class="count">
							<?php echo $Warning ?>
						</td>
					</tr>
					<tr>
						<td class="prio">
							<?php echo _TITLE('WARNING') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td title="<?php echo _TITLE2('Ping times for two hosts') ?>">
			<a class="transparent" href="/pf/graphs.php?submenu=ping">
			<div class="img">
				<img src="/system/dashboard/ping.png" name="ping" alt="ping">
			</div>
			</a>
		</td>
		<td title="<?php echo _TITLE2('Average ping time within last minute to the gateway') ?>">
			<div class="gateway">
				<table>
					<tr>
						<td class="uptime-count" >
							<?php echo $ServiceInfo['collectd']['gateway_ping_time'] ?>
						</td>
					</tr>
					<tr>
						<td class="prio">
							<?php echo _TITLE('GATEWAY') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td title="<?php echo _TITLE2('Average ping time within last minute to the remote host') ?>">
			<div class="remote">
				<table>
					<tr>
						<td class="uptime-count" >
							<?php echo $ServiceInfo['collectd']['remote_ping_time'] ?>
						</td>
					</tr>
					<tr>
						<td class="prio">
							<?php echo _TITLE('REMOTE') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td title="<?php echo _TITLE2('Total number of users currently logged in to the system') ?>">
			<div class="users">
				<table>
					<tr>
						<td class="uptime-count" >
							<?php echo $ServiceInfo['system']['users'] ?>
						</td>
					</tr>
					<tr>
						<td class="prio">
							<?php echo _TITLE('USERS') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td title="<?php echo _TITLE2('System uptime since the last boot up') ?>">
			<div class="uptime">
				<table>
					<tr>
						<td class="uptime-count" >
							<?php echo $ServiceInfo['system']['uptime'] ?>
						</td>
					</tr>
					<tr>
						<td class="prio">
							<?php echo _TITLE('UPTIME') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>
<table>
	<tr style="vertical-align: top">
		<td>
			<table id="modulestatus">
				<?php
				DisplayModuleStatus('system', fn() => System::DisplayDashboardExtras());
				DisplayModuleStatus('pmacct', fn() => System::DisplayPmacctDashboardExtras());
				DisplayModuleStatus('symon');
				DisplayModuleStatus('symux');
				DisplayModuleStatus('collectd');
				?>
			</table>
		</td>
		<td>
			<table id="modulestatus">
				<?php
				DisplayModuleStatus('pf', fn() => Pf::DisplayDashboardExtras());
				DisplayModuleStatus('imspector');
				DisplayModuleStatus('dante');
				?>
			</table>
		</td>
		<td>
			<table id="modulestatus">
				<?php
				DisplayModuleStatus('sslproxy', fn() => SSLproxy::DisplayDashboardExtras());
				DisplayModuleStatus('dnsmasq', fn() => Dnsmasq::DisplayDashboardExtras());
				DisplayModuleStatus('dhcpd', fn() => Dhcpd::DisplayDashboardExtras());
				DisplayModuleStatus('ftp-proxy', fn() => Ftpproxy::DisplayDashboardExtras());
				?>
			</table>
		</td>
		<td>
			<table id="modulestatus">
				<?php
				DisplayModuleStatus('e2guardian', fn() => E2guardian::DisplayDashboardExtras());
				DisplayModuleStatus('clamd', fn() => Clamd::DisplayDashboardExtras());
				DisplayModuleStatus('freshclam');
				DisplayModuleStatus('spamassassin', fn() => Spamassassin::DisplayDashboardExtras());
				DisplayModuleStatus('openssh', fn() => Openssh::DisplayDashboardExtras());
				DisplayModuleStatus('openvpn');
				// DisplayModuleStatus('spamd');
				?>
			</table>
		</td>
		<td>
			<table id="modulestatus">
				<?php
				DisplayModuleStatus('snort', fn() => Snort::DisplayDashboardExtras());
				DisplayModuleStatus('snortinline');
				DisplayModuleStatus('snortips');
				DisplayModuleStatus('p3scan', fn() => P3scan::DisplayDashboardExtras());
				DisplayModuleStatus('smtp-gated', fn() => Smtpgated::DisplayDashboardExtras());
				DisplayModuleStatus('httpd', fn() => Httpd::DisplayDashboardExtras());
				?>
			</table>
		</td>
	</tr>
</table>
<?php
require_once($VIEW_PATH.'/footer.php');
?>
