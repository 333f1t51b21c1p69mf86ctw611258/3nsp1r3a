from xlrd import open_workbook
import string
import sys

BEGIN_LINE = 2
BEGIN_COL  = 0
END_COL    = 9
Column_Info = {
    0: "id",        #A
    1: "DN",        #B
	2: "username",  #C 
    3: "title",     #D
    4: "mail",      #E
    5: "department",#F
    6: "name",      #G
    7: "usertype",  #H
    8: "manager",   #I
}
UserType_Map = {
    'sales manager' : '4',
    'senior manager' : '3',
    'vice president' : '2',
    'president' : '1',

    'administrator' : '1',
    'senior manager' : '2',
    'manager' : '3',
    'employee' : '4',
}
def getEndOfLine(sheet,columnToCheck):
    return sheet.nrows
    #return 1715

def readXls(filename):
    wb = open_workbook(filename)
    data = []

    s = wb.sheets()[0]
    #print 'Sheet:',s.name
    map = {}
    end_line = getEndOfLine(s,1)
    for row in range(BEGIN_LINE-1, end_line):
        map = {}
        for col in range(BEGIN_COL, END_COL):
            #print col,row,'=',s.cell(row,col)
            map[Column_Info[col]] = s.cell(row,col)
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

    if key == 'usertype':
        retval = UserType_Map[retval]

    return retval

def parseData(dCache, dMap):
    retval = ''

    first = True
    for key in Column_Info.values():
        if not first:
            retval = retval + ","
        first = False
        mapValue= getMapValue(dMap,key)
        retval = retval + '\"' + mapValue + "\""

        if key in dMap.keys():
            dCache[dMap[key]] = mapValue

    # double apostrophe
    retval = retval.replace('\'','\'\'')
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
    for c in Column_Info.values():
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
    print "usage: ", sys.argv[0], "<path_to_budget.xls>"
    exit()

if len(sys.argv) != 2:
    usage()
    exit()

filename   = sys.argv[1]
    
doit(filename)

