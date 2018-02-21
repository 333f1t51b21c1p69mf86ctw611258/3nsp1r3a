import mysql.connector
import datetime
import os
import re
import logging
from . import RotatingLogger
from .appconfig import LOGGER_NAME

DBHOST = os.environ['DBHOST']
DBUSER = os.environ['DBUSER']
DBPASSWD = os.environ['DBPASSWORD']
DBNAME = os.environ['DBNAME']

RotatingLogger.setup_logger()
logger = logging.getLogger(LOGGER_NAME)

class DBConnConnectFailed(Exception):
    pass

class DBMaint(object):
    def __init__(self, dbhost=DBHOST, dbuser=DBUSER, dbpasswd=DBPASSWD, dbname=DBNAME):
        self.dbhost = dbhost
        self.dbuser = dbuser
        self.dbpasspw = dbpasswd
        self.dbname = dbname
        self.connection = None

    def __enter__(self):
        try:
            self.connection = mysql.connector.connect(user=self.dbuser, database=self.dbname, host=self.dbhost)
        except mysql.connector.Error as err:
            if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
                raise DBConnConnectFailed('something is wrong with your username or password')
            elif err.errno == errorcode.ER_BAD_DB_ERROR:
                raise DBConnConnectFailed('database does not exist')
        except:
            raise DBConnConnectFailed('unexpected error occurred')
        return self

    def __exit__(self, exctype, excinst, exctb):
        if exctype == DBConnConnectFailed:
            logging.exception('DB connection not created')
        self.connection.close()

    def __repr__(self):
        return '%s(%s)' % (self.__class__.__name__, self.dbname)

    def sync_comm_history(self):
        """Synchronize timestamp between comm and history"""
        cursor = self.connection.cursor()

        # get all timestamps for each attribute_event_logs.subject_id order by id
        #  attr_log[subject_id] = []
        # read update_time
        query_attr = "select id,subject_id,update_time from attribute_event_logs order by subject_id,id"
        try:
            cursor.execute(query_attr)
        except mysql.connector.Error as err:
            raise DBConnConnectFailed('failed executing attribute query')

        attrs = {}
        for (id, subject_id, update_time) in cursor:
            attrs.setdefault(subject_id, []).append(update_time)
 
        # group comments by subject_id from commapp2s order by id
        #  comm[subject_id] = []
        #  update create_at
        query_comm = "select id,subject_id from commapp2s order by subject_id,id"
        try:
            cursor.execute(query_comm)
        except mysql.connector.Error as err:
            raise DBConnConnectFailed('failed executing comment query')

        comms = {}
        for (id, subject_id) in cursor:
            comms.setdefault(subject_id, []).append(id)
 
        # validate if two numbers are correct
        #print 'comm not found in attrs'
        #print set(attrs.keys()) - set(comms.keys())

        # create a map that holds the same number of attrlog and comments
        comms_update = {}
        for subj_id in set(attrs.keys()) & set(comms.keys()):
            #if len(attrs[subj_id]) != len(comms[subj_id]):
            #    print 'comments num does not match:key=', subj_id, ',lendiff=,',len(attrs[subj_id]),len(comms[subj_id])
            comms_update[subj_id] = (comms[subj_id], attrs[subj_id][0])

        #print 'comms to update'
        #print comms_update

        # update DB
        query = ""
        for subj_id in comms_update:
            comm_ids = comms_update[subj_id][0]
            ts = comms_update[subj_id][1]
            query = "update commapp2s set created_at='"+str(ts)+"' where "
            const = ""
            for id in comm_ids:
                if const != "":
                    const += " or "
                const += " id="+str(id)
            query += const
            #print subj_id, query

            try:
                cursor.execute(query)
                self.connection.commit()
            except mysql.connector.Error as err:
                raise DBConnConnectFailed('failed executing comment timestamp update query')

    def delete_table(self, tblname):
        cursor = self.connection.cursor()
        query = 'drop table {}'.format(tblname)
        try:
            cursor.execute(query)
            self.connection.commit()
        except mysql.connector.Error as err:
            logger.debug(
                'table deletion {} failed... continue'.format(tblname),
                extra=dict(function="delete_table")
            )
            pass

    def create_table(self, tblname, type_cols):
        self.delete_table(tblname)

        query = "create table {} (id int(13) unsigned auto_increment primary key, creator_id varchar(30), created varchar(10), updated varchar(10),".format(tblname)
        first = True
        for t in type_cols:
            if not first:
                query += ","
            first = False
            query += '{} {}'.format(t, type_cols[t])
    
        query += ")"

        cursor = self.connection.cursor()
        try:
            logger.debug(
                'query={}'.format(query),
                extra=dict(function="create_table")
            )
            cursor.execute(query)
            self.connection.commit()
        except mysql.connector.Error as err:
            raise DBConnConnectFailed('failed executing table creation query')


