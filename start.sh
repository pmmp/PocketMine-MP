#!/bin/bash
DIR="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"
if [ -f ./php5/bin/php ]; then
mkdir -m 0777 bin/
mv ./php5/bin/php ./bin/php
rm -r -f ./php5/
fi
if [ -f ./bin/php ]; then
./bin/php -d enable_dl=On PocketMine-MP.php $@
fi
php -d enable_dl=On PocketMine-MP.php $@
exit 0