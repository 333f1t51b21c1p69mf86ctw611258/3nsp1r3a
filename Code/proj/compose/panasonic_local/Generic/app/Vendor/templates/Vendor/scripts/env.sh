#! /bin/sh

### platform dependent
case `hostname` in
    'Enspirea-LLCs-Mac-mini.local')  # Mac
        echo 'using config for Mac'
        export LDAPHOST=192.168.1.105
        export LDAPADMINPASSWORD=820davis
        export LDAPADMINUSER=cn=Manager,dc=enspirea,dc=com
        export XAMPPBINPATH=/Applications/XAMPP/xamppfiles/bin/
        export BIRTWORKROOT=/home/enspirea/workspace/Test
        ;;
    'ubuntu')  # ubuntu at Enspirea
        echo 'using config for DELL note'
        export LDAPHOST=192.168.1.105
        export LDAPADMINPASSWORD=820davis
        export LDAPADMINUSER=cn=Manager,dc=enspirea,dc=com
        export XAMPPBINPATH=/opt/lampp/bin/
        export BIRTWORKROOT=/home/enspirea/workspace/Test
        export BIRT_HOME=/home/enspirea/BI/birt-runtime-4_3_1
        ;;
    'ip-172-31-24-254') # AWS
        echo 'using config for AWS'
        export LDAPHOST=localhost
        export LDAPADMINPASSWORD=briodeRocks
        export LDAPADMINUSER=cn=admin,dc=enspirea,dc=com
        export XAMPPBINPATH=/opt/lampp/bin/
        export BIRTWORKROOT=/home/enspirea/workspace/Test
        ;;
    *) # all ubuntu
        echo 'using ubuntu general'
        export LDAPHOST=localhost
        export LDAPADMINPASSWORD=briodeRocks
        export LDAPADMINUSER=cn=admin,dc=enspirea,dc=com
        export XAMPPBINPATH=/opt/lampp/bin/
        export BIRTWORKROOT=/home/ubuntu/workspace/Test
        export BIRT_HOME=/home/ubuntu/BI/birt-runtime-4_3_1
        ;;
esac

### third party location
export OPENLDAPSCHEMADIR=../ldap/schema
export MYSQLPATH=$XAMPPBINPATH

### ringi configuration
export SCRIPTROOT=../scripts
export BUDGETROOT=../budget
export SCHEMAPATH=../db/schema
export USERINFOPATH=../user
export SQLPATH=../db
export UPLOADROOT=../../uploads
export REPORTROOT=$UPLOADROOT/reports

### data source file names
ATTACHMENT_FILENAME=attachment.zip
USERTABLE_CSV_FILENAME=Users.csv

### default system account and passwords
export DEFAULT_USER_PASSWORD=root

### db configuration
export RINGIDBNAME=genericdata
export DBHOST=localhost
export DBUSER=root
export DBPASSWORD=
export DBNAME=$RINGIDBNAME

### workflow configuration
WORKFLOW_OPTION=dept
