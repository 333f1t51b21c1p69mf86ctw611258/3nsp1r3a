# LDAPHelper
import ConfigParser
import ldap
import ldap.modlist as modlist
import sys
import csv

parser = ConfigParser.SafeConfigParser()
parser.read('ldapinfo.ini')

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

