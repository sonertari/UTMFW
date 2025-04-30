<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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

require_once('e2guardian.php');

$Submenu= SetSubmenu('sites');

$SubmenuConf= array(
	'sites'			=> array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("These lists allow or block all of a site. You can omit the leading www. or http://"),
		'IncludeFile'	=> 'lists.sites.php',
		),
	
	'urls'			=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("If you don't want to control access to a site completely, you can use URL lists to define finer access rights."),
		'IncludeFile'	=> 'lists.sites.php',
		),
	
	'exts'			=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("Group users can download files with extensions not in the Blacklist only. If you are having problems downloading files with some extensions, you may need to disable that file type from Blacklist. If the problem persists, you may need to disable the appropriate mime type on the Mimes configuration page too."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'mimes'			=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("Group users can download files with mime types not in the Blacklist only. If you are having problems downloading files, you may need to disable the file's mime type from the BlackList. If the problem persists, you may need to disable the appropriate file extension on the Extensions configuration page too."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'dm_exts'		=>	array(
		'HelpMsg'		=> _HELPWINDOW("Download Manager is responsible for downloading the files requested. The file extensions it manages are in Enabled list. Other files extensions are handled by the user's browser. During download, Fancy Download Manager reports the progress via a progress bar and status information."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'dm_mimes'		=>	array(
		'HelpMsg'		=> _HELPWINDOW("Download Manager is responsible for downloading the content requested. The mime types it manages are in Enabled list. Other mime types are handled by the user's browser. During download, Fancy Download Manager reports the progress via a progress bar and status information."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'virus_sites'	=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("Sites in the Whitelist are not scanned for viruses. You can omit the leading www. or http://"),
		'IncludeFile'	=> 'lists.sites.php',
		),
	
	'virus_urls'	=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("URLs in the Whitelist are not scanned for viruses. You can omit the leading www. or http://"),
		'IncludeFile'	=> 'lists.sites.php',
		),
	
	'virus_exts'	=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("Files with extensions in the Whitelist are not scanned for viruses."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'virus_mimes'	=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("Files with mime types in the Whitelist are not scanned for viruses."),
		'IncludeFile'	=> 'include.lists.php',
		),
	);

$PrintGroupForm= isset($SubmenuConf[$Submenu]['PrintGroupForm']) && $SubmenuConf[$Submenu]['PrintGroupForm'] ? TRUE : FALSE;
$View->ConfHelpMsg= $SubmenuConf[$Submenu]['HelpMsg'];
require_once($SubmenuConf[$Submenu]['IncludeFile']);
?>
