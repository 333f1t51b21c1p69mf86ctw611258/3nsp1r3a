from xlrd import open_workbook
import string
import sys
import DBHelper

COL_ONE   = 0
COL_TWO   = 1
COL_THREE = 2
COL_FOUR  = 3
COL_FIVE  = 4
COL_SIX   = 5
COL_SEVEN = 6
COL_EIGHT = 7
COL_NINE  = 8
COL_TEN   = 9

ID_DBNAME_MAP = {
    COL_ONE   : "Customer_name",
    COL_TWO   : "Detail",
    COL_THREE : "Fee",
    COL_FOUR  : "Quotation_date",
    COL_FIVE  : "Quote_No",
    COL_SIX   : "Invoice_date",
    COL_SEVEN : "Inv_No",
    COL_EIGHT : "PO",
    COL_NINE  : "Payment_date",
    COL_TEN   : "Status",
} 

COL_TOREAD = [COL_ONE, COL_TWO, COL_THREE, COL_FOUR, COL_FIVE, COL_SIX, COL_SEVEN, COL_EIGHT, COL_NINE, COL_TEN]
DBTABLE_MASTER = "attrapp20s"

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
                if val == '' or val == None:
                    continue
                nameToAdd[ID_DBNAME_MAP[col]] = val
            rowsToSave.append(nameToAdd)

    return rowsToSave

def doit(pathToExcel, tablename):
    #print 'reading table:', tablename
    rows = readXls(pathToExcel, tablename)

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

