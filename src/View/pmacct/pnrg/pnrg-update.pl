#!/usr/bin/perl

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

if ( $#ARGV != 1 ) {
        print "USAGE: pnrg-update.pl <filename> <data>\n";
        exit(1);
}

my $filename = $ARGV[0];
my $data = $ARGV[1];
my ($inbytes, $inpkts, $outbytes, $outpkts) = split(/,/, $data);

$cmdline =  "$rrdtool update $filename ";
$cmdline .= "N:$inbytes:$inpkts:$outbytes:$outpkts ";

# print $cmdline . "\n";

` $cmdline `;

