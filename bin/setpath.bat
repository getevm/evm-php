 @echo off
 set KeyName=Path
 set KeyValue="D:\songs;%PATH%"
 setx -m %KeyName% %KeyValue%