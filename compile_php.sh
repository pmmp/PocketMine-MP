#!/bin/bash
echo "[INFO] PocketMine-MP PHP compiler for Linux - by @shoghicp v0.1"
if [ "$(whoami)" != 'root' ]; then
echo "[ERROR] You must be root to run this script"
exit 1;
fi
DIR=`pwd`
mkdir -m 0777 install_data
mkdir -m 0777 php5
cd install_data
apt-get -f -y install 
apt-get -y install \
php5-cli \
php5-common \
php5-curl \
php5-gd \
php5-gmp \
php5-mcrypt \
build-essential \
git-core \
libxml2-dev \
libcurl4-openssl-dev \
libjpeg-dev \
libpng-dev \
libmysqlclient-dev \
libfreetype6-dev \
libmcrypt-dev \
libmhash-dev
wget ftp://ftp.gmplib.org/pub/gmp-5.1.0/gmp-5.1.0.tar.bz2 -O gmp-5.1.0.tar.bz2
tar -jxvf gmp-5.1.0.tar.bz2
cd gmp-5.1.0
./configure
make
make check
make install
wget http://php.net/get/php-5.4.10.tar.gz/from/this/mirror -O php-5.4.10.tar.gz
tar -zxvf php-5.4.10.tar.gz
cd php-5.4.10
cd ext
git clone https://github.com/krakjoe/pthreads.git
cd ../
./buildconf --force
./configure --prefix=$DIR/php5 \
--exec-prefix=$DIR/php5 \
--enable-embedded-mysqli \
--with-openssl \
--with-mcrypt \
--with-mhash \
--enable-exif \
--with-freetype-dir \
--enable-calendar \
--enable-soap \
--enable-mbstring \
--enable-bcmath \
--enable-gd-native-ttf \
--with-gmp \
--with-curl \
--enable-zip \
--with-gd \
--with-jpeg-dir \
--with-png-dir \
--with-mysql \
--with-mcrypt \
--with-zlib \
--enable-pthreads \
--enable-maintainer-zts
make
echo "n" | make test
make install
cd $DIR
rm -r -f install_data/
rmdir install_data/
cd php5