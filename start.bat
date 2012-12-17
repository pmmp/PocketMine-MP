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
FOR /F "tokens=*" %%i in ('php -r "echo 1;"') do SET PHPOUTPUT=%%i
if not "%PHPOUTPUT%"=="1" (
echo [ERROR] Couldn't find PHP in PATH.
pause
exit
)
START /B CMD /C CALL php server.php
START /B /WAIT php input.php 1
pause
exit