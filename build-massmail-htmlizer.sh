#!/bin/bash
# vi: set sw=4 ts=4:

BE='./build-ext.sh'
if [ -e build-ext.sh ]; then
    cp build-ext.sh build-ext.dist.sh
    chmod 755 build-ext.dist.sh
else
    BE='./build-ext-dist.sh'
fi

CMD="$1"
VERSION=`cat VERSION`
EXT="massmail-htmlizer-extension"
NAME="MassMailHtmlizer"
DESCRIPTION="Extends the Crm MassEmail service with Htmlizer capabilities"
MODULE=$NAME

$BE "$CMD" "$VERSION" "$EXT" "$NAME" "$DESCRIPTION" "$MODULE"

