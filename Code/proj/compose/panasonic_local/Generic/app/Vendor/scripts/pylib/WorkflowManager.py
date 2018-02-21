
class RedisManager(object):
    def __init__(self, app_name):
        self.app_name = app_name

        self.post_approve = {}
        self.create_as_groups = []

    def load(self):
        """
        Read configuration from Redis
            # User configuration
            post_approve
            create_as_group

            # Implement later
            # App configuration
            #  - Workflow
            config_roles
            config_roles_members
            config_states
            config_actions

            #  - Layer
            config_layers
        """
        pass

    def save(self):
        """
        Dump a sequence of Redis update scripts

        Dump XML presentation of each configuration
            
        """
        pass
