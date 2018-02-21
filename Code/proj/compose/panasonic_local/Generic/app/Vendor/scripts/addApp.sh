#! /bin/bash
. ./env.sh

# usage addApp [Appname]
#  Appname    : e.g. 'App3'

if [ "$#" -ne 1 ]; then
    echo "Please specify appname"
    exit 1
fi
PLUGINNAME=$1

CONTROLLERBASE='App'
appMode=`redis-cli -h $REDISHOST hget $PLUGINNAME app_kind`
DBMODEL=`redis-cli -h $REDISHOST hget $PLUGINNAME model_name`
_DBMODEL=${DBMODEL,,}
DBMODELINFIXTURE=${_DBMODEL^}
DBTABLENAME=`redis-cli -h $REDISHOST hget $PLUGINNAME db_name`
WORKFLOWONLYBEGIN='\/*'
WORKFLOWONLYEND='*\/'
if [ $appMode == "workflow" ]; then
    CONTROLLERBASE='WorkflowApp'
    WORKFLOWMODEL=`redis-cli -h $REDISHOST hget "$PLUGINNAME"_workflow model_name`
    _WORKFLOWMODEL=${WORKFLOWMODEL,,}
    WORKFLOWMODELINFIXTURE=${_WORKFLOWMODEL^}
    WORKFLOWTABLENAME=`redis-cli -h $REDISHOST hget "$PLUGINNAME"_workflow db_name`
    WORKFLOWVIEWMODEL="V$_WORKFLOWMODEL"
    _WORKFLOWVIEWMODEL=${WORKFLOWVIEWMODEL,,}
    WORKFLOWVIEWTABLENAME="${_WORKFLOWVIEWMODEL}s"
    WORKFLOWVIEWMODELINFIXTURE=${_WORKFLOWVIEWMODEL^}
    COMMENTMODEL=`redis-cli -h $REDISHOST hget "$PLUGINNAME"_workflow comment_model_name`
    _COMMENTMODEL=${COMMENTMODEL,,}
    COMMENTMODELINFIXTURE=${_COMMENTMODEL^}
    COMMENTTABLENAME=`redis-cli -h $REDISHOST hget "$PLUGINNAME"_workflow comment_db_name`
    WORKFLOWONLYBEGIN=
    WORKFLOWONLYEND=
fi

sedFile=/tmp/_appDeploy.sed
rm $sedFile
echo "s/__PLUGINNAME__/$PLUGINNAME/g" >> $sedFile
echo "s/__PLUGINNAMELOWER__/${PLUGINNAME,,}/g" >> $sedFile
echo "s/__CONTROLLERBASE__/$CONTROLLERBASE/g" >> $sedFile
echo "s/__DBMODEL__/$DBMODEL/g" >> $sedFile
echo "s/__DBMODELINFIXTURE__/$DBMODELINFIXTURE/g" >> $sedFile
echo "s/__DBMODELLOWER__/${DBMODEL,,}/g" >> $sedFile
echo "s/__DBTABLENAME__/$DBTABLENAME/g" >> $sedFile
echo "s/__WORKFLOWMODEL__/$WORKFLOWMODEL/g" >> $sedFile
echo "s/__WORKFLOWMODELINFIXTURE__/$WORKFLOWMODELINFIXTURE/g" >> $sedFile
echo "s/__WORKFLOWMODELLOWER__/${WORKFLOWMODEL,,}/g" >> $sedFile
echo "s/__WORKFLOWTABLENAME__/$WORKFLOWTABLENAME/g" >> $sedFile
echo "s/__WORKFLOWVIEWMODEL__/$WORKFLOWVIEWMODEL/g" >> $sedFile
echo "s/__WORKFLOWVIEWTABLENAME__/$WORKFLOWVIEWTABLENAME/g" >> $sedFile
echo "s/__WORKFLOWVIEWMODELINFIXTURE__/$WORKFLOWVIEWMODELINFIXTURE/g" >> $sedFile
echo "s/__WORKFLOWVIEWMODELLOWER__/${WORKFLOWVIEWMODEL,,}/g" >> $sedFile
echo "s/__WORKFLOWONLYBEGIN__/$WORKFLOWONLYBEGIN/g" >> $sedFile
echo "s/__WORKFLOWONLYEND__/$WORKFLOWONLYEND/g" >> $sedFile
echo "s/__COMMENTMODEL__/$COMMENTMODEL/g" >> $sedFile
echo "s/__COMMENTMODELINFIXTURE__/$COMMENTMODELINFIXTURE/g" >> $sedFile
echo "s/__COMMENTTABLENAME__/$COMMENTTABLENAME/g" >> $sedFile

# check appMode
if [ -z $appMode ]; then
    echo 'please specify appMode'
    exit 1
