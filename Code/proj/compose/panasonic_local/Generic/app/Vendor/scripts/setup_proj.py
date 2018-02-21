"""
Setup projects by doing the following:

 Load project config from project.json
    redis_load_proj_config.sh <proj>

 Load App config from project.json 
    iterate thru redis_load_app_config.sh <plugin_name>

 Generate application dirs from project.json
    iterate thru gen_app.sh <app_no>

 Call postdeploy scripts if it's given
    appconfig/postdeploy_<plugin_name>.sh

"""
import json
import os
import sys
import re
from subprocess import call
from pylib import RedisManager

APP_CONFIG_DIR = os.environ['APP_CONFIG_DIR']
PROJ_CONFIG_DIR = os.environ['PROJ_CONFIG_DIR']

def setup_proj(proj_name):
    global APP_CONFIG_DIR

    proj_file = "{}/{}.json".format(PROJ_CONFIG_DIR, proj_name)
    proj_inst = None
    with open(proj_file) as f:
        proj_inst = json.load(f)["projectbase"]

    # cleanup redis before loading globals
    call(['bash', 'redis_clear.sh'])

    # Load global and project config from globals.json and project.json
    call(['bash', 'redis_load_proj_config.sh', proj_name])

    # Load App config from project.json 
    #  iterate thru redis_load_app_config.sh <plugin_name>
    print('proj_inst={}'.format(proj_inst))
    for plugin_name in proj_inst['plugins']:
        call(['bash', 'redis_load_app_base.sh', plugin_name])
        call(['bash', 'redis_load_app_config.sh', plugin_name])

    # Load App config from project.json 
    #  iterate thru gen_app.sh
    for plugin_name in proj_inst['plugins']:
        matched = re.match('App(.*)', plugin_name)
        plugin_app_no = matched.group(1)
        call(['bash', 'gen_app.sh', plugin_app_no])

    # Call postdeploy scripts if it's given
    #  relative to script directory
    #   e.g. projects/script.sh
    if len(proj_inst['post_script']) > 0:
        call(['bash', proj_inst['post_script']])

def usage():
    print('Usage: {} <project_name>'.format(sys.argv[0]))

if __name__ == '__main__':
    if len(sys.argv) < 2:
        usage()
        sys.exit(1)
    project_name = sys.argv[1]
    setup_proj(project_name)
