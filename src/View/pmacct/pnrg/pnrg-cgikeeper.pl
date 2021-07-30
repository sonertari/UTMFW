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

use Getopt::Long;

my $template = '/var/log/utmfw/wui/View/pmacct/pnrg/templates/cgi.tm';
my $spooldir = '';

GetOptions(
	"spool=s" => \$spooldir,
);

if ($spooldir eq '') {
        print "USAGE: pnrg-cgikeeper.pl --spool=<spool directory>\n";
        exit(1);
}
else {
	$spooldir = '.' if ( $spooldir eq '' );
}

my @rrds;
my @cgis;
my @descs;
my %cgis_hash = ();
my %rrds_hash = ();
my %descs_hash = ();
my %temp_hash;

opendir(LIST, $spooldir) || die "ERROR: Can't open $spooldir: $!";
@rrds = grep { /\.rrd$/ } readdir(LIST);
closedir(LIST);

opendir(LIST, $spooldir) || die "ERROR: Can't open $spooldir: $!";
@cgis = grep { /\.cgi$/ } readdir(LIST);
closedir(LIST);

opendir(LIST, $spooldir) || die "ERROR: Can't open $spooldir: $!";
@descs = grep { /\.desc$/ } readdir(LIST);
closedir(LIST);

#
# Let's figure out existing CGIs 
#
foreach my $entry (@rrds) {
        my $root = $entry;

	$root =~ s/\.rrd$//;
	$rrds_hash { $root } = 1;
}

%temp_hash = ();
foreach my $entry (@cgis) {
	my $root = $entry;

	$root =~ s/\.cgi$//; 
	$temp_hash { $root } = 1;
}

foreach my $entry (keys %rrds_hash) {
	if ( exists $temp_hash { $entry } ) {
		next;
	}
	$cgis_hash { $entry } = 1;
}

%temp_hash = ();
foreach my $entry (@descs) {
        my $root = $entry;

        $root =~ s/\.desc$//;
        $temp_hash { $root } = 1;
}

foreach my $entry (keys %rrds_hash) {
        if ( exists $temp_hash { $entry } ) {
                next;
        }
        $descs_hash { $entry } = 1;
}

foreach my $entry (keys %cgis_hash) {
	my $cgi_fname = $spooldir . "/" . $entry . ".cgi";

	build_cgi($entry, $cgi_fname, $template, $spooldir);
}

foreach my $entry (keys %descs_hash) {
        my $desc_fname = $spooldir . "/" . $entry . ".desc";

        build_desc($entry, $desc_fname, $spooldir);
}

sub build_cgi {
	my $rrd_root = shift;
	my $cgi_fname = shift;
	my $templ_fname = shift;
	my $spooldir = shift;
	my $rrd_fname = $spooldir . "/" . $rrd_root . ".rrd";
	my $idx;
	my $tag_idx;

	# Let's load the template
	open ( TEMPL, "< $templ_fname" ) or die "ERROR: Can't open $templ_fname: $!";
	my @template = <TEMPL>;
	close(TEMPL);

	# Let's apply the substitutions
	$idx = 0;
	foreach my $line (@template) {
		if ( $line =~ /__RRDROOT__/ ) {
			$template[$idx] =~ s/__RRDROOT__/$rrd_root/g;
		}

		if ( $line =~ /__RRDFILE__/ ) {
			$template[$idx] =~ s/__RRDFILE__/$rrd_fname/g;
		}

		if ( $line =~ /__IMGROOT__/ ) {
			my $img_root;

			$img_root = $spooldir . "/" . $rrd_root;
			$template[$idx] =~ s/__IMGROOT__/$img_root/g;
		}

		if ( $line =~ /__DEFS__/ ) {
			my $defs = '';

			$defs .= "DEF:inOctets=" . $spooldir . "/" . $rrd_root . ".rrd:" . "inOctets:AVERAGE\n\t"; 
			$defs .= "DEF:inPkts=" . $spooldir . "/" . $rrd_root . ".rrd:" . "inPackets:AVERAGE\n\t"; 
			$defs .= "DEF:outOctets=" . $spooldir . "/" . $rrd_root . ".rrd:" . "outOctets:AVERAGE\n\t"; 
			$defs .= "DEF:outPkts=" . $spooldir . "/" . $rrd_root . ".rrd:" . "outPackets:AVERAGE"; 

			$template[$idx] =~ s/__DEFS__/$defs/;
		}

		if ( $line =~ /__PLOTS__/ ) {
			my $plots = '';

			$plots .= "CDEF:inBits=inOctets,8,*\n\t";
			$plots .= "CDEF:inPackets=inPkts,1,*\n\t";
			$plots .= "CDEF:outBits=outOctets,-8,*\n\t";
			$plots .= "CDEF:outBitsPos=outOctets,8,*\n\t";
			$plots .= "CDEF:outPackets=outPkts,-1,*\n\t";

			$plots .= "CDEF:in_shading=inBits,0.98,*\n\t";
			$plots .= "AREA:in_shading#00FF00:\"in bps\"\n\t";

# 			$plots .= "LINE1:inBits#000000:\n\t";
# 			$plots .= "AREA:inBits#FF0000:\n\t";

# 			$plots .= "GPRINT:inBits:LAST:\"\\t Last in %6.1lf %Sbps\"\n\t";
			$plots .= "GPRINT:inBits:LAST:\"\t Last in %6.1lf %Sbps\"\n\t";
			$plots .= "GPRINT:inBits:MAX:\"Max in %6.1lf %Sbps\\n\"\n\t";

			$plots .= "CDEF:out_shading=outBits,0.98,*\n\t";
			$plots .= "AREA:out_shading#00FFFF:\"out bps\"\n\t";

# 			$plots .= "LINE1:outBits#000000:\n\t";
# 			$plots .= "AREA:outBits#00FF00:\n\t";

			# $plots .= "LINE1:inPackets#FFFFFF:\"in pkts\"\n\t";
			# $plots .= "LINE1:outPackets#FFFFFF:\"out pkts\"\n\t";

# 			$plots .= "GPRINT:outBitsPos:LAST:\"\\tLast out %5.1lf %Sbps\"\n\t";
			$plots .= "GPRINT:outBitsPos:LAST:\"\tLast out %5.1lf %Sbps\"\n\t";
			$plots .= "GPRINT:outBitsPos:MAX:\"Max out %5.1lf %Sbps\\n\"\n\t";

			$template[$idx] =~ s/__PLOTS__/$plots/;
		}

		$idx++;
	}

	# Let's write down the CGI 
	open ( CGI, "> $cgi_fname" ) or die "ERROR: Can't open $cgi_fname: $!";   
	foreach my $line (@template) {
		print CGI $line;
        }
	close(CGI);
	chmod(0755, $cgi_fname);

	print "INFO: Successfully created CGI: $cgi_fname\n";
}

sub build_desc {
	my $rrd_root = shift;
	my $desc_fname = shift;
        my $spooldir = shift;

        open ( DESC, "> $desc_fname" ) or die "ERROR: Can't open $desc_fname: $!";
	print DESC "IPs+" . $rrd_root . "\n";
	close(DESC);

	print "INFO: Successfully created DESC: $desc_fname\n";

}

