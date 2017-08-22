# $ComixWall: Makefile,v 1.6 2016/07/07 05:51:18 soner Exp $
# Original from: Bartlomiej Korupczynski

COMMENT=		content scanning transparent smtp proxy
DISTNAME=		smtp-gated-1.4.16.2
CATEGORIES=		net mail

HOMEPAGE=		http://smtp-proxy.klolik.org
MASTER_SITES=		http://smtp-proxy.klolik.org/files/

MAINTAINER=		Soner Tari <soner@comixwall.org>

# GPL
PERMIT_PACKAGE_CDROM=	Yes
PERMIT_PACKAGE_FTP=	Yes
PERMIT_DISTFILES_FTP=	Yes

WANTLIB=		c

SEPARATE_BUILD=		concurrent
CONFIGURE_STYLE=	gnu

FAKE_FLAGS+=		rel_group=_smtp-gated rel_user=_smtp-gated

.include <bsd.port.mk>
