#! /bin/bash
. ./env.sh

if [ $# -ne 1 ]; then
    echo "Usage: $0 <plugin_name>"
    exit 1
fi

PLUGIN_NAME=$1

$PYTHONEXEPATH/python3 import_excel_data.py $PLUGIN_NAME

