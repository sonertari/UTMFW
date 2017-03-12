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

require_once('pf.php');

$View->Controller($Output, 'GetPfRulesInfo');
$rules= json_decode($Output[0], TRUE);

$Reload= TRUE;
require_once($VIEW_PATH . '/header.php');
?>
<div id="main">
	<table id="logline">
		<tr>
			<th><?php echo _('Number') ?></th>
			<th><?php echo _('Evaluations') ?></th>
			<th><?php echo _('Packets') ?></th>
			<th><?php echo _('Bytes') ?></th>
			<th><?php echo _('States') ?></th>
			<th><?php echo _('State Creations') ?></th>
			<th><?php echo _('Rule') ?></th>
			<th><?php echo _('Inserted') ?></th>
		</tr>
		<?php
		$linenum= 0;
		foreach ($rules as $r) {
			$class= ($linenum++ % 2 == 0) ? 'evenline' : 'oddline';
			?>
			<tr class="<?php echo $class ?>">
				<td class="center" ><?php echo $r['number'] ?></td>
				<td class="right" ><?php echo $r['evaluations'] ?></td>
				<td class="right" ><?php echo $r['packets'] ?></td>
				<td class="right" ><?php echo $r['bytes'] ?></td>
				<td class="right" ><?php echo $r['states'] ?></td>
				<td class="right" ><?php echo $r['stateCreations'] ?></td>
				<td><?php echo htmlentities($r['rule']) ?></td>
				<td><?php echo $r['inserted'] ?></td>
			</tr>
			<?php
		}
		?>
	</table>
</div>
<?php
PrintHelpWindow(_HELPWINDOW('These are the active rules loaded into pf. Note that the rule numbers reported here do not necessarily match with the numbers on the rule editor.'));
require_once($VIEW_PATH . '/footer.php');
?>
