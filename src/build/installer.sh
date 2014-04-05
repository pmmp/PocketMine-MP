#!/bin/bash
PMMP_VERSION=""
LINUX_32_BUILD="PHP_5.5.11_x86_Linux"
LINUX_64_BUILD="PHP_5.5.11_x86-64_Linux"
MAC_32_BUILD="PHP_5.5.11_x86_MacOS"
MAC_64_BUILD="PHP_5.5.11_x86-64_MacOS"
RPI_BUILD="PHP_5.5.11_ARM_Raspbian_hard"
# Temporal build
ODROID_BUILD="PHP_5.5.11_ARM_Raspbian_hard"
AND_BUILD="PHP_5.5.11_ARMv7_Android"
IOS_BUILD="PHP_5.5.9_ARMv6_iOS"
update=off
forcecompile=off
alldone=no

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


while getopts "ucdv:" opt; do
  case $opt in
    u)
	  update=on
      ;;
    c)
	  forcecompile=on
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

echo "[1/3] Cleaning..."
rm -r -f src/
rm -f PocketMine-MP.php
rm -f README.md
rm -f CONTRIBUTING.md
rm -f LICENSE
rm -f start.sh
rm -f start.bat
echo "[2/3] Downloading PocketMine-MP $PMMP_VERSION..."
set +e
download_file "https://github.com/PocketMine/PocketMine-MP/releases/download/$PMMP_VERSION/PocketMine-MP.phar" > PocketMine-MP.phar
if ! [ -s "PocketMine-MP.phar" ]; then
	rm "PocketMine-MP.phar" > /dev/null
	download_file "https://github.com/PocketMine/PocketMine-MP/archive/$PMMP_VERSION.tar.gz" | tar -zx > /dev/null
	COMPILE_SCRIPT="./src/build/compile.sh"
	mv -f PocketMine-MP-$PMMP_VERSION/* ./
	rm -f -r PocketMine-MP-$PMMP_VERSION/
	rm -f ./start.cmd
else
	download_file "https://raw.githubusercontent.com/PocketMine/PocketMine-MP/$PMMP_VERSION/start.sh" > start.sh
	download_file "https://raw.githubusercontent.com/PocketMine/PocketMine-MP/$PMMP_VERSION/src/build/compile.sh" > compile.sh
	COMPILE_SCRIPT="./compile.sh"
fi

chmod +x "$COMPILE_SCRIPT"
chmod +x ./start.sh
if [ "$update" == "on" ]; then
	echo "[3/3] Skipping PHP recompilation due to user request"
else
	echo -n "[3/3] Obtaining PHP:"
	echo " detecting if build is available..."
	if [ "$forcecompile" == "off" ] && [ "$(uname -s)" == "Darwin" ]; then
		set +e
		UNAME_M=$(uname -m)
		IS_IOS=$(expr match $UNAME_M 'iP[a-zA-Z0-9,]*' 2> /dev/null)
		set -e
		if [[ "$IS_IOS" -gt 0 ]]; then
			rm -r -f bin/ >> /dev/null 2>&1
			echo -n "[3/3] iOS PHP build available, downloading $IOS_BUILD.tar.gz..."
			download_file "http://sourceforge.net/projects/pocketmine/files/builds/$IOS_BUILD.tar.gz" | tar -zx > /dev/null 2>&1
			chmod +x ./bin/php5/bin/*
			echo -n " checking..."
			if [ $(./bin/php5/bin/php -r 'echo "yes";' 2>/dev/null) == "yes" ]; then
				echo -n " regenerating php.ini..."
				TIMEZONE=$(date +%Z)
				echo "" > "./bin/php5/bin/php.ini"
				#UOPZ_PATH="$(find $(pwd) -name uopz.so)"
				#echo "zend_extension=\"$UOPZ_PATH\"" >> "./bin/php5/bin/php.ini"
				echo "date.timezone=$TIMEZONE" >> "./bin/php5/bin/php.ini"
				echo "short_open_tag=0" >> "./bin/php5/bin/php.ini"
				echo "asp_tags=0" >> "./bin/php5/bin/php.ini"
				echo "phar.readonly=0" >> "./bin/php5/bin/php.ini"
				echo "phar.require_hash=1" >> "./bin/php5/bin/php.ini"
				echo " done"
				alldone=yes
			else
				echo " invalid build detected"
			fi
		else
			rm -r -f bin/ >> /dev/null 2>&1
			if [ `getconf LONG_BIT` == "64" ]; then
				echo -n "[3/3] MacOS 64-bit PHP build available, downloading $MAC_64_BUILD.tar.gz..."
				MAC_BUILD="$MAC_64_BUILD"
			else
				echo -n "[3/3] MacOS 32-bit PHP build available, downloading $MAC_32_BUILD.tar.gz..."
				MAC_BUILD="$MAC_32_BUILD"
			fi
			download_file "http://sourceforge.net/projects/pocketmine/files/builds/$MAC_BUILD.tar.gz" | tar -zx > /dev/null 2>&1
			chmod +x ./bin/php5/bin/*
			echo -n " checking..."
			if [ $(./bin/php5/bin/php -r 'echo "yes";' 2>/dev/null) == "yes" ]; then
				echo -n " regenerating php.ini..."
				TIMEZONE=$(date +%Z)
				OPCACHE_PATH="$(find $(pwd) -name opcache.so)"
				echo "" > "./bin/php5/bin/php.ini"
				#UOPZ_PATH="$(find $(pwd) -name uopz.so)"
				#echo "zend_extension=\"$UOPZ_PATH\"" >> "./bin/php5/bin/php.ini"
				echo "zend_extension=\"$OPCACHE_PATH\"" >> "./bin/php5/bin/php.ini"
				echo "opcache.enable=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.enable_cli=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.save_comments=0" >> "./bin/php5/bin/php.ini"
				echo "opcache.fast_shutdown=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.max_accelerated_files=4096" >> "./bin/php5/bin/php.ini"
				echo "opcache.interned_strings_buffer=8" >> "./bin/php5/bin/php.ini"
				echo "opcache.memory_consumption=128" >> "./bin/php5/bin/php.ini"
				echo "opcache.optimization_level=0xffffffff" >> "./bin/php5/bin/php.ini"
				echo "date.timezone=$TIMEZONE" >> "./bin/php5/bin/php.ini"
				echo "short_open_tag=0" >> "./bin/php5/bin/php.ini"
				echo "asp_tags=0" >> "./bin/php5/bin/php.ini"
				echo "phar.readonly=0" >> "./bin/php5/bin/php.ini"
				echo "phar.require_hash=1" >> "./bin/php5/bin/php.ini"
				echo " done"
				alldone=yes
			else
				echo " invalid build detected"
			fi
		fi
	else
		grep -q BCM2708 /proc/cpuinfo > /dev/null 2>&1
		IS_RPI=$?
		grep -q ODROID /proc/cpuinfo > /dev/null 2>&1
		IS_ODROID=$?
		if [ "$IS_RPI" -eq 0 ] && [ "$forcecompile" == "off" ]; then
			rm -r -f bin/ >> /dev/null 2>&1
			echo -n "[3/3] Raspberry Pi PHP build available, downloading $RPI_BUILD.tar.gz..."
			download_file "http://sourceforge.net/projects/pocketmine/files/builds/$RPI_BUILD.tar.gz" | tar -zx > /dev/null 2>&1
			chmod +x ./bin/php5/bin/*
			echo -n " checking..."
			if [ $(./bin/php5/bin/php -r 'echo "yes";' 2>/dev/null) == "yes" ]; then
				echo -n " regenerating php.ini..."
				TIMEZONE=$(date +%Z)
				OPCACHE_PATH="$(find $(pwd) -name opcache.so)"
				echo "" > "./bin/php5/bin/php.ini"
				#UOPZ_PATH="$(find $(pwd) -name uopz.so)"
				#echo "zend_extension=\"$UOPZ_PATH\"" >> "./bin/php5/bin/php.ini"
				echo "zend_extension=\"$OPCACHE_PATH\"" >> "./bin/php5/bin/php.ini"
				echo "opcache.enable=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.enable_cli=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.save_comments=0" >> "./bin/php5/bin/php.ini"
				echo "opcache.fast_shutdown=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.max_accelerated_files=4096" >> "./bin/php5/bin/php.ini"
				echo "opcache.interned_strings_buffer=8" >> "./bin/php5/bin/php.ini"
				echo "opcache.memory_consumption=128" >> "./bin/php5/bin/php.ini"
				echo "opcache.optimization_level=0xffffffff" >> "./bin/php5/bin/php.ini"
				echo "date.timezone=$TIMEZONE" >> "./bin/php5/bin/php.ini"
				echo "short_open_tag=0" >> "./bin/php5/bin/php.ini"
				echo "asp_tags=0" >> "./bin/php5/bin/php.ini"
				echo "phar.readonly=0" >> "./bin/php5/bin/php.ini"
				echo "phar.require_hash=1" >> "./bin/php5/bin/php.ini"
				echo " done"
				alldone=yes
			else
				echo " invalid build detected"
			fi
		elif [ "$IS_ODROID" -eq 0 ] && [ "$forcecompile" == "off" ]; then
			rm -r -f bin/ >> /dev/null 2>&1
			echo -n "[3/3] ODROID PHP build available, downloading $ODROID_BUILD.tar.gz..."
			download_file "http://sourceforge.net/projects/pocketmine/files/builds/$ODROID_BUILD.tar.gz" | tar -zx > /dev/null 2>&1
			chmod +x ./bin/php5/bin/*
			echo -n " checking..."
			if [ $(./bin/php5/bin/php -r 'echo "yes";' 2>/dev/null) == "yes" ]; then
				echo -n " regenerating php.ini..."
				OPCACHE_PATH="$(find $(pwd) -name opcache.so)"
				echo "" > "./bin/php5/bin/php.ini"
				#UOPZ_PATH="$(find $(pwd) -name uopz.so)"
				#echo "zend_extension=\"$UOPZ_PATH\"" >> "./bin/php5/bin/php.ini"
				echo "zend_extension=\"$OPCACHE_PATH\"" >> "./bin/php5/bin/php.ini"
				echo "opcache.enable=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.enable_cli=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.save_comments=0" >> "./bin/php5/bin/php.ini"
				echo "opcache.fast_shutdown=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.max_accelerated_files=4096" >> "./bin/php5/bin/php.ini"
				echo "opcache.interned_strings_buffer=8" >> "./bin/php5/bin/php.ini"
				echo "opcache.memory_consumption=128" >> "./bin/php5/bin/php.ini"
				echo "opcache.optimization_level=0xffffffff" >> "./bin/php5/bin/php.ini"
				echo "date.timezone=$TIMEZONE" >> "./bin/php5/bin/php.ini"
				echo "short_open_tag=0" >> "./bin/php5/bin/php.ini"
				echo "asp_tags=0" >> "./bin/php5/bin/php.ini"
				echo "phar.readonly=0" >> "./bin/php5/bin/php.ini"
				echo "phar.require_hash=1" >> "./bin/php5/bin/php.ini"
				echo " done"
				alldone=yes
			else
				echo " invalid build detected"
			fi
		elif [ "$forcecompile" == "off" ] && [ "$(uname -s)" == "Linux" ]; then
			rm -r -f bin/ >> /dev/null 2>&1
			if [ `getconf LONG_BIT` = "64" ]; then
				echo -n "[3/3] Linux 64-bit PHP build available, downloading $LINUX_64_BUILD.tar.gz..."
				LINUX_BUILD="$LINUX_64_BUILD"
			else
				echo -n "[3/3] Linux 32-bit PHP build available, downloading $LINUX_32_BUILD.tar.gz..."
				LINUX_BUILD="$LINUX_32_BUILD"
			fi
			download_file "http://sourceforge.net/projects/pocketmine/files/builds/$LINUX_BUILD.tar.gz" | tar -zx > /dev/null 2>&1
			chmod +x ./bin/php5/bin/*
			echo -n " checking..."
			if [ $(./bin/php5/bin/php -r 'echo "yes";' 2>/dev/null) == "yes" ]; then
				echo -n " regenerating php.ini..."
				OPCACHE_PATH="$(find $(pwd) -name opcache.so)"
				echo "" > "./bin/php5/bin/php.ini"
				#UOPZ_PATH="$(find $(pwd) -name uopz.so)"
				#echo "zend_extension=\"$UOPZ_PATH\"" >> "./bin/php5/bin/php.ini"
				echo "zend_extension=\"$OPCACHE_PATH\"" >> "./bin/php5/bin/php.ini"
				echo "opcache.enable=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.enable_cli=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.save_comments=0" >> "./bin/php5/bin/php.ini"
				echo "opcache.fast_shutdown=1" >> "./bin/php5/bin/php.ini"
				echo "opcache.max_accelerated_files=4096" >> "./bin/php5/bin/php.ini"
				echo "opcache.interned_strings_buffer=8" >> "./bin/php5/bin/php.ini"
				echo "opcache.memory_consumption=128" >> "./bin/php5/bin/php.ini"
				echo "opcache.optimization_level=0xffffffff" >> "./bin/php5/bin/php.ini"
				echo "date.timezone=$TIMEZONE" >> "./bin/php5/bin/php.ini"
				echo "short_open_tag=0" >> "./bin/php5/bin/php.ini"
				echo "asp_tags=0" >> "./bin/php5/bin/php.ini"
				echo "phar.readonly=0" >> "./bin/php5/bin/php.ini"
				echo "phar.require_hash=1" >> "./bin/php5/bin/php.ini"
				echo " done"
				alldone=yes
			else
				echo " invalid build detected"
			fi
		fi
		if [ "$alldone" == "no" ]; then
			set -e
			echo "[3/3] no build found, compiling PHP"
			exec "$COMPILE_SCRIPT"
		fi
	fi
fi
if [ "$COMPILE_SCRIPT" == "./compile.sh" ]; then
	rm "$COMPILE_SCRIPT"
fi
echo "[INFO] Everything done! Run ./start.sh to start PocketMine-MP"
exit 0
