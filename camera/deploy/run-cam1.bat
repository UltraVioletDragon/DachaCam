@echo off
cls
echo Protecting from crashes...
echo If you want to close this script, close the srcds window and type Y depending on your language followed by Enter.
title WebCam Log
:cam
echo (%time%) started.
start /wait java.exe -Djava.ext.dirs=C:\java\DachaCam\lib -jar Run1.jar cam1
echo (%time%) WARNING: closed or crashed, restarting.
ping 127.0.0.1 -n 121 > nul
goto cam
