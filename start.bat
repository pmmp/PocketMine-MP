@echo off
TITLE Pocket-Minecraft-PHP Server - by @shoghicp
echo.
echo             -
echo           /   \
echo        /         \
echo     /    POCKET     \
echo  /    MINECRAFT PHP    \
echo  ^|\     @shoghicp     /^|
echo  ^|.   \           /   .^|
echo  ^| ..     \   /     .. ^|
echo  ^|    ..    ^|    ..    ^|
echo  ^|       .. ^| ..       ^|
echo  \          ^|          /
echo     \       ^|       /
echo        \    ^|    /
echo           \ ^| /
echo.
echo.
cd /d %~dp0
FOR /F "tokens=*" %%i in ('php -r "echo 1;"') do SET PHPOUTPUT=%%i
if not "%PHPOUTPUT%"=="1" (
echo [ERROR] Couldn't find PHP binary in PATH.
ping 127.0.0.1 -n 3 -w 1000>nul
) else (
START /B CMD /C CALL php server.php
START /B /WAIT php input.php 1
)