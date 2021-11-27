#!/bin/bash

PM_WORKERS="auto"

while getopts "t:" OPTION 2> /dev/null; do
	case ${OPTION} in
		t)
			PM_WORKERS="$OPTARG"
			;;
	esac
done

#Run-the-server tests
DATA_DIR="$(pwd)/test_data"
PLUGINS_DIR="$DATA_DIR/plugins"

rm -rf "$DATA_DIR"
rm PocketMine-MP.phar 2> /dev/null
mkdir "$DATA_DIR"
mkdir "$PLUGINS_DIR"

cd tests/plugins/DevTools
php -dphar.readonly=0 ./src/ConsoleScript.php --make ./ --relative ./ --out "$PLUGINS_DIR/DevTools.phar"
cd ../../..
composer make-server

if [ -f PocketMine-MP.phar ]; then
	echo Server phar created successfully.
else
	echo Server phar was not created!
	exit 1
fi

cp -r tests/plugins/TesterPlugin "$PLUGINS_DIR"
echo -e "stop\n" | php PocketMine-MP.phar --no-wizard --disable-ansi --disable-readline --debug.level=2 --data="$DATA_DIR" --plugins="$PLUGINS_DIR" --anonymous-statistics.enabled=0 --settings.async-workers="$PM_WORKERS" --settings.enable-dev-builds=1

output=$(grep '\[TesterPlugin\]' "$DATA_DIR/server.log")
if [ "$output" == "" ]; then
	echo TesterPlugin failed to run tests, check the logs
	exit 1
fi

result=$(echo "$output" | grep 'Finished' | grep -v 'PASS')
if [ "$result" != "" ]; then
	echo "$result"
	echo Some tests did not complete successfully, changing build status to failed
	exit 1
elif [ $(grep -c "ERROR\|CRITICAL\|EMERGENCY" "$DATA_DIR/server.log") -ne 0 ]; then
	echo Server log contains error messages, changing build status to failed
	exit 1
else
	echo All tests passed
fi
