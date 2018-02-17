#!/bin/bash

PHP_BINARY="php"
DIR=""

while getopts "p:d:" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
		d)
		    DIR="$OPTARG"
		    ;;
	esac
done

if [ "$DIR" == "" ]; then
    echo No directory specified
    exit 1
fi

echo Running PHP lint scans on \"$DIR\"...

OUTPUT=`find "$DIR" -name "*.php" -print0 | xargs -0 -n1 -P4 "$PHP_BINARY" -l`

if [ $? -ne 0 ]; then
	echo $OUTPUT | grep -v "No syntax errors"
	exit 1
fi

echo Lint scan completed successfully.