fi
if [ "$appMode" == "db" ]; then
    export dbSchemaFile=db_attrs.xlsx
fi
if [ $appMode == "workflow" ]; then
    export dbSchemaFile=db_attrs.xlsx
    export workflowSchemaFile=wf_attrs.xlsx
    export commentSchemaFile=comments.xlsx
fi
if [ -z $dbSchemaFile ]; then
    echo 'schema file not found for the appmode'
    exit 1
fi

# cleanup
cd ../../
rm -rf Plugin/$PLUGINNAME

# copy template/scaffold
mkdir -p Plugin/$PLUGINNAME
cp -r Vendor/templates/* Plugin/$PLUGINNAME

# Vendor/db/schema: link correct DBname
cd Plugin/$PLUGINNAME/Vendor/db/schema
ln -s ../../../../../Vendor/db/schema/$dbSchemaFile "$DBTABLENAME".xlsx
if [ $appMode == "workflow" ]; then
    ln -s ../../../../../Vendor/db/schema/$workflowSchemaFile "$WORKFLOWTABLENAME".xlsx
    ln -s ../../../../../Vendor/db/schema/$commentSchemaFile "$COMMENTTABLENAME".xlsx
fi

# Controller:
cd ../../../Controller
sed -f $sedFile _AppBaseController.php.template > "$PLUGINNAME"BaseController.php
sed -f $sedFile _AppController.php.template > "$PLUGINNAME"Controller.php
sed -f $sedFile _PluginModelController.php.template > "$DBMODELINFIXTURE"sController.php

# Model: 
cd ../Model
sed -f $sedFile _DBModel.php.template > "$DBMODEL".php
if [ $appMode == "workflow" ]; then
    sed -f $sedFile _WorkflowModel.php.template > "$WORKFLOWMODEL".php
    sed -f $sedFile _WorkflowViewModel.php.template > "$WORKFLOWVIEWMODEL".php
    sed -f $sedFile _CommentModel.php.template > "$COMMENTMODEL".php
fi

# View:
cd ../View
mkdir $PLUGINNAME
mkdir "$DBMODELINFIXTURE"s
cd $PLUGINNAME
ln -s ../../../../View/BriodeCore/* .
if [ $appMode == "workflow" ]; then
    ln -sfn ../../../../View/Workflow/* .
fi
cd ../"$DBMODELINFIXTURE"s
ln -s ../../../../View/Search/* .

# Config:
cd ../../Config
sed -f $sedFile _routes.php.template > routes.php
sed -f $sedFile _bootstrap.php.template > bootstrap.php

# Event:
#cd ../Event
#cp _AttributeChangeLogger.php AttributeChangeLogger.php
#cp _WorkflowChangeLogger.php WorkflowChangeLogger.php

# Test:
cd ../Test/Case
sed -f $sedFile AllTest.php.template > AllTest.php
cd Controller
CONTROLLERTEMPLATEFILE=_DbControllerTest.php.template
if [ $appMode == "workflow" ]; then
    CONTROLLERTEMPLATEFILE=_WorkflowControllerTest.php.template
fi
sed -f $sedFile $CONTROLLERTEMPLATEFILE > "$PLUGINNAME"ControllerTest.php
sed -f $sedFile _DeployTest.php.template > DeployTest.php
ln -s ../../../../../Vendor/projects/ExcelFormat/"$PLUGINNAME".xlsx .
ln -s ../../../../../Vendor/projects/ExcelFormat/"$PLUGINNAME"Formula.xlsx .
cd ../../Fixture
sed -f $sedFile _DbFixture.php.template > "$DBMODELINFIXTURE"Fixture.php
if [ $appMode == "workflow" ]; then
    sed -f $sedFile _WorkflowFixture.php.template > "$WORKFLOWMODELINFIXTURE"Fixture.php
    sed -f $sedFile _WorkflowViewFixture.php.template > "$WORKFLOWVIEWMODELINFIXTURE"Fixture.php
fi 
cd ..  # Test

# Project specific JavaScript/CSS
cd ../webroot/js
ln -sf ../../../../Vendor/projects/webroot/js/"$PLUGINNAME"/* .
cd ../css
ln -sf ../../../../Vendor/projects/webroot/css/"$PLUGINNAME"/* .
cd .. # webroot

### move down to Application Level
# register plugin in core's bootstrap
cd ../../../Config
if [ ! -f ./bootstrap.php ]; then
    cp _bootstrap.php bootstrap.php
fi
pluginFound=`grep "load('$PLUGINNAME'," bootstrap.php`
if [ -z "$pluginFound" ]; then
    mv -f bootstrap.php bootstrap.php.back
    sed "s/\/\*__ADD_PLUGIN_HERE__\*\//CakePlugin::load('$PLUGINNAME', array('bootstrap' => array('bootstrap'), 'routes' => true));\n\/\*__ADD_PLUGIN_HERE__\*\//" bootstrap.php.back >  bootstrap.php
fi


# register event for AVC, Workflow
cd ../Event
# if AttributeChangeLogger.php not exist, create one
if [ ! -f ./AttributeChangeLogger.php ]; then
    cp _AttributeChangeLogger.php AttributeChangeLogger.php
    cp _WorkflowChangeLogger.php WorkflowChangeLogger.php
    cp ../Controller/Component/_NotificationServiceComponent.php ../Controller/Component/NotificationServiceComponent.php
fi
dbModelFound=`grep Model.$DBMODEL.afterSave AttributeChangeLogger.php`
if [ -z "$dbModelFound" ]; then
    mv -f AttributeChangeLogger.php AttributeChangeLogger.php.back 
    sed "s/\/\*__ADD_ATTRIBUTE_HERE__\*\//            'Model.$DBMODEL.afterSave'    => 'subject_changed',\n            'Model.$DBMODEL.beforeSave'    => 'subject_changed',\n\/\*__ADD_ATTRIBUTE_HERE__\*\//" AttributeChangeLogger.php.back > AttributeChangeLogger.php 
    mv -f ../Controller/Component/NotificationServiceComponent.php ../Controller/Component/NotificationServiceComponent.php.back
    sed "s/\/\*__ADD_ATTRIBUTE_HERE__\*\//            'Model.$DBMODEL.afterSave'    => 'subject_changed',\n            'Model.$DBMODEL.beforeSave'    => 'subject_changed',\n\/\*__ADD_ATTRIBUTE_HERE__\*\//" ../Controller/Component/NotificationServiceComponent.php.back > ../Controller/Component/NotificationServiceComponent.php
fi
if [ $appMode == "workflow" ]; then
    workflowFound=`grep Model.$WORKFLOWMODEL.afterSave WorkflowChangeLogger.php`
    if [ -z "$workflowFound" ]; then
        mv -f WorkflowChangeLogger.php WorkflowChangeLogger.php.back 
        sed "s/\/\*__ADD_WORKFLOW_HERE__\*\//            'Model.$WORKFLOWMODEL.afterSave'    => 'subject_changed',\n\/\*__ADD_WORKFLOW_HERE__\*\//" WorkflowChangeLogger.php.back > WorkflowChangeLogger.php 
    fi
fi

# initialize DB
cd ../Plugin/$PLUGINNAME/Vendor/scripts
PLUGINSCRIPTROOT=`pwd`
DBSCHEMAPATH=../db
#$MYSQLPATH/mysql -u root $RINGIDBNAME -e "drop table $DBTABLENAME;"
$PYTHONEXEPATH/python $PLUGINSCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH $DBTABLENAME > $DBSCHEMAPATH/_"$DBTABLENAME".sql
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $RINGIDBNAME < $DBSCHEMAPATH/_"$DBTABLENAME".sql
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $RINGIDBNAME -e "alter table $DBTABLENAME ENGINE=MYISAM;"
if [ $appMode == "workflow" ]; then
    #$MYSQLPATH/mysql -u root $RINGIDBNAME -e "drop table $WORKFLOWTABLENAME;"
    $PYTHONEXEPATH/python $PLUGINSCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH $WORKFLOWTABLENAME > $DBSCHEMAPATH/_"$WORKFLOWTABLENAME".sql
    $MYSQLPATH/mysql -h $DBHOST -u $DBUSER $RINGIDBNAME < $DBSCHEMAPATH/_"$WORKFLOWTABLENAME".sql

    #$MYSQLPATH/mysql -h $DBHOST -u root $RINGIDBNAME -e "drop table $COMMENTTABLENAME;"
    $PYTHONEXEPATH/python $PLUGINSCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH $COMMENTTABLENAME > $DBSCHEMAPATH/_"$COMMENTTABLENAME".sql
    $MYSQLPATH/mysql -h $DBHOST -u $DBUSER $RINGIDBNAME < $DBSCHEMAPATH/_"$COMMENTTABLENAME".sql
    $MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "create view v$WORKFLOWTABLENAME as select a.* from $WORKFLOWTABLENAME a;"
fi

# change permissions in plugin files
cd $PLUGINSCRIPTROOT
cd ../..
chmod -R a+w uploads
rm uploads/* 2> /dev/null
mkdir -p webroot/js
chmod -R a+w webroot/js
rm webroot/js/Generic* 2> /dev/null
chmod -R a+w attachments
chmod -R a+w Model
cd ../../
chmod -R a+w tmp

# generate JavaScript for validation
cd Vendor/scripts
bash ./addValidation.sh $PLUGINNAME
