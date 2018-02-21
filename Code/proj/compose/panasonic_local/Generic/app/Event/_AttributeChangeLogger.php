<?php
App::uses('CakeEventListener', 'Event');

class AttributeChangeLogger implements CakeEventListener {

    public function implementedEvents() {
        return array(
/*__ADD_ATTRIBUTE_HERE__*/
        );
    }

    public function subject_changed($event) {
        //$this->log('AttributeChangeLogger::subjectChanged', 'debug');
        $this->AttributeEventLog = ClassRegistry::init('AttributeEventLog');

        $this->AttributeEventLog->set(array(
            'subject_id'      => $event->data['id'],
            'update_time'     => $event->data['updated_at'],
            'updated_by'      => $event->data['updator_id'],
            'additional_text' => $event->data['additional_text'],
            'plugin_name'     => $event->data['plugin_name'],
        ));
        $this->AttributeEventLog->save();
    }
}
