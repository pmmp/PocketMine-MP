#!/bin/bash

PHP_BINARY="php"

while getopts "p:" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
	esac
done

./tests/lint.sh -p "$PHP_BINARY" -d ./src
if [ $? -ne 0 ]; then
	echo Lint scan failed!
	exit 1
fi

curl https://phar.phpunit.de/phpunit-7.phar --silent --location -o phpunit.phar

"$PHP_BINARY" phpunit.phar --bootstrap vendor/autoload.php tests/phpunit

