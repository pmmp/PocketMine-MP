#!/bin/bash
echo "[INFO] PocketMine-MP installer for Linux [APT VERSION] - by @shoghicp v0.1"
if [ "$(whoami)" != 'root' ]; then
        echo "[ERROR] You must be root to run this script"
#       exit 1;
fi
echo "[0/4] Updating APT and fixing APT"
apt-get -y check
apt-get -y update
echo "[1/4] Installing PHP5"
./compile_php.sh
echo "[3/4] Fetching PocketMine-MP..."
wget https://github.com/shoghicp/PocketMine-MP/archive/master.zip -O latest.zip
echo "[4/4] Extracting..."
unzip -o -q latest.zip
mv -f PocketMine-MP-master/* ./
rm -f -r PocketMine-MP-master/
rm -f latest.zip
chmod 0755 ./start.sh
echo "[INFO] Done"