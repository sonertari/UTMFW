/usr/local/bin/rrdtool create /var/log/utmfw/pmacct/protograph/utmfw.rrd --step 60 \
  DS:all:GAUGE:120:U:U \
  DS:ftp:GAUGE:120:U:U \
  DS:ssh:GAUGE:120:U:U \
  DS:smtp:GAUGE:120:U:U \
  DS:dns:GAUGE:120:U:U \
  DS:http:GAUGE:120:U:U \
  DS:unknown:GAUGE:120:U:U \
  RRA:AVERAGE:0.5:1:10080 \
  RRA:AVERAGE:0.5:300:114
