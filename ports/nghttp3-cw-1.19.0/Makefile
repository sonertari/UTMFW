COMMENT=	implementation of HTTP/3

V=		1.19.0
PKGNAME=	nghttp3-cw-${V}
DISTNAME=	nghttp3-${V}

SHARED_LIBS +=	nghttp3              3.4     # 15.1

CATEGORIES=	www

MAINTAINER=	Soner Tari <sonertari@gmail.com>

HOMEPAGE=	https://nghttp2.org/nghttp3/

# MIT
PERMIT_PACKAGE=	Yes

BUILD_DEPENDS=	net/ngtcp2-cw-1.26.0
RUN_DEPENDS=	net/ngtcp2-cw-1.26.0

SITES=		https://github.com/ngtcp2/nghttp3/releases/download/v$V/

SEPARATE_BUILD=		Yes
DEBUG_PACKAGES=		${BUILD_PACKAGES}

COMPILER=		base-clang ports-gcc
COMPILER_LANGS=		c

# disable dwz
DWZ =:

PREFIX = /opt/local

CONFIGURE_STYLE=	gnu
CONFIGURE_ARGS=		--prefix=${PREFIX} \
			--enable-lib-only

.include <bsd.port.mk>
