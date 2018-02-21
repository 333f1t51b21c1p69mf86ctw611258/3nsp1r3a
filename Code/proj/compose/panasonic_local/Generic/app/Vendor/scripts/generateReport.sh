#! /bin/sh
. ./env.sh

if [ $# -ne 1 ]; then
    echo "Usage: $0 <reportname>"
    exit 1
fi

if [ ! -f $REPORTROOT/$1.html ]; then
$BIRT_HOME/ReportEngine/genReport.sh -f html -o $REPORTROOT/$1.html $BIRTWORKROOT/$1.rptdesign
fi

