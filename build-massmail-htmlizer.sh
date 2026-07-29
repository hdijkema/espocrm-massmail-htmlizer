#!/bin/bash
# vi: set sw=4 ts=4:

set -eu

REPO_DIR=$(
    cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &&
    pwd
)

cd "$REPO_DIR"

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

PATCHER=""

if [ "$CMD" = "install" ]; then
    CRM_DIR="${CRM_DIR:-$HOME/crm}"
    CORE="$CRM_DIR/application/Espo/Modules/Crm/Tools/MassEmail/SendingProcessor.php"

    if [ ! -f "$CRM_DIR/command.php" ]; then
        echo "FOUT: $CRM_DIR lijkt geen EspoCRM-installatie te zijn." >&2
        exit 1
    fi

    CRM_VERSION=$(
        cd "$CRM_DIR"
        php command.php version
    )

    case "$CRM_VERSION" in
        9.0.8|9.1.9|9.2.7)
            PATCHER="./tools/patch-sending-processor-$CRM_VERSION.php"
            ;;
        *)
            echo "FOUT: geen MassMailHtmlizer-patcher voor EspoCRM $CRM_VERSION." >&2
            exit 1
            ;;
    esac

    if [ ! -f "$PATCHER" ]; then
        echo "FOUT: patcher ontbreekt: $PATCHER" >&2
        exit 1
    fi
fi

"$BE" "$CMD" "$VERSION" "$EXT" "$NAME" "$DESCRIPTION" "$MODULE"

if [ "$CMD" = "install" ]; then
    php "$PATCHER" "$CORE"
    php -l "$CORE" >/dev/null

    (
        cd "$CRM_DIR"
        php command.php clear-cache
        php command.php rebuild
        php command.php clear-cache
    )
fi
