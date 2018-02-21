import redis
import json
import os
import logging
import re
import sys
import traceback
import copy
from . import RotatingLogger
from .appconfig import LOGGER_NAME

RotatingLogger.setup_logger()
logger = logging.getLogger(LOGGER_NAME)

BASE_GROUP_ID_APPROVAL = os.environ['BASE_GROUP_ID_APPROVAL']
GLOBAL_CONFIG_FILENAME = '{}/globals.json'.format(os.environ['APP_CONFIG_DIR'])

class RedisManager:
    def __init__(self, plugin_name, json_app_file, json_proj_file=None, project_name=None, host='redis', port=6379, db=0):
        self.r = redis.StrictRedis(host=host, port=port, db=db)
        self.jsondata = self._load_config(json_proj_file, json_app_file)
        self.plugin_name = plugin_name
        self.project_name = project_name

    def get_inst(self):
        return self.r

    def _load_config(self, json_proj_file, json_app_file):
        global GLOBAL_CONFIG_FILENAME

        ret = {}
        with open(GLOBAL_CONFIG_FILENAME) as f:
            ret = json.load(f)
        if json_proj_file is not None:
            with open(json_proj_file) as f:
                ret.update(json.load(f))
        if json_app_file is not None:
            with open(json_app_file) as f:
                ret.update(json.load(f))
        return ret

    def _load_layer_aro(self, user_ids, layer_user_key):
        for u in user_ids:
            user_aro = 'User.' + str(u)
            self.r.rpush(layer_user_key, user_aro)

    def _load_layer_op(self, acl_ops, layer_op_key):
        for o in acl_ops:
            self.r.rpush(layer_op_key, o)

    def _load_layer(self, layer_root, layer_name, user_ids, acl_ops):
        layer_base_key = self.plugin_name + '_acl_layer_' + layer_root
        self.r.sadd(layer_base_key, layer_name)

        layer_user_key = self.plugin_name + '_acl_' + layer_name
        layer_op_key = self.plugin_name + '_acl_ops_' + layer_name
        self._load_layer_aro(user_ids, layer_user_key)
        self._load_layer_op(acl_ops, layer_op_key)

    def load_layers(self):
        """
        acl_base
        """
        logger.debug(
            'load_layers',
            extra=dict(function="load_layers")
        )
        for l in self.jsondata['layers']:
            self._load_layer(l['parent'], l['name'], [], l['op'])

    def _load_action(self, action_name, action_op):
        aco_name = 'Attr' + self.plugin_name.lower()
        aco_key = self.plugin_name + '_acl_action_acos'
        op_key = self.plugin_name + '_acl_action_ops'

        self.r.hset(aco_key, action_name, aco_name)
        self.r.hset(op_key, action_name, action_op)

    def load_actions(self):
        """
        acl_base
        """
        logger.debug(
            'load_actions',
            extra=dict(function="load_actions")
        )
        for a in self.jsondata['actions']:
            self._load_action(a['name'], a['op'])

    def _load_layer_user(self, layer, users):
        acl_layer_key = self.plugin_name + '_acl_' + layer
        for u_id in users:
            u_aro = 'User.' + u_id
            logger.debug(
                'layer_key,user={},{}'.format(acl_layer_key, u_aro),
                extra=dict(function="_load_layer_user")
            )
            self.r.rpush(acl_layer_key, u_aro)

    def load_layer_users(self):
        """
        app_config
        """
        logger.debug(
            'load_layer_users',
            extra=dict(function="load_layer_users")
        )
        users = self.jsondata['layer_users']
        for layer in users:
            self._load_layer_user(layer, users[layer])

    def _new_group_id(self):
        global BASE_GROUP_ID_APPROVAL
        group_dir_key = 'App_acl_groups'
        group_id = int(BASE_GROUP_ID_APPROVAL)
        for x in self.r.hscan_iter(group_dir_key, '*'):
            if int(x[0]) >= group_id:
                group_id = int(x[0]) + 1
        return group_id

    def _create_group(self, group_label, group_name, group_mgr, group_users):
        new_group_no = self._new_group_id()

        group_manager_key = 'App_acl_group' + str(new_group_no) + '_manager'
        group_member_key = 'App_acl_group' + str(new_group_no) + '_member'

        self.r.hset('App_acl_groups', new_group_no, group_name)

        # create group_to_label map
        group_to_label_key = 'App_acl_group_to_label'
        group_id = 'App_acl_group' + str(new_group_no)
        self.r.hset(group_to_label_key, group_id, group_label)

        #FIXME
        # current implementation uses single manager instance
        #for gn in group_mgr:
        aro = 'User.' + str(group_mgr)
        self.r.rpush(group_manager_key, aro)

        for un in group_users:
            aro = 'User.' + str(un)
            self.r.rpush(group_member_key, aro)

    def _load_post_action(self, action, post_action_name, group_notification, sw_post_script, sw_notification, group_mgr, group_users):
        pa_key = self.plugin_name + '_workflow_' + action
        pa_group_name = self.plugin_name + '_workflow_' + action + '_allowed_users'
        self.r.hset(pa_key, 'action', post_action_name)
        self.r.hset(pa_key, 'groupAllowedTo', pa_group_name)
        self.r.hset(pa_key, 'enablePostScript', sw_post_script)
        self.r.hset(pa_key, 'enableGroupNotification', sw_notification)
        if sw_notification.startswith('enable'):
            pa_notification_group_key = self.plugin_name + \
                '_workflow_' + action + '_notification_groups'
            self.r.lpush(pa_notification_group_key, group_notification)

        self._create_group(action, pa_group_name, group_mgr, group_users)

    def load_post_action_groups(self):
        """
        app_config
        """
        logger.debug(
            'load_post_action_groups',
            extra=dict(function="load_post_action_groups")
        )
        try:
            post_actions = self.jsondata['post_action_groups']
        except KeyError:
            logger.debug(
                'post_action not defined... skipping',
                extra=dict(function="load_post_action_groups")
            )
            return

        for pa in post_actions:
            pa_inst = post_actions[pa]
            group_name = pa_inst['group_name_for_notification']
            sw_notification = 'enable' if len(group_name)>0 else "disable"
            self._load_post_action(
                pa,
                pa_inst['post_action_name'],
                group_name,
                pa_inst['sw_post_script'],
                sw_notification,
                pa_inst['allowed_group_mgr'],
                pa_inst['allowed_group_users']
            )

    def _load_create_as(self, name, group_mgr, group_users):
        logger.debug(
            '{}, {}, {}'.format(name, group_mgr, group_users),
            extra=dict(function="_load_create_as")
        )
        op_group_name_base_key = self.plugin_name + '_workflow_create_as'
        op_group_name_spec_key = op_group_name_base_key + '_allowed_users'

        self.r.hset(op_group_name_base_key, 'mode', 'enable')
        self.r.hset(op_group_name_base_key, 'groupAllowedTo', op_group_name_spec_key)

        self._create_group(name, op_group_name_spec_key, group_mgr, group_users)

    def load_create_as_groups(self):
        """
        app_config
        """
        logger.debug(
            'load_create_as_groups',
            extra=dict(function="load_create_as_groups")
        )
        try:
            create_as_groups = self.jsondata['create_as_groups']
        except KeyError:
            logger.debug(
                'create_as not defined... skipping',
                extra=dict(function="load_create_as_groups")
            )
            return

        for ca in create_as_groups:
            ca_inst = create_as_groups[ca]
            self._load_create_as(
                ca,
                ca_inst['allowed_group_mgr'],
                ca_inst['allowed_group_users']
            )

    def _load_approval_op(self, name, sw_upper_to_lower, sw_lower_to_upper, group_mgr, group_users, sw_list_approver_from_lower):
        op_group_name_base_key = self.plugin_name + '_workflow_approval_op_as'
        op_group_name_spec_key = op_group_name_base_key + '_allowed_users'

        self.r.hset(op_group_name_base_key, 'fromUpper', sw_upper_to_lower)
        self.r.hset(op_group_name_base_key, 'fromLower', sw_lower_to_upper)
        self.r.hset(op_group_name_base_key, 'groupAllowedTo', op_group_name_spec_key)
        self.r.hset(op_group_name_base_key, 'listApproversFromLower', sw_list_approver_from_lower)

        self._create_group(name, op_group_name_spec_key, group_mgr, group_users)

    def load_approval_op_groups(self):
        """
        app_config
        """
        logger.debug(
            'load_approval_op_groups',
            extra=dict(function="load_approval_op_groups")
        )
        try:
            approval_op_groups = self.jsondata['approve_op_as_groups']
        except KeyError:
            logger.debug(
                'approval_op not defined... skipping',
                extra=dict(function="load_approval_op_groups")
            )
            return

        for ao in approval_op_groups:
            ao_inst = approval_op_groups[ao]
            self._load_approval_op(
                ao,
                ao_inst['sw_upper_to_lower'],
                ao_inst['sw_lower_to_upper'],
                ao_inst['allowed_group_mgr'],
                ao_inst['allowed_group_users'],
                ao_inst['sw_list_approver_from_lower']
            )

    def _load_group(self, gid, group_name, manager, users):
        self.r.hset('App_acl_groups', gid, group_name)

        # FIXME: manager is always given as single instance
        #for m in manager:
        g_key = 'App_acl_group' + gid + '_manager'
        aro = 'User.'+manager[0]
        self.r.rpush(g_key, aro)

        for u in users:
            g_key = 'App_acl_group' + gid + '_member'
            aro = 'User.'+u
            self.r.rpush(g_key, aro)

    def load_groups(self):
        """
        app_config
        """
        logger.debug(
            'load_groups',
            extra=dict(function="load_groupos")
        )

        try:
            groups = self.jsondata['groups']
        except KeyError:
            logger.debug(
                'groups not defined... skipping',
                extra=dict(function="load_groups")
            )
            return

        for g_inst in groups:
            self._load_group(
                g_inst['id'],
                g_inst['name'],
                g_inst['manager'],
                g_inst['users']
            )

    # added on Feb 25
    def _bulk_hset(self, h_key, pairs):
        """
        takes heap key and (subkey,value) pairs
        """
        for p in pairs:
            self.r.hset(h_key, p[0], p[1])

    def _load_json(self, func_name, json_key):
        ret = None
        logger.debug(
            '{}, jsondata={}'.format(func_name, self.jsondata),
            extra=dict(function=func_name)
        )

        #print(self.jsondata)
        try:
            ret = self.jsondata[json_key]
        except KeyError:
            logger.debug(
                "{} not defined... skipping".format(json_key),
                extra=dict(function=func_name)
            )
            return None

        return ret

    def load_app_base(self):
        self._load_app_core()
        self._load_app_mainview()
        self._load_app_searchview()
        self._load_app_searchexclude()
        self._load_app_notification()
        #self._load_app_db_visibility() -> merged to app_core
        self._load_app_field_calculated()
        self._load_app_validation_fields()
        self._load_app_hidden_fields()
        self._load_app_impexp_keys()

    def _load_app_core(self):
        """
        app_base - redis_create_app.sh, redis_config_report.sh,
                   redis_config_cols_security.sh
        """
        func_name = '_load_app_core'
        json_key = 'appbase'
        json_data = self._load_json(func_name, json_key)

        model_name = 'Attr{}'.format(self.plugin_name.lower())
        db_name = '{}s'.format(model_name.lower())
        read_prohibit_key = '{}_db_read_prohibited'.format(self.plugin_name)
        upload_ignore_key = '{}_upload_ignored'.format(self.plugin_name)
        self.r.hset(self.plugin_name, 'app_kind', json_data['app_kind'])
        self.r.hset(self.plugin_name, 'app_name', json_data['plugin_name'])
        self.r.hset(self.plugin_name, 'model_name', model_name)
        self.r.hset(self.plugin_name, 'db_name', db_name)
        self.r.hset(self.plugin_name, 'db_col_size', json_data['string_size'])
        self.r.hset(self.plugin_name, 'main_page', json_data['main_page'])
        #hidden (prohibited fields)
        self.r.hset(self.plugin_name, 'read_prohibited', read_prohibit_key)
        #db_ignored
        self.r.hset(self.plugin_name, 'db_ignore', upload_ignore_key)
        self.r.hset(self.plugin_name, 'import_calculated_columns', json_data['flag_import_to_ignored_fields'])

        #workflow specific
        if json_data['app_kind'].startswith('workflow'):
            wf_key = '{}_workflow'.format(self.plugin_name)
            wf_model_name = 'Wf{}'.format(self.plugin_name.lower())
            wf_db_name = '{}s'.format(wf_model_name.lower())
            comm_model_name = 'Comm{}'.format(self.plugin_name.lower())
            comm_db_name = '{}s'.format(comm_model_name.lower())
            self.r.hset(wf_key, 'model_name', wf_model_name)
            self.r.hset(wf_key, 'db_name', wf_db_name)
            self.r.hset(wf_key, 'comment_model_name', comm_model_name)
            self.r.hset(wf_key, 'comment_db_name', comm_db_name)

        #default configuration
        acl_key = '{}_acl'.format(self.plugin_name)
        op_key = '{}_op_label'.format(self.plugin_name)

        self.r.hset(acl_key, 'default_acl_aco', model_name)
        self.r.hset(acl_key, 'default_acl_op', 'read')

        self.r.hset(op_key, 'export', "Export Data(.xls)")
        self.r.hset(op_key, 'import_excel', "Import Data(.xls)")
        self.r.hset(op_key, 'import_timesheet', "Import Timesheet(.xls)")
        self.r.hset(op_key, 'import_target', "Import Target(.xls)")
        self.r.hset(op_key, 'create', "Create New Entry")
        self.r.hset(op_key, 'export_with_filter', "Export With Filter")

        #report
        self.r.hset(self.plugin_name, 'report_mode', json_data['sw_report'])

        #db_visibility
        # "readable_by_all" or "acl"
        self.r.hset(self.plugin_name, 'db_visibility', json_data['db_visibility'])

        #FIXME - set report url to report_url but ignored with current impl
        # each url has to be saved separately just like notification url
        # e.g. "sw_report_url": "url|https://www.briode.com/Generic/users/login",
        self.r.hset(self.plugin_name, 'report_url', json_data['sw_report_url'])


    def _load_app_mainview(self):
        """
        app_base  - redis_config_mainview.sh
        """
        func_name = 'load_app_mainview'
        json_key = 'appbase'
        data = self._load_json(func_name, json_key)

        #clear and push mainview columns
        mainview_list_key = '{}_mainview_list'.format(self.plugin_name)
        self.r.ltrim(mainview_list_key, 1, 0)
        for c in data['mainview_columns']:
            self.r.rpush(mainview_list_key, c)

        #clear and push mainview sort columns
        mainview_sort_col_key = '{}_mainview_sort_colname'.format(self.plugin_name)
        self.r.ltrim(mainview_sort_col_key, 1, 0)
        for c in data['mainview_sort_columns']:
            self.r.rpush(mainview_sort_col_key, c)

        #clear and push mainview sort order
        mainview_sort_order_key = '{}_mainview_sort_order'.format(self.plugin_name)
        self.r.ltrim(mainview_sort_order_key, 1, 0)
        for c in data['mainview_sort_order']:
            self.r.rpush(mainview_sort_order_key, c)


    def _load_app_searchview(self):
        """
        app_base  - redis_config_searchview.sh
        """
        func_name = 'load_app_searchview'
        json_key = 'appbase'
        data = self._load_json(func_name, json_key)

        searchview_key = '{}_searchview_list'.format(self.plugin_name)
        self.r.ltrim(searchview_key, 1, 0)
        for sc in data['searchview_columns']:
            self.r.rpush(searchview_key, sc)

    def _load_app_searchexclude(self):
        """
        app_base
        """
        func_name = 'load_app_searchview'
        json_key = 'appbase'
        data = self._load_json(func_name, json_key)

        searchexclude_key = '{}_searchexclude_column'.format(self.plugin_name)
        self.r.ltrim(searchexclude_key, 1, 0)
        json_key = 'searchexclude_columns'
        if json_key in data:
            for sc in data[json_key]:
                self.r.rpush(searchexclude_key, sc)


    def _load_app_notification(self):
        """
        app_base  - redis_config_notification.sh
        """
        func_name = 'load_app_notification'
        json_key = 'appbase'
        data = self._load_json(func_name, json_key)

        notification_key = '{}_notification'.format(self.plugin_name)
        self.r.hset(notification_key, 'email_mode', data['sw_notification'])
        self.r.hset(notification_key, 'email_address', data['notification_email'])

        notification_url_key = \
            '{}_notification_email_urls'.format(self.plugin_name)
        for label in data['notification_url']:
            url = data['notification_url'][label]
            self.r.hset(notification_url_key, label, url)


    def _load_app_field_calculated(self):  # config_js
        """
        app_base  - redis_config_js.sh
        """
        func_name = 'load_app_field_calculated'
        json_key = 'appbase'
        data = self._load_json(func_name, json_key)

        calc_field_key = '{}_upload_ignored'.format(self.plugin_name)
        for calc_f in data['calculated_fields']:
            self.r.sadd(calc_field_key, calc_f)


    def _load_app_validation_fields(self):
        """
        app_base - redis_validation_app*.sh
        """
        func_name = 'load_app_validation_fields'
        json_key = 'appbase'
        data = self._load_json(func_name, json_key)

        for c_name in data["validation_fields"]:
            #print('validation c_name:{}'.format(c_name))
            val_key = '{}_validate_cssclass_val{}'.format( \
                self.plugin_name, c_name )
            #print('validation field inst:{}'.format(data["validation_fields"][c_name]))
            for p_name in data["validation_fields"][c_name]:
                p_val = data["validation_fields"][c_name][p_name]
                self.r.hset(val_key, p_name, p_val)


    def _load_app_hidden_fields(self):
        """
        app_base  - redis_config_cols_security.sh
        """
        func_name = 'load_app_hidden_fields'
        json_key = 'appbase'
        data = self._load_json(func_name, json_key)

        read_prohibit_key = '{}_db_read_prohibited'.format(self.plugin_name)
        read_prohibit_set_key = '{}_rp_1'.format(self.plugin_name)
        model = 'Attr{}'.format(self.plugin_name.lower())
        read_prohibit_set_subkey = '{}/{}'.format(self.plugin_name, model)
        self.r.hset( \
            read_prohibit_key, \
            read_prohibit_set_subkey, \
            read_prohibit_set_key
        )
        for d in data["hidden_fields"]:
            self.r.sadd(read_prohibit_set_key, d)


    def _load_app_impexp_keys(self):
        """
        app_base  - redis_impexp_keys.sh
        """
        func_name = 'load_app_impexp_keys'
        json_key = 'appbase'
        data = self._load_json(func_name, json_key)

        # FIXME
        #  only one value set is tested with real deployment
        impexp_key = '{}_impexp_keys'.format(self.plugin_name)
        for ie_key in data['impexp_keys']:
            self.r.lpush(impexp_key, ie_key)


    def load_workflow_base(self):
        """
        workflowappbase  - redis_config_workflow.sh
        """

        func_name = 'load_workflow_base'
        json_key = 'workflowappbase'
        data = self._load_json(func_name, json_key)

        # return if workflow is undefined (=data is None)
        if data is None:
            return

        #traceback.print_exc(file=sys.stdout)

        logger.debug(
            '##data keys={}'.format(self.jsondata.keys()),
            extra=dict(function="load_workflow_base")
        )
        logger.debug(
            '##workflow  data:{}'.format(data),
            extra=dict(function="load_workflow_base")
        )

        workflow_key = '{}_workflow'.format(self.plugin_name)
        self.r.hset(workflow_key, 'email_mode', data['sw_email'])
        self.r.hset(workflow_key, 'single_excel_up_down', data['sw_excel_imp_exp'])
        self.r.hset(workflow_key, 'assignee_at_approve', data['assignee_at_approve'])

        workflow_url_key = '{}_workflow_email_urls'.format(self.plugin_name)
        for label in data['forward_url']:
            url = data['forward_url'][label]
            self.r.hset(workflow_url_key, label, url)
            logger.debug(
                'load_workflow: {},{}'.format(workflow_url_key, url),
                extra=dict(function="load_workflow_base")
            )

        email_params = data['email_params']
        if len(email_params) > 0:
            email_param_key = '{}_workflow_email'.format(self.plugin_name)
            self.r.hset(email_param_key, 'subject_tag_column', email_params['subject_tag_column'])

        # report with date filter
        report_params = data['report_params']
        if len(report_params) > 0:
            report_cols_key = '{}_workflow_report_columns'.format(self.plugin_name)
            for c in report_params['columns']:
                self.r.rpush(report_cols_key, c)

            report_param_key = '{}_workflow_report_params'.format(self.plugin_name)
            self.r.hset(report_param_key, 'form_id_from', report_params['form_from_name'])
            self.r.hset(report_param_key, 'form_id_to', report_params['form_to_name'])
            self.r.hset(report_param_key, 'filter_column', report_params['filter_column'])

        # default notification email (approve) recipients
        notifiers_at_approval = data['notifier_at_approve']
        if len(notifiers_at_approval) > 0:
            notifier_key = '{}_workflow_notifier_at_approve'.format(self.plugin_name)
            for e in notifiers_at_approval['emails']:
                self.r.rpush(notifier_key, e)

    def load_proj_config(self):
        """
        project - 
            redis_setup_selector_*.sh
            redis_app_base_uri.sh
        """
        func_name = 'load_proj_config'
        json_key = 'projectbase'
        data = self._load_json(func_name, json_key)

        # return if project is not set
        if self.project_name is None:
            logger.debug(
                '*Project is not set, test not executed',
                extra=dict(function="load_proj_config")
            )
            return

        # clear selector before load - redis_clear_selectors.sh
        key_selectors = ['App_list_urls', 'App_list_labels', 'App_navname_labels']
        for k in key_selectors:
            self.r.delete(k)

        # redis_set_labels.sh
        url_key = 'App_list_urls'
        label_key = 'App_list_labels'
        navname_key = 'App_navname_labels'
        appname_key = 'App_name_to_plugin'
        logger.debug(
            '>>>loading data: {}'.format(data),
            extra=dict(function="load_proj_config")
        )
        for plugin_name in data['plugins']:
            plugin_key_url = "{}/main_menu".format(plugin_name)
            self.r.rpush(url_key, plugin_key_url)

            list_name = data['plugins'][plugin_name]['list']
            navbar_name = data['plugins'][plugin_name]['navbar']
            self.r.hset(label_key, plugin_key_url, list_name)
            self.r.hset(navname_key, plugin_name, navbar_name)
            self.r.hset(appname_key, navbar_name, plugin_name)

        # redis_set_initial_plugin.sh
        initial_plugin_key = 'App_initial_plugin'
        self.r.hset(initial_plugin_key, 'default', data['initial_plugin'])

        # redis_app_base_url.sh
        baseurl_key = 'App_base_url'
        self.r.hset(baseurl_key, 'external', data['ext_base_url'])
        if len(data['int_base_url']) > 0:
            self.r.hset(baseurl_key, 'internal', data['int_base_url'])

    def load_project_param(self):
        """
        projectparams
        """
        # clear selector before load
        param_key = 'App_project_params'
        self.r.delete(param_key)

        func_name = 'load_project_param'
        json_key = 'projectparams'
        data = self._load_json(func_name, json_key)
        print(data)
        if data is None:
            return
        print(data)
        for param_name in data:
            self.r.hset(param_key, param_name, data[param_name])

    def load_global_config(self):
        """
        appconfig/globals.json
          "lists" is the only parameters for globa usage
        """
        # clear selector before load - redis_clear_selectors.sh
        for k in self.r.keys('App_ui_options*'):
            self.r.delete(k)

        func_name = 'load_global_config'
        json_key = 'globals'
        data = self._load_json(func_name, json_key)
        for list_name in data['lists']:
            option_key = 'App_ui_options'
            list_key = 'App_ui_options_{}'.format(list_name)
            self.r.hset(option_key, list_name, list_key)
            for o in data['lists'][list_name]:
                self.r.rpush(list_key, o)

        for enum_kind_name in data['enums']:
            enum_key = 'App_enums'
            enum_kind_key = 'App_enum_{}'.format(enum_kind_name)
            self.r.hset(enum_key, enum_kind_name, enum_kind_key)
            for e_val in data['enums'][enum_kind_name]:
                e_str = data['enums'][enum_kind_name][e_val]
                self.r.hset(enum_kind_key, e_val, e_str)

        # FIXME
        # somehow user profile config is hardcoded
        # redis_config_user_profile.sh
        self.r.hset('App_user_profile', 'picture', 'disable')

    def dump_plugins_for_project(self, project_name):
        """
        dump list of plugins concatenated by space
        """
        func_name = 'dump_plugins_for_project'
        json_key = 'projectbase'
        data = self._load_json(func_name, json_key)

        # print('data={}'.format(data))
        # print
        return ' '.join(data['plugins'])

    def get_layer_users(self, app_name):
        """
        app_name : AppXXX
        :return: 
        """
        list_container = {}
        layer_names = ['admin', 'approver', 'controller', 'manager', 'user']
        for layer in layer_names:
            list_key = '{}_acl_{}'.format(app_name, layer)
            users = [x.decode('ascii').split('.')[-1] for x in self.r.lrange(list_key, 0, -1)]
            list_container[layer] = copy.deepcopy(users)

        return dict(layer_users=list_container)

    def get_groups(self, app_name):
        """
        acl stores 
        :return: 
        """

        groups_container = {}

        def post_action():
            region = group_name.split('_')[-1]
            notification_key = '{}_workflow_post_approve_notification_{}'.format(app_name, region)
            group_inst = {
                group_name: dict(
                    post_action_name='review',
                    sw_post_script='enable',
                    group_name_for_notification=notification_key,
                    allowed_group_mgr=manager,
                    allowed_group_users=members
                )
            }
            post_actions = groups_container.setdefault('post_action_groups', dict())
            post_actions.update(group_inst)

        def create_as():
            group_inst = {
                group_name: dict(
                    allowed_group_mgr=manager,
                    allowed_group_users=members
                )
            }
            create_ases = groups_container.setdefault('create_as_groups', dict())
            create_ases.update(group_inst)

        def approve_op_as():
            group_inst = {
                group_name: dict(
                    sw_upper_to_lower='enable',
                    sw_lower_to_upper='disable',
                    sw_list_approver_from_lower='disable',
                    allowed_group_mgr=manager,
                    allowed_group_users=members
                )
            }
            approve_op_ases = groups_container.setdefault('approve_op_as_groups', dict())
            approve_op_ases.update(group_inst)

        group_label_map = self.r.hgetall('App_acl_group_to_label')
        for group_key in sorted(group_label_map.keys()):
            group_name = group_label_map[group_key].decode('ascii')
            group_id = group_key.decode('ascii').split('group')[-1]
            manager_key = 'App_acl_group{}_manager'.format(group_id)
            members_key = 'App_acl_group{}_member'.format(group_id)
            manager = self.r.lrange(manager_key, 0, -1)[0].decode('ascii').split('.')[-1]
            members = [x.decode('ascii').split('.')[-1] for x in self.r.lrange(members_key, 0, -1)]

            func_dispatch = approve_op_as
            if group_name.startswith('post_'):
                func_dispatch = post_action
            elif group_name.startswith('group-'):
                func_dispatch = create_as

            func_dispatch()

        return groups_container
