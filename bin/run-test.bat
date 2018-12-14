@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../src/run-test.php
echo %*
php "%BIN_TARGET%" %*