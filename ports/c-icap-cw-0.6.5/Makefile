COMMENT=	ICAP server for use with web proxies

V=		0.6.5
#REVISION=	0

GH_ACCOUNT=	c-icap
GH_PROJECT=	c-icap-server
GH_TAGNAME=	C_ICAP_$V
PKGNAME=	c-icap-cw-${V}

CATEGORIES=	www

HOMEPAGE=	https://c-icap.sourceforge.net/

# LGPLv2.1+
PERMIT_PACKAGE= Yes

CONFIGURE_STYLE=  autoreconf
AUTOCONF_VERSION= 2.71
AUTOMAKE_VERSION= 1.16
AUTORECONF=	  sh ./RECONF

LIBTOOL_FLAGS=	--tag=disable-static
DEBUG_PACKAGES=	${BUILD_PACKAGES}

COMPILER=	base-clang ports-gcc
COMPILER_LANGS=	c

SHARED_LIBS +=	icapapi                   2.1 # 5.2

# disable dwz
DWZ =:

WANTLIB += brotlicommon brotlidec brotlienc bz2 pcre pthread z zstd c m

PREFIX = /opt/local
SYSCONFDIR=	${PREFIX}/share/examples/c-icap

CONFIGURE_ENV=	CPPFLAGS="-I${LOCALBASE}/include/db4 -I${LOCALBASE}/include" \
		LDFLAGS="-L${LOCALBASE}/lib"

# ipv6 not yet supported in *BSD.
CONFIGURE_ARGS=	--prefix=${PREFIX} \
		--sysconfdir=${SYSCONFDIR} \
		--disable-ipv6 \
		--enable-large-files \
		--without-memcached \
		--with-pcre

USE_GMAKE=	Yes
FAKE_FLAGS=	CONFIGDIR=${SYSCONFDIR}

LIB_DEPENDS=	archivers/brotli \
		archivers/zstd \
		devel/pcre \
		archivers/bzip2

pre-configure:
	${SUBST_CMD} ${WRKSRC}/c-icap.conf.in
	sed -i s,/usr/local/c-icap/etc,${SYSCONFDIR}, ${WRKSRC}/c-icap.conf.in

post-install:
	${INSTALL_DATA_DIR} ${PREFIX}/share/doc/c-icap/
	${INSTALL_DATA} ${WRKSRC}/README ${PREFIX}/share/doc/c-icap/
	rm -f ${PREFIX}/lib/c_icap/*.la

.include <bsd.port.mk>
