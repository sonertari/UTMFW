COMMENT=	transparent SSL/TLS proxy to divert decrypted traffic

V=		0.10.1
PKGNAME=	sslproxy-cw-${V}
DISTNAME=	sslproxy-${V}
EXTRACT_SUFX=	.tar.gz

CATEGORIES=	security

HOMEPAGE=	https://github.com/sonertari/SSLproxy

MAINTAINER=	Soner Tari <sonertari@gmail.com>

# SSLproxy: BSD, khash.h (header-based library): MIT
PERMIT_PACKAGE=	Yes

# remove libevent from wantlibs
#WANTLIB += c crypto event_core event_extra event_openssl event_pthreads
WANTLIB += c crypto
WANTLIB += pthread ssl

MASTER_SITES=	https://github.com/sonertari/SSLproxy/

#LIB_DEPENDS=	devel/libevent-cw-2.1.13

BUILD_DEPENDS=	devel/libevent-cw-2.1.13 \
		www/nghttp2-cw-1.69.0 \
		www/nghttp3-cw-1.18.0 \
		net/libnet/1.1 \
		databases/sqlite3

RUN_DEPENDS=	devel/libevent-cw-2.1.13 \
		www/nghttp2-cw-1.69.0 \
		www/nghttp3-cw-1.18.0 \
		net/libnet/1.1 \
		databases/sqlite3

# following is expected with MALLOC_FLAGS J:
# "cert.t.c:66:F:cert_refcount_inc:cert_refcount_inc_01:0: refcount mismatch"
# the test deliberately does a use-after-free
TEST_DEPENDS=	devel/check
MAKE_FLAGS=	MANDIR=man PREFIX=${TRUEPREFIX} \
		OPENSSL_BASE="/opt/openssl-3.5.7" \
		LIBNGHTTP2_BASE="/opt/local" \
		LIBNGHTTP3_BASE="/opt/local" \
		LIBNGTCP2_BASE="/opt/local" \
		LIBEVENT_BASE="/opt/local"

USE_GMAKE=	Yes

MAKE_FILE=	GNUmakefile

.include <bsd.port.mk>
