<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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
 * Doxygen source documentation iframe.
 */

require_once('include.php');

$Menu = array(
	'docs' => array(
		'Name' => _MENU('Documentation'),
		'Perms' => $ALL_USERS,
		),
	);

require_once($VIEW_PATH.'/header.php');
?>
<iframe frameborder=0 scrolling=auto marginheight=0 marginwidth=0 width="100%" height="700" src="/docs/html/index.html">
Your browser does not support iframes.
</iframe>
<?php
PrintHelpWindow(_HELPWINDOW('You can browse the Doxygen source documentation of this web user interface on this page.'));
require_once($VIEW_PATH.'/footer.php');
?>
