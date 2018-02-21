from pylib.RedisManager import RedisManager
import sys

def load_global_config(project_name, json_proj_file):
    """
    Supposed to be called only once at init
    Data integrity is contained within Redis
    """
    rm = RedisManager(None, None, json_proj_file=json_proj_file, project_name=project_name)

    rm.load_global_config()

def usage():
    print('usage: {} <project_name> <json_file>'.format(sys.argv[0]))

if __name__ == '__main__':
    if len(sys.argv) < 2:
        usage()
        sys.exit(1)

    project_name = sys.argv[1]
    json_file = sys.argv[2]
    load_global_config(project_name, json_file)

