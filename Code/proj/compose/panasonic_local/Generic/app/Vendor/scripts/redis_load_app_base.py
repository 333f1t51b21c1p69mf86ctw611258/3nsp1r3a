from pylib.RedisManager import RedisManager
import sys

def load_app_base(plugin_name, json_file):
    """
    Supposed to be called only once at init
    These are data that will not change over time 
    """
    rm = RedisManager(plugin_name, json_file)

    rm.load_app_base()
    #rm.load_app_mainview()
    #rm.load_app_searchview()
    #rm.load_app_notification()
    #rm.load_app_db_visibility()         # config_db_visibility
    #rm.load_app_field_calculated()      # config_js
    #rm.load_app_validation_fields()     # config_cols_security
    #rm.load_app_hidden_fields()
    #rm.load_app_impexp_keys()
    rm.load_workflow_base()

def usage():
    print('usage: {} <plugin_name> <json_file>'.format(sys.argv[0]))

if __name__ == '__main__':
    if len(sys.argv) < 2:
        usage()
        sys.exit(1)

    plugin_name = sys.argv[1]
    json_file = sys.argv[2]
    load_app_base(plugin_name, json_file)

