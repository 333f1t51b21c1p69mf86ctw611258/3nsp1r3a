#! /bin/bash

# Options:
#   -r  backup database/redis, reload config and restore the backup
#       this won't work when newer build uses different DB schema

#Common
bash redis_clear.sh
bash redis_global.sh
bash redis_config_user_profile.sh disable  # disable profile picture
#backup database

#project specific - select one of the following
#bash setup_proj_dev.sh       # all application
#bash setup_proj_demo.sh      # app14-18,21    FIXME: app14 overlaps with enspirea
bash setup_proj_panasonic.sh # app2,32
#bash setup_proj_awnc.sh      # app3-8,10-13
#bash setup_proj_noritake.sh  # app1,9,39
#bash setup_proj_enspirea.sh  # app14,20
#bash setup_proj_okaya.sh     # app19
#bash setup_proj_tokyomaker.sh # app22,23
#bash setup_proj_meijicorp.sh # app24,25,26,27,28
#bash setup_proj_meijimigration.sh # app29,30,31
#bash setup_proj_matsutani.sh  # app34
#bash setup_proj_unused.sh  # app33,35
#bash setup_proj_cci.sh  # app36,37
#bash setup_proj_kwe.sh  # app38

#restore database
