#!/bin/bash

PHP_BINARY="php"
PM_WORKERS="auto"

while getopts "p:t:" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
		t)
			PM_WORKERS="$OPTARG"
            ;;
	esac
done

bash ci/lint.sh -p "$PHP_BINARY"

if [ $? -ne 0 ]; then
	echo Lint scan failed!
	exit 1
fi

rm server.log 2> /dev/null
mkdir -p ./plugins

echo -e "\nversion\nms\nstop\n" | "$PHP_BINARY" src/pocketmine/PocketMine.php --no-wizard --disable-ansi --disable-readline --debug.level=2  --settings.async-workers="$PM_WORKERS" --settings.enable-dev-builds=1
if ls plugins/Altay/Altay*.phar >/dev/null 2>&1; then
    echo Server phar created successfully.
else
    echo No phar created!
    exit 1
fi