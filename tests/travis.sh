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

./tests/lint.sh -p "$PHP_BINARY" -d ./src/pocketmine

if [ $? -ne 0 ]; then
	echo Lint scan failed!
	exit 1
fi

#Run PHPUnit tests
#7.5.12 introduces changes that set the build on fire because we don't ship libxml - TODO FIX
curl https://phar.phpunit.de/phpunit-7.5.11.phar --silent --location -o phpunit.phar
"$PHP_BINARY" phpunit.phar --bootstrap vendor/autoload.php --fail-on-warning tests/phpunit || exit 1

#Run-the-server tests
DATA_DIR="test_data"
PLUGINS_DIR="$DATA_DIR/plugins"

rm -rf "$DATA_DIR"
rm PocketMine-MP.phar 2> /dev/null

cd tests/plugins/PocketMine-DevTools
"$PHP_BINARY" -dphar.readonly=0 ./src/DevTools/ConsoleScript.php --make ./ --relative ./ --out ../../../DevTools.phar
cd ../../..

"$PHP_BINARY" -dphar.readonly=0 DevTools.phar --make src,vendor,resources --relative ./ --entry src/pocketmine/PocketMine.php --out PocketMine-MP.phar
if [ -f PocketMine-MP.phar ]; then
	echo Server phar created successfully.
else
	echo Server phar was not created!
	exit 1
fi

mkdir "$DATA_DIR"
mkdir "$PLUGINS_DIR"
mv DevTools.phar "$PLUGINS_DIR"
cp -r tests/plugins/TesterPlugin "$PLUGINS_DIR"
echo -e "stop\n" | "$PHP_BINARY" PocketMine-MP.phar --no-wizard --disable-ansi --disable-readline --debug.level=2 --data="$DATA_DIR" --plugins="$PLUGINS_DIR" --anonymous-statistics.enabled=0 --settings.async-workers="$PM_WORKERS" --settings.enable-dev-builds=1

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
