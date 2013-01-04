#!/bin/bash
DIR="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR
if [ -f php5/bin/php ]; then
./php5/bin/php -d enable_dl=On PocketMine-MP.php
exit 0
fi
php -d enable_dl=On PocketMine-MP.php