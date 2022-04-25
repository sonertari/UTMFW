<?php
/*
 * Copyright (C) 2004-2022 Soner Tari
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

/// Max size in MB for log and statistics files to process. This limit is used for files downloaded from the WUI too.
/// The WUI slows down if we try to process larger files.
/// This size should be determined based on the specs of the hardware.
/// The default file size for log rotation is 10 MB for most log files in newsyslog.conf.
$MaxFileSizeToProcess= 10;

/// Default locale for both View and Controller.
$DefaultLocale= 'en_EN';

/// Send module statuses at this priority level and up as notification.
$NotifyLevel= LOG_CRIT;

/// Notifier host.
$NotifierHost= 'https://fcm.googleapis.com/fcm/send';

/// Verify Notifier SSL peers.
$NotifierSSLVerifyPeer= TRUE;

/// Notifier API key.
$NotifierAPIKey= 'AAAA4IgjjmM:APA91bERsNWEVVu9XZtlrB3L1vaA_2qNExBcpII2OU9cgRj3nKENGQ1ZLft38g8cSas_j00liurV5dm6VaBUdoqLrJa3afuBu5ieC6RPPElF2IUNm9l4G21TP4v7L7ArQjhehbPK06c79KlaZtYVzcPONrGpZitR4w';

/// Notifier users.
$NotifierUsers= '[]';

/// Send notifications containing one of these keywords only.
$NotifierFilters= '[]';

/// Wait notifier for this many seconds before giving up.
$NotifierTimeout= 10;
?>
