@echo off

set KeyName=Path
set KeyValue=%1

if "%KeyValue%" == "" ( echo Path value not set. ) else (
    echo "blahblah" >> txt.txt
)

