#!/bin/bash -ex
export PATH=/opt/arm-2013.05/bin:/opt/tools/arm-bcm2708/gcc-linaro-arm-linux-gnueabihf-raspbian/bin:$PATH
export THREADS=2
rm -rf $WORKSPACE/compile.sh
curl --insecure --location https://github.com/PocketMine/PocketMine-MP/raw/master/src/build/compile.sh > $WORKSPACE/compile.sh
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
    
    cp $COMPILEDIR/linux/32bit/{install.log,bin/php5/bin/php} $ARCHIVE/linux/32bit/
fi

if [ "$COMPILE_LINUX_64BIT" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/linux/64bit
    cd $COMPILEDIR/linux/64bit
    
    $SCRIPT
    
    cp $COMPILEDIR/linux/64bit/{install.log,bin/php5/bin/php} $ARCHIVE/linux/64bit/
fi

if [ "$COMPILE_MAC" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/mac
    cd $COMPILEDIR/mac
    
    $SCRIPT mac
    
    cp $COMPILEDIR/mac/{install.log,bin/php5/bin/php} $ARCHIVE/mac/
fi

if [ "$COMPILE_RPI" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/rpi
    cd $COMPILEDIR/rpi
    
    $SCRIPT rpi
    
    cp $COMPILEDIR/rpi/{install.log,bin/php5/bin/php} $ARCHIVE/rpi/
fi

if [ "$CROSSCOMPILE_ANDROID_ARMV6" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/android-armv6
    cd $COMPILEDIR/crosscompile/android-armv6
    
    $SCRIPT crosscompile android-armv6
    
    cp $COMPILEDIR/crosscompile/android-armv6/{install.log,bin/php5/bin/php} $ARCHIVE/crosscompile/android-armv6/
fi

if [ "$CROSSCOMPILE_ANDROID_ARMV7" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/android-armv7
    cd $COMPILEDIR/crosscompile/android-armv7
    
    $SCRIPT crosscompile android-armv7
    
    cp $COMPILEDIR/crosscompile/android-armv7/{install.log,bin/php5/bin/php} $ARCHIVE/crosscompile/android-armv7/
fi

if [ "$CROSSCOMPILE_RPI" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/rpi
    cd $COMPILEDIR/crosscompile/rpi
    
    $SCRIPT crosscompile rpi
    
    cp $COMPILEDIR/crosscompile/rpi/{install.log,bin/php5/bin/php} $ARCHIVE/crosscompile/rpi/
fi

if [ "$CROSSCOMPILE_MAC" = "true" ];
then
    mkdir -p {$COMPILEDIR,$ARCHIVE}/crosscompile/mac
    cd $COMPILEDIR/crosscompile/mac
    
    $SCRIPT crosscompile mac
    
    cp $COMPILEDIR/crosscompile/mac/{install.log,bin/php5/bin/php} $ARCHIVE/crosscompile/mac/
fi
