import sys
from pylib.RedisManager import RedisManager

def dump_plugins(project_name, json_proj_file):
    rm = RedisManager(None, None, json_proj_file=json_proj_file, project_name=project_name)
    print(rm.dump_plugins_for_project(project_name), file=sys.stdout)

def usage():
    print('usage: {} <project_name> <json_file>'.format(sys.argv[0]))

if __name__ == '__main__':
    if len(sys.argv) < 2:
        usage()
        sys.exit(1)

    project_name = sys.argv[1]
    json_file = sys.argv[2]

    dump_plugins(project_name, json_file)
