#! /bin/sh

# usage 
# execute cae bake command
# ./bake.sh $tableName $pluginName

. ../Vendor/scripts/env.sh

CAKEPATH=../Console/cake
PLUGINNAME=$1
TABLENAME=$2
PLUGINPATH=../Plugin/$PLUGINNAME
export PATH=$XAMPPBINPATH:$PATH

echo 'execute cake bake command'
#chown -R daemon $PLUGINPATH/Model
#chown -R daemon $PLUGINPATH/Test/Fixture
#chown -R daemon $PLUGINPATH/Test/Case/Model
"$CAKEPATH" bake model "$TABLENAME" --plugin "$PLUGINNAME"
