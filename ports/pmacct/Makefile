# $ComixWall: Makefile,v 1.5 2009/10/25 21:53:12 soner Exp $
# $OpenBSD: Makefile,v 1.1.1.1 2009/10/03 18:54:44 sthen Exp $

COMMENT=	IP accounting software

DISTNAME=	pmacct-0.12.0rc2
CATEGORIES=	net

HOMEPAGE=	http://www.pmacct.net/

# GPLv2
PERMIT_PACKAGE_CDROM=	Yes
PERMIT_PACKAGE_FTP=	Yes
PERMIT_DISTFILES_CDROM=	Yes
PERMIT_DISTFILES_FTP=	Yes

WANTLIB=	c m pcap z

MASTER_SITES=	${HOMEPAGE}

CONFIGURE_STYLE=gnu
CONFIGURE_ARGS= --enable-64bit

post-install:
	${INSTALL_DATA_DIR} ${PREFIX}/share/doc/pmacct
	${INSTALL_DATA_DIR} ${PREFIX}/share/examples/pmacct/sql
	${INSTALL_DATA} ${WRKSRC}/CONFIG-KEYS ${PREFIX}/share/doc/pmacct/
	${INSTALL_DATA} ${WRKSRC}/EXAMPLES ${PREFIX}/share/doc/pmacct/
	${INSTALL_DATA} ${WRKSRC}/docs/* ${PREFIX}/share/doc/pmacct/
	${INSTALL_DATA} ${WRKSRC}/examples/* ${PREFIX}/share/examples/pmacct/
	${INSTALL_DATA} ${WRKSRC}/sql/README* ${PREFIX}/share/doc/pmacct/
	${INSTALL_DATA} `find ${WRKSRC}/sql -type f \! -name README*` \
	    ${PREFIX}/share/examples/pmacct/sql/

.include <bsd.port.mk>
