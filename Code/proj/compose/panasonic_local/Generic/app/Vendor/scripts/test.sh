#! /bin/bash
. ./env.sh

cd ../../
#for (( c=1; c<=13; c++ ))
for (( c=2; c<=2; c++ ))
do
    ./Console/cake test "App$c" All
done
