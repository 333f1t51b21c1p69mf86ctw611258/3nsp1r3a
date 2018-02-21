FILENAME='redis_global.sh'

res = ''
with open(FILENAME) as f:
    res = "{\n"
    res += "    \"lists\": {\n"
    l_first = True
    for l in f.readlines():
        if l[0] == '#':  continue
        if len(l)<3: continue
        if not l_first:
            res += ",\n"
        l_first = False
        cols = l.split(' ')
        res += "        \"{}\": [\n".format(cols[1])
        c_first = True
        for c in cols[2:]:
            if not c_first:
                res += ",\n"
            c_first = False
            res += '            "{}"'.format(c)
        res += "\n        ]"
    res += "\n    }\n"

print(res)
