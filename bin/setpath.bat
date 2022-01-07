@echo off

SetLocal EnableDelayedExpansion

set KeyName=Path
set OldInstallDir=%1
set NewInstallDir=%2
set OldPath=%Path%
set NewPath=!OldPath:%OldInstallDir%=!

setx /m "%KeyName%" "%NewPath%%NewInstallDir%";

pause
EndLocal