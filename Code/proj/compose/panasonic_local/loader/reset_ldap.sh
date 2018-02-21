#! /bin/bash
cd /var/www/html/Generic/app/Vendor/scripts
#bash reload_user.sh Users.xlsx
bash resetAllPasswords.sh
mysql -h mysql -u root genericdata -e "update users set mail='enspirea.dev@gmail.com'"
#cp active.php /var/www/html/Generic/app/Plugin/App53/uploads

#apt update
#apt install -y zip


