@echo off

set KeyName=DERP
set OldPath=%1
set NewPath=%2

if "%OldPath%" == "" if "%NewPath%" == "" ( echo Path value not set. ) else (
    echo "%KeyName%"
    echo "%OldPath%"
    echo "%NewPath%"

    setx /m "%KeyName%" "%NewName%"
)

