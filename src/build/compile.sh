#!/bin/bash
COMPILER_VERSION="0.14"

PHP_VERSION="5.5.7"
ZEND_VM="GOTO"

LIBEDIT_VERSION="0.3"
ZLIB_VERSION="1.2.8"
PTHREADS_VERSION="0.0.45"
CURL_VERSION="curl-7_33_0"

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
type wget >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"wget\""; read -p "Press [Enter] to continue..."; exit 1; }

export CC="gcc"
COMPILE_FOR_ANDROID=no
RANLIB=ranlib
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
elif [ "$1" == "crosscompile" ]; then
	if [ "$2" == "android" ] || [ "$2" == "android-armv6" ]; then
		COMPILE_FOR_ANDROID=yes
		[ -z "$march" ] && march=armv6;
		[ -z "$mtune" ] && mtune=generic;
		TOOLCHAIN_PREFIX="arm-none-linux-gnueabi"
		export CC="$TOOLCHAIN_PREFIX-gcc"
		CONFIGURE_FLAGS="--host=$TOOLCHAIN_PREFIX"
		[ -z "$CFLAGS" ] && CFLAGS="-uclibc";
		echo "[INFO] Cross-compiling for Android ARMv6"
	elif [ "$2" == "android-armv7" ]; then
		COMPILE_FOR_ANDROID=yes
		[ -z "$march" ] && march=armv7a;
		[ -z "$mtune" ] && mtune=generic-armv7-a;
		TOOLCHAIN_PREFIX="arm-none-linux-gnueabi"
		export CC="$TOOLCHAIN_PREFIX-gcc"
		CONFIGURE_FLAGS="--host=$TOOLCHAIN_PREFIX"
		[ -z "$CFLAGS" ] && CFLAGS="-uclibc";
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
	else
		echo "Please supply a proper platform [android android-armv6 android-armv7 rpi mac] to cross-compile"
		exit 1
	fi
else
	echo "[INFO] Compiling for current machine"
	if [ $(uname -m) == "x86_64" ]; then
		CFLAGS="-mx32 $CFLAGS"
	fi
fi

type $CC >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"$CC\""; read -p "Press [Enter] to continue..."; exit 1; }

[ -z "$THREADS" ] && THREADS=1;
[ -z "$march" ] && march=native;
[ -z "$mtune" ] && mtune=native;
[ -z "$CFLAGS" ] && CFLAGS="";
[ -z "$CONFIGURE_FLAGS" ] && CONFIGURE_FLAGS="";

$CC -O3 -march=$march -mtune=$mtune -fno-gcse $CFLAGS -Q --help=target >> "$DIR/install.log" 2>&1
if [ $? -ne 0 ]; then
	$CC -O3 -fno-gcse $CFLAGS -Q --help=target >> "$DIR/install.log" 2>&1
	if [ $? -ne 0 ]; then
		export CFLAGS="-O3 -fno-gcse "
	else
		export CFLAGS="-O3 -fno-gcse $CFLAGS"
	fi
else
	export CFLAGS="-O3 -march=$march -mtune=$mtune -fno-gcse $CFLAGS"
fi


rm -r -f install_data/ >> "$DIR/install.log" 2>&1
rm -r -f php5/ >> "$DIR/install.log" 2>&1
rm -r -f bin/ >> "$DIR/install.log" 2>&1
mkdir -m 0777 install_data >> "$DIR/install.log" 2>&1
mkdir -m 0777 php5 >> "$DIR/install.log" 2>&1
mkdir -m 0777 bin >> "$DIR/install.log" 2>&1
cd install_data
set -e

#PHP 5
echo -n "[PHP] downloading $PHP_VERSION..."
wget http://php.net/get/php-$PHP_VERSION.tar.gz/from/this/mirror -q -O - | tar -zx >> "$DIR/install.log" 2>&1
mv php-$PHP_VERSION php
echo " done!"

if [ 1 ] || [ "$1" == "crosscompile" ] || [ "$1" == "rpi" ]; then
	HAVE_LIBEDIT="--without-libedit"
else
	#libedit
	echo -n "[libedit] downloading $LIBEDIT_VERSION..."
	wget http://download.sourceforge.net/project/libedit/libedit/libedit-$LIBEDIT_VERSION/libedit-$LIBEDIT_VERSION.tar.gz -q -O - | tar -zx >> "$DIR/install.log" 2>&1
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
wget http://zlib.net/zlib-$ZLIB_VERSION.tar.gz -q -O - | tar -zx >> "$DIR/install.log" 2>&1
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
   HAVE_CURL="shared,/usr/local"
else
	#curl
	echo -n "[cURL] downloading $CURL_VERSION..."
	wget https://github.com/bagder/curl/archive/$CURL_VERSION.tar.gz --no-check-certificate -q -O - | tar -zx >> "$DIR/install.log" 2>&1
	mv curl-$CURL_VERSION curl
	echo -n " checking..."
	cd curl
	./buildconf >> "$DIR/install.log" 2>&1
	./configure --enable-ipv6 \
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
	--prefix="$DIR/install_data/php/ext/curl" \
	--disable-shared \
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
wget http://pecl.php.net/get/pthreads-$PTHREADS_VERSION.tgz --no-check-certificate -q -O - | tar -zx >> "$DIR/install.log" 2>&1
mv pthreads-$PTHREADS_VERSION "$DIR/install_data/php/ext/pthreads"
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
./configure $OPTIMIZATION--prefix="$DIR/php5" \
--exec-prefix="$DIR/php5" \
--with-curl="$HAVE_CURL" \
--with-zlib="$DIR/install_data/php/ext/zlib" \
--with-zlib-dir="$DIR/install_data/php/ext/zlib" \
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
echo " done!"
cd "$DIR"
echo -n "[INFO] Cleaning up..."
rm -r -f install_data/ >> "$DIR/install.log" 2>&1
mv php5/bin/php bin/php
rm -r -f php5/ >> "$DIR/install.log" 2>&1
date >> "$DIR/install.log" 2>&1
echo " done!"
echo "[PocketMine] You should start the server now using \"./start.sh\""
echo "[PocketMine] If it doesn't works, please send the \"install.log\" file to the Bug Tracker"
