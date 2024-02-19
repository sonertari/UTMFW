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

use SSLproxy\RuleSet;

require_once('include.php');

$LogConf = array(
	'sslproxy' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		),
	);

class Sslproxy extends View
{
	public $Model= 'sslproxy';
	public $Layout= 'sslproxy';

	public $RuleSet;

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('SSL Proxy');

		if (!isset($_SESSION['sslproxy']['ruleset'])) {
			$_SESSION['sslproxy']['ruleset']= new RuleSet();
		}
		$this->RuleSet= &$_SESSION['sslproxy']['ruleset'];
	}

	function PrintStatsMaxValues($interval)
	{
		$key2Titles = array(
			'Load' => _TITLE2('Connections'),
			'UploadKB' => _TITLE2('Total Upload (KB)'),
			'DownloadKB' => _TITLE2('Total Download (KB)'),
			'CreateTime' => _TITLE2('Connection Duration'),
			'AccessTime' => _TITLE2('Connection Idle Time'),
			'Fd' => _TITLE2('File Descriptors'),
			);
		?>
		<table id="logline" style="width: 10%;">
			<tr>
				<th></th>
				<th nowrap><?php echo _('Max Values') ?></th>
			</tr>
		<?php
		$this->Controller($Output, 'GetMaxStats', $interval);
		$maxValues= json_decode($Output[0], TRUE);
		
		foreach ($key2Titles as $key => $title) {
			?>
			<tr>
				<th style="text-align: right;" nowrap>
					<?php echo $title ?>
				</th>
				<td style="text-align: center;">
					<?php echo $maxValues[$key] ?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
	}

	static function DisplayDashboardExtras()
	{
		global $ServiceInfo;
		?>
		<tr>
			<td colspan="4">
				<a class="transparent" href="/sslproxy/connstats.php"><img src="/system/dashboard/sslproxy.png" name="sslproxy" alt="sslproxy" title="<?php echo _TITLE2('Connections handled by the SSL Proxy') ?>"></a>
			</td>
		</tr>
		<tr>
			<td id="dashboard" colspan="4" title="<?php echo _TITLE2('Number of connections handled by the SSL Proxy') ?>">
				<a class="transparent-white" href="/sslproxy/conns.php">
				<div id="count" style="background-color: #008194">
					<table>
						<tr class="count">
							<td class="count">
							<?php echo $ServiceInfo['sslproxy']['conns'] ?>
							</td>
						</tr>
						<tr>
							<td class="prio">
							<?php echo _TITLE('CONNECTIONS') ?>
							</td>
						</tr>
					</table>
				</div>
				</a>
			</td>
		</tr>
		<tr>
			<td id="dashboard" colspan="4" title="<?php echo _TITLE2('Number of file descriptors used by the SSL Proxy') ?>">
				<a class="transparent-white" href="/sslproxy/logs.php">
				<div id="count" style="background-color: #da5400">
					<table>
						<tr class="count">
							<td class="count">
							<?php echo $ServiceInfo['sslproxy']['fds'] ?>
							</td>
						</tr>
						<tr>
							<td class="prio">
							<?php echo _TITLE('FILE DESCRIPTORS') ?>
							</td>
						</tr>
					</table>
				</div>
				</a>
			</td>
		</tr>
		<?php
	}
}

$View= new Sslproxy();

// Load the main sslproxy configuration if the ruleset is empty
if (in_array($_SESSION['USER'], $ADMIN) && $View->RuleSet->filename == '') {
	$filepath= '/etc/sslproxy/sslproxy.conf';
	$ruleSet= new RuleSet();
	if ($ruleSet->load($filepath, 0, TRUE)) {
		$View->RuleSet= $ruleSet;
		PrintHelpWindow(_NOTICE('Rules loaded') . ': ' . $View->RuleSet->filename);
	} else {
		PrintHelpWindow('<br>' . _NOTICE('Failed loading') . ": $filepath", NULL, 'ERROR');
	}
}
?>
