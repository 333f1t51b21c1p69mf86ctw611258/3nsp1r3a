#! /bin/sh

python ./convXlsSchemaToSql.py ./ attributes > _attributes.sql

/opt/lampp/bin/mysql -u root genericdata < _attributes.sql

