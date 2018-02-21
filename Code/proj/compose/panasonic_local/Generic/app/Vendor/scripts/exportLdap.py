import ConfigParser
import ldap
import sys

parser = ConfigParser.SafeConfigParser()
parser.read('ldapinfo.ini')

# Active Directory attributes
src_attrs = [ 
        'cn',
        'departmentNumber',
        'givenname',
        'mail',
        'manager',
        'title',
        'userPassword',
       ]

# Active Directory to MySQL user table mapping
# make the following list sorted by key (left column)
db_map = {
        'DN'                : 'DN',
        'cn'                : 'username',
        'departmentNumber'  : 'department',
        'givenname'         : 'givenname',
        'mail'              : 'mail',
        'manager'           : 'manager',
        'title'             : 'title',
        'userPassword'      : 'password',
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
    return ldapConn

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

def getSourceUserData(serverToConnect, ldapConn):
    baseDN = parser.get(serverToConnect,'base_user_dn')
    filter = 'objectClass=person'
    search_res = ldapConn.search_s( baseDN, ldap.SCOPE_SUBTREE, filter, src_attrs)

    return search_res

def getMapValue(map,key):
    retval = ''
    
    try:
        retval = map[key]
    except:
        pass

    retval = "".join(retval)

    return retval

def parseUser(dbcache, person):
    retval = ''

    for key in src_attrs:
        mapValue= getMapValue(person,key)
        retval = retval + '\"' + mapValue + "\","

        if key in db_map.keys():
            dbcache[db_map[key]] = mapValue

    return retval

def genCsvForMySqlImport(personCache):
    line = ""
    keys = db_map.keys()
    keys.sort()
    #print keys

    first = False
    for col in keys:
        if first:
            line = line + ","
        colval = ""
        try:
            colval = personCache[db_map[col]]
        except:
            pass 
        line = line + '\"' + colval + "\""
        
    print line

def saveCsv(ldapRes):
    # header title
    line = 'DN,'
    for a in src_attrs:
        line = line + db_map[a] +","
    print line

    # data
    for person in ldapRes:
        personCache = {}
        personCache['DN'] = person[0]
        dn,personMap = person[0],person[1]
        line = '\"' + dn + "\","
        line = line + parseUser(personCache,personMap)
        #print line

        # dump CSV importable onto mysql
        #print personCache
        genCsvForMySqlImport(personCache)

    return

#### main function

def doit(ldapServer):
    ldapConn=getLDAPServer(ldapServer)
    ldapSearchRes = getSourceUserData(ldapServer, ldapConn)
    saveCsv( ldapSearchRes )


def usage():
    print "usage: ", sys.argv[0], "<ldapsrc_in_ldapinfo.ini>"
    exit()

if len(sys.argv) != 2:
    usage()
    exit()

ldapsrc = sys.argv[1]

doit(ldapsrc)
