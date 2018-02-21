#! /bin/bash
. ./env.sh

# usage addValidation [Appname]
#  Appname    : e.g. 'App3'

if [ "$#" -ne 1 ]; then
    echo "Please specify appname"
    exit 1
fi
PLUGINNAME=$1

FIELDS_TO_VALIDATE=`redis-cli -h $REDISHOST keys "$PLUGINNAME"_validate_cssclass*|sed 's/'$PLUGINNAME'_validate_cssclass_val//g'`
#echo 'pluginname=' $PLUGINNAME
if [[ -z "$FIELDS_TO_VALIDATE" ]]; then
    echo "No validation is found for $PLUGINNAME, exiting."
    exit 0
fi

sedFile=/tmp/_appDeploy.sed
cd ../../Plugin/$PLUGINNAME/webroot/js/
JSFile="BriodeValidation_generated.js"
echo '$(document).ready(function(){' > $JSFile
for FIELD in $FIELDS_TO_VALIDATE
do
    rm $sedFile
    echo "s/__VAL_RANGE_CLASS__/$FIELD/g" >> $sedFile
    VAL_KEY=$PLUGINNAME'_validate_cssclass_val'"$FIELD"
    VAL_KIND=`redis-cli -h $REDISHOST hget $VAL_KEY kind`
    if [ "$VAL_KIND" == "higher_or_equal" ]; then
        VAL_VALUE=`redis-cli -h $REDISHOST hget $VAL_KEY value`
        echo "s/__VAL_RANGE_LOWERTHAN__/null/g" >> $sedFile
        echo "s/__VAL_RANGE_COND_LOWERTHAN__/false/g" >> $sedFile
        echo "s/__VAL_RANGE_HIGHERTHAN__/$VAL_VALUE/g" >> $sedFile
        echo "s/__VAL_RANGE_COND_HIGHERTHAN__/true/g" >> $sedFile
    fi
    if [ "$VAL_KIND" == "higher" ]; then
        VAL_VALUE=`redis-cli -h $REDISHOST hget $VAL_KEY value`
        echo "s/__VAL_RANGE_LOWERTHAN__/null/g" >> $sedFile
        echo "s/__VAL_RANGE_COND_LOWERTHAN__/false/g" >> $sedFile
        echo "s/__VAL_RANGE_HIGHERTHAN__/$VAL_VALUE/g" >> $sedFile
        echo "s/__VAL_RANGE_COND_HIGHERTHAN__/false/g" >> $sedFile
    fi
    if [ "$VAL_KIND" == "lower_or_equal" ]; then
        VAL_VALUE=`redis-cli -h $REDISHOST hget $VAL_KEY value`
        echo "s/__VAL_RANGE_LOWERTHAN__/$VAL_VALUE/g" >> $sedFile
        echo "s/__VAL_RANGE_COND_LOWERTHAN__/true/g" >> $sedFile
        echo "s/__VAL_RANGE_HIGHERTHAN__/null/g" >> $sedFile
        echo "s/__VAL_RANGE_COND_HIGHERTHAN__/false/g" >> $sedFile
    fi
    if [ "$VAL_KIND" == "lower" ]; then
        VAL_VALUE=`redis-cli -h $REDISHOST hget $VAL_KEY value`
        echo "s/__VAL_RANGE_LOWERTHAN__/$VAL_VALUE/g" >> $sedFile
        echo "s/__VAL_RANGE_COND_LOWERTHAN__/false/g" >> $sedFile
        echo "s/__VAL_RANGE_HIGHERTHAN__/null/g" >> $sedFile
        echo "s/__VAL_RANGE_COND_HIGHERTHAN__/false/g" >> $sedFile
    fi
    if [ "$VAL_KIND" == "between" ]; then
        VAL_HIGHERTHAN_VALUE=`redis-cli -h $REDISHOST hget $VAL_KEY high_value`
        VAL_HIGHERTHAN_KIND=`redis-cli -h $REDISHOST hget $VAL_KEY high_kind`
        VAL_LOWERTHAN_VALUE=`redis-cli -h $REDISHOST hget $VAL_KEY low_value`
        VAL_LOWERTHAN_KIND=`redis-cli -h $REDISHOST hget $VAL_KEY low_kind`
        if [ "$VAL_HIGHERTHAN_KIND" == "lower_or_equal" ]; then
            echo "s/__VAL_RANGE_COND_LOWERTHAN__/true/g" >> $sedFile
        else    
            echo "s/__VAL_RANGE_COND_LOWERTHAN__/false/g" >> $sedFile
        fi
        if [ "$VAL_LOWERTHAN_KIND" == "higher_or_equal" ]; then
            echo "s/__VAL_RANGE_COND_HIGHERTHAN__/true/g" >> $sedFile
        else
            echo "s/__VAL_RANGE_COND_HIGHERTHAN__/false/g" >> $sedFile
        fi
        echo "s/__VAL_RANGE_HIGHERTHAN__/$VAL_LOWERTHAN_VALUE/g" >> $sedFile
        echo "s/__VAL_RANGE_LOWERTHAN__/$VAL_HIGHERTHAN_VALUE/g" >> $sedFile
    fi
    cat _val_range.js.template | sed -f $sedFile >> $JSFile
done
echo '});' >> $JSFile
