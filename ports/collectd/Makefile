# $OpenBSD: Makefile,v 1.65 2021/02/23 19:39:40 sthen Exp $

COMMENT-main =		system metrics collection engine
COMMENT-rrdtool =	collectd rrdtool plugin
COMMENT-ping =		collectd ping plugin

V =			5.12.0
DISTNAME =		collectd-$V
PKGNAME-main =		collectd-$V
PKGNAME-rrdtool =	collectd-rrdtool-$V
PKGNAME-ping =		collectd-ping-$V
CATEGORIES =		sysutils
REVISION-main =		0
REVISION-ping =		0
REVISION-rrdtool =	2

HOMEPAGE =		http://www.collectd.org/
SHARED_LIBS +=		collectdclient 1.0

# GPLv2
PERMIT_PACKAGE =	Yes

MASTER_SITES =		${HOMEPAGE}/files/
EXTRACT_SUFX =		.tar.bz2

MODULES =		lang/python
MODPY_VERSION =	${MODPY_DEFAULT_VERSION_2}
MODPY_RUNDEP =		No

MULTI_PACKAGES =	-main -rrdtool -ping

WANTLIB-rrdtool =	X11 Xext Xrender cairo expat fontconfig \
			freetype glib-2.0 gobject-2.0 \
			harfbuzz iconv intl lzma m \
			pango-1.0 pangocairo-1.0 \
			pixman-1 png pthread rrd xcb \
			xcb-render xcb-shm xml2 z
LIB_DEPENDS-rrdtool =	net/rrdtool
RUN_DEPENDS-rrdtool =	collectd-$V:${BASE_PKGPATH},-main

LIB_DEPENDS-ping =	net/liboping
RUN_DEPENDS-ping =	collectd-$V:${BASE_PKGPATH},-main
WANTLIB-ping =		m oping pthread

LIB_DEPENDS +=	net/curl \
		textproc/libxml \
		sysutils/libstatgrab \
		security/libgcrypt \
		devel/libyajl

WANTLIB += c crypto gpg-error kvm curl xml2 lzma nghttp2
WANTLIB += m pcap pthread ssl xml2 z statgrab gcrypt iconv intl
WANTLIB += yajl

CONFIGURE_STYLE =	autoreconf
AUTOCONF_VERSION =	2.69
AUTOMAKE_VERSION =	1.16
# uses -export-symbol-regex
USE_LIBTOOL=		gnu
LIBTOOL_FLAGS =	--tag=disable-static
CONFIGURE_ARGS +=--with-librrd=${LOCALBASE} \
		--with-libstatgrab=${LOCALBASE} \
		--enable-all-plugins=no

# no, varnishapi.h not in our varnish package
#		--with-libvarnish=${LOCALBASE}

ENABLED_PLUGINS =	cpu ping rrdtool syslog tail logparser notify_fifo

.for _plugin in ${ENABLED_PLUGINS}
CONFIGURE_ARGS += --enable-${_plugin}
.endfor
#CONFIGURE_ARGS += --enable-debug

CONFIGURE_ENV +=	PYTHON_CONFIG='${LOCALBASE}/bin/python${MODPY_VERSION}-config' \
			CPPFLAGS='-I${LOCALBASE}/include' \
			LDFLAGS='-L${LOCALBASE}/lib -L${X11BASE}/lib -pthread'

post-install:
	rm -f ${PREFIX}/lib/collectd/*.la

.include <bsd.port.mk>
