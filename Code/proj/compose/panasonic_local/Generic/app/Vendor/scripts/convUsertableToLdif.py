import sys
import mysql.connector
import datetime
from pylib import CSVHelper

DEPARTMENT_COLUMNS = ['department']
PEOPLE_COLUMNS     = ['username', 'department', 'name', 'mail']

def printDepartmentLdif(dept):
    print "dn: ou=" + dept \
                    + ",dc=enspirea,dc=com" 
    print "objectClass: organizationalUnit" 
    print "ou: " + dept
    print
    
def printPeopleLdif(row):
    print "dn: uid=" + row['username'] \
                     + ",ou=" \
                     + row['department'] \
                     + ",dc=enspirea,dc=com"
    print "objectClass: top"
    print "objectClass: person"
    print "objectClass: organizationalPerson"
    print "objectClass: inetOrgPerson"
    print "uid: "   + row['username']
    print "cn: "    + row['name']
    print "sn: "    + row['name']
    print "givenName: " + row['name']
    print "mail: "  + row['mail']
    print

def dumpDepartment(row):
    printDepartmentLdif(row)

def dumpPeople(row):
    if set(PEOPLE_COLUMNS) != set( set(PEOPLE_COLUMNS)&set(row) ):
        print "not enough info"
        return

    printPeopleLdif(row)


def getDept(rows):
    depts = {}
    for row in rows:
        #print "** reading row:" , row
        if set(DEPARTMENT_COLUMNS) != set( set(DEPARTMENT_COLUMNS)&set(row) ):
            print "not enough info"
            return

        #print 'dept=', row['department']
        depts[row['department']] = None

    #print "**Dept found : ", depts.keys()
    return depts.keys()

def doit(csvfilename):
    header, rows = CSVHelper.readCsv(csvfilename)

    depts = getDept(rows)
    for row in depts:
        dumpDepartment(row)

    for row in rows:
        dumpPeople(row)


def usage():
    print "usage: ", sys.argv[0], "<path_to_usertable.csv>"
    exit()

if len(sys.argv) != 2:
    usage()
    exit()

csvfilename   = sys.argv[1]

doit(csvfilename) 
