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
 * WUI library functions.
 */

/**
 * Common HTML footer lines.
 *
 * @todo This could be in a separate file to include, not a function.
 */
function AuthHTMLFooter()
{
	global $ADMIN, $SessionTimeout, $View;

	$_SESSION['Timeout']= time() + $SessionTimeout;
	?>
	</table>
	<table>
		<tr id="footer">
			<td class="user">
				<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?logout"><?php echo _TITLE('Logout') ?></a>

				(<label id="timeout"></label>)
				<script language="javascript" type="text/javascript">
					<!--
					// Add one to session timeout start, to LogUserOut() after redirect below (it's PHP's task)
					// Otherwise session timeout restarts from max
					var timeout= <?php echo $_SESSION['Timeout'] - time() ?> + 1;
					function countdown()
					{
						if (timeout > 0) {
							timeout-= 1;
							min= Math.floor(timeout / 60);
							sec= timeout % 60;
							// Pad left
							if (sec.toString().length < 2) {
								sec= "0" + sec;
							}
							document.getElementById("timeout").innerHTML= min + ":" + sec;
						}
						else {
							// redirect
							window.location= "/index.php";
							return;
						}
						setTimeout("countdown()", 1000);
					}
					countdown();
					// -->
				</script>

				<?php echo $_SESSION['USER'].'@'.filter_input(INPUT_SERVER, 'REMOTE_ADDR') ?>
			</td>
			<td>
				<?php echo _TITLE('Copyright') ?> (C) 2017 Soner Tari. <?php echo _TITLE('All rights reserved.') ?>
			</td>
		</tr>
	<?php
	HTMLFooter();
}

/**
 * Checks and prints a warning if the page is not active.
 *
 * $active is set during left and top menu creation according to logged in user.
 *
 * @param bool $active Whether the page was active or not.
 */
function CheckPageActivation($active)
{
	global $VIEW_PATH, $Submenu;

	if (!$active) {
		echo _TITLE('Resource not available').': '.$Submenu;

		require_once($VIEW_PATH.'/footer.php');
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Page not active.');
		exit(1);
	}
}

/**
 * Prints the message in a simple box, without an image.
 *
 * Used in simple info boxes on the right of components.
 * New lines are replaced with html breaks before displaying.
 *
 * @warning Checks if $msg is empty, because some automatized functions may
 * not pass a non-empth string (such as on configuration pages), thus the box
 * should not be displayed. Just take debug logs.
 *
 * @param string $msg Message to display.
 * @param int $width Box width, defaults to 300px.
 */
function PrintHelpBox($msg= '', $width= 300)
{
	global $ShowHelpBox;

	if ($ShowHelpBox) {
		if ($msg !== '') {
			?>
			<table id="helpbox" style="width: <?php echo $width ?>px;">
				<tr>
					<td class="leftbar">
					</td>
					<td>
						<?php
						echo preg_replace("/\n/", '<br />', _($msg));
						?>
					</td>
				</tr>
			</table>
			<?php
			return;
		}
		else {
			wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, '$msg empty');
		}
	}
}

/**
 * Prints the message in a box with a title bar and an image.
 *
 * Used as the main explanation box on a page.
 * New lines are replaced with html breaks before displaying.
 *
 * @warning $Width type should be string, because some functions use 'auto'.
 *
 * @param string $msg Message to display.
 * @param int $width Box width, defaults to auto.
 * @param string $type Image type to display.
 */
