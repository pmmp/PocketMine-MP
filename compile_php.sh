#!/bin/bash
echo "[INFO] PocketMine-MP PHP compiler for Linux - by @shoghicp v0.2"
if [ "$(whoami)" != 'root' ]; then
echo "[ERROR] You must be root to run this script"
exit 1;
fi
DIR=`pwd`
date > $DIR/install.log
mkdir -m 0777 install_data >> $DIR/install.log 2>&1
mkdir -m 0777 php5 >> $DIR/install.log 2>&1
cd install_data
apt-get -f -y install >> $DIR/install.log 2>&1
apt-get -y install \
build-essential \
autoconf >> $DIR/install.log 2>&1

#zlib
echo -n "[zlib] Downloading..."
wget http://zlib.net/zlib-1.2.7.tar.gz -q -O zlib-1.2.7.tar.gz
echo -n " extracting..."
tar -zxvf zlib-1.2.7.tar.gz >> $DIR/install.log 2>&1
mv zlib-1.2.7 zlib
echo -n " compiling..."
cd zlib
./configure >> $DIR/install.log 2>&1
make >> $DIR/install.log 2>&1
make install >> $DIR/install.log 2>&1
echo " done!"
cd ..

#OpenSSL
echo -n "[OpenSSL] downloading..."
wget ftp://ftp.openssl.org/source/openssl-1.0.1c.tar.gz -q -O openssl-1.0.1c.tar.gz
echo -n " extracting..."
tar -zxvf openssl-1.0.1c.tar.gz >> $DIR/install.log 2>&1
mv openssl-1.0.1c openssl
echo -n " compiling..."
cd openssl
./config >> $DIR/install.log 2>&1
make >> $DIR/install.log 2>&1
make install >> $DIR/install.log 2>&1
echo " done!"
cd ..

#GMP
echo -n "[GMP] downloading..."
wget ftp://ftp.gmplib.org/pub/gmp-5.1.0/gmp-5.1.0.tar.bz2 -q -O gmp-5.1.0.tar.bz2
echo -n " extracting..."
tar -jxvf gmp-5.1.0.tar.bz2 >> $DIR/install.log 2>&1
mv gmp-5.1.0 gmp
echo -n " compiling..."
cd gmp
./configure >> $DIR/install.log 2>&1
make >> $DIR/install.log 2>&1
make install >> $DIR/install.log 2>&1
echo " done!"
cd ..

echo -n "[cURL] downloading..."
wget https://github.com/bagder/curl/archive/master.tar.gz -q -O curl-master.tar.gz 
echo -n " extracting..."
tar -zxvf curl-master.tar.gz >> $DIR/install.log 2>&1
mv curl-master curl
echo -n " compiling..."
cd curl
./buildconf >> $DIR/install.log 2>&1
./configure >> $DIR/install.log 2>&1
make >> $DIR/install.log 2>&1
make install >> $DIR/install.log 2>&1
echo " done!"
cd ..

#PHP 5
echo -n "[PHP5] downloading..."
wget http://php.net/get/php-5.4.10.tar.gz/from/this/mirror -q -O php-5.4.10.tar.gz
echo -n " extracting..."
tar -zxvf php-5.4.10.tar.gz >> $DIR/install.log 2>&1
mv php-5.4.10 php
echo " done!"
cd php/ext
echo "[PHP pthreads] downloading..."
wget https://github.com/krakjoe/pthreads/archive/master.tar.gz -q -O pthreads-master.tar.gz 
echo -n " extracting..."
tar -zxvf pthreads-master.tar.gz >> $DIR/install.log 2>&1
mv pthreads-master pthreads
echo " done!"
cd ../
echo -n "[PHP5] compiling..."
./buildconf --force
./configure --prefix=$DIR/php5 \
--exec-prefix=$DIR/php5 \
--enable-embedded-mysqli \
--with-openssl \
--enable-bcmath \
--with-gmp \
--with-curl \
--enable-zip \
--with-zlib \
--enable-sockets \
--enable-pthreads \
--enable-maintainer-zts \
--enable-cli
make
make install
echo " done!"
cd $DIR
echo -n "[INFO] Cleaning up..."
rm -r -f install_data/
rmdir install_data/
echo " done!"
echo "[PocketMine] You should start the server now using \"./start.sh\""