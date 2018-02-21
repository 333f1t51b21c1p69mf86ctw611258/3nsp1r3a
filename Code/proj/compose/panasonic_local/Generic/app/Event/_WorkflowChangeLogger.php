<?php
App::uses('CakeEventListener', 'Event');

class WorkflowChangeLogger implements CakeEventListener {

    public function implementedEvents() {
        return array(
/*__ADD_WORKFLOW_HERE__*/
        );
    }

    public function subject_changed($event) {
        // get user email address 
        $username = $event->data['username'];

        // post events
        $this->WorkflowEventLog = ClassRegistry::init('WorkflowEventLog');
        
        $this->WorkflowEventLog->set(array(
            'subject_id'      => $event->data['subject_id'],
            'updated_at'      => $event->data['updated_at'],
            'state'           => $event->data['state'],
            'state_new'       => $event->data['state_new'],
            'action'          => $event->data['action'],
            'case_state'      => $event->data['case_state'],
            'case_state_text' => $event->data['case_state_text'],
            'username'        => $username,
            'additional_text' => $event->data['additional_text'],
            'plugin_name'     => $event->data['plugin_name'],
            'assignee'        => $event->data['assignee'],
            'prev_assignee'   => $event->data['prev_assignee'],
            'nth'             => $event->data['nth'],
        ));
        $this->WorkflowEventLog->save();
    }
}
