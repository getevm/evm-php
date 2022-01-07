@echo off

SetLocal EnableDelayedExpansion
set KeyName=Path
set OldInstallDir=%1
set OldInstallDir=%OldInstallDir:"=%
set NewInstallDir=%2
set NewInstallDir=%NewInstallDir:"=%
set OldPath=%Path%
set NewPath=!OldPath:%OldInstallDir%=!
set NewPath=%NewPath:"=%

echo %NewPath%%NewInstallDir%

:: setx /m "%KeyName%" "";

EndLocal
