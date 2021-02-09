<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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

require_once('sslproxy.php');

$View->UploadLogFile();

if (filter_has_var(INPUT_POST, 'DeleteSpec')) {
	/// @attention Specs is an array, so filter_input() does not work here.
	foreach ($_POST['Specs'] as $Specs) {
		$View->Controller($Output, 'DelSpec', $Specs);
	}
}
if (filter_has_var(INPUT_POST, 'AddSpec') && filter_has_var(INPUT_POST, 'SpecToAdd')) {
	$View->Controller($Output, 'AddSpec', filter_input(INPUT_POST, 'SpecToAdd'));
}

if (filter_has_var(INPUT_POST, 'DeleteSite')) {
	/// @attention Sites is an array, so filter_input() does not work here.
	foreach ($_POST['Sites'] as $Site) {
		$View->Controller($Output, 'DelPassSite', $Site);
	}
}
if (filter_has_var(INPUT_POST, 'AddSite') && filter_has_var(INPUT_POST, 'SiteToAdd')) {
	$View->Controller($Output, 'AddPassSite', filter_input(INPUT_POST, 'SiteToAdd'));
}

$CustomFunc= 'PrintProxySpecsDownloadCACertForm';

require_once('../lib/conf.php');
?>
