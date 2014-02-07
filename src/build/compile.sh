#!/bin/bash
COMPILER_VERSION="0.16"

PHP_VERSION="5.5.9"
ZEND_VM="GOTO"

LIBEDIT_VERSION="0.3"
ZLIB_VERSION="1.2.8"
PTHREADS_VERSION="0.1.0"
PHPYAML_VERSION="1.1.1"
YAML_VERSION="0.1.4"
CURL_VERSION="curl-7_35_0"

echo "[PocketMine] PHP installer and compiler for Linux & Mac"
DIR="$(pwd)"
date > "$DIR/install.log" 2>&1
uname -a >> "$DIR/install.log" 2>&1
echo "[INFO] Checking dependecies"
type make >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"make\""; read -p "Press [Enter] to continue..."; exit 1; }
type autoconf >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"autoconf\""; read -p "Press [Enter] to continue..."; exit 1; }
type automake >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"automake\""; read -p "Press [Enter] to continue..."; exit 1; }
type libtool >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"libtool\""; read -p "Press [Enter] to continue..."; exit 1; }
type m4 >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"m4\""; read -p "Press [Enter] to continue..."; exit 1; }
type wget >> "$DIR/install.log" 2>&1 || type curl >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"wget\" or \"curl\""; read -p "Press [Enter] to continue..."; exit 1; }
type getconf >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"getconf\""; read -p "Press [Enter] to continue..."; exit 1; }

#Needed to use aliases
shopt -s expand_aliases
type wget >> "$DIR/install.log" 2>&1
if [ $? -eq 0 ]; then
	alias download_file="wget --no-check-certificate -q -O -"
else
	type curl >> "$DIR/install.log" 2>&1
	if [ $? -eq 0 ]; then
		alias download_file="curl --insecure --silent --location"
	else
		echo "error, curl or wget not found"
	fi
fi

export CC="gcc"
COMPILE_FOR_ANDROID=no
RANLIB=ranlib
HAVE_MYSQLI="--with-mysqli=mysqlnd"
if [ "$1" == "rpi" ]; then
	[ -z "$march" ] && march=armv6zk;
	[ -z "$mtune" ] && mtune=arm1176jzf-s;
	[ -z "$CFLAGS" ] && CFLAGS="-mfloat-abi=hard -mfpu=vfp";
	echo "[INFO] Compiling for Raspberry Pi ARMv6zk hard float"
elif [ "$1" == "mac" ]; then
	[ -z "$march" ] && march=prescott;
	[ -z "$mtune" ] && mtune=generic;
	[ -z "$CFLAGS" ] && CFLAGS="-fomit-frame-pointer";
	echo "[INFO] Compiling for Intel MacOS"
elif [ "$1" == "ios" ]; then
	[ -z "$march" ] && march=armv6;
	[ -z "$mtune" ] && mtune=cortex-a8;
	echo "[INFO] Compiling for iOS ARMv6"
