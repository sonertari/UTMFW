COMMENT=	event notification library

V=		2.1.13
PKGNAME=	libevent-${V}-cw
FLAVORS=	cw
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

BUILD_DEPENDS=	security/openssl-3.5.7-cw
RUN_DEPENDS=	security/openssl-3.5.7-cw

SITES=		https://github.com/libevent/libevent/releases/download/release-$V-stable/

WANTLIB=	crypto pthread ssl

MODULES=	lang/python
MODPY_RUNDEP=	No
MODPY_ADJ_FILES=event_rpcgen.py

# disable dwz
DWZ =:

PREFIX = /opt/local

CONFIGURE_STYLE=gnu
CONFIGURE_ENV +=	CPPFLAGS="-I/opt/openssl-3.5.7/include" \
			PKG_CONFIG_PATH="/opt/openssl-3.5.7/lib/pkgconfig" \
			LDFLAGS="-L/opt/openssl-3.5.7/lib -Wl,-rpath,/opt/openssl-3.5.7/lib"

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
