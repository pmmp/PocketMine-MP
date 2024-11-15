#!/usr/bin/env bash
DIR="$(cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd)"
cd "$DIR" || { echo "Couldn't change directory to $DIR"; exit 1; }

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
	elif [[ -n $(type php 2> /dev/null) ]]; then
		PHP_BINARY=$(type -p php)
	else
		echo "Couldn't find a PHP binary in system PATH or $PWD/bin/php7/bin"
		echo "Please refer to the installation instructions at https://doc.pmmp.io/en/rtfd/installation.html"
		exit 1
	fi
fi

if [ "$POCKETMINE_FILE" == "" ]; then
	if [ -f ./PocketMine-MP.phar ]; then
		POCKETMINE_FILE="./PocketMine-MP.phar"
	else
		echo "PocketMine-MP.phar not found"
		echo "Downloads can be found at https://github.com/pmmp/PocketMine-MP/releases"
		exit 1
	fi
fi

LOOPS=0

handle_exit_code() {
	local exitcode=$1
	if [ "$exitcode" -eq 134 ] || [ "$exitcode" -eq 139 ]; then #SIGABRT/SIGSEGV
		echo ""
		echo "ERROR: The server process was killed due to a critical error (code $exitcode) which could indicate a problem with PHP."
		echo "Updating your PHP binary is recommended."
		echo "If this keeps happening, please open a bug report."
		echo ""
	elif [ "$exitcode" -eq 143 ]; then #SIGKILL, maybe user intervention
		echo ""
		echo "WARNING: Server was forcibly killed!"
		echo "If you didn't kill the server manually, this probably means the server used too much memory and was killed by the system's OOM Killer."
		echo "Please ensure your system has enough available RAM."
		echo ""
	elif [ "$exitcode" -ne 0 ] && [ "$exitcode" -ne 137 ]; then #normal exit / SIGTERM
		echo ""
		echo "WARNING: Server did not shut down correctly! (code $exitcode)"
		echo ""
	fi
}

set +e

if [ "$DO_LOOP" == "yes" ]; then
	while true; do
		if [ ${LOOPS} -gt 0 ]; then
			echo "Restarted $LOOPS times"
		fi
		"$PHP_BINARY" "$POCKETMINE_FILE" "$@"
		handle_exit_code $?
		echo "To escape the loop, press CTRL+C now. Otherwise, wait 5 seconds for the server to restart."
		echo ""
		sleep 5
		((LOOPS++))
	done
else
	"$PHP_BINARY" "$POCKETMINE_FILE" "$@"
	exitcode=$?
	handle_exit_code $exitcode
	exit $exitcode
fi
