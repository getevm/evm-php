@echo off
set KeyName=Path
set KeyValue=%1
setx -m %KeyName% %KeyValue%
