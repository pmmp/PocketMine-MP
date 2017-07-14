@echo off

set DO_LOOP="yes"

title FrontierEdge Startup
cd /d %~dp0
tasklist /FI "IMAGENAME eq mintty.exe" 2>NUL | find /I /N "mintty.exe">NUL
if %ERRORLEVEL% equ 0 (
    goto :loop
) else (
    goto :start
)

:loop
tasklist /FI "IMAGENAME eq mintty.exe" 2>NUL | find /I /N "mintty.exe">NUL
if %ERRORLEVEL% equ 0 (
    goto :loop
) else (
	goto :start
)

:start
if exist bin\php\php.exe (
    set PHP_BINARY=bin\php\php.exe
) else (
    set PHP_BINARY=php
)
if exist PocketMine-MP.phar (
    set POCKETMINE_FILE=FrontierEdge.phar
) else (
    )if exist src\pocketmine\PocketMine.php (
            set POCKETMINE_FILE=src\pocketmine\PocketMine.php
    ) else (
            echo "Couldn't find a valid FrontierEdge installation"
            pause
            exit 1
    
)

if exist bin\mintty.exe (
	start "" bin\mintty.exe -o Columns=88 -o Rows=32 -o AllowBlinking=0 -o FontQuality=3 -o Font="Consolas" -o FontHeight=10 -o CursorType=0 -o CursorBlinks=1 -h error -t "FrontierEdge" -i bin/pocketmine.ico -w max %PHP_BINARY% %POCKETMINE_FILE% --enable-ansi %*
) else (
	%PHP_BINARY% -c bin\php %POCKETMINE_FILE% %*
)

if %DO_LOOP% == "yes" (
	goto :loop
)