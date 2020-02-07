#!/bin/bash

set -e

LEVELDB_VERSION="$1"
if [ ! -f "./leveldb-mcpe/built_version" ] || [ $(cat "./leveldb-mcpe/built_version") != "$LEVELDB_VERSION" ]; then
    echo "Building new LevelDB"
    rm -rf "./leveldb-mcpe" || true
    mkdir "./leveldb-mcpe"

    curl -fsSL "https://github.com/pmmp/leveldb-mcpe/archive/$LEVELDB_VERSION.tar.gz" | tar -zx
    mv "./leveldb-mcpe-$LEVELDB_VERSION" leveldb-mcpe-build
    cd leveldb-mcpe-build
    make -j4 sharedlibs

    #put the artifacts in a separate dir, to avoid bloating travis cache"
    mv out-shared/libleveldb.* ../leveldb-mcpe
    mv include ../leveldb-mcpe
    cd ../leveldb-mcpe
    echo "$LEVELDB_VERSION" > "./built_version"
    cd ..
else
    echo "Using cached build for LevelDB version $LEVELDB_VERSION"
fi
