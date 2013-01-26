#!/bin/bash
COMPILER_VERSION="0.5"
PHP_VERSION="5.4.11"
ZLIB_VERSION="1.2.7"
GMP_VERSION="5.1.0"
PTHREADS_VERSION="2ef11a4341d12c697d508138863f3c79b3729189"
CURL_VERSION="curl-7_28_1"

echo "[PocketMine] PHP installer and compiler for Linux - by @shoghicp v$COMPILER_VERSION"
DIR=`pwd`
date > $DIR/install.log 2>&1
uname -a >> $DIR/install.log 2>&1
echo "[INFO] Checking dependecies"
type make >> $DIR/install.log 2>&1 || { echo >&2 "[ERROR] Please install \"make\""; exit 1; }
type autoconf >> $DIR/install.log 2>&1 || { echo >&2 "[ERROR] Please install \"autoconf\""; exit 1; }
type automake >> $DIR/install.log 2>&1 || { echo >&2 "[ERROR] Please install \"automake\""; exit 1; }
type gcc >> $DIR/install.log 2>&1 || { echo >&2 "[ERROR] Please install \"gcc\""; exit 1; }
type m4 >> $DIR/install.log 2>&1 || { echo >&2 "[ERROR] Please install \"m4\""; exit 1; }
rm -r -f install_data/ >> $DIR/install.log 2>&1
rm -r -f php5/ >> $DIR/install.log 2>&1
mkdir -m 0777 install_data >> $DIR/install.log 2>&1
mkdir -m 0777 php5 >> $DIR/install.log 2>&1
cd install_data

#PHP 5
echo -n "[PHP5] downloading $PHP_VERSION..."
wget http://php.net/get/php-$PHP_VERSION.tar.gz/from/this/mirror -q -O - | tar -zx >> $DIR/install.log 2>&1
mv php-$PHP_VERSION php
echo " done!"

#zlib
echo -n "[zlib] downloading $ZLIB_VERSION..."
wget http://zlib.net/zlib-$ZLIB_VERSION.tar.gz -q -O - | tar -zx >> $DIR/install.log 2>&1
mv zlib-$ZLIB_VERSION zlib
echo -n " checking..."
cd zlib
./configure --prefix=$DIR/install_data/php/ext/zlib \
--static >> $DIR/install.log 2>&1
echo -n " compiling..."
make >> $DIR/install.log 2>&1
echo -n " installing..."
make install >> $DIR/install.log 2>&1
echo -n " cleaning..."
cd ..
rm -r -f ./zlib
echo " done!"

#GMP
echo -n "[GMP] downloading $GMP_VERSION..."
wget ftp://ftp.gmplib.org/pub/gmp-$GMP_VERSION/gmp-$GMP_VERSION.tar.bz2 -q -O - | tar -xj >> $DIR/install.log 2>&1
mv gmp-$GMP_VERSION gmp
echo -n " checking..."
cd gmp
./configure --prefix=$DIR/install_data/php/ext/gmp \
--disable-shared >> $DIR/install.log 2>&1
echo -n " compiling..."
make >> $DIR/install.log 2>&1
echo -n " installing..."
make install >> $DIR/install.log 2>&1
echo -n " cleaning..."
cd ..
rm -r -f ./gmp
echo " done!"

echo -n "[cURL] downloading $CURL_VERSION..."
wget https://github.com/bagder/curl/archive/$CURL_VERSION.tar.gz --no-check-certificate -q -O - | tar -zx >> $DIR/install.log 2>&1
mv curl-$CURL_VERSION curl
echo -n " checking..."
cd curl
./buildconf >> $DIR/install.log 2>&1
./configure --prefix=$DIR/install_data/php/ext/curl \
--disable-shared >> $DIR/install.log 2>&1
echo -n " compiling..."
make >> $DIR/install.log 2>&1
echo -n " installing..."
make install >> $DIR/install.log 2>&1
echo -n " cleaning..."
cd ..
rm -r -f ./curl
echo " done!"

#pthreads
echo -n "[PHP pthreads] downloading $PTHREADS_VERSION..."
wget https://github.com/krakjoe/pthreads/archive/$PTHREADS_VERSION.tar.gz --no-check-certificate -q -O - | tar -zx >> $DIR/install.log 2>&1
mv pthreads-$PTHREADS_VERSION $DIR/install_data/php/ext/pthreads
echo " done!"

echo -n "[PHP5] checking..."
MAX_MEMORY=$(free -m | awk '/^Mem:/{print $2}')
if [ $MAX_MEMORY -gt 2048 ]
then
  echo -n " enabling optimizations..."
  OPTIMIZATION="--enable-inline-optimization "
else
  OPTIMIZATION=""
fi
cd php
./buildconf --force >> $DIR/install.log 2>&1
./configure $OPTIMIZATION--prefix=$DIR/php5 \
--exec-prefix=$DIR/php5 \
--enable-embedded-mysqli \
--enable-bcmath \
--with-gmp=$DIR/install_data/php/ext/gmp \
--with-curl=$DIR/install_data/php/ext/curl \
--with-zlib=$DIR/install_data/php/ext/zlib \
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
--enable-cli >> $DIR/install.log 2>&1
echo -n " compiling..."
make >> $DIR/install.log 2>&1
echo -n " installing..."
make install >> $DIR/install.log 2>&1
echo " done!"
cd $DIR
echo -n "[INFO] Cleaning up..."
rm -r -f install_data/ >> $DIR/install.log 2>&1
date >> $DIR/install.log 2>&1
echo " done!"
echo "[PocketMine] You should start the server now using \"./start.sh\""
echo "[PocketMine] If it doesn't works, please send the \"install.log\" file to the Bug Tracker"