function PrintHelpWindow($msg, $width= 'auto', $type= 'INFO')
{
	global $IMG_PATH, $InHelpRegion, $ErrorMsg, $WarnMsg, $InfoMsg, $ShowHelpBox;

	/**
	 * Types of help boxes.
	 *
	 * @param string name Title string.
	 * @param string icon Image to display on the top-left corner of the box.
	 */
	$HelpBoxTypes = array(
		'INFO' => array(
			'name' => _TITLE('INFORMATION'),
			'icon' => 'info.png'
			),
		'ERROR' => array(
			'name' => _TITLE('ERROR'),
			'icon' => 'error.png'
			),
		'WARN' => array(
			'name' => _TITLE('WARNING'),
			'icon' => 'warning.png'
			),
	);

	$boxes= array(
		'ERROR' => 'ErrorMsg',
		'WARN' => 'WarnMsg',
		'INFO' => 'InfoMsg',
		);

	if (array_key_exists($type, $boxes)) {
		${$boxes[$type]}.= ${$boxes[$type]} ? '<br />'.$msg : $msg;
	}

	if (isset($InHelpRegion) && $InHelpRegion) {
		foreach ($boxes as $type => $msgname) {
			if (($type !== 'INFO') || $ShowHelpBox) {
				if (isset(${$msgname}) && (${$msgname} !== '')) {
					${$msgname}= preg_replace("/\n/", '<br />', ${$msgname});
					?>
					<table id="mainhelpbox" style="width: <?php echo $width ?>">
						<tr>
							<th colspan="2">
								<?php echo _($HelpBoxTypes[$type]['name']) ?>
							</th>
						</tr>
						<tr>
							<td class="image">
								<img src="<?php echo $IMG_PATH.$HelpBoxTypes[$type]['icon'] ?>" name="utmfw" alt="utmfw" border="0">
							</td>
							<td>
								<?php echo ${$msgname} ?>
							</td>
						</tr>
					</table>
					<?php
					// Messsage is printed now, reinitialize it
					${$msgname}= '';
				}
			}
		}
	}
}

/**
 * Gets the log file.
 */
function GetLogFile()
{
	global $View;

	$logfile= '';

	if (filter_has_var(INPUT_POST, 'LogFile')) {
		$logfile= filter_input(INPUT_POST, 'LogFile');
	}
	else if ($_SESSION[$View->Model]['LogFile']) {
		$logfile= $_SESSION[$View->Model]['LogFile'];
	}

	if ($View->Controller($output, 'SelectLogFile', $logfile)) {
		$logfile= $output[0];
	}
	else {
		$View->Controller($output, 'SelectLogFile', '');
		$logfile= $output[0];
	}

	$_SESSION[$View->Model]['LogFile']= $logfile;

	return $logfile;
}

/**
 * Prints dropdown box and buttons for logs archives.
 *
 * @param string $logfile Log file selected by user.
 */
