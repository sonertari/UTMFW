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

//require_once('../lib/vars.php');

/// Stats page warning message.
$StatsWarningMsg= _NOTICE('Analysis of statistical data may take a long time to process. Please be patient. Also note that if you refresh this page frequently, CPU load may increase considerably.');

/// Main help box used on all statistics pages.
$StatsHelpMsg= _HELPWINDOW('This page displays statistical data collected over the log files of this module.

You can change the date of statistics using drop-down boxes. An empty value means match-all. For example, if you choose 3 for month and empty value for day fields, the charts and lists display statistics for all the days in March. Choosing empty value for month means empty value for day field as well.

For single dates, Horizontal chart direction is assumed. For date ranges, default graph style is Daily, and direction is Vertical. Graph style can be changed to Hourly for date ranges, where cumulative hourly statistics are shown. In Daily style, horizontal direction is not possible.');

$Submenu= SetSubmenu('general');
require_once("../lib/stats.$Submenu.php");
?>
