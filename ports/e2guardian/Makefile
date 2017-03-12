# $ComixWall: Makefile,v 1.2 2016/07/27 03:02:59 soner Exp $

COMMENT =	content scanning web filter
V =		3.4.0.3
DISTNAME =	e2guardian-${V}
CATEGORIES =	www net

HOMEPAGE =	http://www.e2guardian.org/

MAINTAINER =	Soner Tari <soner@comixwall.org>

# GPLv2+
PERMIT_PACKAGE_CDROM =	Yes

WANTLIB =               c m pcre pcreposix stdc++ z

# You may need to download the source package yourself,
# and copy it under /usr/ports/distfiles/
# https://github.com/e2guardian/e2guardian/archive/v3.4.0.3.tar.gz
#MASTER_SITES=		https://github.com/e2guardian/e2guardian/archive/
#DISTFILES =		v${V}.tar.gz

LIB_DEPENDS =           devel/pcre

CONFIGURE_STYLE =	gnu
AUTOCONF_VERSION =	2.69
AUTOMAKE_VERSION =	1.15

CONFIGURE_ENV =		CPPFLAGS="-I${LOCALBASE}/include" \
			LDFLAGS="-L${LOCALBASE}/lib"
CONFIGURE_ARGS =	--localstatedir=/var \
			--with-proxyuser=_e2guardian \
			--with-proxygroup=_e2guardian \
			--enable-clamd \
			--enable-segv-backtrace=no \
			--enable-email \
			--enable-trickledm

pre-configure:
	@cd ${WRKSRC}; env AUTOCONF_VERSION=${AUTOCONF_VERSION} \
		AUTOMAKE_VERSION=${AUTOMAKE_VERSION} sh autogen.sh

FLAVORS =               debug
FLAVOR ?=
.if ${FLAVOR:Mdebug}
CONFIGURE_ARGS +=       --with-dgdebug
.endif

FAKE_FLAGS +=		DGCONFDIR=${PREFIX}/share/examples/e2guardian \
			rel_group=_e2guardian rel_user=_e2guardian

.include <bsd.port.mk>
