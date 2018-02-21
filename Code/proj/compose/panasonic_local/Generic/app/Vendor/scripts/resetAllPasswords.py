from pylib import CSVHelper
from pylib import LDAPHelper
import sys
import platform

def resetPassword(dn,ldapConn,newpassword):
    print "resetPassword for dn=", dn, ",pass=", newpassword
    ldapConn.passwd_s( dn, None, newpassword )


def readUserDNs(filename):
    print "loading Master tables..."

    header, rows = CSVHelper.readCsv(filename)

    # take DNs
    dnIndex = 0
    for col in header:
        if col == 'DN':
            break
        dnIndex = dnIndex + 1

    dnArray = []
    for r in rows:
        dnArray.append(r['DN'])
        
    return dnArray

def doit(ldapServer, filename,newPassword):
    DNs = readUserDNs(filename)

    ldapConn = LDAPHelper.getLDAPServer(ldapServer)

    for dn in DNs:
        if dn:
            resetPassword(dn,ldapConn,newPassword)

def usage():
    print "usage: ", sys.argv[0], "<usertable.csv> <newPassword>"
    exit()

if len(sys.argv) != 3:
    usage()
    exit()


os = platform.system()
hostname = platform.node()

doit('ldap_docker', sys.argv[1], sys.argv[2])

