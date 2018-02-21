#! /bin/sh

TmpDir=/tmp
DeployPath=../app/Vendor/excel

rm -rf $DeployPath
unzip PHPExcel_1.7.8-with_documentation-msoffice_format.zip Classes/*
mv ./Classes $DeployPath
cp ./gitignore $DeployPath/.gitignore

cp usertable.csv ../app/Vendor/user
cd ../app/Vendor/scripts
./setup.sh
