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

function build_leveldb {
	local LEVELDB_VERSION="$1"
	echo "Building LevelDB"
        rm -rf "./leveldb-mcpe" || true
	rm -rf "./leveldb-mcpe-build" || true
        mkdir "./leveldb-mcpe"
        curl -fsSL "https://github.com/pmmp/leveldb/archive/$LEVELDB_VERSION.tar.gz" | tar -zx
        mv "./leveldb-$LEVELDB_VERSION" leveldb-mcpe-build
        cd leveldb-mcpe-build
        CFLAGS="-fPIC" CXXFLAGS="-fPIC" cmake . \
            -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR" \
            -DCMAKE_PREFIX_PATH="$INSTALL_DIR" \
            -DCMAKE_INSTALL_LIBDIR=lib \
            -DLEVELDB_BUILD_TESTS=OFF \
            -DLEVELDB_BUILD_BENCHMARKS=OFF \
            -DLEVELDB_SNAPPY=OFF \
            -DLEVELDB_ZSTD=OFF \
            -DLEVELDB_TCMALLOC=OFF \
            -DCMAKE_BUILD_TYPE=Release
        make -j4 install
        cd ..
}
build_leveldb 84348b9b826cc280cde659185695d2170b54824c

cd php-$VERSION
cd ext/

# 1: extension name
# 2: extension version
# 3: URL to get .tar.gz from
# 4: Name of extracted directory to move
function get_extension_tar_gz {
	curl -sSL "$3" | tar -zx
	mv "$4" "$1"
}

# 1: extension name
# 2: extension version
# 3: github user name
# 4: github repo name
# 5: version prefix (optional)
function get_github_extension {
	get_extension_tar_gz "$1" "$2" "https://github.com/$3/$4/archive/$5$2.tar.gz" "$4-$2"
}

get_github_extension pthreads 2bcd8b8c10395d58b8a9bc013e3a5328080c867f pmmp pthreads
get_github_extension yaml 2.2.0 php pecl-file_formats-yaml
get_github_extension leveldb 2e3f740b55af1eb6dfc648dd451bcb7d6151c26c pmmp php-leveldb
get_github_extension chunkutils2 5a4dcd6ed74e0db2ca9a54948d4f3a065e386db5 pmmp ext-chunkutils2
get_github_extension morton 0.1.2 pmmp ext-morton
get_github_extension igbinary 3.1.4 igbinary igbinary
get_github_extension ds 2ddef84d3e9391c37599cb716592184315e23921 php-ds ext-ds

rm -rf crypto
git clone --recursive https://github.com/bukka/php-crypto.git crypto
cd crypto
git checkout -qf 5f26ac91b0ba96742cc6284cd00f8db69c3788b2
git submodule update --init --recursive
cd ..


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
	--enable-chunkutils2 \
	--enable-ds \
	--enable-igbinary \
	--enable-mbstring \
	--enable-morton \
	--enable-pthreads \
	--enable-simplexml \
	--enable-sockets \
	--enable-xml \
	--enable-xmlreader \
	--enable-xmlwriter \
	--with-crypto \
	--with-curl \
	--with-gmp \
	--with-leveldb="$INSTALL_DIR" \
	--with-libxml \
	--with-openssl \
	--with-openssl \
	--with-yaml \
	--with-zip \
	--with-zlib \
	--without-pear \
	--without-sqlite3

make -j8 install
