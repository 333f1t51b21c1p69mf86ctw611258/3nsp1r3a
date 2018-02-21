import urllib
import urllib2
import os

BRIODEHOST = os.environ['BRIODEHOST']

paypal_url = 'http://' + BRIODEHOST + '/Generic/paypal_ack'
values = {
    'test1' : 1,
    'test2' : 2,
}
req = urllib2.Request(url=paypal_url, data=urllib.urlencode(values))
f = urllib2.urlopen(req)
print f.read()
