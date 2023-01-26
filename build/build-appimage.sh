#!/bin/sh

set -eu

if false; then ####

[ ! -d PocketMine.AppDir ] || rm -r PocketMine.AppDir
mkdir PocketMine.AppDir

(cd PocketMine.AppDir && ../php/compile.sh \
	-t linux64 \
	-j $(nproc) \
	-f \
	-g \
	-P ${PM_VERSION_MAJOR:-4})

fi ####

PHP=$(realpath ./PocketMine.AppDir/bin/php7/bin/php)

[ -f composer.phar ] || (wget -O - https://getcomposer.org/installer | ${PHP})
(cd .. && $PHP build/composer.phar install --no-dev)

$PHP -dphar.readonly=0 ./server-phar.php && mv PocketMine-MP.phar PocketMine.AppDir

cat >./PocketMine.AppDir/AppRun <<EOF
#!/bin/sh
\$(dirname \$0)/bin/php7/bin/php \$(dirname \$0)/PocketMine-MP.phar "\$@"
EOF
chmod +x ./PocketMine.AppDir/AppRun

cat >./PocketMine.AppDir/PocketMine.desktop <<EOF
[Desktop Entry]
Name=PocketMine-MP
Exec=pocketmine
Icon=pocketmine
Type=Application
Categories=Game;
EOF

wget -O ./PocketMine.AppDir/pocketmine.png "https://github.com/PocketMine.png?size=256"

[ -f appimagetool ] || (wget -O appimagetool \
	https://github.com/AppImage/AppImageKit/releases/download/13/appimagetool-x86_64.AppImage \
	&& chmod +x appimagetool)
./appimagetool PocketMine.AppDir/
