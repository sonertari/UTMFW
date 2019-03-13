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

// @attention The following three lines are for the case that the Dashboard is requested from the right menu, instead of System>Info>Dashboard
require_once('include.php');
$TopMenu= 'info';
$Submenu= 'dashboard';

$ServiceStatus= array();
if ($View->Controller($Output, 'GetServiceStatus')) {
	$ServiceStatus= json_decode($Output[0], TRUE);
}

const COLUMN_COUNT= 4;

$StatusTitles= array(
	'C' => _TITLE('Critical Error'),
	'E' => _TITLE('Error'),
	'W' => _TITLE('Warning'),
	'R' => _TITLE('Running'),
	'S' => _TITLE('Stopped'),
	'N' => _TITLE('No Errors'),
	);

$ModuleNames= array(
	'snortinline' => 'snort',
	'clamd' => 'clamav',
	'freshclam' => 'clamav',
	'symon' => 'monitoring',
	'symux' => 'monitoring',
	'pmacct' => 'monitoring',
	);

$SubmenuNames= array(
	'system' => 'system',
	'pf' => 'pf',
	'dhcpd' => 'dhcpd',
	);

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
?>
<table id="dashboard">
	<tr>
		<td>
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
		<td>
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
		<td>
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
	</tr>
</table>
<table id="modulestatus">
	<tr>
		<td>
			<strong><?php echo _TITLE('Module Status').':' ?></strong>
		</td>
	</tr>
	<?php
	$Id= 0;
	foreach ($Status2Images as $Status => $Image) {
		$moduleCount= 0;
		foreach ($ModelsToStat as $Module => $Caption) {
			if (array_key_exists($Module, $ServiceStatus) &&
					($Status == $ServiceStatus[$Module]['Status'] || $Status == $ServiceStatus[$Module]['ErrorStatus'])) {

				$RunStatus= $ServiceStatus[$Module]['Status'];
				$ErrorStatus= $ServiceStatus[$Module]['ErrorStatus'];

				// XXX?
				if ($Status == $RunStatus) {
					$StatusImage= $Image;
					$ErrorStatusImage= $Status2Images[$ErrorStatus];
				}
				else if ($Status == $ErrorStatus) {
					$StatusImage= $Status2Images[$RunStatus];
					$ErrorStatusImage= $Image;
				}

				$Critical= $ServiceStatus[$Module]['Critical'];
				$Error= $ServiceStatus[$Module]['Error'];
				$Warning= $ServiceStatus[$Module]['Warning'];
				$Logs= $ServiceStatus[$Module]['Logs'];

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

				// XXX
				$ServiceStatus[$Module]['Status']= '';
				$ServiceStatus[$Module]['ErrorStatus']= '';

				if ($moduleCount % COLUMN_COUNT == 0) {
					?>
					<tr>
					<?php
				}

				$FormId= 'form'.$Id++;

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
				?>
				<td class="module">
					<form id="<?php echo $FormId ?>" name="<?php echo $FormId ?>" action="<?php echo "/$ModuleName/info.php?submenu=$SubmenuName" ?>" method="post"></form>
					<div onclick="document.<?php echo $FormId ?>.submit()">
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
							</tr>
							<tr>
								<td class="moduleerrorcounts" colspan="3">
									<?php echo $ErrorCountsStr ?>
								</td>
							</tr>
						</table>
					</div>
				</td>
				<?php
				if (++$moduleCount % COLUMN_COUNT == 0) {
					?>
					</tr>
					<?php
				}
			}
		}
	}
	?>
</table>
<?php
require_once($VIEW_PATH.'/footer.php');
?>
