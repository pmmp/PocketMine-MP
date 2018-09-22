#!/bin/bash
DIR="$(cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd)"
cd "$DIR"

while getopts "p:f:l" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
		f)
			POCKETMINE_FILE="$OPTARG"
			;;
		l)
			DO_LOOP="yes"
			;;
		\?)
			break
			;;
	esac
done

if [ "$PHP_BINARY" == "" ]; then
	if [ -f ./bin/php7/bin/php ]; then
		export PHPRC=""
		PHP_BINARY="./bin/php7/bin/php"
	elif [[ ! -z $(type php) ]]; then
		PHP_BINARY=$(type -p php)
	else
		echo "Couldn't find a working PHP 7.2 binary, please use the installer."
		exit 1
	fi
fi

if [ "$POCKETMINE_FILE" == "" ]; then
	if [ -f ./Altay.phar ]; then
		POCKETMINE_FILE="./Altay.phar"
	elif [ -f ./src/pocketmine/PocketMine.php ]; then
		POCKETMINE_FILE="./src/pocketmine/PocketMine.php"
	else
		echo "Couldn't find a valid Altay installation"
		exit 1
	fi
fi

LOOPS=0

set +e

if [ "$DO_LOOP" == "yes" ]; then
	while true; do
		if [ ${LOOPS} -gt 0 ]; then
			echo "Restarted $LOOPS times"
		fi
		"$PHP_BINARY" "$POCKETMINE_FILE" $@
		echo "To escape the loop, press CTRL+C now. Otherwise, wait 3 seconds for the server to restart."
		sleep 3
		((LOOPS++))
	done
else
	exec "$PHP_BINARY" "$POCKETMINE_FILE" $@
fi