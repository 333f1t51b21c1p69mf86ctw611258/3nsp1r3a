"""
Not tested: 
    App_acl_group_to_label
"""
from unittest import TestCase
from pylib.RedisManager import RedisManager
import os
import redis
import json
import copy

class TestRedisManager(TestCase):
    def setUp(self):
        self.plugin_name = 'AppTest'
        self.proj_name = 'test'
        self.json_globals_file = '{}/appconfig/globals.json'.format(os.getcwd())
        self.json_proj_file = '{}/projects/{}.json'.format(os.getcwd(), self.proj_name)
        self.json_app_file = '{}/appconfig/{}.json'.format(os.getcwd(), self.plugin_name)
        self.rm = RedisManager( \
            self.plugin_name, \
            self.json_app_file, \
            json_proj_file=self.json_proj_file, \
            project_name="test" \
        )
        self.r = self.rm.get_inst()
        self.json = self._load_json()
        self._delete_keys()

    def _load_json(self):
        ret = {}
        with open(self.json_globals_file) as f:
            ret = json.load(f)
        with open(self.json_proj_file) as f:
            ret.update(json.load(f))
        with open(self.json_app_file) as f:
            ret.update(json.load(f))
        return ret

    def _delete_keys(self):
        key_ptn = '{}_*'.format(self.plugin_name)

        keys = self.r.keys(key_ptn)
        #print('cleaning up following keys...{}'.format(keys))
        
        for k in keys:
            self.r.delete(k)

        keys = self.r.keys(key_ptn)
        print('Redis cleared for plugin{}, keys found...{}'.format(self.plugin_name, keys))

    def tearDown(self):
        pass

    def _add_validation(self, redis, json):
        for f in json:
            key = '{}_validate_cssclass_val{}'.format(self.plugin_name, f)
            redis[key] = copy.deepcopy(json[f])
        
    def _json_to_redis(self, json_key):
        print(self.json.keys())
        json_sub = self.json[json_key]
        redis = {}

        if json_key.startswith("appbase"):
            sub_redis = {}
            app_kind = json_sub['app_kind']
            sub_redis['app_name'] = json_sub['plugin_name']
            sub_redis['app_kind'] = app_kind
            if app_kind.startswith('db'):
                model_name = 'Attr{}'.format(self.plugin_name.lower())
                db_name = '{}s'.format(model_name.lower())
                sub_redis['model_name'] = model_name
                sub_redis['db_name'] = db_name
            elif app_kind.startswith('workflow'):
                model_name = 'Wf{}'.format(self.plugin_name.lower())
                db_name = '{}s'.format(model_name.lower())
                com_model_name = 'Comm{}'.format(self.plugin_name.lower())
                com_db_name = '{}s'.format(model_name.lower())
                sub_redis['model_name'] = model_name
                sub_redis['db_name'] = db_name
                sub_redis['comment_model_name'] = com_model_name
                sub_redis['comment_db_name'] = com_db_name

            sub_redis['db_col_size'] = json_sub['string_size']
            sub_redis['main_page'] = json_sub['main_page']
            # redis_config_db_visibility.sh
            sub_redis['db_visibility'] = json_sub['db_visibility']
            # redis_config_js.sh(2)
            sub_redis['db_ignore'] = '{}_upload_ignored'.format(self.plugin_name)
            sub_redis['import_calculated_columns'] = json_sub['flag_import_to_ignored_fields']
            # redis_config_cols_security.sh(2)
            sub_redis['read_prohibited'] = '{}_db_read_prohibited'.format(self.plugin_name)
            redis[self.plugin_name] = copy.deepcopy(sub_redis)

            sub_redis = {}
            sub_redis['default_acl_aco'] = model_name
            sub_redis['default_acl_op'] = 'read'
            acl_key = '{}_acl'.format(self.plugin_name)
            redis[acl_key] = copy.deepcopy(sub_redis)

            sub_redis = {}
            sub_redis['export'] = 'Export Data(.xls)'
            sub_redis['import_excel'] = 'Import Data(.xls)'
            sub_redis['import_timesheet'] = 'Import Timesheet(.xls)'
            sub_redis['import_target'] = 'Import Target(.xls)'
            sub_redis['create'] = 'Create New Entry'
            op_key = '{}_op_label'.format(self.plugin_name)
            redis[op_key] = copy.deepcopy(sub_redis)

            # redis_config_mainview.sh
            #sub_redis = {}
            key_mainview_list = '{}_mainview_list'.format(self.plugin_name)
            key_mainview_sort_colname = '{}_mainview_sort_colname'.format(self.plugin_name)
            key_mainview_sort_order = '{}_mainview_sort_order'.format(self.plugin_name)
            redis[key_mainview_list] = json_sub['mainview_columns']
            redis[key_mainview_sort_colname] = json_sub['mainview_sort_columns']
            redis[key_mainview_sort_order] = json_sub['mainview_sort_order']

            # redis_config_searchview.sh
            key_searchview_list = '{}_searchview_list'.format(self.plugin_name)
            redis[key_searchview_list] = json_sub['searchview_columns']

            # search exclude
            key_searchexclude_column = '{}_searchexclude_column'.format(self.plugin_name)
            redis[key_searchexclude_column] = json_sub['searchexclude_columns']

            # redis_config_notification.sh
            sub_redis = {}
            sub_redis['email_mode'] = json_sub['sw_notification']
            sub_redis['email_address'] = json_sub['notification_email']
            key_notification = '{}_notification'.format(self.plugin_name)
            redis[key_notification] = sub_redis
            import re
            sub_redis = {}
            for label in json_sub['notification_url']:
                sub_redis[label] = json_sub['notification_url'][label]
            key_notification_email_url = \
                '{}_notification_email_urls'.format(self.plugin_name)
            redis[key_notification_email_url] = sub_redis
            
            # redis_config_js.sh(2)
            key_col_ignored = '{}_upload_ignored'.format(self.plugin_name)
            redis[key_col_ignored] = json_sub['calculated_fields']

            # redis_validation_app*.sh
            self._add_validation(redis, json_sub['validation_fields'])

            # redis_config_cols_security.sh(2)
            key_read_prohibited = '{}_db_read_prohibited'.format(self.plugin_name)
            sub_redis = {}
            rp_key = '{}/Attr{}'.format(self.plugin_name, self.plugin_name.lower())
            key_read_prohibited_set = '{}_rp_1'.format(self.plugin_name)
            sub_redis[rp_key] = key_read_prohibited_set
            redis[key_read_prohibited] = sub_redis
             
            # redis_impexp_keys.sh
            key_impexp_keys = '{}_impexp_keys'.format(self.plugin_name)
            redis[key_impexp_keys] = json_sub['impexp_keys']

        elif json_key.startswith('workflowappbase'):
            key_workflowappbase = "{}_workflow".format(self.plugin_name)
            sub_redis = {}
            sub_redis['email_mode'] = json_sub['sw_email']
            sub_redis['single_excel_up_down'] = json_sub['sw_excel_imp_exp']
            sub_redis['assignee_at_approve'] = json_sub['assignee_at_approve']
            redis[key_workflowappbase] = copy.deepcopy(sub_redis)

            if json_sub['email_params'] is not None:
                sub_redis = {}
                sub_redis['subject_tag_column'] = json_sub['email_params']['subject_tag_column']
                key_email_params = '{}_workflow_email'.format(self.plugin_name)
                redis[key_email_params] = copy.deepcopy(sub_redis)
            key_workflow_url = '{}_workflow_email_urls'.format(self.plugin_name)
            sub_redis = dict(
                external_baseurl = "https://testsite/testcontroller"
            )
            redis[key_workflow_url] = copy.deepcopy(sub_redis)

            """
            "report_params": {
                "columns": [
                    "report_col1",
                    "report_col2"
                ],
                "form_from_name": "ts_from",
                "form_to_name": "ts_to",
                "filter_column": "Date"
            }
            """
            key_workflow_report_columns = \
                "{}_workflow_report_columns".format(self.plugin_name)
            redis[key_workflow_report_columns] = \
                json_sub['report_params']['columns']
            
            key_workflow_report_params = \
                "{}_workflow_report_params".format(self.plugin_name)
            sub_redis = dict(
                form_id_from = json_sub['report_params']['form_from_name'],
                form_id_to = json_sub['report_params']['form_to_name'],
                filter_column = json_sub['report_params']['filter_column']
            )
            redis[key_workflow_report_params] = sub_redis

            key_workflow_notifier_at_approvals = \
                "{}_workflow_notifier_at_approve".format(self.plugin_name)
            redis[key_workflow_notifier_at_approvals] = \
                json_sub['notifier_at_approve']['emails']
        
        elif json_key.startswith('projectbase'):
            """
            "plugins" : {
                "App2" : {
                    "list" : "Win-Win",
                    "navbar" : "Win-Win"
                },
                "App32" : {
                    "list" : "Price List",
                    "navbar" : "Price List"
                }
            },
            "initial_plugin": "App2",
            "ext_base_url": "https://panasonic.briode.com",
            "int_base_url": "",
            "post_script" : ""
            """
            key_list_url = "App_list_urls"
            key_list_label = "App_list_labels"
            key_navname_label = "App_navname_labels"
            key_name_to_plugin = "App_name_to_plugin"
            redis[key_list_url] = []
            redis[key_list_label] = {}
            redis[key_navname_label] = {}
            redis[key_name_to_plugin] = {}
            for plugin in json_sub['plugins']:
                # redis_setup_selector_*.sh
                plugin_inst = json_sub['plugins'][plugin]
                plugin_list = plugin_inst['list']
                plugin_navbar = plugin_inst['navbar']

                plugin_key = "{}/main_menu".format(plugin)
                redis[key_list_url].append(plugin_key)

                sub_redis = {}
                sub_redis[plugin_key] = plugin_list
                redis[key_list_label].update(copy.deepcopy(sub_redis))

                sub_redis = {}
                sub_redis[plugin] = plugin_navbar
                redis[key_navname_label].update(copy.deepcopy(sub_redis))

                sub_redis = {}
                sub_redis[plugin_navbar] = plugin
                redis[key_name_to_plugin].update(copy.deepcopy(sub_redis))

            key_initial_plugin = 'App_initial_plugin'
            sub_redis = {}
            sub_redis['default'] = json_sub['initial_plugin']
            redis[key_initial_plugin] = copy.deepcopy(sub_redis)

            # redis_app_base_uri.sh
            sub_redis = {}
            sub_redis['external'] = json_sub['ext_base_url']
            if len(json_sub['int_base_url']) > 0:
                sub_redis['internal'] = json_sub['int_base_url']
            redis['App_base_url'] = copy.deepcopy(sub_redis)

        elif json_key.startswith('globals'):
            option_key = 'App_ui_options'
            redis[option_key] = {}
            for list_name in json_sub['lists']:
                list_key = 'App_ui_options_{}'.format(list_name)
                sub_redis = {}
                sub_redis[list_name] = list_key
                redis[option_key].update(copy.deepcopy(sub_redis))
                redis[list_key] = []
                for opt in json_sub['lists'][list_name]:
                    redis[list_key].append(opt)

            enum_key = 'App_enums'
            redis[enum_key] = {}
            for enum_kind_name in json_sub['enums']:
                enum_kind_key = 'App_enum_{}'.format(enum_kind_name)
                sub_redis = {}
                sub_redis[enum_kind_name] = enum_kind_key
                redis[enum_key].update(copy.deepcopy(sub_redis))
                redis[enum_kind_key] = copy.deepcopy(json_sub['enums'][enum_kind_name])

            # user profile
            sub_redis = {}
            sub_redis['picture'] = 'disable'
            redis['App_user_profile'] = copy.deepcopy(sub_redis)
            
        return redis  #expected values

    def _assert_comp(self, observed, expected):
        self.assertEqual(observed, expected, 'Incorrect Return Value')

    def _comp_json_vs_hash(self, r_exp, redis_key, redis_kv):
        for sub_k in redis_kv:
            #print('redis_kv,sub_k={},{}'.format(redis_kv, sub_k))
            print(' checking subkey={}'.format(sub_k))
            r_real = self.r.hget(redis_key, sub_k)
            #print(' r_real={}'.format(r_real))
            if r_real is not None:
                r_real = r_real.decode('ascii')
            #print('r_real={}'.format(r_real))
            r_exp = redis_kv[sub_k]
            if r_real != r_exp:
                # expected key not found
                print(' subkey {} does not exist'.format(sub_k))
                return False
        return True

    def _comp_json_vs_set(self, r_exp, redis_key, redis_val):
        print('comp:redis_key={}'.format(redis_key))
        r_real = None
        try:
            r_real = self.r.lrange(redis_key, 0, -1)
            if len(r_real) == 0:
                print('key {} not found in redis'.format(redis_key))
                return 
        except redis.exceptions.ResponseError:
            # lrange against list
            r_real = list(self.r.smembers(redis_key))

        try:
            r_real = [x.decode('ascii') for x in r_real]
        except UnicodeDecodeError:
            print ('***UnicodeDecodeError:r_real={}'.format(r_real))
            r_real = None

        #print('comparing list:{}, {}'.format(r_real, redis_val))
        self._assert_comp(sorted(redis_val), sorted(r_real))

    def _comp_json_vs_list_or_set(self, r_exp, redis_key, redis_val):
        self._comp_json_vs_set(r_exp, redis_key, redis_val)

    def _run_test(self, expected):
        #print('redis expected:{}'.format(r_exp))
        for r_key in expected:
            print('Key to test={}'.format(r_key))
            print('r_key,type={},{}'.format(r_key, type(expected[r_key])))
            #hash
            if type(expected[r_key])==dict:
                self.assertTrue(self._comp_json_vs_hash(expected, r_key, expected[r_key]))
            #list or set
            if type(expected[r_key])==list:
                self._comp_json_vs_list_or_set(expected, r_key, expected[r_key])

    def test_load_app_base(self):
        self.rm.load_app_base()
        #print('json loaded:{}'.format(self.json))
        r_exp = self._json_to_redis("appbase")
        print('##### Testing appbase...')
        self._run_test(r_exp)

    def test_load_workflow_app_base(self):
        self.rm.load_workflow_base()
        #print('json loaded:{}'.format(self.json))
        r_exp = self._json_to_redis("workflowappbase")
        print('##### Testing workflowappbase...')
        self._run_test(r_exp)

    def test_load_project_config(self):
        self.rm.load_proj_config()
        r_exp = self._json_to_redis("projectbase")
        print('##### Testing project load...')
        print('r_exp={}'.format(r_exp))
        self._run_test(r_exp)

    def test_load_global_config(self):
        self.rm.load_global_config()
        r_exp = self._json_to_redis("globals")
        print('##### Testing global load...')
        self._run_test(r_exp)

if __name__ == '__main__':
    unittest.main()

