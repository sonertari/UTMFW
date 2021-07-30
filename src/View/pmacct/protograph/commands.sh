/usr/local/bin/pmacct -c src_port,dst_port -N "*,*" -S -p /var/log/utmfw/run/traf.pipe
/usr/local/bin/pmacct -c src_port,dst_port -N "*,21;21,*;*,20;20,*" -S -p /var/log/utmfw/run/traf.pipe -r
/usr/local/bin/pmacct -c src_port,dst_port -N "*,22;22,*" -S -p /var/log/utmfw/run/traf.pipe -r
/usr/local/bin/pmacct -c src_port,dst_port -N "*,25;25,*;*,465;465,*" -S -p /var/log/utmfw/run/traf.pipe -r
/usr/local/bin/pmacct -c src_port,dst_port -N "*,53;53,*" -S -p /var/log/utmfw/run/traf.pipe -r
/usr/local/bin/pmacct -c src_port,dst_port -N "*,80;80,*;*,443;443,*" -S -p /var/log/utmfw/run/traf.pipe -r
/usr/local/bin/pmacct -c src_port,dst_port -N "*,*" -S -p /var/log/utmfw/run/traf.pipe -r
