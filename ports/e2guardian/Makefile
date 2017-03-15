COMMENT =	content scanning web filter
DISTNAME =	e2guardian-3.5.1
CATEGORIES =	www net

HOMEPAGE =	http://www.e2guardian.org/

MAINTAINER =	Soner Tari <sonertari@gmail.com>

# GPLv2+
PERMIT_PACKAGE_CDROM =	Yes

WANTLIB =               c m pcre pcreposix stdc++ z

# You may need to download the source package yourself,
# and copy it under /usr/ports/distfiles/
MASTER_SITES=	https://github.com/e2guardian/e2guardian/releases/tag/v3.5.1/

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
