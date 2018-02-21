import csv
import re

def escape(val):
    return re.sub(r'\'','',val)

def convertToMap(columns, row):
    map = {}
    for i in range(len(columns)):
        key = columns[i]
        val = row[i]
        map[key] = escape(val)

    #print map
    return map
#def convertToMap(columns, row):
#    first = True
#    for c in columns:
#        print c
#        key = c
#        val = row[key]
#        map[key] = escape(val)
#
#    return map 

def readCsv(filename):
    columns = []
    rows    = []
    with open(filename, 'rb') as csvfile:
        csvreader = csv.reader(csvfile, delimiter=',', quotechar='\"')
        #csvreader = csv.reader(csvfile, delimiter=',')
        lineno = 0
        for row in csvreader:
            lineno = lineno + 1
            if lineno == 1:
                # list of column header
                columns = row
                continue

            map = convertToMap(columns,row)
            rows.append(map)

            lineno = lineno + 1

    return (columns,rows)

def printRows(columns,rows):
    output = ""

    # print header
    first = True
    for c in columns:
        if not first:
            output = output + ","
        first = False
        output = output +  '\"' + c + '\"'
    print output

    for r in rows:
        output = ""
        first = True
        for c in columns:
            if not first:
                output = output + ","
            first = False
            output = output + '\"' + r[c] + '\"'
        print output
