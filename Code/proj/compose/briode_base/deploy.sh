#! /bin/bash
if [ $# -ne 1 ]; then
    echo "Usage: $0 <project_name>"
    exit 1
fi

../../login.sh

PROJECT=$1

#if [ 0 -eq 1 ]; then
##### copy webapps #####
cd share
aws s3 cp s3://enspireacom/Public/docker/briode/share/webapps webapps --recursive
cd ..


##### setup Briode core #####
# create Generic folder
#if [ ! -d "Generic" ]; then
#    bash setup.sh
#    cd Generic
#    git submodule init 
#    git submodule update 
#    cd ..
#fi

docker stop $PROJECT
docker-compose up -d
sleep 3
docker exec -i -t $PROJECT /bin/sh -c "cd /var/www/html/Generic/app/Vendor/scripts ; /bin/bash deploy.sh $PROJECT init ; cd /loader; /bin/bash ./loader.sh"
docker stop $PROJECT
# make attachment tmp directory writable
sudo chmod -R a+w Generic/app/webroot/img/tmp

# load existing attachment/UI layout
cd share
sudo tar xvf briode.tar
cd ..
cd Generic/app/Plugin
for app in App*; do
  echo ${app}
  cd ${app}
  sudo rm -rf uploads
  sudo rm -rf attachments
  sudo ln -s ../../../../share/${app}/uploads .
  sudo ln -s ../../../../share/${app}/attachments .
  sudo chmod a+w -R ../../../../share/${app}/uploads
  sudo chmod a+w -R ../../../../share/${app}/attachments
  cd ..
done

cd ../../..
#bash run.sh
docker-compose up -d
#fi

# load initial LDAP users
LDAPDATAFILE=ldap${PROJECT}-data.gz
if [ ! -f "backup/ldap/${LDAPDATAFILE}" ]; then
    cp loader/ldap${PROJECT}-* backup/ldap
fi
if [ -f "backup/ldap/${LDAPDATAFILE}" ]; then
    cd tools
    bash reload_ldap.sh $PROJECT
    cd ..
fi

if [ 0 -eq 1 ]; then
    # load initial Redis cache
    if [ -f 'loader/appendonly.aof' ]; then
        docker stop ${PROJECT}_redis_1
        sudo cp  loader/appendonly.aof redis
        docker-compose up -d
    else
    echo 'Make sure to reload Excel UI formats'
            echo 'And copy loader/appendonly.aof to loader after that'
    fi
fi 
