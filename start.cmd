@echo off
TITLE PocketMine-MP Server - by @shoghicp
COLOR 0F
mode con: cols=90
cd /d %~dp0
FOR /F "tokens=*" %%i in ('php -r "echo 1;"') do SET PHPOUTPUT=%%i
if not "%PHPOUTPUT%"=="1" (
echo [ERROR] Couldn't find PHP binary in PATH.
ping 127.0.0.1 -n 3 -w 1000>nul
) else (
	if exist php.cmd (
		if exist bin\ansicon.exe (
			bin\ansicon.exe php.cmd -d enable_dl=On PocketMine-MP.php --enable-ansi %*
		) else (
			php.cmd -d enable_dl=On PocketMine-MP.php %*
		)
	) else (
		if exist bin\ansicon.exe (
			bin\ansicon.exe php -d enable_dl=On PocketMine-MP.php --enable-ansi %*
		) else (
			php -d enable_dl=On PocketMine-MP.php %*
		)
	)	
)
pause
