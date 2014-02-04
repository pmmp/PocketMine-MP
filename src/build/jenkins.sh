#!/bin/bash -x
export PATH=/opt/arm-2013.05/bin:/opt/tools/arm-bcm2708/gcc-linaro-arm-linux-gnueabihf-raspbian/bin:/opt/arm-unknown-linux-uclibcgnueabi/bin:$PATH
export THREADS=2

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
    
    CFLAGS=-m32 march=i686 mtune=generic $SCRIPT
    
    cp -r $COMPILEDIR/linux/32bit/{install.log,bin/*,install_data/*} $ARCHIVE/linux/32bit/
	if [ ! -f $COMPILEDIR/linux/32bit/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$COMPILE_LINUX_64BIT" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/linux/64bit
    cd $COMPILEDIR/linux/64bit
    
    $SCRIPT
    
    cp -r $COMPILEDIR/linux/64bit/{install.log,bin/*,install_data/*} $ARCHIVE/linux/64bit/
	if [ ! -f $COMPILEDIR/linux/64bit/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$COMPILE_MAC" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/mac
    cd $COMPILEDIR/mac
    
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
    
    cp -r $COMPILEDIR/mac/{install.log,bin/*,install_data/*} $ARCHIVE/mac/
	if [ ! -f $COMPILEDIR/mac/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$COMPILE_RPI" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/rpi
    cd $COMPILEDIR/rpi
    
    $SCRIPT rpi
    
    cp -r $COMPILEDIR/rpi/{install.log,bin/*,install_data/*} $ARCHIVE/rpi/
	if [ ! -f $COMPILEDIR/rpi/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$CROSSCOMPILE_ANDROID_ARMV6" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/android-armv6
    cd $COMPILEDIR/crosscompile/android-armv6
    
    $SCRIPT crosscompile android-armv6
    
    cp -r $COMPILEDIR/crosscompile/android-armv6/{install.log,bin/*,install_data/*} $ARCHIVE/crosscompile/android-armv6/
	if [ ! -f $COMPILEDIR/crosscompile/android-armv6/bin/php5/bin/php ]; then
		exit 1
	fi
fi

if [ "$CROSSCOMPILE_ANDROID_ARMV7" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/android-armv7
    cd $COMPILEDIR/crosscompile/android-armv7
    
    $SCRIPT crosscompile android-armv7
    
    cp -r $COMPILEDIR/crosscompile/android-armv7/{install.log,bin/*,install_data/*} $ARCHIVE/crosscompile/android-armv7/
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
    
    cp -r $COMPILEDIR/crosscompile/rpi/{install.log,bin/*,install_data/*} $ARCHIVE/crosscompile/rpi/
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
