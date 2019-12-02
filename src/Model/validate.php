<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
 *
 * This file is part of PFRE.
 *
 * PFRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PFRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PFRE.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @file
 * Regex constants and vars used to validate rules.
 */

define('RE_BOOL', '^[01]$');
define('RE_NAME', '^[\w_.-]{0,50}$');
define('RE_NUM', '^\d{1,20}$');
define('RE_SHA1', '^[a-f\d]{40}$');
define('RE_DGSUBCAT', '^[\w/]{1,50}$');

/// "Macro names must start with a letter, digit, or underscore, and may contain any of those characters"
$RE_ID= '[\w_-]{1,50}';
define('RE_ID', "^$RE_ID$");

$RE_MACRO_VAR= '\$' . $RE_ID;

/// @todo What are possible macro values?
define('RE_MACRO_VALUE', '^((\w|\$)[\w_.\/\-*]{0,49}|)$');

$RE_IF_NAME= '\w{1,20}';
$RE_IF_MODIF= '(|:(0|broadcast|network|peer))';

$RE_IF= "($RE_IF_NAME|$RE_MACRO_VAR)$RE_IF_MODIF";
define('RE_IF', "^$RE_IF$");

$RE_IF_PAREN= "\(\s*$RE_IF\s*\)";
define('RE_IFSPEC', "^(|!)($RE_IF|$RE_IF_PAREN)$");

$RE_PROTO= '[\w-]{1,50}';
define('RE_PROTOSPEC', "^($RE_PROTO|$RE_MACRO_VAR)$");

define('RE_AF', '^(inet|inet6)$');
define('RE_DIRECTION', '^(in|out)$');

$RE_IP= '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
/// pfctl gets stuck if there are no spaces around the dash -
$RE_IP_RANGE= "$RE_IP\s+\-\s+$RE_IP";
$RE_IP6= '[\w:.\/]{1,50}';

/// @todo Is dash - possible in hostnames?
$RE_HOSTNAME= '[\w.\/_]{1,100}';

$RE_ADDRESS_KEYWORDS= '(any|no\-route|self|urpf\-failed)';

$RE_WEIGHT= '(|\s+weight\s+\d{1,5})';

$RE_ADDRESS_BASE= "($RE_IF|$RE_IF_PAREN|$RE_HOSTNAME|$RE_ADDRESS_KEYWORDS|$RE_IP|$RE_IP_RANGE|$RE_IP6|$RE_MACRO_VAR)";
$RE_ADDRESS= "($RE_IF|$RE_IF_PAREN|$RE_HOSTNAME|$RE_ADDRESS_KEYWORDS|$RE_IP|$RE_IP_RANGE|$RE_IP6|$RE_MACRO_VAR)$RE_WEIGHT";
$RE_ADDRESS_NET= "$RE_ADDRESS_BASE\s*\/\s*\d{1,2}$RE_WEIGHT";

$RE_TABLE_VAR= "<$RE_ID>";

$RE_TABLE_ADDRESS= "($RE_HOSTNAME|$RE_IF|self|$RE_IP|$RE_IP6|$RE_MACRO_VAR)";
$RE_TABLE_ADDRESS_NET= "$RE_TABLE_ADDRESS\s*\/\s*\d{1,2}";
define('RE_TABLE_ADDRESS', "^(|!)($RE_TABLE_ADDRESS|$RE_TABLE_ADDRESS_NET)$");

$RE_HOST= "(|!)($RE_ADDRESS|$RE_ADDRESS_NET|$RE_TABLE_VAR$RE_WEIGHT)";

define('RE_HOST', "^$RE_HOST$");
define('RE_REDIRHOST', "^($RE_ADDRESS|$RE_ADDRESS_NET)$");

$RE_HOST_AT_IF= "$RE_HOST\s*@\s*$RE_IF";
$RE_IF_ADDRESS_NET= "\(\s*$RE_IF(|\s+$RE_ADDRESS|\s+$RE_ADDRESS_NET)\s*\)$";

define('RE_ROUTEHOST', "^($RE_HOST|$RE_HOST_AT_IF|$RE_IF_ADDRESS_NET)$");

$RE_PORT= '[\w<>=!:\s-]{1,50}';
define('RE_PORT', "^($RE_PORT|$RE_MACRO_VAR)$");

$RE_PORTSPEC= '[\w*:\s-]{1,50}';
define('RE_PORTSPEC', "^($RE_PORTSPEC|$RE_MACRO_VAR)$");

$RE_FLAGS= '([FSRPAUEW\/]{1,10}|any)';
define('RE_FLAGS', "^($RE_FLAGS|$RE_MACRO_VAR)$");

$RE_W_1_10= '^\w{1,10}$';
define('RE_W_1_10', "^($RE_W_1_10|$RE_MACRO_VAR)$");

define('RE_STATE', '^(no|keep|modulate|synproxy)$');
define('RE_MAXPKTRATE', '^[\d]{1,10}\/[\d]{1,10}$');
define('RE_PROBABILITY', '^[\d.]{1,10}(|%)$');

$RE_OS= '[\w.*:\/_\s-]{1,50}';
define('RE_OS', "^($RE_OS|$RE_MACRO_VAR)$");

define('RE_ANCHOR_ID', '^[\w_\/*-]{1,100}$');

define('RE_BLANK', "^\n{0,10}$");
/// @todo Should we disallow $ and ` chars in comments?
/// For example, define('RE_COMMENT_INLINE', '^[^$`]{0,100}$');
define('RE_COMMENT_INLINE', '^[\s\S]{0,100}$');
define('RE_COMMENT', '^[\s\S]{0,1000}$');

define('RE_ACTION', '^(pass|match|block)$');
define('RE_BLOCKOPTION', '^(drop|return|return-rst|return-icmp|return-icmp6)$');

/// @todo Enum types instead
define('RE_TYPE', '^[a-z-]{1,30}$');

define('RE_SOURCE_HASH_KEY', '^\w{16,}$');

define('RE_BLOCKPOLICY', '^(drop|return)$');
define('RE_STATEPOLICY', '^(if-bound|floating)$');
define('RE_OPTIMIZATION', '^(normal|high-latency|satellite|aggressive|conservative)$');
define('RE_RULESETOPTIMIZATION', '^(none|basic|profile)$');
define('RE_DEBUG', '^(emerg|alert|crit|err|warning|notice|info|debug)$');
define('RE_REASSEMBLE', '^(yes|no)$');
define('RE_SYNCOOKIES', '^(never|always|adaptive)$');
define('RE_PERCENT', '^[\d.]{1,10}%$');

define('RE_BANDWIDTH', '^\d{1,16}(|K|M|G)$');
define('RE_BWTIME', '^\d{1,16}ms$');

define('RE_REASSEMBLE_TCP', '^tcp$');

define('RE_CONNRATE', '^\d{1,20}\/\d{1,20}$');
define('RE_SOURCETRACKOPTION', '^(rule|global)$');

$RE_ICMPOPT= '[\w-]{1,20}';
define('RE_ICMPTYPE', "^$RE_ICMPOPT(| code $RE_ICMPOPT)$");
define('RE_ICMPCODE', "^$RE_ICMPOPT$");
?>
