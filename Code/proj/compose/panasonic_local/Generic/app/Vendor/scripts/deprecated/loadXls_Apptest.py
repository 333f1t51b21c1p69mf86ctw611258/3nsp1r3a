from xlrd import open_workbook
import string
import sys
from pylib import DBHelper
from pylib.ExcelHelper import ExcelHelper

COL_ONE   = 0
COL_TWO   = 1
COL_THREE = 2
COL_FOUR  = 3
COL_FIVE  = 4
COL_SIX   = 5
COL_SEVEN = 6
COL_EIGHT = 7

ID_DBNAME_MAP = {
    COL_ONE   : "Application_No",
    COL_TWO   : "Project",
    COL_THREE : "Purpose",
    COL_FOUR  : "Date",
    COL_FIVE  : "State",
    COL_SIX   : "Expense",
    COL_SEVEN : "Asset",
    COL_EIGHT : "Total",
}
COLS = [ 
    "Application_No",
    "Project",
    "Purpose",
    "Date",
    "State",
    "Expense",
    "Asset",
    "Total"
]

COL_TOREAD = [COL_ONE, COL_TWO, COL_THREE, COL_FOUR, COL_FIVE, COL_SIX, COL_SEVEN, COL_EIGHT]
DBTABLE_MASTER = "attrapptests"

def readXls(filename,tablename,rowFrom=1):
    wb = open_workbook(filename)
    rowsToSave = []

    for s in wb.sheets():
        #print 'Sheet:',s.name
        first = True
        for row in range(rowFrom,s.nrows):
            nameToAdd = {}
            first = False
            for col in COL_TOREAD:
                val = s.cell(row,col).value
                #print col, val.value
                nameToAdd[ID_DBNAME_MAP[col]] = val
            rowsToSave.append(nameToAdd)

    return rowsToSave

def doit(pathToExcel, tablename):
    #print 'reading table:', tablename
    eh = ExcelHelper()
    rows = eh.readXls(pathToExcel, tablename, COLS)

    header = ID_DBNAME_MAP.values() 
    keyColNames = ID_DBNAME_MAP.values() 
    DBHelper.updateDB(tablename,header,rows,keyColNames)

def usage():
    print "usage: ", sys.argv[0], "<XLS_TO_LOAD.xlsx>"
    exit()

if len(sys.argv) != 2:
    usage()
    exit()

pathToExcel = sys.argv[1]
 
doit( pathToExcel, DBTABLE_MASTER)

