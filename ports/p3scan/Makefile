COMMENT=		content scanning transparent POP3 proxy
DISTNAME=		p3scan-2.3.2
CATEGORIES=		net mail

HOMEPAGE=		http://p3scan.sourceforge.net/
MASTER_SITES=		${MASTER_SITE_SOURCEFORGE:=p3scan/}

MAINTAINER=		Soner Tari <sonertari@gmail.com>

# GPL
PERMIT_PACKAGE=	Yes

WANTLIB=		c crypto ssl
BUILD_DEPENDS=		devel/pcre

SEPARATE_BUILD=		No
CONFIGURE_STYLE=	No

DATADIR=		/var/spool/p3scan
P3SCANUSER=		_p3scan:_p3scan
SUBST_VARS=		DATADIR P3SCANUSER

FAKE_FLAGS+=            rel_group=_p3scan rel_user=_p3scan

pre-configure:
	@perl -pi -e 's,!!LOCALBASE!!,${LOCALBASE},g' ${WRKSRC}/{p3scan.conf,p3scan.h}
	@gunzip ${WRKSRC}/{p3scan.8.gz,p3scan_readme.8.gz}

.include <bsd.port.mk>
