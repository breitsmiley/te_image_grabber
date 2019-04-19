#!/bin/sh
set -e 

#if [ "$APP_D_ENTRYPOINT_MODE" == 'init' ]; then
#
#    FILE_PACKAGE_JSON="package.json"
#    FILE_TSCONFIG_JSON="tsconfig.json"
#    if [ ! -f "$FILE_PACKAGE_JSON" ]
#    then
#       echo "File $FILE_PACKAGE_JSON not found. Exit"
#       exit 1
#    elif [ ! -f "$FILE_TSCONFIG_JSON" ]
#    then
#        echo "File $FILE_TSCONFIG_JSON not found. Exit"
#        exit 1
#    else
#        echo "OK"

#        npm install
#        npm install

##        apk --no-cache add --virtual .native-deps \
##        g++ gcc libgcc libstdc++ linux-headers make python && \
##        npm install -g node-gyp &&\
##        npm install && \
##        apk del .native-deps
#    fi
#fi
#
exec "$@"