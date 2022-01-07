@echo off

set KeyName=DERP
set OldPath=%1
set NewPath=%2

echo "%KeyName%"
echo "%OldPath%"
echo "%NewPath%"

setx /m "%KeyName%" "%NewPath%"
pause

