#!/bin/bash
# vi: set sw=4 ts=4:

set -eu

BE='./build-ext.sh'

if [ -e build-ext.sh ]; then
    cp build-ext.sh build-ext.dist.sh
    chmod 755 build-ext.dist.sh
else
    BE='./build-ext.dist.sh'
fi

CMD="${1:-}"
VERSION=$(cat VERSION)
EXT="massmail-htmlizer-extension"
NAME="MassMailHtmlizer"
DESCRIPTION="Adds MassEmail related entities to email-template placeholders"
MODULE=$NAME

"$BE" "$CMD" "$VERSION" "$EXT" "$NAME" "$DESCRIPTION" "$MODULE"

if [ "$CMD" = "install" ]; then
    CRM_DIR="${CRM_DIR:-$HOME/crm}"
    CORE="$CRM_DIR/application/Espo/Modules/Crm/Tools/MassEmail/SendingProcessor.php"

    php ./tools/patch-sending-processor-9.0.8.php "$CORE"

    (
        cd "$CRM_DIR"
        php command.php clear-cache
        php command.php rebuild
        php command.php clear-cache
    )
fi
