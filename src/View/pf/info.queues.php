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

require_once ('pf.php');

$View->Controller($Output, 'GetPfQueueInfo');
$queues= json_decode($Output[0], TRUE);

$Reload= TRUE;
require_once($VIEW_PATH . '/header.php');
?>
<div id="main">
	<table id="logline">
		<tr>
			<th><?php echo _('Name') ?></th>
			<th><?php echo _('Packets') ?></th>
			<th><?php echo _('Bytes') ?></th>
			<th><?php echo _('Dropped Packets') ?></th>
			<th><?php echo _('Dropped Bytes') ?></th>
			<th><?php echo _('Queue Length') ?></th>
		</tr>
		<?php
		$linenum= 0;
		foreach ($queues as $q) {
			$class= ($linenum++ % 2 == 0) ? 'evenline' : 'oddline';
			?>
			<tr class="<?php echo $class ?>">
				<td class="center" ><?php echo $q['name'] ?></td>
				<td class="right" ><?php echo $q['pkts'] ?></td>
				<td class="right" ><?php echo $q['bytes'] ?></td>
				<td class="right" ><?php echo $q['droppedPkts'] ?></td>
				<td class="right" ><?php echo $q['droppedBytes'] ?></td>
				<td class="center" ><?php echo $q['length'] ?></td>
			</tr>
			<?php
		}
		?>
	</table>
</div>
<?php
PrintHelpWindow(_HELPWINDOW('These are queue statistics reported by pf.'));
require_once($VIEW_PATH . '/footer.php');
?>
