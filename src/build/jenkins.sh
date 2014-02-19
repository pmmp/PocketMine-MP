#!/bin/bash -x
export PATH="/opt/arm-2013.05/bin:/opt/tools/arm-bcm2708/gcc-linaro-arm-linux-gnueabihf-raspbian/bin:/opt/arm-unknown-linux-uclibcgnueabi/bin:$PATH"
export THREADS=2
PHP_VERSION="5.5.9"

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

rm -rf $WORKSPACE/compile.sh
download_file "https://github.com/PocketMine/PocketMine-MP/raw/master/src/build/compile.sh" > $WORKSPACE/compile.sh
chmod +x $WORKSPACE/compile.sh
SCRIPT="$WORKSPACE/compile.sh"
ARCHIVE=$WORKSPACE/archive
COMPILEDIR=$WORKSPACE/compile
rm -rf $ARCHIVE $COMPILEDIR
mkdir -p $ARCHIVE
mkdir -p $COMPILEDIR

if [ "$COMPILE_LINUX_32BIT" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/linux/32bit
    cd $COMPILEDIR/linux/32bit
    
    OPENSSL_TARGET="linux-generic32" CFLAGS="-m32" march=i386 mtune=none $SCRIPT linux openssl
    
    tar -czf PHP_${PHP_VERSION}_x86_Linux.tar.gz bin/
    cp -r $COMPILEDIR/linux/32bit/{install.log,PHP_${PHP_VERSION}_x86_Linux.tar.gz,install_data/*} $ARCHIVE/linux/32bit/
	if [ ! -f $COMPILEDIR/linux/32bit/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$COMPILE_LINUX_64BIT" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/linux/64bit
    cd $COMPILEDIR/linux/64bit
    
    OPENSSL_TARGET="linux-x86_64" CFLAGS="-m64" march=x86-64 mtune=none $SCRIPT linux openssl
    
    tar -czf PHP_${PHP_VERSION}_x86-64_Linux.tar.gz bin/
    cp -r $COMPILEDIR/linux/64bit/{install.log,PHP_${PHP_VERSION}_x86-64_Linux.tar.gz,install_data/*} $ARCHIVE/linux/64bit/
	if [ ! -f $COMPILEDIR/linux/64bit/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$COMPILE_MAC_32" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/mac32
    cd $COMPILEDIR/mac32
    
	curl -L http://ftpmirror.gnu.org/libtool/libtool-2.4.2.tar.gz | tar -xz > /dev/null
	cd libtool-2.4.2
	./configure --prefix="$COMPILEDIR/mac/libtool" > /dev/null
	make > /dev/null
	make install
	cd ../
	rm -rf libtool-2.4.2
	export LIBTOOL="$COMPILEDIR/mac/libtool/bin/libtool"
	export LIBTOOLIZE="$COMPILEDIR/mac/libtool/bin/libtoolize"
    $SCRIPT mac curl
    
    tar -czf PHP_${PHP_VERSION}_x86_MacOS.tar.gz bin/
    cp -r $COMPILEDIR/mac32/{install.log,PHP_${PHP_VERSION}_x86_MacOS.tar.gz,install_data/*} $ARCHIVE/mac32/
	if [ ! -f $COMPILEDIR/mac32/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$COMPILE_MAC_64" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/mac64
    cd $COMPILEDIR/mac64
    
	curl -L http://ftpmirror.gnu.org/libtool/libtool-2.4.2.tar.gz | tar -xz > /dev/null
	cd libtool-2.4.2
	./configure --prefix="$COMPILEDIR/mac/libtool" > /dev/null
	make > /dev/null
	make install
	cd ../
	rm -rf libtool-2.4.2
	export LIBTOOL="$COMPILEDIR/mac/libtool/bin/libtool"
	export LIBTOOLIZE="$COMPILEDIR/mac/libtool/bin/libtoolize"
    $SCRIPT mac64 curl
    
    tar -czf PHP_${PHP_VERSION}_x86-64_MacOS.tar.gz bin/
    cp -r $COMPILEDIR/mac64/{install.log,PHP_${PHP_VERSION}_x86-64_MacOS.tar.gz,install_data/*} $ARCHIVE/mac64
	if [ ! -f $COMPILEDIR/mac64/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$COMPILE_RPI" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/rpi
    cd $COMPILEDIR/rpi
    
    $SCRIPT rpi
    
    tar -czf PHP_${PHP_VERSION}_ARM_Raspbian_hard.tar.gz bin/
    cp -r $COMPILEDIR/rpi/{install.log,PHP_${PHP_VERSION}_ARM_Raspbian_hard.tar.gz,install_data/*} $ARCHIVE/rpi/
	if [ ! -f $COMPILEDIR/rpi/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$CROSSCOMPILE_ANDROID_ARMV6" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/android-armv6
    cd $COMPILEDIR/crosscompile/android-armv6
    
    $SCRIPT crosscompile android-armv6
    
    tar -czf PHP_${PHP_VERSION}_ARMv6_Android.tar.gz bin/
    cp -r $COMPILEDIR/crosscompile/android-armv6/{install.log,PHP_${PHP_VERSION}_ARMv6_Android.tar.gz,install_data/*} $ARCHIVE/crosscompile/android-armv6/
	if [ ! -f $COMPILEDIR/crosscompile/android-armv6/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$CROSSCOMPILE_ANDROID_ARMV7" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/android-armv7
    cd $COMPILEDIR/crosscompile/android-armv7
    
    $SCRIPT crosscompile android-armv7
    
    tar -czf PHP_${PHP_VERSION}_ARMv7_Android.tar.gz bin/
    cp -r $COMPILEDIR/crosscompile/android-armv7/{install.log,PHP_${PHP_VERSION}_ARMv7_Android.tar.gz,install_data/*} $ARCHIVE/crosscompile/android-armv7/
	if [ ! -f $COMPILEDIR/crosscompile/android-armv7/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$CROSSCOMPILE_IOS_ARMV6" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/ios-armv6
    cd $COMPILEDIR/crosscompile/ios-armv6
	curl -L http://ftpmirror.gnu.org/libtool/libtool-2.4.2.tar.gz | tar -xz > /dev/null
	cd libtool-2.4.2
	./configure --prefix="$COMPILEDIR/crosscompile/ios-armv6/libtool" > /dev/null
	make > /dev/null
	make install
	cd ../
	rm -rf libtool-2.4.2
	export LIBTOOL="$COMPILEDIR/crosscompile/ios-armv6/libtool/bin/libtool"
	export LIBTOOLIZE="$COMPILEDIR/crosscompile/ios-armv6/libtool/bin/libtoolize"
    PATH="/Developer/Platforms/iPhoneOS.platform/Developer/usr/bin:$PATH" $SCRIPT crosscompile ios-armv6
    
    cp -r $COMPILEDIR/crosscompile/ios-armv6/{install.log,bin/*,install_data/*} $ARCHIVE/crosscompile/ios-armv6/
	if [ ! -f $COMPILEDIR/crosscompile/ios-armv6/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$CROSSCOMPILE_IOS_ARMV7" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/ios-armv7
    cd $COMPILEDIR/crosscompile/ios-armv7
	curl -L http://ftpmirror.gnu.org/libtool/libtool-2.4.2.tar.gz | tar -xz > /dev/null
	cd libtool-2.4.2
	./configure --prefix="$COMPILEDIR/crosscompile/ios-armv7/libtool" > /dev/null
	make > /dev/null
	make install
	cd ../
	rm -rf libtool-2.4.2
	export LIBTOOL="$COMPILEDIR/crosscompile/ios-armv7/libtool/bin/libtool"
	export LIBTOOLIZE="$COMPILEDIR/crosscompile/ios-armv7/libtool/bin/libtoolize"
    PATH="/Developer/Platforms/iPhoneOS.platform/Developer/usr/bin:$PATH" $SCRIPT crosscompile ios-armv7
    
    cp -r $COMPILEDIR/crosscompile/ios-armv7/{install.log,bin/*,install_data/*} $ARCHIVE/crosscompile/ios-armv7/
	if [ ! -f $COMPILEDIR/crosscompile/ios-armv7/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$CROSSCOMPILE_RPI" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/rpi
    cd $COMPILEDIR/crosscompile/rpi
    
    $SCRIPT crosscompile rpi
    
    tar -czf PHP_${PHP_VERSION}_ARM_Raspbian_hard.tar.gz bin/
    cp -r $COMPILEDIR/crosscompile/rpi/{install.log,PHP_${PHP_VERSION}_ARM_Raspbian_hard.tar.gz,install_data/*} $ARCHIVE/crosscompile/rpi/
	if [ ! -f $COMPILEDIR/crosscompile/rpi/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$CROSSCOMPILE_MAC" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/mac
    cd $COMPILEDIR/crosscompile/mac
    
    $SCRIPT crosscompile mac curl
    
    cp -r $COMPILEDIR/crosscompile/mac/{install.log,bin/*,install_data/*} $ARCHIVE/crosscompile/mac/
	if [ ! -f $COMPILEDIR/crosscompile/mac/bin/php5/bin/php ]; then
		exit 1
	fi
fi
