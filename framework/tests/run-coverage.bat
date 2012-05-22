@echo off

Z:
set PHPDIR=Z:\usr\local\php5

set TESTING=true
set PHP_BIN=%PHPDIR%\php.exe
set PHP_UNIT=%PHPDIR%\phpunit
%PHP_BIN% %PHP_UNIT% --testdox --coverage-html ./coverage ./AllTests.php