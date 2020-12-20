VERSION=7.4.13
sudo apt update && sudo apt install -y \
	re2c \
	libtool \
	libtool-bin \
	zlib1g-dev \
	libcurl4-openssl-dev \
	libxml2-dev \
	libyaml-dev \
	libgmp-dev \
	libzip-dev \
	libssl-dev

curl -sSL https://www.php.net/distributions/php-$VERSION.tar.gz | tar -xz

INSTALL_DIR="$(pwd)/bin/php7"
cd php-$VERSION
cd ext/
curl -sSL https://github.com/pmmp/pthreads/archive/2bcd8b8c10395d58b8a9bc013e3a5328080c867f.tar.gz | tar -xz
curl -sSL https://github.com/php/pecl-file_formats-yaml/archive/2.2.0.tar.gz | tar -xz
cd ..

CFLAGS="$CFLAGS -march=x86-64"
CXXFLAGS="$CXXFLAGS -march=x86-64"

./buildconf --force
./configure \
	--prefix="$INSTALL_DIR" \
	--exec-prefix="$INSTALL_DIR" \
	--enable-maintainer-zts \
	--enable-cli \
	--disable-cgi \
	--disable-phpdbg \
	--disable-mbregex \
	--disable-pdo \
	--disable-session \
	--enable-mbstring \
	--enable-pthreads \
	--enable-simplexml \
	--enable-sockets \
	--enable-xml \
	--enable-xmlreader \
	--enable-xmlwriter \
	--with-curl \
	--with-gmp \
	--with-libxml \
	--with-openssl \
	--with-yaml \
	--with-zip \
	--with-zlib \
	--without-pear \
	--without-sqlite3

make -j8 install
