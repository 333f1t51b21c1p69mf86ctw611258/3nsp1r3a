#! /bin/bash

for fn in *.json; do
    #echo $i
    #sed -i 's/"report_fields": \[\]/"report_params": {}/' App${i}.json
    sed -i 's/^    [}\]]/    },/' $fn
    sed -i 's/^}$/    "projectparams": {}\n}/' $fn
done

jsonlint -v *.json
