import mysql.connector
import datetime
import os

DBHOST = os.environ['DBHOST']
DBUSER = os.environ['DBUSER']
DBPASSWORD = os.environ['DBPASSWORD']
DBNAME = os.environ['DBNAME']

IGNORE_AT_UPDATE = []
IGNORE_AT_INSERT = []

ADDITIONAL_ATTRS = {
    #'creator_id'    : 'LDAP',
}

ENUM_FIRST_VALUE = 100

def connect():
    cnx = mysql.connector.connect(user=DBUSER, database=DBNAME)

    return cnx

def getSelectQuery(DBconn, header,dbTable):
    query = "Select "
    first = True
    for c in header:
        if not first:
            query = query + ","
        first = False
        query = query + c
    query = query + " FROM " + dbTable

    #print "query in getSelectQuery=", query
    cursor = DBconn.cursor()
    cursor.execute(query)

    rows = []
    columns = tuple( [d[0].decode('utf8').encode('ascii','ignore') for d in cursor.description] )
    entryFound = False
    for r in cursor:
        result = dict(zip(columns, r))
        entryFound = True
        rows.append(result)
    cursor.close()
   
    return rows

def getUpdateQuery(header, row, dbTable, keyColNames):
    #print "## getUpdateQuery, row = ", row
    #print header
    query = "UPDATE " + dbTable + " "
    query = query + "SET "
    first = True
    for i in range(len(header)):
        if header[i] in IGNORE_AT_UPDATE:
            continue

        if not first:
            query = query + ','
        first = False
        #print header[i], row
        query = query + header[i] + "=" + "\'"+str(row[header[i].lower()]) + "\' "

    query = query + "WHERE " 
    first = True
    for i in range(len(keyColNames)):
        if not first:
            query = query + " AND "
        first = False
        query = query + keyColNames[i] + " = \'" + str(row[keyColNames[i].lower()]) + "\'"

    return query
       

def getInsertQuery(dbTable, header, row):
    query = "INSERT INTO " + dbTable + " "
    query = query + "("
    first = True
    
    for col in header:
        #print col
        if not first:
            query = query + ","
        first = False
        query = query + col 
    query = query + ")"
    query = query + " VALUES ("
    first = True
    for colname in header:
        val = row[colname]
        #print val
        if not first:
            query = query + ","
        first = False
        query = query + "\'" + str(val) + "\'"
    query = query + ")"

    return query

def rowAlreadyExist(cursor, dbTable, header, row, keyColNames, columnsToSave=None):
    dnFound = False

    for col in keyColNames:
        if not col in header:
            print col, " field not found!"
            return None

    query = "SELECT * "  
    #first = True
    #for col in keyColNames:
    #    if not first:
    #        query = query + ','
    #    first = False
    #    query = query + col
    query = query + " from " + dbTable
    query = query + " WHERE " 
    first = True
    for col in keyColNames:
        if not first:
            query = query + ' AND '
        first = False
        query = query + col + " = " + "\'" + str(row[col]) + "\'"

    #print row
    #print query

    cursor.execute(query)
    #print cursor.__dict__

    result = {}
    columns = tuple( [d[0].decode('utf8').encode('ascii','ignore') for d in cursor.description] )
    entryFound = False
    for r in cursor:
        result = dict(zip(columns, r))
        #entry found
        entryFound = True
    cursor.close()

    #print result, query

    if not entryFound:
        # suppressing this message as it's happening too often
        #print keyColNames, " not found"
        return None

    saveColumns = []
    if columnsToSave:
        for c in columnsToSave:
            saveColumns.append(c.lower())
    # overwrite fetched with newer
    for rkey,rval in row.items():
        # columns for generated numbers should not be overwritten
        if not rkey.lower() in saveColumns:
            result[rkey.lower()] = rval

    #print "   result for query = ", result
    return result

def updateRow(DBconn, dbTable, header, row, keyColNames): 
    query = ""
    cursor = DBconn.cursor()

    fetchedRow = rowAlreadyExist(cursor, dbTable, header, row, keyColNames)
    if fetchedRow:
        query = getUpdateQuery(header, fetchedRow, dbTable, keyColNames)
    else:
        #print "rows to insert = ", row
        query = getInsertQuery(dbTable, header, row)

    #print query

    cursor = DBconn.cursor(True)   
    cursor.execute(query)
    DBconn.commit()
    cursor.close()

def queryTable(tablename, columns):
    DBconn = connect()

    rows = getSelectQuery(DBconn, columns, tablename)
    
    DBconn.close()

    return rows

def updateDB(dbTable,header,rows,keyColNames):
    DBconn = connect()

    header = header + ADDITIONAL_ATTRS.keys()
    for row in rows:
        #print "**** ENTERING ****"
        #print row
        row.update(ADDITIONAL_ATTRS)
        updateRow(DBconn, dbTable, header, row, keyColNames)

    DBconn.close() 

def updateRowIncremental(DBConn, dbTable, header, row, enumColName, keyColNames, incColName):
    query = ""
    cursor = DBConn.cursor()

    # create saved column
    savedColumn = [incColName,]
    fetchedRow = rowAlreadyExist(cursor, dbTable, header, row, keyColNames, savedColumn)
    if fetchedRow:
        # should be identical, just overwrite it
        query = getUpdateQuery(header, fetchedRow, dbTable, keyColNames)
    else:
        # assign new value and insert
        cursor = DBConn.cursor(True)
        row[incColName] = getNextEnumValue(cursor, dbTable, enumColName, incColName, row)
        query = getInsertQuery(dbTable, header, row)

    #print query

    cursor = DBConn.cursor(True)
    cursor.execute(query)
    DBConn.commit()
    cursor.close()

def updateDBIncremental(dbTable, header, rows, enumColName, keyColNames, incColName):
    DBConn = connect()
    cursor = DBConn.cursor()
    query = None
    #for r in rows:
    #    print "rows to insert = ", r
    for row in rows:
        query = updateRowIncremental(DBConn, dbTable, header, row, enumColName, keyColNames, incColName)

    DBConn.close()

def getNextEnumValue(cursor, dbTable, enumColName, incColName, row):

    query = "select max(" + incColName + ") as 'maxvalue' FROM "  \
            + dbTable + " WHERE "  \
            + enumColName + "=\'" + row[enumColName] + "\'"

    #print query
    cursor.execute(query)

    retval = str(ENUM_FIRST_VALUE)
    #print "## getNextEnumValue, query  = ", query
    #print "## getNextEnumValue, retval = ", retval
    for dummy in cursor:
        #entry found
        #print dummy
        if dummy[0] and dummy[0] != 'None':
            #print dummy[0]
            retval = str(int(dummy[0])+1)

    cursor.close()

    #print "next enum val = ", retval
    return retval    
