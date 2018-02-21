from pylib.DBMaint import DBMaint
import os

def doit(dbhost, dbuser, dbpasswd, dbname):
    with DBMaint(dbhost=dbhost, dbuser=dbuser, dbpasswd=dbpasswd, dbname=dbname) as dbm:
        dbm.sync_comm_history()

dbhost = os.environ['DBHOST']
dbuser = os.environ['DBUSER']
dbpasswd = os.environ['DBPASSWORD']
dbname = os.environ['DBNAME']
doit(dbhost, dbuser, dbpasswd, dbname)
