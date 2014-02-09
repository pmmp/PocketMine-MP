#!/bin/bash
PMMP_VERSION=""
MAC_BUILD="PHP_5.5.9_x86_MacOS"
RPI_BUILD="PHP_5.5.9_ARM_Raspbian_hard"
AND_BUILD="PHP_5.5.9_ARMv7_Android"
IOS_BUILD="PHP_5.5.9_ARMv6_iOS"
update=off

#Needed to use aliases
shopt -s expand_aliases
type wget > /dev/null 2>&1
if [ $? -eq 0 ]; then
	alias download_file="wget --no-check-certificate -q -O -"
else
	type curl >> /dev/null 2>&1
	if [ $? -eq 0 ]; then
		alias download_file="curl --insecure --silent --location"
	else
		echo "error, curl or wget not found"
	fi
fi


while getopts "udv:" opt; do
  case $opt in
    u)
	  update=on
      ;;
	d)
	  PMMP_VERSION="master"
      ;;
	v)
	  PMMP_VERSION="$OPTARG"
      ;;
    \?)
      echo "Invalid option: -$OPTARG" >&2
	  exit 1
      ;;
  esac
done

if [ "$PMMP_VERSION" == "" ]; then
	PMMP_VERSION=$(download_file "https://api.github.com/repos/PocketMine/PocketMine-MP/tags" | grep '"name": "[A-Za-z0-9_\.]*",' | head -1 | sed -r 's/[ ]*"name": "([A-Za-z0-9_\.]*)",[ ]*/\1/')
	if [ "$PMMP_VERSION" == "" ]; then
		echo "[ERROR] Couldn't get the latest PocketMine-MP version"
		exit 1
	fi
fi

echo "[INFO] PocketMine-MP $PMMP_VERSION downloader & installer for Linux & Mac"

echo "[0/3] Cleaning..."
rm -r -f src/
rm -f PocketMine-MP.php
rm -f README.md
rm -f CONTRIBUTING.md
rm -f LICENSE
rm -f start.sh
rm -f start.bat
echo "[1/3] Downloading PocketMine-MP $PMMP_VERSION..."
set -e
download_file "https://github.com/PocketMine/PocketMine-MP/archive/$PMMP_VERSION.tar.gz" | tar -zx > /dev/null
mv -f PocketMine-MP-$PMMP_VERSION/* ./
rm -f -r PocketMine-MP-$PMMP_VERSION/
rm -f ./start.cmd
chmod +x ./start.sh
chmod +x ./src/build/compile.sh
if [ $update == on ]; then
	echo "[3/3] Skipping PHP recompilation due to user request"
else
	echo -n "[3/3] Obtaining PHP:"
	echo " detecting if build is available..."
	if [ "$(uname -s)" == "Darwin" ]; then
		set +e
		UNAME_M=$(uname -m)
		IS_IOS=$(expr match $UNAME_M 'iP[a-zA-Z0-9,]*')
		set -e
		if [ $IS_IOS -gt 0 ]; then
			rm -r -f bin/ >> /dev/null 2>&1
			echo -n "[3/3] iOS PHP build available, downloading $IOS_BUILD.tar.gz..."
			download_file "http://sourceforge.net/projects/pocketmine/files/builds/$IOS_BUILD.tar.gz" | tar -zx > /dev/null 2>&1
			chmod +x ./bin/php5/bin/*
			echo -n " regenerating php.ini..."
			echo "date.timezone=$TIMEZONE" >> "./bin/php5/lib/php.ini"
			echo "short_open_tag=0" >> "./bin/php5/lib/php.ini"
			echo "asp_tags=0" >> "./bin/php5/lib/php.ini"
			echo " done"
		else
			rm -r -f bin/ >> /dev/null 2>&1
			echo -n "[3/3] Mac OSX PHP build available, downloading $MAC_BUILD.tar.gz..."
			download_file "http://sourceforge.net/projects/pocketmine/files/builds/$MAC_BUILD.tar.gz" | tar -zx > /dev/null 2>&1
			chmod +x ./bin/php5/bin/*
			echo -n " regenerating php.ini..."
			OPCACHE_PATH=$(find "./bin/php5" -name opcache.so)
			echo "zend_extension=\"$OPCACHE_PATH\"" > "./bin/php5/lib/php.ini"
			echo "opcache.enable=1" >> "./bin/php5/lib/php.ini"
			echo "opcache.enable_cli=1" >> "./bin/php5/lib/php.ini"
			echo "opcache.save_comments=0" >> "./bin/php5/lib/php.ini"
			echo "opcache.fast_shutdown=1" >> "./bin/php5/lib/php.ini"
			echo "opcache.max_accelerated_files=4096" >> "./bin/php5/lib/php.ini"
			echo "opcache.interned_strings_buffer=8" >> "./bin/php5/lib/php.ini"
			echo "opcache.memory_consumption=128" >> "./bin/php5/lib/php.ini"
			echo "opcache.optimization_level=0xffffffff" >> "./bin/php5/lib/php.ini"
			echo "date.timezone=$TIMEZONE" >> "./bin/php5/lib/php.ini"
			echo "short_open_tag=0" >> "./bin/php5/lib/php.ini"
			echo "asp_tags=0" >> "./bin/php5/lib/php.ini"
			echo " done"
		fi
	else
		set +e
		grep -q BCM2708 /proc/cpuinfo > /dev/null 2>&1
		if [ $? -eq 0 ]; then
			set -e
			rm -r -f bin/ >> /dev/null 2>&1
			echo -n "[3/3] Raspberry Pi PHP build available, downloading $RPI_BUILD.tar.gz..."
			download_file "http://sourceforge.net/projects/pocketmine/files/builds/$RPI_BUILD.tar.gz" | tar -zx > /dev/null 2>&1
			chmod +x ./bin/php5/bin/*
			echo -n " regenerating php.ini..."
			OPCACHE_PATH=$(find "./bin/php5" -name opcache.so)
			echo "zend_extension=\"$OPCACHE_PATH\"" > "./bin/php5/lib/php.ini"
			echo "opcache.enable=1" >> "./bin/php5/lib/php.ini"
			echo "opcache.enable_cli=1" >> "./bin/php5/lib/php.ini"
			echo "opcache.save_comments=0" >> "./bin/php5/lib/php.ini"
			echo "opcache.fast_shutdown=1" >> "./bin/php5/lib/php.ini"
			echo "opcache.max_accelerated_files=4096" >> "./bin/php5/lib/php.ini"
			echo "opcache.interned_strings_buffer=8" >> "./bin/php5/lib/php.ini"
			echo "opcache.memory_consumption=128" >> "./bin/php5/lib/php.ini"
			echo "opcache.optimization_level=0xffffffff" >> "./bin/php5/lib/php.ini"
			echo "date.timezone=$TIMEZONE" >> "./bin/php5/lib/php.ini"
			echo "short_open_tag=0" >> "./bin/php5/lib/php.ini"
			echo "asp_tags=0" >> "./bin/php5/lib/php.ini"
			echo " done"
		else
			set -e
			echo "[3/3] no build found, compiling PHP"
			exec ./src/build/compile.sh
		fi
	fi
fi
echo "[INFO] Everything done! Run ./start.sh to start PocketMine-MP"
exit 0
