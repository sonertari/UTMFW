COMMENT=	library for HTTP/2

V=		1.71.0
PKGNAME=	nghttp2-cw-${V}
DISTNAME=	nghttp2-${V}

SHARED_LIBS +=	nghttp2              0.24     # 43.0

CATEGORIES=	www

MAINTAINER=	Soner Tari <sonertari@gmail.com>

HOMEPAGE=	https://nghttp2.org/

# MIT
PERMIT_PACKAGE=	Yes

BUILD_DEPENDS=	security/openssl-cw-3.5.7
RUN_DEPENDS=	security/openssl-cw-3.5.7

WANTLIB +=	crypto ssl

SITES=		https://github.com/tatsuhiro-t/nghttp2/releases/download/v$V/

SEPARATE_BUILD=		Yes
DEBUG_PACKAGES=		${BUILD_PACKAGES}

# disable dwz
DWZ =:

PREFIX = /opt/local

CONFIGURE_STYLE=	gnu
CONFIGURE_ENV +=	PKG_CONFIG_PATH="/opt/openssl-3.5.7/lib/pkgconfig:/opt/local/lib/pkgconfig" \
			LDFLAGS="-L/opt/openssl-3.5.7/lib -L/opt/local/lib -Wl,-rpath,/opt/openssl-3.5.7/lib -Wl,-rpath,/opt/local/lib"
# - examples have conflicts (libevent, libevent2, libev)
# - hpack-tools require a C++11 compiler (array)
# Both of these pull in additional libraries; deliberately trying to keep
# dependencies light for this port (dep path nghttp2->curl->cmake)
CONFIGURE_ARGS+=	--prefix=${PREFIX} \
			--with-openssl \
			--disable-examples \
			--disable-hpack-tools \
			--disable-python-bindings \
			--without-libnghttp3 \
			--without-libngtcp2 \
			--without-libxml2

# Tests don't do much unless cunit is present at configure time.
# As above, don't pull this in, to keep dependencies light (and reduce
# risk of circular deps)
pre-test:
	@if ! [ -f ${LOCALBASE}/lib/libcunit.a ]; then echo; \
	    echo "====================================================="; \
	    echo "To run tests, install the cunit package and re-build."; \
	    echo "Dependency is not forced to keep dependencies light."; \
	    echo "====================================================="; \
	    echo; exit 1; \
	fi

.include <bsd.port.mk>
