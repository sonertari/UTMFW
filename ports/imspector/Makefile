COMMENT=		instant messaging transparent proxy
DISTNAME=		imspector-0.9
PKGNAME=		${DISTNAME}
CATEGORIES=		net

HOMEPAGE=		http://www.imspector.org
MASTER_SITES=		${HOMEPAGE}/downloads/

MAINTAINER=		Soner Tari <soner@comixwall.org>

# GPL
PERMIT_PACKAGE=	Yes

WANTLIB+=		${COMPILER_LIBCXX} c m
CONFIGURE_STYLE=	No
BUILD_DEPENDS=		databases/sqlite3

FAKE_FLAGS+=		rel_group=_imspector rel_user=_imspector

.include <bsd.port.mk>
