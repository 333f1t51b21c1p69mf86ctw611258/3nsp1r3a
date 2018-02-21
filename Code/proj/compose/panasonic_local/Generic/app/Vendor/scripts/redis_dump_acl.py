import sys
import json
import os


def dump_acl():
    from pylib.RedisManager import RedisManager
    rm = RedisManager(None, None, json_file, project_name)
    result = rm.get_groups(app_name)
    result.update(rm.get_layer_users(app_name))
    print(json.dumps(result, indent=4), file=sys.stdout)


def dump_acl_to_file(json_file):
    from pylib.RedisManager import RedisManager
    rm = RedisManager(None, None, json_file, project_name)
    new_acl = rm.get_groups(app_name)
    new_acl.update(rm.get_layer_users(app_name))

    with open(json_file) as f:
        appconf = json.load(f)
        appconf.update(new_acl)

    import datetime
    timestamp = datetime.datetime.now().strftime('%Y%m%d%H%M%S')
    json_file_backup = 'appconfig/backup/{}.json'.format(json_file.replace('/','.').split('.')[1], timestamp)

    from shutil import copyfile
    copyfile(json_file, json_file_backup)
    with open(json_file, 'w') as f:
        print(json.dumps(appconf, indent=4), file=f)


def usage():
    # usage: python3 redis_dump_acl.py panasonic appconfig/App2.json App2
    print('usage: {} <project_name> <json_file> <app_name>'.format(sys.argv[0]))

if __name__ == '__main__':
    if len(sys.argv) < 3:
        usage()
        sys.exit(1)

    # for debug purpose - create /tmp/logs
    log_dir = '/tmp/logs'
    directory = os.path.dirname(log_dir)
    if not os.path.exists(log_dir):
        os.makedirs(log_dir)
    from pathlib import Path
    fname = log_dir+'/pylib.log'
    Path(fname).touch()

    project_name = sys.argv[1]
    json_file = sys.argv[2]
    app_name = sys.argv[3]

    dump_acl_to_file(json_file)
