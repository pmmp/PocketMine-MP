#!/bin/bash

PHP_BINARY="php"

while getopts "p:" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
	esac
done

echo Running PHP lint scans...
shopt -s globstar
for file in src/pocketmine/**/*.php; do
	OUTPUT=`"$PHP_BINARY" -l "$file"`
	[ $? -ne 0 ] && echo -n "$OUTPUT" && exit 1
done
echo Lint scan completed successfully.