elif [ "$1" == "crosscompile" ]; then
	HAVE_MYSQLI="--without-mysqli"
	if [ "$2" == "android" ] || [ "$2" == "android-armv6" ]; then
		COMPILE_FOR_ANDROID=yes
		[ -z "$march" ] && march=armv6;
		[ -z "$mtune" ] && mtune=arm1136jf-s;
		TOOLCHAIN_PREFIX="arm-unknown-linux-uclibcgnueabi"
		export CC="$TOOLCHAIN_PREFIX-gcc"
		CONFIGURE_FLAGS="--host=$TOOLCHAIN_PREFIX --enable-static-link --disable-ipv6"
		CFLAGS="-uclibc --static $CFLAGS";
		LDFLAGS="--static"
		echo "[INFO] Cross-compiling for Android ARMv6"
	elif [ "$2" == "android-armv7" ]; then
		COMPILE_FOR_ANDROID=yes
		[ -z "$march" ] && march=armv7-a;
		[ -z "$mtune" ] && mtune=cortex-a8;
		TOOLCHAIN_PREFIX="arm-unknown-linux-uclibcgnueabi"
		export CC="$TOOLCHAIN_PREFIX-gcc"
		CONFIGURE_FLAGS="--host=$TOOLCHAIN_PREFIX --enable-static-link --disable-ipv6"
		CFLAGS="-uclibc --static $CFLAGS";
		LDFLAGS="--static"
		echo "[INFO] Cross-compiling for Android ARMv7"
	elif [ "$2" == "rpi" ]; then
		TOOLCHAIN_PREFIX="arm-linux-gnueabihf"
		[ -z "$march" ] && march=armv6zk;
		[ -z "$mtune" ] && mtune=arm1176jzf-s;
		[ -z "$CFLAGS" ] && CFLAGS="-mfloat-abi=hard -mfpu=vfp";
		export CC="$TOOLCHAIN_PREFIX-gcc"
		CONFIGURE_FLAGS="--host=$TOOLCHAIN_PREFIX"
		[ -z "$CFLAGS" ] && CFLAGS="-uclibc";
		echo "[INFO] Cross-compiling for Raspberry Pi ARMv6zk hard float"
	elif [ "$2" == "mac" ]; then
		[ -z "$march" ] && march=prescott;
		[ -z "$mtune" ] && mtune=generic;
		[ -z "$CFLAGS" ] && CFLAGS="-fomit-frame-pointer";
		TOOLCHAIN_PREFIX="i686-apple-darwin10"
		export CC="$TOOLCHAIN_PREFIX-gcc"
		CONFIGURE_FLAGS="--host=$TOOLCHAIN_PREFIX"
		#zlib doesn't use the correct ranlib
		RANLIB=$TOOLCHAIN_PREFIX-ranlib
		echo "[INFO] Cross-compiling for Intel MacOS"
	elif [ "$2" == "ios" ] || [ "$2" == "ios-armv6" ]; then
		[ -z "$march" ] && march=armv6;
		[ -z "$mtune" ] && mtune=generic-armv6;
		CONFIGURE_FLAGS="--target=arm-apple-darwin10"
	elif [ "$2" == "ios-armv7" ]; then
		[ -z "$march" ] && march=armv7-a;
		[ -z "$mtune" ] && mtune=generic-armv7-a;
		CONFIGURE_FLAGS="--target=arm-apple-darwin10"
	else
		echo "Please supply a proper platform [android android-armv6 android-armv7 rpi mac ios ios-armv6 ios-armv7] to cross-compile"
		exit 1
	fi
elif [ -z "$CFLAGS" ]; then
	if [ `getconf LONG_BIT` = "64" ]; then
		echo "[INFO] Compiling for current machine using 64-bit"
		CFLAGS="-m64 $CFLAGS"
	else
		echo "[INFO] Compiling for current machine using 32-bit"
		CFLAGS="-m32 $CFLAGS"
	fi
fi

cat > test.c <<'CTEST'
#include <stdio.h>
main(){
	printf("Hello world\n");
}
CTEST


type $CC >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"$CC\""; read -p "Press [Enter] to continue..."; exit 1; }

[ -z "$THREADS" ] && THREADS=1;
[ -z "$march" ] && march=native;
[ -z "$mtune" ] && mtune=native;
[ -z "$CFLAGS" ] && CFLAGS="";
[ -z "$LDFLAGS" ] && LDFLAGS="";
[ -z "$CONFIGURE_FLAGS" ] && CONFIGURE_FLAGS="";


if [ "$mtune" != "none" ]; then
	$CC -march=$march -mtune=$mtune $CFLAGS -o test test.c >> "$DIR/install.log" 2>&1
	if [ $? -eq 0 ]; then
		CFLAGS="-march=$march -mtune=$mtune -fno-gcse $CFLAGS"
	fi
else
	$CC -march=$march $CFLAGS -o test test.c >> "$DIR/install.log" 2>&1
	if [ $? -eq 0 ]; then
		CFLAGS="-march=$march -fno-gcse $CFLAGS"
	fi
fi

rm test >> "$DIR/install.log" 2>&1
rm test.c >> "$DIR/install.log" 2>&1

export CFLAGS="-O2 $CFLAGS"
export LDFLAGS="$LDFLAGS"

rm -r -f install_data/ >> "$DIR/install.log" 2>&1
rm -r -f bin/ >> "$DIR/install.log" 2>&1
mkdir -m 0777 install_data >> "$DIR/install.log" 2>&1
mkdir -m 0777 bin >> "$DIR/install.log" 2>&1
cd install_data
set -e

#PHP 5
echo -n "[PHP] downloading $PHP_VERSION..."
download_file "http://php.net/get/php-$PHP_VERSION.tar.gz/from/this/mirror" | tar -zx >> "$DIR/install.log" 2>&1
mv php-$PHP_VERSION php
echo " done!"

if [ 1 ] || [ "$1" == "crosscompile" ] || [ "$1" == "rpi" ]; then
	HAVE_LIBEDIT="--without-libedit"
