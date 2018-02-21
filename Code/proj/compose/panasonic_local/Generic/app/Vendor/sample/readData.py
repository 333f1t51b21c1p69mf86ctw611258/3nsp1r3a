from xlrd import open_workbook
import string
import sys

#END_OF_ROW_LABEL  = "TOTAL"

BEGIN_LINE = 1
BEGIN_COL  = 1
END_COL    = 9
Column_Info = {
 1: "appNo",
 2: "project",
 3: "purpose",
 4: "date",
 5: "state",
 6: "expense",
 7: "asset",
 8: "total",
}

DB_Columns = [
 "appNo",
 "project",
 "purpose",
 "date",
 "state",
 "expense",
 "asset",
 "total",
]


def getEndOfLine(sheet,columnToCheck):
    # end of line not found, process upto the max sheet size
    return sheet.nrows

def readXls(filename):
    wb = open_workbook(filename)
    data = []

    for s in wb.sheets():
        #print 'Sheet:',s.name
        map = {}
        end_line = getEndOfLine(s,1)
        for row in range(BEGIN_LINE, end_line):
            map = {}
            for col in range(BEGIN_COL, END_COL):
                #print col,row,'=',s.cell(row,col)
                map[Column_Info[col]] = s.cell(row,col-1)
            data.append(map)
 
    return data

def getMapValue(map,key):
    retval = ''

    try:
        retval = map[key]
    except:
        pass

    #retval = "".join(retval.value)
    try:
        retval = str(retval.value)
    except:
        retval = ''

    return retval

def parseData(dCache, dMap):
    retval = ''

    first = True
    for key in DB_Columns:
        if not first:
            retval = retval + ","
        first = False
        mapValue= getMapValue(dMap,key)
        retval = retval + '\"' + mapValue + "\""

        if key in dMap.keys():
            dCache[dMap[key]] = mapValue

    return retval

def genCsvForMySqlImport(dCache):
    line = ""
    keys = dCache.keys()
    keys.sort()
    #print keys

    first = True
    for col in keys:
        if not first:
            line = line + ","
        first = False
        colval = ""
        try:
            colval = dCache[db_map[col]]
        except:
            pass
        line = line + "\"" + colval + "\""

    print line

def saveCsv(dArray):
    # header title
    line = ""
    first = True
    for c in DB_Columns:
        if not first:
            line = line + ","
        first = False
        line = line + "\"" + c + "\""
    print line

    # data
    #print dArray
    for dMap in dArray:
        dCache = {}
        line = parseData(dCache,dMap)
        print line

        # dump CSV importable onto mysql
        #print personCache
        #genCsvForMySqlImport(dCache)

    #print line

    return

def doit(filename):
    dArray = readXls(filename)
    saveCsv(dArray)


def usage():
    print "usage: ", sys.argv[0], "<excel_file.xls>"
    exit()

if len(sys.argv) != 2:
    usage()
    exit()

filename   = sys.argv[1]
    
doit(filename)

