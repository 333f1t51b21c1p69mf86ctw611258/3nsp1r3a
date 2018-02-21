#! /bin/bash
. ./env.sh

python3 -m unittest pylib_test/TestRedisManager.py
#python3 -m unittest pylib_test/TestExcelImport.py

