COMMENT=	transparent SSL/TLS proxy to divert decrypted traffic

DISTNAME=	sslproxy-0.9.5
EXTRACT_SUFX=	.tar.gz

CATEGORIES=	security

HOMEPAGE=	https://github.com/sonertari/SSLproxy

MAINTAINER=	Soner Tari <sonertari@gmail.com>

# SSLproxy: BSD, khash.h (header-based library): MIT
PERMIT_PACKAGE=	Yes

WANTLIB += c crypto event_core event_extra event_openssl event_pthreads
WANTLIB += pthread ssl

MASTER_SITES=	https://github.com/sonertari/SSLproxy/

LIB_DEPENDS=	devel/libevent2
BUILD_DEPENDS=	net/libnet/1.1 \
		databases/sqlite3
RUN_DEPENDS=	devel/libevent2 \
		net/libnet/1.1 \
		databases/sqlite3
# following is expected with MALLOC_FLAGS J:
# "cert.t.c:66:F:cert_refcount_inc:cert_refcount_inc_01:0: refcount mismatch"
# the test deliberately does a use-after-free
TEST_DEPENDS=	devel/check
MAKE_FLAGS=	MANDIR=man PREFIX=${TRUEPREFIX}

USE_GMAKE=	Yes

MAKE_FILE=	GNUmakefile

.include <bsd.port.mk>
