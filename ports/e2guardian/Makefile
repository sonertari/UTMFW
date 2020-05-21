# $OpenBSD: Makefile,v 1.2 2020/03/01 16:45:02 bket Exp $

GH_ACCOUNT =	e2guardian
GH_PROJECT =	e2guardian
GH_TAGNAME =	v5.3.4

COMMENT =	content scanning web filter
CATEGORIES =	www net

HOMEPAGE =	http://www.e2guardian.org/

MAINTAINER =	Soner Tari <sonertari@gmail.com>

# GPLv2+
PERMIT_PACKAGE =	Yes

WANTLIB += ${COMPILER_LIBCXX} c m pcre pcreposix z

COMPILER =	base-clang ports-gcc

LIB_DEPENDS =		devel/pcre

CONFIGURE_STYLE =	autoreconf
AUTOCONF_VERSION =	2.69
AUTOMAKE_VERSION =	1.16
AUTORECONF =		${WRKSRC}/autogen.sh

CONFIGURE_ENV =		CPPFLAGS="-I${LOCALBASE}/include" \
			LDFLAGS="-L${LOCALBASE}/lib"
CONFIGURE_ARGS =	--with-proxygroup=_e2guardian \
			--with-proxyuser=_e2guardian \
			--enable-clamd \
			--enable-email

FLAVORS =		debug
FLAVOR ?=
.if ${FLAVOR:Mdebug}
CONFIGURE_ARGS +=	--with-dgdebug
.endif

FAKE_FLAGS +=		DGCONFDIR=${PREFIX}/share/examples/e2guardian \
			rel_group=_e2guardian \
			rel_user=_e2guardian

.include <bsd.port.mk>
