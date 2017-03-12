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

/** @file
 * Prints left menu (main menu), datetime, and sensor values.
 */

require_once('lib/vars.php');

$DirHandle= opendir($VIEW_PATH);
while (FALSE !== ($DirName= readdir($DirHandle))) {
	if (is_dir("$VIEW_PATH/$DirName")) {
		$ModuleFiles[]= $DirName;
	}
}
closedir($DirHandle);

$ServiceStatus= '';
if ($View->Controller($Output, 'GetServiceStatus')) {
	$ServiceStatus= implode(',', $Output);
}
?>
<td id="mainmenuframe" rowspan=2>
	<table class="fixed">
		<tr>
			<td id="mainmenu">
				<table>
					<tr id="leftmenutext">
						<td class="center">
							<?php echo exec('/bin/date "+%d.%m.%Y %H:%M"') ?>
						</td>
					</tr>
				</table>

				<ul>
				<?php
				foreach ($UTMFW_MODULES as $Module => $ModuleConf) {
					if (in_array($Module, $ModuleFiles) && in_array($_SESSION['USER'], $ModuleConf['Perms'])) {
						$Class= '';
						if (strpos($_SERVER['PHP_SELF'], "/$Module/") !== FALSE) {
							$Class= 'class="active" ';
						}

						if (in_array($Module, $ModelsToStat)) {
							if (preg_match("/$Module=R/", $ServiceStatus)) {
								$Image= 'run.png';
								$Name= 'R';
							}
							else {
								$Image= 'stop.png';
								$Name= 'S';
							}
						}
						else {
							$Image= 'transparent.png';
							$Name= ' ';
						}
						?>
						<li <?php echo $Class ?>>
							<a href="<?php echo "/$Module/index.php" ?>">
								<img src="<?php echo $IMG_PATH.$Image ?>" name="<?php echo $Name ?>" alt="<?php echo $Name ?>" align="absmiddle"><?php echo _($ModuleConf['Name']) ?>
							</a>
						</li>
						<?php
					}
				}
				?>
				</ul>

				<?php
				if ($View->Controller($SensorReadings, 'GetSysCtl', 'hw.sensors')) {
					$Sensors= array();
					foreach ($SensorReadings as $Sensor) {
						// hw.sensors.lm1.temp1=38.50 degC (zone temperature)
						if (preg_match('/^.*=\s*([\d.]+)\s*((degC|RPM).*)$/', $Sensor, $Match)) {
							$Sensors[]= array(
								'Value' => $Match[1],
								'Unit' 	=> $Match[2],
								);
						}
					}
					// Print only if sensor values are available
					if (count($Sensors) > 0) {
						?>
						<div id="leftmenutext">
							<table align="right">
								<tr>
									<td colspan="2">
										<?php echo _MENU('Sensors').':' ?>
									</td>
								</tr>
								<?php
								foreach ($Sensors as $Sensor) {
									?>
									<tr>
										<td class="value">
											<?php echo $Sensor['Value'] ?>
										</td>
										<td class="unit">
											<?php echo wordwrap($Sensor['Unit'], 12, '<br />', TRUE) ?>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
						<?php
					}
				}
				?>
			</td>
		</tr>
	</table>
</td>

