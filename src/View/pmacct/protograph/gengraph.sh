#!/bin/sh

HOST=utmfw
RRD=/var/www/htdocs/utmfw/View/pmacct/protograph/${HOST}.rrd
IMAGE_NAME=/var/www/htdocs/utmfw/View/pmacct/protograph/${HOST}-hourly.gif
DURATION=3600

/usr/local/bin/rrdtool graph ${IMAGE_NAME} --start -${DURATION} -v "bits per second" -F -t "Overall Protocol (Port) Usage" \
  DEF:alltraf=${RRD}:all:AVERAGE \
  DEF:ftptraf=${RRD}:ftp:AVERAGE \
  DEF:sshtraf=${RRD}:ssh:AVERAGE \
  DEF:smtptraf=${RRD}:smtp:AVERAGE \
  DEF:dnstraf=${RRD}:dns:AVERAGE \
  DEF:httptraf=${RRD}:http:AVERAGE \
  DEF:unknowntraf=${RRD}:unknown:AVERAGE \
  CDEF:allbits=alltraf,8,*,60,/ \
  CDEF:ftpbits=ftptraf,8,*,60,/ \
  CDEF:sshbits=sshtraf,8,*,60,/ \
  CDEF:smtpbits=smtptraf,8,*,60,/ \
  CDEF:dnsbits=dnstraf,8,*,60,/ \
  CDEF:httpbits=httptraf,8,*,60,/ \
  CDEF:unknownbits=unknowntraf,8,*,60,/ \
  COMMENT:"Service Max Min Average Last\n" \
  AREA:allbits#D0D0D0:"ALL " \
  GPRINT:allbits:MAX:"%6.2lf %sbps" \
  GPRINT:allbits:MIN:"%6.2lf %sbps" \
  GPRINT:allbits:AVERAGE:"%6.2lf %sbps" \
  GPRINT:allbits:LAST:"%6.2lf %sbps\\n" \
  LINE1:ftpbits#FF0000:"FTP " \
  GPRINT:ftpbits:MAX:"%6.2lf %sbps" \
  GPRINT:ftpbits:MIN:"%6.2lf %sbps" \
  GPRINT:ftpbits:AVERAGE:"%6.2lf %sbps" \
  GPRINT:ftpbits:LAST:"%6.2lf %sbps\\n" \
  LINE1:sshbits#00FF00:"SSH " \
  GPRINT:sshbits:MAX:"%6.2lf %sbps" \
  GPRINT:sshbits:MIN:"%6.2lf %sbps" \
  GPRINT:sshbits:AVERAGE:"%6.2lf %sbps" \
  GPRINT:sshbits:LAST:"%6.2lf %sbps\\n" \
  LINE1:smtpbits#0000FF:"SMTP " \
  GPRINT:smtpbits:MAX:"%6.2lf %sbps" \
  GPRINT:smtpbits:MIN:"%6.2lf %sbps" \
  GPRINT:smtpbits:AVERAGE:"%6.2lf %sbps" \
  GPRINT:smtpbits:LAST:"%6.2lf %sbps\\n" \
  LINE1:dnsbits#FFFF00:"DNS " \
  GPRINT:dnsbits:MAX:"%6.2lf %sbps" \
  GPRINT:dnsbits:MIN:"%6.2lf %sbps" \
  GPRINT:dnsbits:AVERAGE:"%6.2lf %sbps" \
  GPRINT:dnsbits:LAST:"%6.2lf %sbps\\n" \
  LINE1:httpbits#FF00FF:"HTTP " \
  GPRINT:httpbits:MAX:"%6.2lf %sbps" \
  GPRINT:httpbits:MIN:"%6.2lf %sbps" \
  GPRINT:httpbits:AVERAGE:"%6.2lf %sbps" \
  GPRINT:httpbits:LAST:"%6.2lf %sbps\\n" \
  LINE1:unknownbits#F49028:"Unknown " \
  GPRINT:unknownbits:MAX:"%6.2lf %sbps" \
  GPRINT:unknownbits:MIN:"%6.2lf %sbps" \
  GPRINT:unknownbits:AVERAGE:"%6.2lf %sbps" \
  GPRINT:unknownbits:LAST:"%6.2lf %sbps\\n" > /dev/null