else
	#libedit
	echo -n "[libedit] downloading $LIBEDIT_VERSION..."
	download_file "http://download.sourceforge.net/project/libedit/libedit/libedit-$LIBEDIT_VERSION/libedit-$LIBEDIT_VERSION.tar.gz" | tar -zx >> "$DIR/install.log" 2>&1
	echo -n " checking..."
	cd libedit
	./configure --prefix="$DIR/install_data/php/ext/libedit" --enable-static >> "$DIR/install.log" 2>&1
	echo -n " compiling..."
	if make -j $THREADS >> "$DIR/install.log" 2>&1; then
		echo -n " installing..."
		make install >> "$DIR/install.log" 2>&1
		HAVE_LIBEDIT="--with-libedit=\"$DIR/install_data/php/ext/libedit\""
	else
		echo -n " disabling..."
		HAVE_LIBEDIT="--without-libedit"
	fi
	echo -n " cleaning..."
	cd ..
	rm -r -f ./libedit
	echo " done!"
fi

#zlib
echo -n "[zlib] downloading $ZLIB_VERSION..."
download_file "http://zlib.net/zlib-$ZLIB_VERSION.tar.gz" | tar -zx >> "$DIR/install.log" 2>&1
mv zlib-$ZLIB_VERSION zlib
echo -n " checking..."
cd zlib
RANLIB=$RANLIB ./configure --prefix="$DIR/install_data/php/ext/zlib" \
--static >> "$DIR/install.log" 2>&1
echo -n " compiling..."
make -j $THREADS >> "$DIR/install.log" 2>&1
echo -n " installing..."
make install >> "$DIR/install.log" 2>&1
echo -n " cleaning..."
cd ..
rm -r -f ./zlib
echo " done!"

if [ "$(uname -s)" == "Darwin" ] && [ "$1" != "crosscompile" ] && [ "$2" != "curl" ]; then
   HAVE_CURL="shared,/usr"
else
	#curl
	echo -n "[cURL] downloading $CURL_VERSION..."
	download_file "https://github.com/bagder/curl/archive/$CURL_VERSION.tar.gz" | tar -zx >> "$DIR/install.log" 2>&1
	mv curl-$CURL_VERSION curl
	echo -n " checking..."
	cd curl
	if [ ! -f ./configure ]; then
		./buildconf --force >> "$DIR/install.log" 2>&1
	fi
	./configure --disable-dependency-tracking \
	--enable-ipv6 \
	--enable-optimize \
	--enable-http \
	--enable-ftp \
	--disable-dict \
	--enable-file \
	--disable-gopher \
	--disable-imap \
	--disable-pop3 \
	--disable-rtsp \
	--disable-smtp \
	--disable-telnet \
	--disable-tftp \
	--disable-ldap \
	--disable-ldaps \
	--without-libidn \
	--enable-threaded-resolver \
	--prefix="$DIR/install_data/php/ext/curl" \
	--disable-shared \
	--enable-static \
	$CONFIGURE_FLAGS >> "$DIR/install.log" 2>&1
	echo -n " compiling..."
	make -j $THREADS >> "$DIR/install.log" 2>&1
	echo -n " installing..."
	make install >> "$DIR/install.log" 2>&1
	echo -n " cleaning..."
	cd ..
	rm -r -f ./curl
	echo " done!"
	HAVE_CURL="$DIR/install_data/php/ext/curl"
fi

#pthreads
echo -n "[PHP pthreads] downloading $PTHREADS_VERSION..."
download_file "http://pecl.php.net/get/pthreads-$PTHREADS_VERSION.tgz" | tar -zx >> "$DIR/install.log" 2>&1
mv pthreads-$PTHREADS_VERSION "$DIR/install_data/php/ext/pthreads"
echo " done!"

#PHP YAML
echo -n "[PHP YAML] downloading $PHPYAML_VERSION..."
download_file "http://pecl.php.net/get/yaml-$PHPYAML_VERSION.tgz" | tar -zx >> "$DIR/install.log" 2>&1
mv yaml-$PHPYAML_VERSION "$DIR/install_data/php/ext/yaml"
echo " done!"

#YAML
echo -n "[YAML] downloading $YAML_VERSION..."
download_file "http://pyyaml.org/download/libyaml/yaml-$YAML_VERSION.tar.gz" | tar -zx >> "$DIR/install.log" 2>&1
mv yaml-$YAML_VERSION yaml
echo -n " checking..."
cd yaml
RANLIB=$RANLIB ./configure \
--prefix="$DIR/install_data/php/ext/yaml" \
--enable-static \
--disable-shared \
$CONFIGURE_FLAGS >> "$DIR/install.log" 2>&1
echo -n " compiling..."
make -j $THREADS >> "$DIR/install.log" 2>&1
echo -n " installing..."
make install >> "$DIR/install.log" 2>&1
echo -n " cleaning..."
cd ..
rm -r -f ./yaml
echo " done!"

echo -n "[PHP]"
set +e
if which free >/dev/null; then
	MAX_MEMORY=$(free -m | awk '/^Mem:/{print $2}')
