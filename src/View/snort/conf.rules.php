<?php
/*
 * Copyright (C) 2004-2020 Soner Tari
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

require_once('snort.php');

/**
 * Provides enabled/disabled snort rules in list boxes, and buttons to modify.
 */
function PrintRulesForms()
{
	global $View;
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<td style="width: 0;">
			<?php
			echo _TITLE2('Enabled Rules').':';
			$View->Controller($output, 'GetRules', $_SESSION[$View->Model]['ConfOpt']);
			?>
			<br />
			<select name="RulesToChange[]" multiple style="width: 200px; height: 400px;">
				<?php
				foreach ($output as $rule){
					$rulename= $rule;
					if (preg_match('/^(.*)\.rules$/', $rule, $match)) {
						$rulename= $match[1];
					}

					if (filter_has_var(INPUT_POST, 'RulesToChange') && in_array($rule, $_POST['RulesToChange'])) {
						$selected= ' selected';
					}
					else {
						$selected= '';
					}
					?>
					<option value="<?php echo $rule ?>"<?php echo $selected ?>><?php echo $rulename ?></option>
					<?php
				}
				?>
			</select>
			<br />
			<input type="submit" name="MoveUp" value="<?php echo _CONTROL('Move Up') ?>"/>
			<br />
			<input type="submit" name="MoveDown" value="<?php echo _CONTROL('Move Down') ?>"/>
		</td>
		<td class="center" style="width: 0;">
			<input type="submit" name=">>" value=">>"/>
			<br />
			<input type="submit" name="&lt&lt" value="&lt&lt"/>
		</td>
		<td style="width: 0; vertical-align: top;">
			<?php
			echo _TITLE2('Disabled Rules').':';
			$View->Controller($output, 'GetDisabledRules', $_SESSION[$View->Model]['ConfOpt']);
			sort($output);
			?>
			<br />
			<select name="RulesToChange[]" multiple style="width: 200px; height: 400px;">
				<?php
				foreach ($output as $rule){
					$rulename= $rule;
					if (preg_match('/^(.*)\.rules$/', $rule, $match)) {
						$rulename= $match[1];
					}
					?>
					<option value="<?php echo $rule ?>"><?php echo $rulename ?></option>
					<?php
				}
				?>
			</select>
		</td>
	</form>
	<?php
}

if (filter_has_var(INPUT_POST, '>>')) {
	foreach ($_POST['RulesToChange'] as $Rule) {
		$View->Controller($Output, 'DisableRule', $Rule, $_SESSION[$View->Model]['ConfOpt']);
	}
}
else if (filter_has_var(INPUT_POST, '<<')) {
	foreach ($_POST['RulesToChange'] as $Rule) {
		$View->Controller($Output, 'EnableRule', $Rule, $_SESSION[$View->Model]['ConfOpt']);
	}
}
else if (filter_has_var(INPUT_POST, 'MoveUp')) {
	foreach ($_POST['RulesToChange'] as $Rule) {
		$View->Controller($Output, 'MoveRuleUp', $Rule, $_SESSION[$View->Model]['ConfOpt']);
	}
}
else if (filter_has_var(INPUT_POST, 'MoveDown')) {
	$SelectedRules= $_POST['RulesToChange'];
	for ($i= count($SelectedRules) - 1; $i >= 0; $i--) {
		$View->Controller($Output, 'MoveRuleDown', $SelectedRules[$i], $_SESSION[$View->Model]['ConfOpt']);
	}
}

$View->SetSessionConfOpt();

require_once($VIEW_PATH.'/header.php');

$View->PrintConfOptForm();
?>
<table>
	<tr>
		<?php
		PrintRulesForms();
		?>
		<td>
			<?php
			PrintHelpBox(_HELPBOX2('You can customize your rule sets here. Include only the relevant rule sets.

Some of rule sets are disabled by default. These rules are either site policy specific or require tuning in order to not generate false positive alerts in most environments.'), 200);
			?>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW("The IDS uses rules categorized in different packages. Note that you cannot obtain a 'better' IDS by enabling all the rules. In fact, irrelevant rules may trigger false alarms.

Updated Snort rules for your version of the software are released periodically. Make sure you have appropriate rules installed on your system."));
require_once($VIEW_PATH.'/footer.php');
?>
