#! /bin/bash

for i in {3..39}; do
    #echo $i
    #sed -i 's/"report_fields": \[\]/"report_params": {}/' App${i}.json
    #sed -i 's/^    [}\]]  /    },/' App${i}.json
    #sed -i 's/^}$/    "globalparams": {}\n}/' App${i}.json
    #sed -i 's/globalparams/projectparams/' App${i}.json
    #sed -i 's/,\n    "projectparams": {}\n//' App${i}.json
    sed -i 's/\("workflowappbase": {\)/\1\n        "assignee_at_approve": "",/' App${i}.json
done

jsonlint -v *.json
