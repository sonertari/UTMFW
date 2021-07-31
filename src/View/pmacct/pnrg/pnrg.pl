#!/usr/bin/perl -w

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

my $rrd_create = '/var/www/htdocs/utmfw/View/pmacct/pnrg/pnrg-create.pl';
my $rrd_update = '/var/www/htdocs/utmfw/View/pmacct/pnrg/pnrg-update.pl';
my $pmacct_client = '/usr/local/bin/pmacct';
my $memtable_in  = "$pmacct_client -s -p /var/log/utmfw/run/pmacct_in.pipe";
my $memtable_out = "$pmacct_client -s -p /var/log/utmfw/run/pmacct_out.pipe";

use Getopt::Long;

# Getopt stuff
my $spooldir = '';

GetOptions(
	"spool=s" => \$spooldir,
);

if ($spooldir eq '') {
	print "USAGE: pnrg.pl --spool=<directory>\n";
	exit(1);
}
else {
	$spooldir = '.' if ( $spooldir eq '' ); 
}

my @table_in = ` $memtable_in `; 
my @table_out = ` $memtable_out `; 
my %table = ();
my $idx;

$idx = 0;
foreach my $entry ( @table_in ) {
	my @map = split(/\s+/, $entry);

	if ($idx <= 0) {
		$idx++;
		next;
	}
	if ($#map < 0) {
		$idx = -999;
		next;
	}

	$table{ $map[0] } = $map[2] . ',' . $map[1] . ',0,0';

	$idx++;
}

$idx = 0;
foreach my $entry ( @table_out ) {
        my @map = split(/\s+/, $entry);

        if ($idx <= 0) {
		$idx++;
		next;
	}
	if ($#map < 0) {
		$idx = -999;
	        next;
	}

	if (exists $table{ $map[0] }) {  
		$table{ $map[0] } =~ s/0,0$/$map[2],$map[1]/;
	}
	else {
		$table{ $map[0] } = '0,0,' . $map[2] . ',' . $map[1];
	}

	$idx++;
}

foreach my $entry ( keys %table ) {
	my $rrdfile = $spooldir . "/" . $entry . ".rrd";

	if ( not stat($rrdfile) ) {
		` $rrd_create $rrdfile `;
	}

	` $rrd_update $rrdfile $table{ $entry } `;
}

