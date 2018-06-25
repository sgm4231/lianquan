@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../medz/gb-t-2260/scripts/raw-build.php
php "%BIN_TARGET%" %*
