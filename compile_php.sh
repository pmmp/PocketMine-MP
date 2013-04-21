#!/bin/bash
COMPILER_VERSION="0.11"

PHP_VERSION="5.4.14"
ZEND_VM="GOTO"

ZLIB_VERSION="1.2.7"
PTHREADS_VERSION="53eb5d9ee6ec9c00ffa698681ecd132edeb5b8b2"
CURL_VERSION="curl-7_30_0"

echo "[PocketMine] PHP installer and compiler for Linux & Mac - by @shoghicp v$COMPILER_VERSION"
DIR="$(pwd)"
date > "$DIR/install.log" 2>&1
uname -a >> "$DIR/install.log" 2>&1
echo "[INFO] Checking dependecies"
type make >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"make\""; read -p "Press [Enter] to continue..."; exit 1; }
type autoconf >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"autoconf\""; read -p "Press [Enter] to continue..."; exit 1; }
type automake >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"automake\""; read -p "Press [Enter] to continue..."; exit 1; }
type libtool >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"libtool\""; read -p "Press [Enter] to continue..."; exit 1; }
type gcc >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"gcc\""; read -p "Press [Enter] to continue..."; exit 1; }
type m4 >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"m4\""; read -p "Press [Enter] to continue..."; exit 1; }
type wget >> "$DIR/install.log" 2>&1 || { echo >&2 "[ERROR] Please install \"wget\""; read -p "Press [Enter] to continue..."; exit 1; }

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

#zlib
echo -n "[zlib] downloading $ZLIB_VERSION..."
wget http://zlib.net/zlib-$ZLIB_VERSION.tar.gz -q -O - | tar -zx >> "$DIR/install.log" 2>&1
mv zlib-$ZLIB_VERSION zlib
echo -n " checking..."
cd zlib
./configure --prefix="$DIR/install_data/php/ext/zlib" \
--static >> "$DIR/install.log" 2>&1
echo -n " compiling..."
make >> "$DIR/install.log" 2>&1
echo -n " installing..."
make install >> "$DIR/install.log" 2>&1
echo -n " cleaning..."
cd ..
rm -r -f ./zlib
echo " done!"

echo -n "[cURL] downloading $CURL_VERSION..."
wget https://github.com/bagder/curl/archive/$CURL_VERSION.tar.gz --no-check-certificate -q -O - | tar -zx >> "$DIR/install.log" 2>&1
mv curl-$CURL_VERSION curl
echo -n " checking..."
cd curl
./buildconf >> "$DIR/install.log" 2>&1
./configure --prefix="$DIR/install_data/php/ext/curl" \
--disable-shared >> "$DIR/install.log" 2>&1
echo -n " compiling..."
make >> "$DIR/install.log" 2>&1
echo -n " installing..."
make install >> "$DIR/install.log" 2>&1
echo -n " cleaning..."
cd ..
rm -r -f ./curl
echo " done!"

#pthreads
echo -n "[PHP pthreads] downloading $PTHREADS_VERSION..."
wget https://github.com/krakjoe/pthreads/archive/$PTHREADS_VERSION.tar.gz --no-check-certificate -q -O - | tar -zx >> "$DIR/install.log" 2>&1
mv pthreads-$PTHREADS_VERSION "$DIR/install_data/php/ext/pthreads"
echo " done!"

echo -n "[PHP]"
set +e
if which free >/dev/null; then
    MAX_MEMORY=$(free -m | awk '/^Mem:/{print $2}')
else
    MAX_MEMORY=$(top -l 1 | grep PhysMem: | awk '{print $10}' | tr -d 'a-zA-Z')
fi
if [ $MAX_MEMORY -gt 2048 ]
then
  echo -n " enabling optimizations..."
  OPTIMIZATION="--enable-inline-optimization "
else
  OPTIMIZATION=""
fi
set -e
echo -n " checking..."
cd php
rm -rf ./aclocal.m4 >> "$DIR/install.log" 2>&1
rm -rf ./autom4te.cache/ >> "$DIR/install.log" 2>&1
rm -f ./configure >> "$DIR/install.log" 2>&1
./buildconf --force >> "$DIR/install.log" 2>&1
./configure $OPTIMIZATION--prefix="$DIR/php5" \
--exec-prefix="$DIR/php5" \
--enable-embedded-mysqli \
--enable-bcmath \
--with-curl="$DIR/install_data/php/ext/curl" \
--with-zlib="$DIR/install_data/php/ext/zlib" \
--disable-libxml \
--disable-xml \
--disable-dom \
--disable-simplexml \
--disable-xmlreader \
--disable-xmlwriter \
--without-pear \
--disable-cgi \
--disable-session \
--enable-ctype \
--without-iconv \
--without-pdo-sqlite \
--enable-sockets \
--enable-shared=no \
--enable-static=yes \
--enable-pcntl \
--enable-pthreads \
--enable-maintainer-zts \
--enable-zend-signals \
--with-zend-vm=$ZEND_VM \
--enable-cli >> "$DIR/install.log" 2>&1
echo -n " compiling..."
make >> "$DIR/install.log" 2>&1
echo -n " installing..."
make install >> "$DIR/install.log" 2>&1
echo " done!"
cd "$DIR"
echo -n "[INFO] Cleaning up..."
rm -r -f install_data/ >> "$DIR/install.log" 2>&1
mv php5/bin/php bin/php
rm -r -f php/ >> "$DIR/install.log" 2>&1
date >> "$DIR/install.log" 2>&1
echo " done!"
echo "[PocketMine] You should start the server now using \"./start.sh\""
echo "[PocketMine] If it doesn't works, please send the \"install.log\" file to the Bug Tracker"