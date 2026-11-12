COMMENT=	implementation of the RFC 9000 QUIC protocol

V=		1.26.0
PKGNAME=	ngtcp2-cw-${V}
DISTNAME=	ngtcp2-${V}

SHARED_LIBS+=	ngtcp2			5.4	# 25.0
SHARED_LIBS+=	ngtcp2_crypto_ossl	3.1

CATEGORIES=	net

MAINTAINER=	Soner Tari <sonertari@gmail.com>

HOMEPAGE=	https://nghttp2.org/ngtcp2/

# MIT
PERMIT_PACKAGE=	Yes

BUILD_DEPENDS=	security/openssl-cw-3.5.9
RUN_DEPENDS=	security/openssl-cw-3.5.9

WANTLIB +=	crypto ssl

SITES=		https://github.com/ngtcp2/ngtcp2/releases/download/v$V/

PKGSPEC=	ngtcp2-cw->=1.26,<1.27

SEPARATE_BUILD=		Yes
DEBUG_PACKAGES=		${BUILD_PACKAGES}

# disable dwz
DWZ =:

PREFIX = /opt/local

CONFIGURE_STYLE=	gnu
CONFIGURE_ENV +=	PKG_CONFIG_PATH="/opt/openssl-3.5.9/lib/pkgconfig:/opt/local/lib/pkgconfig" \
			LDFLAGS="-L/opt/openssl-3.5.9/lib -L/opt/local/lib -Wl,-rpath,/opt/openssl-3.5.9/lib -Wl,-rpath,/opt/local/lib"
CONFIGURE_ARGS+=	--prefix=${PREFIX} \
			--with-openssl

.include <bsd.port.mk>
