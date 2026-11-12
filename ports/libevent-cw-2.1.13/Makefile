COMMENT=	event notification library

V=		2.1.13
PKGNAME=	libevent-cw-${V}
DISTNAME=	libevent-${V}-stable
CATEGORIES=	devel
MAINTAINER=	Soner Tari <sonertari@gmail.com>
HOMEPAGE=	https://libevent.org/
# disable revision suffix in the package name
#REVISION=	0

SHARED_LIBS+=	event	0.0 # 7.2
SHARED_LIBS+=	event_core	2.1	# 7.2
SHARED_LIBS+=	event_extra	1.0	# 7.2
SHARED_LIBS+=	event_openssl	1.0	# 7.2
SHARED_LIBS+=	event_pthreads	0.0	# 7.2

# BSD
PERMIT_PACKAGE=	Yes

BUILD_DEPENDS=	security/openssl-cw-3.5.9
RUN_DEPENDS=	security/openssl-cw-3.5.9

SITES=		https://github.com/libevent/libevent/releases/download/release-$V-stable/

WANTLIB=	crypto pthread ssl

MODULES=	lang/python
MODPY_RUNDEP=	No
MODPY_ADJ_FILES=event_rpcgen.py

# disable dwz
DWZ =:

PREFIX = /opt/local

CONFIGURE_STYLE=gnu
CONFIGURE_ENV +=	CPPFLAGS="-I/opt/openssl-3.5.9/include" \
			PKG_CONFIG_PATH="/opt/openssl-3.5.9/lib/pkgconfig" \
			LDFLAGS="-L/opt/openssl-3.5.9/lib -Wl,-rpath,/opt/openssl-3.5.9/lib"

# linker gives errors for samples and regress
CONFIGURE_ARGS+=	--prefix=${PREFIX} \
			--disable-samples \
			--disable-libevent-regress

DEBUG_PACKAGES=	${BUILD_PACKAGES}

MAKE_FLAGS=	RELEASE=""

SEPARATE_BUILD =Yes

# Skip compatibility bits that conflict with libevent1
#FAKE_FLAGS=	LIBEVENT_LIBS_LA="libevent_core.la libevent_extra.la \
#		    libevent_openssl.la libevent_pthreads.la" \
#		EVENT1_HDRS=""

.include <bsd.port.mk>
