@Echo off
echo Start.BAT running %date% %time% >> c:\errors\log.log
SET ABS_PATH=%~dp0
echo %ABS_PATH%
for /f %%j in ("java.exe") do (
       set JAVA_HOME=%%~dp$PATH:j
        )
@echo JAVA_HOME = %JAVA_HOME%
set PATH=%PATH%;%ABS_PATH%Sikuli\libs;C:\Program Files\Amazon Corretto\jdk11\bin
@echo PATH = %PATH%
%ABS_PATH%"\Sikuli\Sikuli-IDE.bat" -r C:\inetpub\wwwroot\sikuli\AdobeRec.skl

PAUSE






