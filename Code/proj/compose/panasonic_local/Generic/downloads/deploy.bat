set TMPUNZIPPATH=%TEMP%
set ZIPFILEPATH=%CD%
set DEPLOYPATH=%CD%\..\app\Vendor\excel

rmdir /S /Q %DEPLOYPATH%
CScript unzip.vbs %TMPUNZIPPATH% %ZIPFILEPATH%\PHPExcel_1.7.8-with_documentation-msoffice_format.zip
move %TMPUNZIPPATH%\Classes %DEPLOYPATH%
copy gitignore %DEPLOYPATH%\.gitignore

cp usertable.csv ../app/Vendor/user
cd ../app/Vendor/scripts
cmd /c createTables.bat
