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

/** @file
 * View setup.
 */

/// Whether to display help boxes on the right.
$ShowHelpBox= TRUE;

/// Default session timeout in secs.
$SessionTimeout= 300;

/// Whether to run Controller commands over SSH.
$UseSSH= FALSE;

/// Default reload rate in seconds for dynamic pages, e.g. live pages.
$DefaultReloadRate= 10;

/// Time server to use.
$TimeServer= '0.ubuntu.pool.ntp.org';

/// How many NVP sets will be printed horizontally.
$NvpColCount= 2;
?>
