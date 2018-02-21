from pylib.RedisManager import RedisManager
import sys

def load_acl_base(plugin_name, json_file):
    """
    Data affects ACL structure - cake acl command required

    TODO:
        Remove per-user aro management
        User instance will be managed as Redis entries
    """
    rm = RedisManager(plugin_name, json_file)

    rm.load_layers()
    rm.load_actions()

def usage():
    print('usage: {} <plugin_name> <json_file>'.format(sys.argv[0]))

if __name__ == '__main__':
    if len(sys.argv) < 2:
        usage()
        sys.exit(1)

    plugin_name = sys.argv[1]
    json_file = sys.argv[2]
    load_acl_base(plugin_name, json_file)

