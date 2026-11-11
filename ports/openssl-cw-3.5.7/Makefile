# static link, need bump on update:
# security/sslproxy-cw-0.10.1 devel/libevent-cw-2.1.13
# www/nghttp2-cw-1.69.0 www/nghttp3-cw-1.18.0 net/ngtcp2-cw-1.25.0

.if ${MACHINE_ARCH} != aarch64 && ${MACHINE_ARCH} != amd64 && ${MACHINE_ARCH} != riscv64
USE_NOEXECONLY =	Yes
.endif

COMMENT=    TLS/SSL library and tools

# skip PORTROACH limit for newest version
#PORTROACH=	limit:^3\.2
V=		    3.5.7
PKGNAME=	openssl-cw-${V}
DISTNAME=   openssl-${V}

CATEGORIES= security net

HOMEPAGE=   https://www.openssl.org/

MAINTAINER= Soner Tari <sonertari@gmail.com>

PERMIT_PACKAGE= Yes

EXTRACT_SUFX= .tar.gz
SITES=      https://github.com/openssl/openssl/releases/download/openssl-${V}/

PKGSPEC=	openssl-cw->=3.5,<3.6
# disable v0 revision suffix in the package name
#EPOCH=		0

SHLIBVER=	37.0
SHARED_LIBS=	crypto ${SHLIBVER} \
		ssl ${SHLIBVER}

COMPILER=	base-clang ports-gcc
COMPILER_LANGS=	c

# disable dwz
DWZ =:

WANTLIB=	c pthread

DEBUG_PACKAGES = ${BUILD_PACKAGES}

CONFIGURE_STYLE=	simple
CONFIGURE_SCRIPT=	config

PREFIX = /opt/openssl-3.5.7

# install to /opt - this port is *not* intended to be
# picked up by configure scripts without explicitly CPPFLAGS/LDFLAGS.
# common names like lib/openssl, openssl/lib, etc, *do* get picked up.
CONFIGURE_ARGS+=	--prefix=${PREFIX} \
			--openssldir=${PREFIX}/etc/ssl \
			no-rc5 no-ssl3 \
			no-devcryptoeng \
			shared

# Avoid T4 assembly issues https://github.com/openssl/openssl/issues/20523
.if ${MACHINE_ARCH} == sparc64
CONFIGURE_ARGS+=	no-asm
.endif

# Assembly assumes it is on arm v8, which is not the case
.if ${MACHINE_ARCH} == arm
CONFIGURE_ARGS+=	no-asm
.endif

INSTALL_TARGET=		install_sw install_ssldirs install_man_docs

SUBST_VARS+=		SHLIBVER

MAKE_FLAGS+=	CC="${CC}" \
		MANSUFFIX=

post-patch:
	sed -i 's,^SHLIB_VERSION=.*,SHLIB_VERSION=${SHLIBVER},' \
	    ${WRKSRC}/VERSION.dat

pre-configure:
	${SUBST_CMD} ${WRKSRC}/exporters/cmake/OpenSSLConfig.cmake.in

post-configure:
	cd ${WRKSRC}; ${SETENV} ${MAKE_ENV} ${MAKE_PROGRAM} \
	    ${MAKE_FLAGS} -f ${MAKE_FILE} depend

.include <bsd.port.mk>
