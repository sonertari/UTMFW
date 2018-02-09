<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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
 * Global setup.
 */

/// Force HTTPs, needs SSL configuration in the web server configuration.
$ForceHTTPs= TRUE;

/// Project-wide log level used in wui_syslog() and ctlr_syslog().
$LOG_LEVEL= LOG_INFO;

/// Max inline anchors allowed.
$MaxAnchorNesting= 2;

/// Wait pfctl output for this many seconds before giving up.
$PfctlTimeout= 5;

/// Interval to check module status for displaying.
$StatusCheckInterval= 60;

/// Default locale for both View and Controller.
$DefaultLocale= 'en_EN';
?>
