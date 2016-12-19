#!/bin/bash
echo -e "version\nmakeserver\nstop\n" | php src/pocketmine/PocketMine.php --no-wizard --disable-ansi --disable-readline --debug.level=2
if ls plugins/DevTools/PocketMine*.phar >/dev/null 2>&1; then
    echo Server phar created successfully.
else
    echo No phar created!
    exit 1
fi
