from pylib.RedisManager import RedisManager
import sys

def load_app_config(plugin_name, json_app_file):
    """
    Supposed to be called only once at init
    Data integrity is contained within Redis

     TODO
        user aro should be managed dynamically only in Redis
        ACL aro only provides layer structure
    """
    rm = RedisManager(plugin_name, json_app_file)

    rm.load_layer_users()
    rm.load_post_action_groups()
    rm.load_approval_op_groups()
    rm.load_create_as_groups()
    rm.load_groups()

def usage():
    print('usage: {} <plugin_name> <json_file>'.format(sys.argv[0]))

if __name__ == '__main__':
    if len(sys.argv) < 2:
        usage()
        sys.exit(1)

    plugin_name = sys.argv[1]
    json_file = sys.argv[2]
    load_app_config(plugin_name, json_file)

