from xlrd import open_workbook
import string
import sys

QUERY_HEAD      = "CREATE TABLE IF NOT EXISTS "
QUERY_COMP_UNIQ = ",UNIQUE KEY compUniqDummy ("
QUERY_OPTION    = ") ENGINE=InnoDB;"

COL_NAME =0
COL_TYPE =2
COL_PRI  =3
COL_NULL =4
COL_UNIQ =5
COL_BIGGEST =5

COL_TOREAD = [COL_NAME, COL_TYPE, COL_PRI, COL_NULL]

DECLARATIVE_STR = {
    COL_PRI  : "PRIMARY KEY auto_increment",
    COL_NULL : "NOT NULL",
    COL_UNIQ : "UNIQUE",
}

def genDeclarativeStr(colIndex,value):
    retval = ""
    #print type(value)
    if isinstance(value,str) or isinstance(value,unicode):
        #print "==>value is string"
        value = value.strip()
        if len(value.strip()) == 0:
            return  ""
        
    if colIndex in DECLARATIVE_STR:
        #print "declarative var found= '", value , "'"
        retval = DECLARATIVE_STR[colIndex]
   
    return retval + ' '
 

def genQuerySnip(colIndex,value):
    retval = ''
    if colIndex == COL_NAME:
        retval = value;
    elif colIndex == COL_TYPE:
        if colIndex == COL_TYPE and value == "tinyint(1)":
            retval = "bool"
        else:
            retval = value;
    else:
        retval = genDeclarativeStr(colIndex,value)

    return (retval + ' ').lower()

        
def readXls(pathToExcel,tablename,rowFrom=1):
    filename = tablename + ".xlsx"
    filename = pathToExcel + "/" + filename 
    wb = open_workbook(filename)
    data = []

    print QUERY_HEAD + tablename + '('
    for s in wb.sheets():
        #print 'Sheet:',s.name
        first = True
        uniqColumns = []
        for row in range(rowFrom,s.nrows):
            if not first:
                print ","
            first = False
            for col in range(0, COL_BIGGEST+1):
                val = s.cell(row,col).value
                if col in COL_TOREAD:
                    #print col, val.value
                    val = genQuerySnip(col,val)
                    if val.strip() != "":
                        print val
                if col == COL_UNIQ:
                    #print "COL_UNIQ found"
                    uniqVal = genDeclarativeStr(col,val)
                    #print "  value =", uniqVal, "."
                    if uniqVal != "":
                        colName = s.cell(row,COL_NAME).value
                        #print "  name of column =", colName
                        uniqColumns.append(colName)
        if len(uniqColumns)>0:
            print QUERY_COMP_UNIQ
            first = True
            for col in uniqColumns:
                if not first:
                    print ","
                first = False
                print col
            print ")"
    print QUERY_OPTION

def doit(pathToExcel, tablename):
    #print 'reading table:', tablename
    dArray = readXls(pathToExcel, tablename)

def usage():
    print "usage: ", sys.argv[0], "<schema_directory> <tablename>"
    exit()

if len(sys.argv) != 3:
    usage()
    exit()

pathToExcel = sys.argv[1]
tablename   = sys.argv[2]
 
doit( pathToExcel, tablename )