function PrintLogFileChooser($logfile)
{
	global $View;

	$selectedlogs= '';
	?>
	<table>
		<tr>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<?php echo _TITLE('Archives').': ' ?>
					<select name="LogFile">
						<?php
						if ($View->Controller($output, 'GetLogFilesList')) {
							$filelist= json_decode($output[0], TRUE);
							foreach ($filelist as $filepath => $startdate) {
								$file= basename($filepath);

								$option= sprintf('%-19s - %s', $startdate, $file);
								if (preg_match('/.*\.gz$/', $file)) {
									// $logfile does not have .gz extension, because it points to the file decompressed by the controller
									// Update this local copy for comparison and to print it below
									$logfile.= basename($logfile).'.gz' == $file ? '.gz' : '';
								}

								if (basename($logfile) == $file) {
									$selected= 'selected';
									$selectedlogs= $option;
								}
								else {
									$selected= '';
								}
								?>
								<option <?php echo $selected ?> value="<?php echo $filepath ?>"><?php echo $option ?></option>
								<?php
							}
						}
						?>
					</select>
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
					<input type="submit" name="Download" value="<?php echo _CONTROL('Download') ?>"/>
				</form>
			</td>
			<td>
				<strong><?php echo _TITLE('Selected').': '.$selectedlogs ?></strong>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Prints NVP graph vertically.
 *
 * @param array $data Data filled in elsewhere.
 * @param string $color Color of the bars.
 * @param string $title Graph title, if provided.
 */
function PrintNVPsVGraph($data, $color= 'red', $title= '')
{
	if (!isset($data)) {
		$data= array();
	}

	$max= 0;
	if (count($data) > 0) {
		$max= max($data);
	}
	?>
	<strong><?php echo $title ?></strong> <?php echo _TITLE('total').'= '.array_sum($data) ?>
	<table id="statsgraph">
		<?php
		foreach ($data as $name => $value) {
			$width= 0;
			if ($value > 0) {
				$width= intval(100 * $value / $max);
			}
			?>
			<tr>
				<td class="hlegend">
					<table>
						<tr id="hbar">
							<td class="legend">
								<?php echo $name ?>
							</td>
						</tr>
					</table>
				</td>
				<td>
					<table>
						<tr id="hbar">
							<td style="width: <?php echo $width ?>%; background: <?php echo $color ?>;">
							</td>
							<?php
							if ($value > 0) {
								?>
								<td class="value"><?php echo $value ?></td>
								<?php
							}
							else {
								?>
								<td class="valuezero"> </td>
								<?php
							}
							?>
						</tr>
					</table>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}

/**
 * Prints vertical graph accross the data range.
 *
 * @todo Can combine with PrintNVPsVGraph()?
 *
 * @param array $data Data filled in elsewhere.
 * @param string $color Color of the bars.
 * @param string $title Graph title, if provided.
 */
function PrintVGraph($data, $color= 'red', $title= '')
{
	if (!isset($data)) {
		$data= array();
	}

	$max= 0;
	if (count($data) > 0) {
		$max= max($data);
	}
	?>
	<strong><?php echo $title ?></strong> <?php echo _TITLE('total').'= '.array_sum($data) ?>
	<table id="statsgraph">
		<tr>
			<td>
				<?php
				for ($i= 0; $i < count($data); $i++) {
					$i= sprintf('%02d', $i);
					$width= 0;
					if (!isset($data[$i])) {
						$data[$i]= 0;
					}
					if ($data[$i] > 0) {
						$width= intval(100 * $data[$i] / $max);
					}
					?>
					<table>
						<tr id="hbar">
							<td class="legend">
								<?php printf('%02d', $i) ?>
							</td>
							<td style="width: <?php echo $width ?>%; background-color: <?php echo $color ?>;">
							</td>
							<?php
							if ($data[$i] > 0) {
								?>
								<td class="value"><?php echo $data[$i] ?></td>
								<?php
							}
							else {
								?>
								<td class="valuezero"> </td>
								<?php
							}
							?>
						</tr>
					</table>
					<?php
				}
				?>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Prints horizontal graph accross the data range.
 *
 * @param array $data Data filled in elsewhere.
 * @param string $color Color of the bars.
 * @param string $title Graph title, if provided.
 */
function PrintHGraph($data, $color= 'red', $title= '')
{
	if (!isset($data)) {
		$data= array();
	}

	$max= 0;
	if (count($data) > 0) {
		$max= max($data);
	}
	?>
	<strong><?php echo $title ?></strong> <?php echo _TITLE('total').'= '.array_sum($data) ?>
	<table id="statsgraph">
		<tr class="hgraph">
			<?php
			for ($i= 0; $i < count($data); $i++) {
				$i= sprintf('%02d', $i);
				?>
				<td>
					<table>
						<tr>
							<td class="bartop">
								<?php
								$height= 0;
								if (!isset($data[$i])) {
									$data[$i]= 0;
								}
								if ($data[$i] > 0) {
									echo $data[$i];
									$height= intval(100 * $data[$i] / $max);
								}
								?>
							</td>
						</tr>
						<tr id="bar">
							<td id="bar" height="<?php echo $height ?>px" style="background-color: <?php echo $color ?>;">
							</td>
						</tr>
						<tr>
							<td class="legend">
								<?php printf('%02d', $i) ?>
							</td>
						</tr>
					</table>
				</td>
				<?php
			}
			?>
		</tr>
	</table>
	<?php
}

/**
 * Prints NVP statistics.
 *
 * @param array $nvps Name-Value-Pair to print.
 * @param string $title Title.
 * @param int $maxcount Number of NVPs to print.
 */
function PrintNVPs($nvps, $title, $maxcount= 100)
{
	?>
	<strong><?php echo $title ?></strong>
	<table id="stats">
		<?php
		if (isset($nvps)) {
			arsort($nvps);

			$count= 0;
			foreach ($nvps as $name => $value) {
				?>
				<tr>
					<td class="value">
						<?php echo $value ?>
					</td>
					<td class="name">
						<?php
						// Empty strings print default gettext header lines otherwise
						echo $name !== '' ? _(htmlspecialchars($name)):'';
						?>
					</td>
				</tr>
				<?php
				if (++$count >= $maxcount) {
					break;
				}
			}
		}
		?>
	</table>
	<?php
}

/**
 * Main function that prints both the graph and the NVPs below it.
 *
 * @param array $stats Data set.
 * @param array $date Datetime struct.
 * @param string $parent Parent field name to get count field.
 * @param array $conf Attributes of graph, title and color.
 * @param string $type Graph direction, horizontal or vertical.
 * @param string $style Precision of graph.
 */
function PrintGraphNVPSet($stats, $date, $parent, $conf, $type, $style)
{
	global $NvpColCount;

	$printfunc= ($type == 'Horizontal') ? 'PrintHGraph' : 'PrintVGraph';
	if ($style == 'Hourly') {
		FillGraphDataRange($data, $stats, $date, 24, $parent);
	}
	else {
		FillDatesGraphData($data, $stats, $date, 'Sum', $parent);
		$printfunc= 'PrintNVPsVGraph';
	}

	if (isset($conf['Divisor'])) {
		DivideArrayData($data, $conf['Divisor']);
	}
	?>
	<table id="statset">
		<tr>
			<td>
				<?php
				$printfunc($data, $conf['Color'], _($conf['Title']));
				?>
				<table>
					<?php
					$count= 0;
					foreach ($conf['NVPs'] as $name => $title) {
						if (($count % $NvpColCount) == 0) {
							?>
							<tr>
							<?php
						}
						?>
						<td class="nvps">
							<?php
							$nvps= array();
							FillNVPs($nvps, $stats, $date, $parent, $name, $style);
							if (isset($conf['Divisor'])) {
								DivideArrayData($nvps, $conf['Divisor']);
							}
							PrintNVPs($nvps, _($title), 10);
							?>
						</td>
						<?php
						if (($count++ % $NvpColCount) == ($NvpColCount - 1)) {
							?>
							</tr>
							<?php
						}
					}
					?>
				</table>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Main function that prints minutes graph and the NVPs below it.
 *
 * @param array $stats Data set.
 * @param string $parent Parent field name to get count field.
 * @param array $conf Attributes of graph, title and color.
 * @param string $type Graph direction, horizontal or vertical.
 */
function PrintMinutesGraphNVPSet($stats, $parent, $conf, $type)
{
	$PrintGraphFunc= ($type == 'Horizontal') ? 'PrintHGraph' : 'PrintVGraph';
	FillGraphData($data, $stats['Mins'], 60, $parent);

	if (isset($conf['Divisor'])) {
		DivideArrayData($data, $conf['Divisor']);
	}
	?>
	<table id="statset">
		<tr>
			<td>
				<?php
				$PrintGraphFunc($data, $conf['Color'], _($conf['Title']));
				?>
				<table>
					<tr>
						<?php
						/// @todo More than 2 or 3 NVPs under graph may be a problem
						foreach ($conf['NVPs'] as $name => $title) {
							if (isset($stats[$parent]) && isset($stats[$parent][$name])) {
								?>
								<td class="nvps">
									<?php
									unset($nvps);
									$nvps= $stats[$parent][$name];
									if (isset($conf['Divisor'])) {
										DivideArrayData($nvps, $conf['Divisor']);
									}
									PrintNVPs($nvps, _($title), 10);
									?>
								</td>
								<?php
							}
						}
						?>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Divides array values by a divisor.
 *
 * Used for KB statistics.
 *
 * @param array $data Name-Value-Pair.
 * @param int $divisor Divisor.
 */
function DivideArrayData(&$data, $divisor)
{
	if (isset($data) && ($divisor != 0)) {
		foreach ($data as $name => $value) {
			$data[$name]= ceil($value / $divisor);
		}
	}
}

/**
 * Fills graph data struct based on dates.
 *
 * Collects the counts classified in dates already obtained in $stats.
 *
 * If Month or Day field is empty the sub fields are assumed all
 * inclusive, respectively.
 *
 * @param array $data Data used by graph functions.
 * @param array $stats Statistics collected elsewhere.
 * @param array $datearray Datetime struct.
 * @param string $name Count field name in $stats array.
 * @param string $parent Parent field name in to get count field, if provided.
 */
function FillDatesGraphData(&$data, $stats, $datearray, $name, $parent= '')
{
	global $View;

	if ($datearray['Month'] == '') {
		for ($m= 1; $m <= 12; $m++) {
			$m= sprintf('%02d', $m);
			for ($d= 1; $d <= 31; $d++) {
				$d= sprintf('%02d', $d);
				$datearray['Month']= $m;
				$datearray['Day']= $d;
				$date= $View->FormatDate($datearray);
				SetGraphData($data, $stats, $date, $name, $parent);
			}
		}
	}
	else if ($datearray['Day'] == '') {
		for ($d= 1; $d <= 31; $d++) {
			$d= sprintf('%02d', $d);
			$datearray['Day']= $d;
			$date= $View->FormatDate($datearray);
			SetGraphData($data, $stats, $date, $name, $parent);
		}
	}
	else {
		$date= $View->FormatDate($datearray);
		SetGraphData($data, $stats, $date, $name, $parent);
	}
}

function SetGraphData(&$data, $stats, $date, $name, $parent)
{
	if ($parent == '') {
		if (isset($stats[$date][$name])) {
			$data[$date]= $stats[$date][$name];
		}
	}
	else {
		if (isset($stats[$date][$parent][$name])) {
			$data[$date]= $stats[$date][$parent][$name];
		}
	}
}

/**
 * Fills graph data struct based on a given range according to date.
 *
 * Range is either 24 (Hours in a day) or 60 (Minutes in an hour)
 *
 * If Month or Day field is empty the sub fields are assumed all
 * inclusive, respectively.
 *
 * @param array $data Data used by graph functions.
 * @param array $stats Statistics collected elsewhere.
 * @param array $datearray Datetime struct.
 * @param string $range Size of the range, 24 or 60.
 * @param string $parent Parent field name in to get count field.
 */
function FillGraphDataRange(&$data, $stats, $datearray, $range, $parent)
{
	global $View;

	if ($datearray['Month'] == '') {
		for ($m= 1; $m <= 12; $m++) {
			$m= sprintf('%02d', $m);
			for ($d= 1; $d <= 31; $d++) {
				$d= sprintf('%02d', $d);
				$datearray['Month']= $m;
				$datearray['Day']= $d;
				$date= $View->FormatDate($datearray);
				FillGraphData($data, $stats[$date]['Hours'], $range, $parent, 'Sum');
			}
		}
	}
	else if ($datearray['Day'] == '') {
		for ($d= 1; $d <= 31; $d++) {
			$d= sprintf('%02d', $d);
			$datearray['Day']= $d;
			$date= $View->FormatDate($datearray);
			FillGraphData($data, $stats[$date]['Hours'], $range, $parent, 'Sum');
		}
	}
	else {
		$date= $View->FormatDate($datearray);
		FillGraphData($data, $stats[$date]['Hours'], $range, $parent, 'Sum');
	}
}

/**
 * Fills graph data struct based on a given range.
 *
 * Analogous to FillDatesGraphData(), except dates are handled in
 * FillGraphDataRange().
 *
 * Range is either 24 (Hours in a day) or 60 (Minutes in an hour).
 * Converts size number in to 2 digit string to index data array.
 *
 * @param array $data Data used by graph functions.
 * @param array $stats Statistics collected elsewhere.
 * @param int $range Size of the range, 24 or 60.
 * @param string $parent Parent field name to get count field.
 * @param string $name Count field name in $stats array.
 */
function FillGraphData(&$data, $stats, $range, $parent, $name= '')
{
	if (isset($stats)) {
		for ($hm= 0; $hm < $range; $hm++) {
			$hm= sprintf('%02d', $hm);

			/// @attention All hours and minutes should be initialized with 0,
			/// even if there is no stats for them
			// Such initialization is faster than any if condition
			$data[$hm]+= 0;

			if ($name != '') {
				if (isset($stats[$hm][$parent][$name])) {
					$data[$hm]+= $stats[$hm][$parent][$name];
				}
			}
			else {
				if (isset($stats[$hm][$parent])) {
					$data[$hm]+= $stats[$hm][$parent];
				}
			}
		}
	}
}

/**
 * Sums up count fields of data arrays.
 *
 * @param array $data Cumulative data summed up.
 * @param array $stats Original data.
 * @param array $datearray Datetime struct.
 * @param string $parent Parent field name to get count field.
 * @param string $name Count field name.
 * @param string $style Graph style, flag to sum days or hours.
 */
function FillNVPs(&$data, $stats, $datearray, $parent, $name, $style)
{
	global $View;

	if ($datearray['Month'] == '') {
		for ($m= 1; $m <= 12; $m++) {
			$m= sprintf('%02d', $m);
			for ($d= 1; $d <= 31; $d++) {
				$datearray['Month']= $m;
				$datearray['Day']= sprintf('%02d', $d);
				$date= $View->FormatDate($datearray);
				MergeStats($data, $stats[$date], $parent, $name, $style);
			}
		}
	}
	else if ($datearray['Day'] == '') {
		for ($d= 1; $d <= 31; $d++) {
			$datearray['Day']= sprintf('%02d', $d);
			$date= $View->FormatDate($datearray);
			MergeStats($data, $stats[$date], $parent, $name, $style);
		}
	}
	else {
		$date= $View->FormatDate($datearray);
		MergeStats($data, $stats[$date], $parent, $name, $style);
	}
}

/**
 * Sums up count fields of data arrays.
 *
 * Sums the values of names.
 * $style is used once to merge minutes of an hour.
 *
 * @todo How to fix $style comparison with gettexted string?
 *
 * @param array $data Cumulative data merged
 * @param array $stats Original data array, passed down as NVPs in recursion
 * @param string $parent Parent field name to get count field
 * @param string $name Count field name
 * @param string $style Graph style, flag to sum days or hours
 */
function MergeStats(&$data, $stats, $parent, $name, $style)
{
	if ($style == _('Hourly')) {
		for ($h= 0; $h < 60; $h++) {
			$h= sprintf('%02d', $h);
			SumData($data, $stats['Hours'][$h][$parent][$name]);
		}
	}
	else {
		SumData($data, $stats[$parent][$name]);
	}
}

/**
 * Adds statistics values to the given data set.
 */
function SumData(&$data, $stats)
{
	if (isset($stats)) {
		foreach ($stats as $name => $value) {
			$data[$name]+= $value;
		}
	}
}

/**
 * Updates refresh interval for live pages
 */
function SetRefreshInterval()
{
	global $View;

	if (filter_has_var(INPUT_POST, 'RefreshInterval')) {
		if (preg_match('/^\d+$/', filter_input(INPUT_POST, 'RefreshInterval'))) {
			$_SESSION[$View->Model]['ReloadRate']= filter_input(INPUT_POST, 'RefreshInterval') >= 3 ? filter_input(INPUT_POST, 'RefreshInterval'):3;
		}
		else {
			PrintHelpWindow(_NOTICE('FAILED').': '._TITLE('Refresh interval').': '.filter_input(INPUT_POST, 'RefreshInterval'), 'auto', 'ERROR');
		}
	}
}

/**
 * Prints archives log help box.
 */
function PrintLogsHelp($msg)
{
	if (isset($msg)) {
		$msg.= "\n\n"._HELPWINDOW('Log keeping capacity of UTMFW is limited only by the size of the disks on your system.

If you are not seeing as many number of lines as you were expecting, this may be because the log file has turned over and put in a compressed archive file. The default maximum number of archive files for most services is 100, and can be configured on the system configuration pages. Depending on how busy a service it is, this many log archives may mean months of logging in most cases. You can download the log files using the Download button.

You can search the logs by entering keywords or extended regular expressions in Regexp box. Regular expressions are de-facto standard for text searching.');
	}

	PrintHelpWindow($msg);
}

/**
 * Prints live logs help box.
 */
function PrintLiveLogsHelp($msg)
{
	if (isset($msg)) {
		$msg.= "\n\n"._HELPWINDOW('If you are not seeing as many number of lines as you were expecting, this may be because the log file has turned over and put in a compressed archive file.

You can search the logs by entering keywords or extended regular expressions in Regexp box. Regular expressions are de-facto standard for text searching.');
	}

	PrintHelpWindow($msg);
}

/**
 * Processes posted start line on logs pages.
 */
function ProcessStartLine(&$startline)
{
	global $View;

	if (filter_has_var(INPUT_POST, 'StartLine')) {
		if (preg_match('/^\d+$/', filter_input(INPUT_POST, 'StartLine'))) {
			$_SESSION[$View->Model]['StartLine']= filter_input(INPUT_POST, 'StartLine') - 1;
		}
		else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Page start line').': '.filter_input(INPUT_POST, 'StartLine'), 'auto', 'ERROR');
		}
	}

	if ($_SESSION[$View->Model]['StartLine']) {
		$startline= $_SESSION[$View->Model]['StartLine'];
	}
	else {
		$startline= 0;
		$_SESSION[$View->Model]['StartLine']= $startline;
	}
}

/**
 *  Processes posted navigation buttons on logs pages.
 */
function ProcessNavigationButtons($linesperpage, $total, &$startline, &$headstart)
{
	global $View;

	if (count($_POST)) {
		if (filter_has_var(INPUT_POST, 'First')) {
			$startline= 0;
		}
		else if (filter_has_var(INPUT_POST, 'Previous')) {
			$startline-= $linesperpage;
		}
		else if (filter_has_var(INPUT_POST, 'Next')) {
			$startline+= $linesperpage;
		}
		else if (filter_has_var(INPUT_POST, 'Last')) {
			$startline= $total;
		}
	}

	$headstart= $startline + $linesperpage;
	if ($headstart > $total) {
		$headstart= $total;
		$startline= $headstart - $linesperpage;
	}
	if ($startline < 0) {
		$startline= 0;
		$headstart= $linesperpage;
	}
	$_SESSION[$View->Model]['StartLine']= $startline;
}

/**
 * Displays navigation buttons on logs pages.
 *
 * @todo $hidden seems like a hack, find a better way?
 *
 * @param int $start First line to start listing.
 * @param int $total Number of lines in the logs (obtained somewhere else).
 * @param int $count Number of lines to list.
 * @param string $re Regexp to use in grep over logs.
 * @param string $hidden Some modules may need extra hidden inputs added to form.
 */
function PrintLogHeaderForm($start, $total, $count, $re, $hidden)
{
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<table id="nvp">
			<tr class="oddline">
				<td>
					<?php echo _TITLE('Line').':' ?>
					<input type="text" name="StartLine" style="width: 50px;" maxlength="10" value="<?php echo $start + 1 ?>" />/<?php echo $total ?>
				</td>
				<td>
					<?php echo _TITLE('Lines per page').':' ?>
					<input type="text" name="LinesPerPage" style="width: 30px;" maxlength="3" value="<?php echo $count ?>" />
				</td>
				<td>
					<?php echo _TITLE('Regexp').':' ?>
					<input type="text" name="SearchRegExp" style="width: 300px;" maxlength="200" value="<?php echo $re ?>" />
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</td>
			</tr>
			<tr class="evenline">
				<td class="center" colspan="5">
					<input type="submit" name="First" value="<?php echo _CONTROL('<< First') ?>"/>
					<input type="submit" name="Previous" value="<?php echo _CONTROL('< Previous') ?>"/>
					<input type="submit" name="Next" value="<?php echo _CONTROL('Next >') ?>"/>
					<input type="submit" name="Last" value="<?php echo _CONTROL('Last >>') ?>"/>
				</td>
			</tr>
		</table>
		<?php
		if ($hidden) {
			echo $hidden;
		}
		?>
	</form>
	<?php
}

/**
 * Displays controls on live logs pages.
 *
 * Uses session variables.
 */
function PrintLiveLogHeaderForm()
{
	global $View;
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<table id="nvp">
			<tr class="oddline">
				<td>
					<?php echo _TITLE('Refresh interval').':' ?><input type="text" name="RefreshInterval" style="width: 20px;" maxlength="2" value="<?php echo $_SESSION[$View->Model]['ReloadRate'] ?>" />
					<?php echo _TITLE('secs') ?>
				</td>
				<td>
					<?php echo _TITLE('Lines per page').':' ?><input type="text" name="LinesPerPage" style="width: 20px;" maxlength="2" value="<?php echo $_SESSION[$View->Model]['LinesPerPage'] ?>" />
				</td>
				<td>
					<?php echo _TITLE('Regexp').':' ?><input type="text" name="SearchRegExp" style="width: 300px;" maxlength="200" value="<?php echo $_SESSION[$View->Model]['SearchRegExp'] ?>" />
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</td>
			</tr>
		</table>
	</form>
	<?php
}

/**
 * Updates logs and live logs session variables.
 *
 * @param int $count Max line per log page.
 * @param string $re Regexp to use over logs file.
 */
function UpdateLogsPageSessionVars(&$count, &$re)
{
	global $View;

	if (filter_has_var(INPUT_POST, 'LinesPerPage')) {
		if (preg_match('/^\d+$/', filter_input(INPUT_POST, 'LinesPerPage'))) {
			$_SESSION[$View->Model]['LinesPerPage']= filter_input(INPUT_POST, 'LinesPerPage');
		}
		else {
			PrintHelpWindow(_NOTICE('FAILED').': '._TITLE('Lines per page').': '.filter_input(INPUT_POST, 'LinesPerPage'), 'auto', 'ERROR');
		}
	}

	if ($_SESSION[$View->Model]['LinesPerPage']) {
		$count= $_SESSION[$View->Model]['LinesPerPage'];
	}
	else {
		$count= 25;
		$_SESSION[$View->Model]['LinesPerPage']= $count;
	}

	// Empty regexp posted is used to clear the session regexp, use isset() here
	if (filter_has_var(INPUT_POST, 'SearchRegExp')) {
		$_SESSION[$View->Model]['SearchRegExp']= filter_input(INPUT_POST, 'SearchRegExp');
	}

	if ($_SESSION[$View->Model]['SearchRegExp']) {
		$re= RemoveBackSlashes($_SESSION[$View->Model]['SearchRegExp']);
		$_SESSION[$View->Model]['SearchRegExp']= $re;
	}
	else {
		$re= '';
	}
}

/**
 * Prints table headers for logs pages.
 *
 * $view may be different from View object name, hence passed as a param.
 *
 * @param string $view Module name, $LogConf index.
 */
function PrintTableHeaders($view)
{
	global $LogConf;

	$gettextheaders= array(
		_TITLE('Time'),
		_TITLE('Process'),
		_TITLE('Log'),
		_TITLE('Rule'),
		_TITLE('Rule'),
		_TITLE('DateTime'),
		_TITLE('Target'),
		_TITLE('Size'),
		_TITLE('Level'),
	);
	?>
	<tr id="logline">
		<th><?php echo _('Line') ?></th>
		<?php
		foreach ($LogConf[$view]['Fields'] as $header) {
			?>
			<th><?php echo _($header) ?></th>
			<?php
		}
		?>
	</tr>
	<?php
}

/**
 * Displays log fields in columns.
 *
 * This cannot be a View member function, because used by non-Views too.
 *
 * @param int $linenum Line number of the log line
 * @param array $cols Parsed log line
 * @param string $module Module name if different from $View, $LogConf index
 */
function PrintLogCols($linenum, $cols, $module= '')
{
	global $LogConf, $View;

	/// Module name may be different from the current View name
	if ($module == '') {
		$module= $View->Model;
	}

	$View->FormatLogCols($cols);

	// Center the line number column
	?>
	<td class="center">
		<?php echo $linenum ?>
	</td>
	<?php
	foreach ($LogConf[$module]['Fields'] as $field) {
		?>
		<td>
			<?php echo $cols[$field] ?>
		</td>
		<?php
	}
}

function RemoveBackSlashes($str) {

 	return preg_replace('/\\\\\\\\/', '\\', $str);
}

/**
 * Reads sysctl hw values.
 *
 * @param array $names Names of hw values to read.
 * @param array $hw sysctl values in NVP form, output.
 */
function GetHwInfo($names, &$hw)
{
	global $View;

	$hw= array();
	if ($View->Controller($output, 'GetSysCtl', 'hw')) {
		// Create text from array first
		$lines= implode("\n", $output);
		foreach ($names as $name) {
			if (preg_match("/^hw\.$name=(.*)$/m", $lines, $match)) {
				$hw[$name]= $match[1];
			}
			else {
				$hw[$name]= _('Unknown');
			}
		}
	}
}
?>
