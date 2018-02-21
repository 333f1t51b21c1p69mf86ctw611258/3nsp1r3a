#! /bin/sh
. ./env.sh

if [ $# -ne 2 ]; then
    echo "Usage: $0 <reportname> <state>"
    exit 1
fi

export BIRT_HOME=/home/enspirea/BI/birt-runtime-4_3_1
if [ ! -f $REPORTROOT/$1_$2.html ]; then
#/home/enspirea/BI/birt-runtime-4_3_1/ReportEngine/genReport.sh -f html -o $REPORTROOT/$1_$2.html -p RP_State=$2 $BIRTWORKROOT/$1.rptdesign
$BIRT_HOME/ReportEngine/genReport.sh -f html -o $REPORTROOT/$1_$2.html $BIRTWORKROOT/$1.rptdesign
fi




