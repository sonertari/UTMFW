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

/**
 * Parses the uptime and hw info output from pfw.
 *
 * @param string $infolines uptime output, run elsewhere
 * @return array Parsed info
 */
function parse_info($infolines)
{
	preg_match('/up (.*), \d+ user.*averages: (\d[\d\.,]+)[,\s]+(\d[\d\.,]+)[,\s]+(\d[\d\.,]+)/', $infolines[0], $match);
	$info['uptime'] = $match[1];
	$info['load_1'] = $match[2];
	$info['load_5'] = $match[3];
	$info['load_15'] = $match[4];
	$info['date'] = $infolines[1];
	$info['securelevel'] = $infolines[2];
	$info['ip4forward'] = $infolines[3];
	$info['ip6forward'] = $infolines[4];
	$info['hostname'] = $infolines[5];
	$info['os'] = $infolines[6];

	return $info;
}

$GoingDown= FALSE;
if (count($_POST)) {
	if (filter_has_var(INPUT_POST, 'Restart')) {
		PrintHelpWindow(_NOTICE('System is restarting...'), 'auto', 'WARN');
		$View->Controller($Output, 'Restart');
	}
	else if (filter_has_var(INPUT_POST, 'Stop')) {
		PrintHelpWindow(_NOTICE('System is going down...'), 'auto', 'WARN');
		$View->Controller($Output, 'Shutdown');
	}
	$GoingDown= TRUE;
}

if (!$GoingDown) {
	$View->Controller($Output, 'GetSystemInfo');
	$System= parse_info($Output);

	GetHwInfo(array('machine', 'model', 'ncpu', 'cpuspeed', 'physmem', 'diskcount', 'disknames', 'product', 'vendor', 'uuid'), $Hardware);

	$View->Controller($Output, 'GetStatus');

	$Reload= TRUE;
}

require_once($VIEW_PATH.'/header.php');

if (!$GoingDown) {
	$View->PrintStatusForm(FALSE, TRUE, FALSE, TRUE);
	?>
	<table id="nvp" class="shadowbox" style="width: 600px;">
		<tr class="oddline">
			<td class="title"><?php echo _TITLE('Version') ?></td>
			<td>UTMFW <?php echo VERSION ?></td>
		</tr>
		<tr class="oddline">
			<td class="title"><?php echo _TITLE('Operating System') ?></td>
			<td><?php echo $System['os'] ?></td>
		</tr>
		<tr class="evenline">
			<td class="title"><?php echo _TITLE('Hostname') ?></td>
			<td><?php echo $System['hostname'] ?></td>
		</tr>
		<tr class="oddline">
			<td class="title"><?php echo _TITLE('Uptime') ?></td>
			<td><?php echo $System['uptime'] ?></td>
		</tr>
		<tr class="oddline">
			<td class="title"><?php echo _TITLE('Date') ?></td>
			<td><?php echo $System['date'] ?></td>
		</tr>
		<tr class="evenline">
			<td class="title"><?php echo _TITLE('Machine') ?></td>
			<td><?php echo $Hardware['machine'] ?></td>
		</tr>
		<tr class="evenline">
			<td class="title"><?php echo _TITLE('Processor') ?></td>
			<td><?php echo $Hardware['model'] ?></td>
		</tr>
		<tr class="evenline">
			<td class="title"><?php echo _TITLE('CPUs') ?></td>
			<td><?php echo $Hardware['ncpu'] ?> @ <?php echo $Hardware['cpuspeed'] ?> MHz</td>
		</tr>
		<tr class="evenline">
			<td class="title"><?php echo _TITLE('CPU Load') ?></td>
			<td>
			<?php echo _TITLE('1 minute average').': '.$System['load_1'] ?><br />
			<?php echo _TITLE('5 minute average').': '.$System['load_5'] ?><br />
			<?php echo _TITLE('15 minute average').': '.$System['load_15'] ?></td>
		</tr>
		<tr class="oddline">
			<td class="title"><?php echo _TITLE('Physical Memory') ?></td>
			<td><?php echo round($Hardware['physmem']/1048576) ?> MB</td>
		</tr>
		<tr class="evenline">
			<td class="title"><?php echo _TITLE('Disks') ?></td>
			<td><?php echo $Hardware['diskcount'] ?>: <?php echo $Hardware['disknames'] ?></td>
		</tr>
		<tr class="evenline">
			<td class="title"><?php echo _TITLE('Partitions') ?></td>
			<td>
			<?php
			if ($View->Controller($Output, 'GetPartitions')) {
				?>
				<table>
				<?php
				foreach ($Output as $Partition) {
					if (preg_match('/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)$/', $Partition, $Match)) {
						?>
						<tr>
						<?php
						for ($i= 1; $i <= 6; $i++) {
							?>
							<td class="pfafterhours"><?php echo $Match[$i] ?></td>
							<?php
						}
						?>
						</tr>
						<?php
					}
				}
				?>
				</table>
				<?php
			}
			?>
			</td>
		</tr>
		<tr class="oddline">
			<td class="title"><?php echo _TITLE('Motherboard') ?></td>
			<td><?php echo $Hardware['product'] ?> by <?php echo $Hardware['vendor'] ?></td>
		</tr>
		<tr class="oddline">
			<td class="title"><?php echo _TITLE('Serial Number') ?></td>
			<td><?php echo $Hardware['uuid'] ?></td>
		</tr>
		<tr class="evenline">
			<td class="title"><?php echo _TITLE('Secure Level') ?></td>
			<td><?php echo $System['securelevel'] ?></td>
		</tr>
		<tr>
			<td class="evenline lastLineFirstCell title" style="border-bottom: none;"><?php echo _TITLE('IP Forwarding') ?></td>
			<td class="evenline lastLineLastCell" style="border-bottom: none;">
			<?php
				$Status= $System['ip4forward'] === '1' ? _TITLE('enabled'):_TITLE('disabled');
				echo _('IPv4').' '.$Status;
			?>
			<br />
			<?php
				$Status= $System['ip6forward'] === '1' ? _TITLE('enabled'):_TITLE('disabled');
				echo _('IPv6').' '.$Status;
			?>
			</td>
		</tr>
	</table>
	<?php
}
require_once($VIEW_PATH.'/footer.php');
?>