else
	MAX_MEMORY=$(top -l 1 | grep PhysMem: | awk '{print $10}' | tr -d 'a-zA-Z')
fi
if [ $MAX_MEMORY -gt 512 ] && [ "$1" != "crosscompile" ]; then
	echo -n " enabling optimizations..."
	OPTIMIZATION="--enable-inline-optimization "
else
	OPTIMIZATION="--disable-inline-optimization "
fi
set -e
echo -n " checking..."
cd php
rm -rf ./aclocal.m4 >> "$DIR/install.log" 2>&1
rm -rf ./autom4te.cache/ >> "$DIR/install.log" 2>&1
rm -f ./configure >> "$DIR/install.log" 2>&1
./buildconf --force >> "$DIR/install.log" 2>&1
if [ "$1" == "crosscompile" ]; then
	sed -i 's/pthreads_working=no/pthreads_working=yes/' ./configure
	export LIBS="-lpthread -ldl"
	CONFIGURE_FLAGS="$CONFIGURE_FLAGS --enable-opcache=no"

fi
./configure $OPTIMIZATION--prefix="$DIR/bin/php5" \
--exec-prefix="$DIR/bin/php5" \
--with-curl="$HAVE_CURL" \
--with-zlib="$DIR/install_data/php/ext/zlib" \
--with-zlib-dir="$DIR/install_data/php/ext/zlib" \
--with-yaml="$DIR/install_data/php/ext/yaml" \
$HAVE_LIBEDIT \
--disable-libxml \
--disable-xml \
--disable-dom \
--disable-simplexml \
--disable-xmlreader \
--disable-xmlwriter \
--disable-cgi \
--disable-session \
--disable-debug \
--disable-phar \
--disable-pdo \
--without-pear \
--without-iconv \
--without-pdo-sqlite \
--enable-ctype \
--enable-sockets \
--enable-shared=no \
--enable-static=yes \
--enable-shmop \
--enable-pcntl \
--enable-pthreads \
--enable-maintainer-zts \
--enable-zend-signals \
$HAVE_MYSQLI \
--enable-embedded-mysqli \
--enable-bcmath \
--enable-cli \
--enable-zip \
--with-zend-vm=$ZEND_VM \
$CONFIGURE_FLAGS >> "$DIR/install.log" 2>&1
echo -n " compiling..."
if [ $COMPILE_FOR_ANDROID == "yes" ]; then
	sed -i 's/-export-dynamic/-all-static/g' Makefile
fi
make -j $THREADS >> "$DIR/install.log" 2>&1
echo -n " installing..."
make install >> "$DIR/install.log" 2>&1
echo " generating php.ini..."

TIMEZONE=$(date +%Z)
touch "$DIR/bin/php5/lib/php.ini"
if [ "$1" != "crosscompile" ]; then
	OPCACHE_PATH=$(find "$DIR/bin/php5" -name opcache.so)
	echo "zend_extension=\"$OPCACHE_PATH\"" >> "$DIR/bin/php5/lib/php.ini"
	echo "opcache.enable=1" >> "$DIR/bin/php5/lib/php.ini"
	echo "opcache.enable_cli=1" >> "$DIR/bin/php5/lib/php.ini"
	echo "opcache.save_comments=0" >> "$DIR/bin/php5/lib/php.ini"
	echo "opcache.fast_shutdown=1" >> "$DIR/bin/php5/lib/php.ini"
	echo "opcache.max_accelerated_files=4096" >> "$DIR/bin/php5/lib/php.ini"
	echo "opcache.interned_strings_buffer=8" >> "$DIR/bin/php5/lib/php.ini"
	echo "opcache.memory_consumption=128" >> "$DIR/bin/php5/lib/php.ini"
	echo "opcache.optimization_level=0xffffffff" >> "$DIR/bin/php5/lib/php.ini"
fi
if [ "$HAVE_CURL" == "shared,/usr" ]; then
	echo "extension=curl.so" >> "$DIR/bin/php5/lib/php.ini"
fi
echo "date.timezone=$TIMEZONE" >> "$DIR/bin/php5/lib/php.ini"
echo "short_open_tag=0" >> "$DIR/bin/php5/lib/php.ini"
echo "asp_tags=0" >> "$DIR/bin/php5/lib/php.ini"

echo " done!"
cd "$DIR"
echo -n "[INFO] Cleaning up..."
rm -r -f install_data/ >> "$DIR/install.log" 2>&1
date >> "$DIR/install.log" 2>&1
echo " done!"
echo "[PocketMine] You should start the server now using \"./start.sh.\""
echo "[PocketMine] If it doesn't work, please send the \"install.log\" file to the Bug Tracker."
