#!/bin/bash
echo "------------------------------------------------------------"
echo "|                     PocketMine Updater                   |"
echo "|By sekjun9878 and PocketMine-MP Development Team          |"
echo "|By using this program you agree to the zlib/libpng license|"
echo "------------------------------------------------------------"

#Initialise
update_channel=$(awk -F'=' '/update-channel/ {print $2}' server.properties)
current_update=$(awk -F'=' '/last-update/ {print $2}' server.properties)

echo "Update Information"
echo "Update Channel: Development"
echo "Current Update: "$current_update
echo ""
echo "WARNING WARNING WARNING"
echo "THIS PROGRAM IS CURRENTLY IN TESTING"
echo "THIS PROGRAM MIGHT DELETE YOUR MAP / SETTINGS / PROPERTIES DATA"
echo "BY PROCEEDING YOU UNDERSTAND THE RISKS OF RUNNING THIS PROGRAM"

read -p "Do you wish to proceed(y/n)?"
[ "$(echo $REPLY | tr [:upper:] [:lower:])" == "y" ] || exit

#Delete the src directory
echo -n "Deleting the src directory..."
rm -r src
echo "done"
#Get the stable Release
echo -n "Getting the latest development release..."
wget -q https://github.com/shoghicp/PocketMine-MP/archive/master.zip -O UpdateData.zip
echo "done"
echo -n "Unzipping data..."
unzip -q UpdateData.zip
echo "done"
echo -n "Overwriting PocketMine-MP..."
cp -fr ./PocketMine-MP-master/* ./
echo "done"
echo -n "Removing temporary files..."
rm -r PocketMine-MP-master
rm UpdateData.zip
echo "done"
echo "Finished Updating PocketMine-MP"