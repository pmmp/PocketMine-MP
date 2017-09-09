#!/bin/bash

PHP_BINARY="php"

while getopts "p:" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
	esac
done

./tests/lint.sh -p "$PHP_BINARY"

if [ $? -ne 0 ]; then
	echo Lint scan failed!
	exit 1
fi

rm server.log 2> /dev/null
rm PocketMine-MP.phar 2> /dev/null

cd tests/plugins/PocketMine-DevTools
"$PHP_BINARY" -dphar.readonly=0 ./src/DevTools/ConsoleScript.php --make ./ --relative ./ --out ../../../DevTools.phar
cd ../../..

"$PHP_BINARY" -dphar.readonly=0 DevTools.phar --make src --relative ./ --entry src/pocketmine/PocketMine.php --out PocketMine-MP.phar
if [ -f PocketMine-MP.phar ]; then
	echo Server phar created successfully.
else
	echo Server phar was not created!
	exit 1
fi


mkdir plugins 2> /dev/null
mv DevTools.phar plugins
cp -r tests/plugins/PocketMine-TesterPlugin ./plugins
echo -e "stop\n" | "$PHP_BINARY" PocketMine-MP.phar --no-wizard --disable-ansi --disable-readline --debug.level=2

output=$(grep '\[TesterPlugin\]' server.log)
if [ "$output" == "" ]; then
	echo TesterPlugin failed to run tests, check the logs
	exit 1
fi

result=$(echo "$output" | grep 'Finished' | grep -v 'PASS')
if [ "$result" != "" ]; then
	echo "$result"
	echo Some tests did not complete successfully, changing build status to failed
	exit 1
elif [ $(grep -c "ERROR\|CRITICAL\|EMERGENCY" server.log) -ne 0 ]; then
	echo Server log contains error messages, changing build status to failed
	exit 1
else
	echo All tests passed
fi
