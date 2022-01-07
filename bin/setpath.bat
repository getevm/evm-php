@echo off

set KeyName=DERP
set KeyValue=%1

if "%KeyValue%" == "" ( echo Path value not set. ) else (
    echo "%KeyName%"
    echo "%KeyValue%"

    setx /m "%KeyName%" "%KeyValue%"
)

