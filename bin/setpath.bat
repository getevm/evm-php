@echo off

SetLocal EnableDelayedExpansion
set KeyName=Path
set OldInstallDir=%1
set OldInstallDir=%OldInstallDir:"=%
set NewInstallDir=%2
set OldPath=%Path%
set NewPath=!OldPath:%OldInstallDir%=!

echo %OldInstallDir%
echo %NewInstallDir%
echo "%NewPath%"
::setx /m "%KeyName%" "%NewPath%";
pause
EndLocal