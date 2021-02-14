VERSION="$1"

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

INSTALL_DIR="$(pwd)/bin/php7"

export CFLAGS="$CFLAGS -march=x86-64"
export CXXFLAGS="$CXXFLAGS -march=x86-64"

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

rm -rf php-build
git clone https://github.com/php-build/php-build.git
cd php-build
./install-dependencies.sh
echo '"pthreads",,"https://github.com/pmmp/pthreads.git",,,"extension",' >> share/php-build/extension/definition
echo '"leveldb",,"https://github.com/pmmp/php-leveldb.git",,"--with-leveldb='$INSTALL_DIR'","extension",' >> share/php-build/extension/definition
echo '"chunkutils2",,"https://github.com/pmmp/ext-chunkutils2.git",,,"extension",' >> share/php-build/extension/definition
echo '"morton",,"https://github.com/pmmp/ext-morton.git",,,"extension",' >> share/php-build/extension/definition
PHP_BUILD_INSTALL_EXTENSION="\
pthreads=@2bcd8b8c10395d58b8a9bc013e3a5328080c867f \
yaml=2.2.0 \
leveldb=@2e3f740b55af1eb6dfc648dd451bcb7d6151c26c \
chunkutils2=@5a4dcd6ed74e0db2ca9a54948d4f3a065e386db5 \
morton=@0.1.2 \
igbinary=3.1.4 \
ds=1.3.0 \
" PHP_BUILD_ZTS_ENABLE=on PHP_BUILD_CONFIGURE_OPTS='--with-gmp' ./bin/php-build "$VERSION" "$INSTALL_DIR"

rm -rf crypto
git clone --recursive https://github.com/bukka/php-crypto.git crypto
cd crypto
git checkout -qf 5f26ac91b0ba96742cc6284cd00f8db69c3788b2
git submodule update --init --recursive
"$INSTALL_DIR/bin/phpize"
./configure --with-php-config="$INSTALL_DIR/bin/php-config"
make -j8 install
echo "extension=crypto.so" >> "$INSTALL_DIR/etc/conf.d/crypto.ini"
cd ..

rm "$INSTALL_DIR/etc/conf.d/xdebug.ini" || true
cp install-dependencies.sh "$INSTALL_DIR"
