COMMENT=	Suricata ICAP service

V=			0.1.0
PKGNAME=	icapsuricata-cw-${V}
DISTNAME=	icapsuricata-${V}

CATEGORIES=	www security

MAINTAINER=	Soner Tari <sonertari@gmail.com>

HOMEPAGE=	https://github.com/sonertari/icapsuricata/

# GPL 2
PERMIT_PACKAGE=	Yes

WANTLIB +=	c pthread

SITES=		https://github.com/sonertari/icapsuricata/releases/download/v${V}/

#LIB_DEPENDS +=	devel/pcre2

BUILD_DEPENDS=	www/c-icap-cw-0.6.5 \
		security/suricata-cw-8.0.7
RUN_DEPENDS=	www/c-icap-cw-0.6.5 \
		security/suricata-cw-8.0.7

# disable dwz
DWZ =:

PREFIX = /opt/local

# required for pcre2
CFLAGS+=	-I${LOCALBASE}/include

# PATH is required for libsuricata-config
MAKE_FLAGS=		CICAP_PREFIX=${PREFIX} \
			LDFLAGS+="-L${PREFIX}/lib -Wl,-rpath,${PREFIX}/lib -Wl,-rpath,/usr/local/lib" \
			PATH="${PREFIX}/bin:${PATH}"

USE_GMAKE=	Yes

post-install:
	${INSTALL_DATA_DIR} ${PREFIX}/lib/c_icap
	${INSTALL_DATA} ${WRKBUILD}/srv_suricata.so ${PREFIX}/lib/c_icap/
	${INSTALL_DATA_DIR} ${PREFIX}/share/examples/c-icap
	${INSTALL_DATA} ${WRKBUILD}/icapsuricata.conf ${PREFIX}/share/examples/c-icap/

.include <bsd.port.mk>
