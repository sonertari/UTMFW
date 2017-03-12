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
 * All log archive pages include this file.
 * Log configuration is in $LogConf.
 */

require_once('../lib/vars.php');

$View->UploadLogFile();

$LogFile= GetLogFile();

ProcessStartLine($StartLine);
UpdateLogsPageSessionVars($LinesPerPage, $SearchRegExp);

$View->Controller($Output, 'GetFileLineCount', $LogFile, $SearchRegExp);
$LogSize= $Output[0];

ProcessNavigationButtons($LinesPerPage, $LogSize, $StartLine, $HeadStart);

require_once($VIEW_PATH.'/header.php');
		
PrintLogFileChooser($LogFile);

PrintLogHeaderForm($StartLine, $LogSize, $LinesPerPage, $SearchRegExp, $CustomHiddenInputs);
?>
<table id="logline">
	<?php
	PrintTableHeaders($View->Model);

	$View->Controller($Output, 'GetLogs', $LogFile, $HeadStart, $LinesPerPage, $SearchRegExp);
	$Logs= json_decode($Output[0], TRUE);
	
	$LineCount= $StartLine + 1;
	foreach ($Logs as $Logline) {
		$View->PrintLogLine($Logline, $LineCount++);
	}
	?>
</table>
<?php
PrintHelpWindow($View->LogsHelpMsg);
require_once($VIEW_PATH.'/footer.php');
?>
