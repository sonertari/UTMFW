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

/** @file
 * WUI library functions.
 */

/**
 * Prints the message in a simple box, without an image.
 *
 * Used in simple info boxes on the right of components.
 * New lines are replaced with html breaks before displaying.
 *
 * @warning Checks if $msg is empty, because some automatized functions may
 * not pass a non-empty string (such as on configuration pages), thus the box
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
 * @param mixed $width Box width, defaults to auto.
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
	} else if ($_SESSION[$View->Model]['LogFile']) {
		$logfile= $_SESSION[$View->Model]['LogFile'];
	}

	if ($View->Controller($output, 'SelectLogFile', $logfile)) {
		$logfile= $output[0];
	} else if ($View->Controller($output, 'SelectLogFile', '')) {
		$logfile= $output[0];
	} else {
		return FALSE;
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
	<table style="width: auto;">
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

/// Provides a unique form id for NVP and graph print functions
$FormIdCount = 0;

/**
 * Prints NVP graph vertically.
 *
 * @param array $data Data filled in elsewhere.
 * @param string $color Color of the bars.
 * @param string $title Graph title, if provided.
 */
function PrintNVPsVGraph($data, $color= 'red', $title= '')
{
	global $FormIdCount, $View;

	if (!isset($data)) {
		$data= array();
	}

	$dataValues= array_map(function ($a) { return $a['value']; }, $data);

	$max= 0;
	if (count($dataValues) > 0) {
		$max= max($dataValues);
	}
	?>
	<strong><?php echo $title ?></strong> <?php echo _TITLE('total').'= '.array_sum($dataValues) ?>
	<table id="statsgraph">
		<?php
		foreach ($data as $name => $valueArray) {
			$value= $valueArray['value'];
			$dateArray= $valueArray['date'];

			$formId= 'form'.$FormIdCount++;

			$width= 0;
			if ($value > 0) {
				$width= intval(100 * $value / $max);
			}
			?>
			<form id="<?php echo $formId ?>" name="<?php echo $formId ?>" action="<?php echo $View->StatsPage ?>?submenu=daily" method="post">
				<input type="hidden" name="Month" value="<?php echo $dateArray['Month'] ?>" />
				<input type="hidden" name="Day" value="<?php echo $dateArray['Day'] ?>" />
				<input type="hidden" name="Hour" value="<?php echo $dateArray['Hour'] ?>" />
				<input type="hidden" name="Apply" value="Apply" />
				<input type="hidden" name="Sender" value="Stats" />
			</form>
			<tr onclick="document.<?php echo $formId ?>.submit()" style="cursor: pointer;" title="<?php echo _TITLE('Click to search in the stats') ?>">
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
 * Prints vertical graph across the data range.
 *
 * @todo Can combine with PrintNVPsVGraph()?
 *
 * @param array $data Data filled in elsewhere.
 * @param string $color Color of the bars.
 * @param string $title Graph title, if provided.
 * @param boolean $page TRUE if the caller is hourly stats page, used to jump to the correct page.
 * @param string $needle Search regexp to filter the logs, used by hourly stats.
 * @param string $logFile Current log file, used by live stats pages to set the log file to the active one.
 */
function PrintVGraph($data, $color= 'red', $title= '', $page= 'general', $style= 'Daily', $needle= '', $logFile= '')
{
	global $FormIdCount, $View;

	if (!isset($data)) {
		$data= array();
	}

	$dataValues= array_map(function ($a) { return $a['value']; }, $data);

	$max= 0;
	if (count($dataValues) > 0) {
		$max= max($dataValues);
	}
	?>
	<strong><?php echo $title ?></strong> <?php echo _TITLE('total').'= '.array_sum($dataValues) ?>
	<table id="statsgraph">
		<tr>
			<td>
				<?php
				if ($page == 'hourly' || ($page == 'general' && $style= 'Hourly')) {
					$action= "$View->LogsPage?submenu=archives";
					$title= _TITLE('Click to search in the logs');
				} else {
					$action= "$View->StatsPage?submenu=hourly";
					$title= _TITLE('Click to search in the stats');
				}

				for ($i= 0; $i < count($data); $i++) {
					$i= sprintf('%02d', $i);
					$width= 0;
					if (!isset($data[$i])) {
						$data[$i]['value']= 0;
					}
					if ($data[$i]['value'] > 0) {
						$width= intval(100 * $data[$i]['value'] / $max);
					}

					$dateArray= $data[$i]['date'];

					if ($page == 'general' && $style= 'Hourly') {
						$dateArray['Month']= '';
						$dateArray['Day']= '';
					}

					$formId= 'form'.$FormIdCount++;
					?>
					<form id="<?php echo $formId ?>" name="<?php echo $formId ?>" action="<?php echo $action ?>" method="post">
						<input type="hidden" name="SearchRegExp" value="" />
						<input type="hidden" name="SearchNeedle" value="<?php echo $needle ?>" />
						<input type="hidden" name="Month" value="<?php echo $dateArray['Month'] ?>" />
						<input type="hidden" name="Day" value="<?php echo $dateArray['Day'] ?>" />
						<input type="hidden" name="Hour" value="<?php echo $dateArray['Hour'] ?>" />
						<input type="hidden" name="Minute" value="<?php echo $dateArray['Minute'] ?>" />
						<?php
						if ($logFile != '') {
							?>
							<input type="hidden" name="LogFile" value="<?php echo $logFile ?>" />
							<?php
						}
						?>
						<input type="hidden" name="Apply" value="Apply" />
						<input type="hidden" name="Sender" value="Stats" />
					</form>
					<table>
						<tr id="hbar" onclick="document.<?php echo $formId ?>.submit()" style="cursor: pointer;" title="<?php echo $title ?>">
							<td class="legend">
								<?php printf('%02d', $i) ?>
							</td>
							<td style="width: <?php echo $width ?>%; background-color: <?php echo $color ?>;">
							</td>
							<?php
							if ($data[$i]['value'] > 0) {
								?>
								<td class="value"><?php echo $data[$i]['value'] ?></td>
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
 * Prints horizontal graph across the data range.
 *
 * @param array $data Data filled in elsewhere.
 * @param string $color Color of the bars.
 * @param string $title Graph title, if provided.
 * @param boolean $page TRUE if the caller is hourly stats page, used to jump to the correct page.
 * @param string $needle Search regexp to filter the logs, used by hourly stats.
 * @param string $logFile Current log file, used by live stats pages to set the log file to the active one.
 */
function PrintHGraph($data, $color= 'red', $title= '', $page= 'general', $style= 'Daily', $needle= '', $logFile= '')
{
	global $FormIdCount, $View;

	if (!isset($data)) {
		$data= array();
	}

	$dataValues= array_map(function ($a) { return $a['value']; }, $data);

	$max= 0;
	if (count($dataValues) > 0) {
		$max= max($dataValues);
	}
	?>
	<strong><?php echo $title ?></strong> <?php echo _TITLE('total').'= '.array_sum($dataValues) ?>
	<table id="statsgraph">
		<tr class="hgraph">
			<?php
			if ($page == 'hourly' || ($page == 'general' && $style= 'Hourly')) {
				$action= "$View->LogsPage?submenu=archives";
				$title= _TITLE('Click to search in the logs');
			} else {
				$action= "$View->StatsPage?submenu=hourly";
				$title= _TITLE('Click to search in the stats');
			}

			for ($i= 0; $i < count($data); $i++) {
				$i= sprintf('%02d', $i);
				$dateArray= $data[$i]['date'];

				if ($page == 'general' && $style= 'Hourly') {
					$dateArray['Month']= '';
					$dateArray['Day']= '';
				}

				$formId= 'form'.$FormIdCount++;
				?>
				<form id="<?php echo $formId ?>" name="<?php echo $formId ?>" action="<?php echo $action ?>" method="post">
					<input type="hidden" name="SearchRegExp" value="" />
					<input type="hidden" name="SearchNeedle" value="<?php echo $needle ?>" />
					<input type="hidden" name="Month" value="<?php echo $dateArray['Month'] ?>" />
					<input type="hidden" name="Day" value="<?php echo $dateArray['Day'] ?>" />
					<input type="hidden" name="Hour" value="<?php echo $dateArray['Hour'] ?>" />
					<input type="hidden" name="Minute" value="<?php echo $dateArray['Minute'] ?>" />
					<?php
					if ($logFile != '') {
						?>
						<input type="hidden" name="LogFile" value="<?php echo $logFile ?>" />
						<?php
					}
					?>
					<input type="hidden" name="Apply" value="Apply" />
					<input type="hidden" name="Sender" value="Stats" />
				</form>
				<td onclick="document.<?php echo $formId ?>.submit()" style="cursor: pointer;" title="<?php echo $title ?>">
					<table>
						<tr>
							<td class="bartop">
								<?php
								$height= 0;
								if (!isset($data[$i])) {
									$data[$i]['value']= 0;
								}
								if ($data[$i]['value'] > 0) {
									echo $data[$i]['value'];
									$height= intval(100 * $data[$i]['value'] / $max);
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
 * @param array $nvps Name-Value-Pairs to print.
 * @param string $title Title.
 * @param int $maxcount Number of NVPs to print.
 * @param boolean $pie TRUE if we are to print pie chart trigger image.
 * @param string $needle Search regexp to filter the logs.
 * @param string $prefix Regexp to insert before the search string.
 * @param string $postfix Regexp to insert after the search string.
 * @param array $dateArray Datetime to restrict the searches.
 * @param string $logFile Current log file, used by live stats pages to set the log file to the active one.
 */
function PrintNVPs($nvps, $title, $maxcount= 100, $pie=TRUE, $needle='', $prefix='', $postfix='', $dateArray=array(), $logFile= '')
{
	global $IMG_PATH, $View, $FormIdCount;
	?>
	<strong><?php echo $title ?></strong>
	<table id="stats">
		<?php
		if (isset($nvps)) {
			arsort($nvps);

			$count= 0;
			foreach ($nvps as $n => $value) {
				?>
				<tr>
					<td class="value">
						<?php echo $value ?>
					</td>
					<td class="name">
						<?php
						// Empty strings print default gettext header lines otherwise
						$name= $n !== '' ? _(htmlspecialchars($n)):'-';
						
						// Needle can be used to disable log searches
						if ($needle === FALSE) {
							echo $name;
						} else {
							// Need unique ids for each form for submission to work
							$formId = 'form'.$FormIdCount++;

							// Need SearchRegExp and action URL supplied externally
							// Regex should search for a case sensitive exact match, otherwise github matches live.github or GITHUB too

							// Caveats of this regexp method:
							// @todo P3scan Number of e-mails Clean Exit: Searches all, need a separate parser, e.g. for (Clean Exit). Mails: 1
							// @todo P3scan pop3s: Cannot find pop3s, need to make POP3S lowercase
							// @todo Smtp-gated Source IPs: Searches and finds all IPs, not just source (or destination), the same issue with other modules
							// @todo Openssh briefstats password: Searches and finds all password words, not just failed attempts
							// @todo syslog "last message repeated x times": Causes more date lines in brief stats than can be parsed, need -rr option in OpenBSD 6.x/syslog
							// @todo Briefstats should use the Total Needle too, otherwise we cannot get general statistics for, say, spamassassin

							// Use default pre/postfixes if not supplied by the caller
							if ($prefix == '') {
								// @attention Use [[:blank:]]+, [:blank:]+ does not work
								$prefix= '([[:blank:]]+)';
							}

							if ($postfix == '') {
								$postfix= '([^[:alnum:]]+)';
							}

							/// @attention Do not use the needle in the regexp, use it separately, or grep takes too long,
							// ~30 secs if search name is long, as in SSLproxy error or warning messages. Two cascaded greps are very fast.
							// Otherwise, the following would/could do the same job.
							//$regexp= $needle == '' ?
							//	"($prefix|^)${name}($postfix|$)" :
							//	"(($prefix|^)${name}$postfix.*($needle)|($needle).*$prefix${name}($postfix|$))";

							$regexp= "($prefix|^)".Escape($name, '()[]+?')."($postfix|$)";

							/// @attention Do not use href in anchor, otherwise href overrides the onclick action sometimes, hence the cursor style
							?>
							<form id="<?php echo $formId ?>" name="<?php echo $formId ?>" action="<?php echo $View->LogsPage ?>?submenu=archives" method="post">
								<input type="hidden" name="SearchRegExp" value="<?php echo $regexp ?>" />
								<input type="hidden" name="SearchNeedle" value="<?php echo $needle ?>" />
								<input type="hidden" name="Month" value="<?php echo $dateArray['Month'] ?>" />
								<input type="hidden" name="Day" value="<?php echo $dateArray['Day'] ?>" />
								<input type="hidden" name="Hour" value="<?php echo $dateArray['Hour'] ?>" />
								<?php
								if ($logFile != '') {
									?>
									<input type="hidden" name="LogFile" value="<?php echo $logFile ?>" />
									<?php
								}
								?>
								<input type="hidden" name="Sender" value="Stats" />
							</form>
							<a onclick="document.<?php echo $formId ?>.submit()" style="cursor: pointer;" title="<?php echo _TITLE('Click to search in the logs') ?>"><?php echo $name ?></a>
							<?php
						}
						?>
					</td>
					<?php
					if ($count == 0 && $pie) {
						?>
						<img id="chart" class="chart-trigger" onclick="generateChart(<?php echo str_replace('"', "'", str_replace("'", "\'", json_encode($nvps))) ?>, <?php echo "'".str_replace("'", "\'", $title)."'" ?>);"
							src="<?php echo $IMG_PATH.'chart.png' ?>" name="<?php echo $title ?>" alt="<?php echo $title ?>" align="absmiddle" >
						<?php
					}
					?>
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
 * @param string $prefix Regexp to insert before the search string.
 * @param string $postfix Regexp to insert after the search string.
 */
function PrintGraphNVPSet($stats, $date, $parent, $conf, $type, $style, $prefix, $postfix, $page)
{
	global $NvpColCount;

	// The default is Horizontal
	$printFunc= ($type == 'Vertical') ? 'PrintVGraph' : 'PrintHGraph';
	if ($style == 'Hourly') {
		FillGraphDataRange($data, $stats, $date, 24, $parent);
	}
	else {
		FillDatesGraphData($data, $stats, $date, 'Sum', $parent);
		$printFunc= 'PrintNVPsVGraph';
	}

	if (isset($conf['Divisor'])) {
		DivideArrayData($data, $conf['Divisor']);
	}
	?>
	<table id="statset">
		<tr>
			<td>
				<?php
				$printFunc($data, $conf['Color'], _($conf['Title']), $page, $style, $conf['Needle']);
				if (array_key_exists('NVPs', $conf) && count($conf['NVPs']) > 0) {
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
								PrintNVPs($nvps, _($title), 10, TRUE, $conf['Needle'], $prefix, $postfix, $date);
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
					<?php
				}
				?>
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
 * @param string $prefix Regexp to insert before the search string.
 * @param string $postfix Regexp to insert after the search string.
 * @param array $dateArray Datetime to restrict the searches.
 * @param string $logFile Current log file, used by live stats pages to set the log file to the active one.
 */
function PrintMinutesGraphNVPSet($stats, $parent, $conf, $type, $prefix, $postfix, $dateArray, $logFile='')
{
	// The default is Horizontal
	$printGraphFunc= ($type == 'Vertical') ? 'PrintVGraph' : 'PrintHGraph';
	FillGraphData($data, $stats['Mins'], 60, $parent, '', $dateArray);

	if (isset($conf['Divisor'])) {
		DivideArrayData($data, $conf['Divisor']);
	}
	?>
	<table id="statset">
		<tr>
			<td>
				<?php
				$printGraphFunc($data, $conf['Color'], _($conf['Title']), 'hourly', 'Hourly', $conf['Needle'], $logFile);
				if (array_key_exists('NVPs', $conf) && count($conf['NVPs']) > 0) {
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
										PrintNVPs($nvps, _($title), 10, TRUE, $conf['Needle'], $prefix, $postfix, $dateArray, $logFile);
										?>
									</td>
									<?php
								}
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
			if (is_array($value)) {
				$data[$name]['value']= ceil($data[$name]['value'] / $divisor);
			} else {
				$data[$name]= ceil($value / $divisor);
			}
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
 * @param array $dateArray Datetime struct.
 * @param string $name Count field name in $stats array.
 * @param string $parent Parent field name in to get count field, if provided.
 */
function FillDatesGraphData(&$data, $stats, $dateArray, $name, $parent= '')
{
	if ($dateArray['Month'] == '') {
		for ($m= 1; $m <= 12; $m++) {
			$m= sprintf('%02d', $m);
			for ($d= 1; $d <= 31; $d++) {
				$d= sprintf('%02d', $d);
				$dateArray['Month']= $m;
				$dateArray['Day']= $d;
				SetGraphData($data, $stats, $dateArray, $name, $parent);
			}
		}
	}
	else if ($dateArray['Day'] == '') {
		for ($d= 1; $d <= 31; $d++) {
			$d= sprintf('%02d', $d);
			$dateArray['Day']= $d;
			SetGraphData($data, $stats, $dateArray, $name, $parent);
		}
	}
	else {
		SetGraphData($data, $stats, $dateArray, $name, $parent);
	}
}

function SetGraphData(&$data, $stats, $dateArray, $name, $parent)
{
	global $View;

	$date= $View->FormatDate($dateArray);

	if ($parent == '') {
		if (isset($stats[$date][$name])) {
			$data[$date]= array(
				'value' => $stats[$date][$name],
				'date' => $dateArray,
				);
		}
	}
	else {
		if (isset($stats[$date][$parent][$name])) {
			$data[$date]= array(
				'value' => $stats[$date][$parent][$name],
				'date' => $dateArray,
				);
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
 * @param array $dateArray Datetime struct.
 * @param string $range Size of the range, 24 or 60.
 * @param string $parent Parent field name in to get count field.
 */
function FillGraphDataRange(&$data, $stats, $dateArray, $range, $parent)
{
	global $View;

	if ($dateArray['Month'] == '') {
		for ($m= 1; $m <= 12; $m++) {
			$m= sprintf('%02d', $m);
			for ($d= 1; $d <= 31; $d++) {
				$d= sprintf('%02d', $d);
				$dateArray['Month']= $m;
				$dateArray['Day']= $d;
				$date= $View->FormatDate($dateArray);
				FillGraphData($data, $stats[$date]['Hours'], $range, $parent, 'Sum', $dateArray);
			}
		}
	}
	else if ($dateArray['Day'] == '') {
		for ($d= 1; $d <= 31; $d++) {
			$d= sprintf('%02d', $d);
			$dateArray['Day']= $d;
			$date= $View->FormatDate($dateArray);
			FillGraphData($data, $stats[$date]['Hours'], $range, $parent, 'Sum', $dateArray);
		}
	}
	else {
		$date= $View->FormatDate($dateArray);
		FillGraphData($data, $stats[$date]['Hours'], $range, $parent, 'Sum', $dateArray);
	}
}

/**
 * Fills graph data struct based on a given range.
 *
 * Analogous to FillDatesGraphData(), except dates are handled in
 * FillGraphDataRange().
 *
 * Range is either 24 (Hours in a day) or 60 (Minutes in an hour).
 * Converts size number into 2 digit string to index data array.
 *
 * @param array $data Data used by graph functions.
 * @param array $stats Statistics collected elsewhere.
 * @param int $range Size of the range, 24 or 60.
 * @param string $parent Parent field name to get count field.
 * @param string $name Count field name in $stats array.
 * @param array $dateArray Datetime to restrict the searches.
 */
function FillGraphData(&$data, $stats, $range, $parent, $name= '', $dateArray=array())
{
	if (isset($stats)) {
		for ($hm= 0; $hm < $range; $hm++) {
			$hm= sprintf('%02d', $hm);

			/// @attention All hours and minutes should be initialized with 0,
			/// even if there is no stats for them
			// Such initialization is faster than any if condition
			$data[$hm]['value']+= 0;

			if (!isset($data[$hm]['date'])) {
				if ($range == 24) {
					$dateArray['Hour']= $hm;
				} else {
					$dateArray['Minute']= $hm;
				}
				$data[$hm]['date']= $dateArray;
			}

			if ($name != '') {
				if (isset($stats[$hm][$parent][$name])) {
					$data[$hm]['value']+= $stats[$hm][$parent][$name];
				}
			}
			else {
				if (isset($stats[$hm][$parent])) {
					$data[$hm]['value']+= $stats[$hm][$parent];
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
	global $View, $TopMenu;

	if (filter_has_var(INPUT_POST, 'RefreshInterval')) {
		if (preg_match('/^\d+$/', filter_input(INPUT_POST, 'RefreshInterval'))) {
			$_SESSION[$View->Model][$TopMenu]['ReloadRate']= filter_input(INPUT_POST, 'RefreshInterval') >= 3 ? filter_input(INPUT_POST, 'RefreshInterval'):3;
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
	global $View, $TopMenu;

	$pageSession= &$_SESSION[$View->Model][$TopMenu];

	if (filter_has_var(INPUT_POST, 'StartLine')) {
		if (preg_match('/^\d+$/', filter_input(INPUT_POST, 'StartLine'))) {
			$pageSession['StartLine']= filter_input(INPUT_POST, 'StartLine') - 1;
		}
		else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Page start line').': '.filter_input(INPUT_POST, 'StartLine'), 'auto', 'ERROR');
		}
	}

	if ($pageSession['StartLine']) {
		$startline= $pageSession['StartLine'];
	}
	else {
		$startline= 0;
		$pageSession['StartLine']= $startline;
	}
}

/**
 *  Processes posted navigation buttons on logs pages.
 */
function ProcessNavigationButtons($linesperpage, $total, &$startline, &$headstart)
{
	global $View, $TopMenu;

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
	$_SESSION[$View->Model][$TopMenu]['StartLine']= $startline;
}

/**
 * Displays navigation buttons on logs pages.
 *
 * @todo $hidden seems like a hack, find a better way?
 *
 * @param int $start First line to start listing.
 * @param int $total Number of lines in the logs (obtained somewhere else).
 * @param int $count Number of lines to list.
 * @param string $re Regexp to use with grep over logs.
 * @param string $hidden Some modules may need extra hidden inputs added to form.
 * @param string $needle Optional regexp to use with a second grep over logs, used by Stats pages.
 * @param boolean $showDateTimeSelect TRUE if we are to show datetime selection.
 * @param array $dateArray Datetime to filter the logs.
 */
function PrintLogHeaderForm($start, $total, $count, $re, $hidden, $needle='', $showDateTimeSelect=FALSE, $dateArray=array())
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
					<?php echo _TITLE('Needle').':' ?>
					<input type="text" name="SearchNeedle" style="width: 100px;" maxlength="200" value="<?php echo $needle ?>" />
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</td>
			</tr>
			<tr class="evenline">
				<td class="center" colspan="<?php echo $showDateTimeSelect ? 2:3 ?>">
					<input type="submit" name="First" value="<?php echo _CONTROL('<< First') ?>"/>
					<input type="submit" name="Previous" value="<?php echo _CONTROL('< Previous') ?>"/>
					<input type="submit" name="Next" value="<?php echo _CONTROL('Next >') ?>"/>
					<input type="submit" name="Last" value="<?php echo _CONTROL('Last >>') ?>"/>
				</td>
				<?php
				if ($showDateTimeSelect) {
					?>
					<td>
						<?php echo _TITLE('Month').':' ?>
						<select name="Month" style="width: 50px;">
							<option value=""></option>
							<?php
							for ($m= 1; $m <= 12; $m++) {
								$m= sprintf('%02d', $m);
								$selected= ($dateArray['Month'] == $m) ? 'selected' : '';
								?>
								<option <?php echo $selected ?> value="<?php echo $m ?>"><?php echo $m ?></option>
								<?php
							}
							?>
						</select>
						<?php echo _TITLE('Day').':' ?>
						<select name="Day" style="width: 50px;">
							<option value=""></option>
							<?php
							for ($d= 1; $d <= 31; $d++) {
								$d= sprintf('%02d', $d);
								$selected= ($dateArray['Day'] == $d) ? 'selected' : '';
								?>
								<option <?php echo $selected ?> value="<?php echo $d ?>"><?php echo $d ?></option>
								<?php
							}
							?>
						</select>
						<?php echo _TITLE('Hour').':' ?>
						<select name="Hour">
							<option value=""></option>
							<?php
							for ($h= 0; $h < 24; $h++) {
								$h= sprintf('%02d', $h);
								$selected= ($dateArray['Hour'] == $h) ? 'selected' : '';
								?>
								<option <?php echo $selected ?> value="<?php echo $h ?>"><?php echo $h ?></option>
								<?php
							}
							?>
						</select>
						<?php echo _TITLE('Minute').':' ?>
						<select name="Minute">
							<option value=""></option>
							<?php
							for ($m= 0; $m < 60; $m++) {
								$m= sprintf('%02d', $m);
								$selected= ($dateArray['Minute'] == $m) ? 'selected' : '';
								?>
								<option <?php echo $selected ?> value="<?php echo $m ?>"><?php echo $m ?></option>
								<?php
							}
							?>
						</select>
						<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
						<input type="submit" name="Defaults" value="<?php echo _CONTROL('Defaults') ?>"/>
					</td>
					<?php
				}
				?>
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
	global $View, $TopMenu;
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<table id="nvp">
			<tr class="oddline">
				<td>
					<?php echo _TITLE('Refresh interval').':' ?><input type="text" name="RefreshInterval" style="width: 20px;" maxlength="2" value="<?php echo $_SESSION[$View->Model][$TopMenu]['ReloadRate'] ?>" />
					<?php echo _TITLE('secs') ?>
				</td>
				<td>
					<?php echo _TITLE('Lines per page').':' ?><input type="text" name="LinesPerPage" style="width: 20px;" maxlength="2" value="<?php echo $_SESSION[$View->Model][$TopMenu]['LinesPerPage'] ?>" />
				</td>
				<td>
					<?php echo _TITLE('Regexp').':' ?><input type="text" name="SearchRegExp" style="width: 300px;" maxlength="200" value="<?php echo $_SESSION[$View->Model][$TopMenu]['SearchRegExp'] ?>" />
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
 * @param string $needle Optional regexp to use with a second grep over logs, used by Stats pages.
 */
function UpdateLogsPageSessionVars(&$count, &$re, &$needle)
{
	global $View, $TopMenu;

	$pageSession= &$_SESSION[$View->Model][$TopMenu];

	if (filter_has_var(INPUT_POST, 'LinesPerPage')) {
		if (preg_match('/^\d+$/', filter_input(INPUT_POST, 'LinesPerPage'))) {
			$pageSession['LinesPerPage']= filter_input(INPUT_POST, 'LinesPerPage');
		}
		else {
			PrintHelpWindow(_NOTICE('FAILED').': '._TITLE('Lines per page').': '.filter_input(INPUT_POST, 'LinesPerPage'), 'auto', 'ERROR');
		}
	}

	if ($pageSession['LinesPerPage']) {
		$count= $pageSession['LinesPerPage'];
	}
	else {
		$count= 25;
		$pageSession['LinesPerPage']= $count;
	}

	// Empty regexp posted is used to clear the session regexp
	if (filter_has_var(INPUT_POST, 'SearchRegExp')) {
		$pageSession['SearchRegExp']= filter_input(INPUT_POST, 'SearchRegExp');
	}

	if ($pageSession['SearchRegExp']) {
		$re= RemoveBackSlashes($pageSession['SearchRegExp']);
		$pageSession['SearchRegExp']= $re;
	}
	else {
		$re= '';
	}

	if (filter_has_var(INPUT_POST, 'SearchNeedle')) {
		$pageSession['SearchNeedle']= filter_input(INPUT_POST, 'SearchNeedle');
	}

	if ($pageSession['SearchNeedle']) {
		$needle= RemoveBackSlashes($pageSession['SearchNeedle']);
		$pageSession['SearchNeedle']= $needle;
	}
	else {
		$needle= '';
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
	?>
	<tr id="logline">
		<th><?php echo _('Line') ?></th>
		<?php
		foreach ($LogConf[$view]['Fields'] as $field => $caption) {
			?>
			<th><?php echo $caption ?></th>
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
 * @param array $lastlinenum Last line number, used to detect the last line
 * @param string $class Cell class, used for highlighting
 * @param string $module Module name if different from $View, $LogConf index
 */
function PrintLogCols($linenum, $cols, $lastlinenum= -1, $class= '', $module= '')
{
	global $LogConf, $View;

	/// Module name may be different from the current View name
	if ($module == '') {
		$module= $View->Model;
	}

	$View->FormatLogCols($cols);

	$lastLine= $linenum == $lastlinenum;
	// Center the line number column
	?>
	<tr>
		<td class="center<?php echo $lastLine ? ' lastLineFirstCell':'' ?><?php echo ($class == '') ? '':" $class" ?>">
			<?php echo $linenum ?>
		</td>
		<?php
		$totalCols= count($LogConf[$module]['Fields']);
		$count= 1;
		foreach ($LogConf[$module]['Fields'] as $field => $caption) {
			$cellClass= $class;
			if ($lastLine && $count++ == $totalCols) {
				$cellClass.= ' lastLineLastCell';
			}

			$nowrap = ($field == 'Date' || $field == 'DateTime') ? ' nowrap' : '';
			?>
			<td<?php echo ($cellClass == '') ? '':' class="'.$cellClass.'"' ?><?php echo $nowrap ?>>
				<?php echo $cols[$field] ?>
			</td>
			<?php
		}
		?>
	</tr>
	<?php
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
