#!/bin/bash
echo Running PHP lint scans...
shopt -s globstar
for file in src/pocketmine/*.php src/pocketmine/**/*.php; do
    OUTPUT=`php -l "$file"`
    [ $? -ne 0 ] && echo -n "$OUTPUT" && exit 1
done
echo Lint scan completed successfully.