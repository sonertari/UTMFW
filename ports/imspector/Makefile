COMMENT=		instant messaging transparent proxy
DISTNAME=		imspector-0.9
PKGNAME=		${DISTNAME}
CATEGORIES=		net

HOMEPAGE=		http://www.imspector.org
MASTER_SITES=		${HOMEPAGE}/downloads/

MAINTAINER=		Soner Tari <soner@comixwall.org>

# GPL
PERMIT_PACKAGE_CDROM=	Yes
PERMIT_PACKAGE_FTP=	Yes
PERMIT_DISTFILES_FTP=	Yes

MODULES=		converters/libiconv
WANTLIB=		c m stdc++
LIB_DEPENDS=		databases/sqlite3

CONFIGURE_STYLE=	No

FAKE_FLAGS+=		rel_group=_imspector rel_user=_imspector

.include <bsd.port.mk>
