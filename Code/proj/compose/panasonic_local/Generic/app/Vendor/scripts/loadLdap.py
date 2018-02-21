import ConfigParser
import ldap
import ldap.modlist as modlist
import sys
import csv
import platform

parser = ConfigParser.SafeConfigParser()
parser.read('ldapinfo.ini')

# Active Directory attributes
src_attrs = [ 
        'DN',
        'cn',
        'departmentNumber',
        'mail',
        'manager',
        'name',
        'title',
        'userPassword',
       ]

additional_attr_vals = {
        'sn': 'dummy',
}

def getLDAPConn(serverToConnect):
    ldapHostUrl = 'ldap://' + parser.get(serverToConnect,'host')
    #print 'ldapHostUrl=', ldapHostUrl
    con = ldap.initialize(ldapHostUrl)
    if( not con ):
        return None

    #print 'connected to LDAP server', con    
    return con

def bindLDAPServer(serverToConnect, ldapConn):
    adminDN  = parser.get(serverToConnect,'admin_dn')
    adminPwd = parser.get(serverToConnect,'admin_pwd')

    ldapObj = ldapConn.simple_bind_s(adminDN, adminPwd)
    if( ldapObj[0] != 97 ):
        return None

    #print 'bind successful :', ldapObj
    return ldapObj

def getLDAPServer(serverToConnect):
    con = getLDAPConn(serverToConnect)

    if( not con ):
        print 'host not reachable'
        return None

    bindResult = bindLDAPServer(serverToConnect, con)
    if( not bindResult ):
        print 'not found LDAP server' 
        return None

    return con

def convertToMap(columns, row):
    map = {}
    for i in range(len(columns)):
        if not columns[i] in src_attrs:
            print 'column is not required to add to Map :' , columns[i]
            continue
        key = columns[i]
        val = row[i]
        map[key] = val

    map.update(additional_attr_vals)
    print map
    return map

def loadCsv(csvfile):
    columns = []
    rows    = []
    with open(csvfile, 'rb') as csvfile:
        csvreader = csv.reader(csvfile, delimiter=',', quotechar='"')
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

def resetPassword(dn,row,ldapConn,newpassword):
    ldapConn.passwd_s( dn, None, newpassword )

def updateUser(row,ldapConn,password):
    print row
    attrs = {}
    dn = row['DN']
    attrs['objectclass'] = ['top','person','organizationalPerson','inetOrgPerson']
    # attrs['DN'] = dn
    for key in row.keys():
        attrs[key] = row[key]

    del attrs['DN']
    try:
        ldif = modlist.addModlist(attrs)
        ldapConn.add_s(dn,ldif)
    except:
        ldif = modlist.modifyModlist(attrs,attrs)
        ldapConn.modify_s(dn,ldif)

    resetPassword(dn,row,ldapConn,password)

def updateUsers(rows,ldapConn,password):
    for row in rows:
        updateUser(row,ldapConn,password)


#### main function

def doit(ldapServer,csvfile,password):
    ldapConn=getLDAPServer(ldapServer)
    (col,rows) = loadCsv(csvfile)
    updateUsers(rows,ldapConn,password)
    ldapConn.unbind_s()

def usage():
    print "usage: ", sys.argv[0], "<usertable.csv> <default_password>"
    exit()

if len(sys.argv) != 3:
    usage()
    exit()

csvfile = sys.argv[1]
password = sys.argv[2]

os = platform.system()
hostname = platform.node()

doit('ldap_docker', csvfile, password)
