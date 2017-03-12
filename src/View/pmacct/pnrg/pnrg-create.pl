#!/usr/bin/perl -w 

#
# pnrg is Copyright (C) 2006 by Pedro Sanchez
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
#

# Centralize this somewhere!
my $rrdtool = '/usr/local/bin/rrdtool';
my $cmdline = '';

if ( $#ARGV != 0 ) {
	print "USAGE: pnrg-create.pl <filename>\n"; 
	exit(1);
}

my $filename = $ARGV[0]; 

# $cmdline =  "$rrdtool create $filename --step 300 ";
$cmdline =  "$rrdtool create $filename --step 60 ";
# $cmdline =  "$rrdtool create $filename --step 1 ";
$cmdline .= "DS:inOctets:COUNTER:1800:0:4294967295 ";
$cmdline .= "DS:inPackets:COUNTER:1800:0:4294967295 ";
$cmdline .= "DS:outOctets:COUNTER:1800:0:4294967295 ";
$cmdline .= "DS:outPackets:COUNTER:1800:0:4294967295 ";
$cmdline .= "RRA:AVERAGE:0.5:1:600 ";
$cmdline .= "RRA:AVERAGE:0.5:6:600 ";
$cmdline .= "RRA:AVERAGE:0.5:24:600 ";
$cmdline .= "RRA:AVERAGE:0.5:288:732 ";
$cmdline .= "RRA:MAX:0.5:1:600 ";
$cmdline .= "RRA:MAX:0.5:6:600 ";
$cmdline .= "RRA:MAX:0.5:24:600 ";
$cmdline .= "RRA:MAX:0.5:288:732 ";

` $cmdline `;
