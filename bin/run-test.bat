@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../src/run-test.php
php "%BIN_TARGET%" %*