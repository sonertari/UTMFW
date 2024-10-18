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

require_once('include.php');

$LogConf = array(
	'blacklists' => array(
		'Fields' => array(
			'Category' => _TITLE('Category'),
			'Site' => _TITLE('Site'),
			),
		),
	);

class Blacklists extends View
{
	public $Model= 'blacklists';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->ConfHelpMsg= _HELPWINDOW('You can search categories on this page.');
	}
	
	function FormatLogCols(&$cols)
	{
		$link= 'http://'.$cols['Site'];
		$cols['Site']= '<a href="'.$link.'">'.$cols['Site'].'</a>';
	}
}

$View= new Blacklists();

/**
 * Displays a form to search sites/urls in black lists.
 *
 * @param string $site Search string.
 */
function PrintSiteCategorySearchForm($site)
{
	?>
	<table>
		<tr>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<?php echo _TITLE('Search site').': ' ?>
					<input type="text" name="SearchSite" style="width: 200px;" maxlength="100" value="<?php echo $site ?>"/>
					<input type="submit" name="Search" value="<?php echo _CONTROL('Search') ?>"/>
					<input type="submit" name="Defaults" value="<?php echo _CONTROL('Defaults') ?>"/>
				</form>
			</td>
			<td>
				<?php
				PrintHelpBox(_HELPBOX2('Here you can search sites and urls in category listings. Regexp below can help you further refine your search.'));
				?>
			</td>
		</tr>
	</table>
	<?php
}

if (filter_has_var(INPUT_POST, 'Defaults')) {
	unset($_SESSION[$View->Model]['LogFile']);
	unset($_SESSION[$View->Model]['SearchSite']);
} else if (filter_has_var(INPUT_POST, 'SearchSite')) {
	$SearchSite= filter_input(INPUT_POST, 'SearchSite');
	$_SESSION[$View->Model]['SearchSite']= $SearchSite;
	
	if ($View->Controller($Output, 'SearchSite', $SearchSite)) {
		$LogFile= $Output[0];
		$_SESSION[$View->Model]['LogFile']= $LogFile;
	}
	else {
		$LogFile= FALSE;
	}
}

$LogFile= isset($_SESSION[$View->Model]['LogFile']) ? $_SESSION[$View->Model]['LogFile'] : '';
$SearchSite= isset($_SESSION[$View->Model]['SearchSite']) ? $_SESSION[$View->Model]['SearchSite'] : '';

require_once($VIEW_PATH.'/header.php');
		
PrintSiteCategorySearchForm($SearchSite);

/// @attention $LogFile may be NULL too, not just FALSE or a string
if ($LogFile) {
	ProcessStartLine($StartLine);
	UpdateLogsPageSessionVars($LinesPerPage, $SearchRegExp, $SearchNeedle);

	$LogSize= 0;
	if ($View->Controller($Output, 'GetFileLineCount', $LogFile, $SearchRegExp)) {
		$LogSize= $Output[0];
	}

	ProcessNavigationButtons($LinesPerPage, $LogSize, $StartLine, $HeadStart);

	PrintLogHeaderForm($StartLine, $LogSize, $LinesPerPage, $SearchRegExp, $CustomHiddenInputs);
	?>
	<table id="logline">
		<?php
		PrintTableHeaders($View->Model);

		$Logs= array();
		if ($View->Controller($Output, 'GetLogs', $LogFile, $HeadStart, $LinesPerPage, $SearchRegExp)) {
			$Logs= json_decode($Output[0], TRUE);
		}

		$LineCount= $StartLine + 1;
		$LastLineNum= $StartLine + min(array(count($Logs), $LinesPerPage));
		foreach ($Logs as $Log) {
			$View->PrintLogLine($Log, $LineCount++, $LastLineNum);
		}
		?>
	</table>
	<?php
}

$View->ConfHelpMsg.= ' '._HELPWINDOW("Although this is called a 'blacklist', the categories can be used as white or grey lists also. Being listed does not infer that the site is bad, these are just lists of sites.\n\n");

PrintHelpWindow($View->ConfHelpMsg);
require_once($VIEW_PATH.'/footer.php');
?>
