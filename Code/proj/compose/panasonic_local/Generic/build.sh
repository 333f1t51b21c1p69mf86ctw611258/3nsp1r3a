#! /bin/sh
cd app/Vendor/scripts/
./deploy.sh -i
cd ../../..
chmod -R a+w app/tmp
sudo chmod a+w app/uploads/
sudo chmod a+w app/webroot/js/
cd app/Plugin/App1/Vendor/scripts/
./deploy.sh 
cd ../../../App2/Vendor/scripts/
./deploy.sh 

