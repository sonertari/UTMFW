<?php
/** @file
 * All graphs pages based on symon include this file.
 *
 * This file is part of symon/syweb, but needed some modifications to integrate into UTMFW.
 * Licencing is the same as symon's.
 */

$Reload= TRUE;
require_once ("../symon/class_session.inc");
require_once ("../symon/class_layout.inc");

$session->getform('start');
$session->getform('end');
$session->getform('width');
$session->getform('heigth');
$session->getform('layout');
$session->getform('timespan');
$session->getform('size');
$hidden= '';

require_once($VIEW_PATH.'/header.php');
?>
<table class="controls">
	<form action="graphs.php" method="post">
		<tr>
			<td>
				<?php echo _TITLE2('Timespan').':' ?>
				<select size=1 name="timespan">
					<?php
					$values = array();
					foreach($symon['defaults']['timespan']['namedvalues'] as $key => $value) {
						$values[$key] = $key;
					}
					$session->printoptions('timespan', $values);
					?>
				</select>
				<?php echo _TITLE2('Size').':' ?>
				<select size=1 name="size">
					<?php
					$values = array();
					foreach($symon['defaults']['size']['namedvalues'] as $key => $value) {
						$values[$key] = $key;
					}
					$session->printoptions('size', $values);
					?>
				</select>
				<input type="submit" value="<?php echo _CONTROL('Redraw') ?>">
			</td>
		</tr>
		<?php
		if ($session->get('timespan') == 'custom') {
			?>
			<tr>
				<td>
					<?php echo _TITLE2('timespan').': ' ?>
					<?php echo _TITLE2('start') ?>
					<input type="text" size=10 name="start" value="<?php echo $session->get("start") ?>">
					<?php echo _TITLE2('end') ?>
					<input type="text" size=10 name="end" value="<?php echo $session->get("end") ?>">
				</td>
			</tr>
			<?php
		}
		else {
			$hidden = '<input type="hidden" name="start" value="' . $session->get("start") . '">' . "\n" . '<input type="hidden" name="end" value="' . $session->get("end") . '">';
		}
		if ($session->get('size') == 'custom') {
			?>
			<tr>
				<td>
					<?php echo _TITLE2('size').': ' ?>
					<?php echo _TITLE2('width') ?>
					<input type="text" size=10 name="width" value="<?php echo $session->get("width") ?>">
					<?php echo _TITLE2('height') ?>
					<input type="text" size=10 name="heigth" value="<?php echo $session->get("heigth") ?>">
				</td>
			</tr>
			<?php
		}
		else {
			$hidden.= '<input type="hidden" name="width" value="' . $session->get("width") . '">' . "\n" . '<input type="hidden" name="heigth" value="' . $session->get("heigth") . '">';
		}
		if ($hidden) {
			echo $hidden;
		}
		?>
		<input type="hidden" name="action" value="view">
	</form>
</table>
<?php
$l = new Layout($View->Layout);

$gts = $l->getgrouptitles();
$n = count($gts);
if ($n > 1) {
	echo '<div class="groups" style="width: 100%">', "\n";
	for ($i = 0; $i < $n; $i++) {
		echo '<a href="graphs.php#' . $gts[$i] . '">' . _($gts[$i]) . '</a>';
		if ($i != $n - 1) {
			echo ' | ';
		}
	}
	echo "</div>\n";
}

$graphs = $l->render();

if ($View->GraphHelpMsg !== '') {
	$View->GraphHelpMsg.= "\n\n";
}

PrintHelpWindow($View->GraphHelpMsg._HELPWINDOW('You can change the timespan and size of the graphs.'));
require_once($VIEW_PATH.'/footer.php');
?>
