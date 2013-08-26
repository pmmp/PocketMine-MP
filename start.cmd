@echo off
TITLE PocketMine-MP server software for Minecraft: Pocket Edition
COLOR 0F
mode con: cols=110
cd /d %~dp0
FOR /F "tokens=*" %%i in ('php -r "echo 1;"') do SET PHPOUTPUT=%%i
if not "%PHPOUTPUT%"=="1" (
echo [ERROR] Couldn't find PHP binary in PATH.
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
