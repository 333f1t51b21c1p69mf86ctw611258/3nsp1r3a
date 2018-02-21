#! /bin/sh
. ./env.sh

bash setup_proj_demo.sh
bash setup_proj_panasonic.sh
bash setup_proj_awnc.sh
bash setup_proj_noritake.sh
bash setup_proj_enspirea.sh
bash setup_proj_okaya.sh
bash setup_proj_tokyomaker.sh
bash setup_proj_meijicorp.sh
bash setup_proj_meijimigration.sh
bash setup_proj_matsutani.sh
bash setup_proj_unused.sh
bash setup_proj_cci.sh
bash setup_proj_kwe.sh

# overrides selector configuration at last
bash redis_setup_selector_dev.sh

bash ./redis_app_base_uri.sh http://remote.enspirea.com:8089 http://192.168.1.8:8080

# overrides login redirect configuration
redis-cli hdel App_login_redirect_by_usertype 1
redis-cli hdel App_login_redirect_by_usertype 2
redis-cli hdel App_login_redirect_by_usertype 3
redis-cli hdel App_login_redirect_by_usertype 4
