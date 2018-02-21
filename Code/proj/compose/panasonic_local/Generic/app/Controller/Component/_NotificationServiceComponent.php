<?php
App::uses('CakeEventListener', 'Event');
App::uses('CakeEvent', 'Event');

class NotificationServiceComponent extends Component implements CakeEventListener {
    var $controller;

    public function initialize(Controller $controller) {
        $this->controller = $controller;
    }

    public function startup(Controller $controller) {
        parent::startup($controller);
        $attr_log = ClassRegistry::init('AttributeEventLog');
        $attr_log->getEventManager()->attach($this);
    }

    public function implementedEvents() {
        return array(
/*__ADD_ATTRIBUTE_HERE__*/
        );
    }

    public function subject_changed($event){
        $this->controller->sendUpdateNotification($event->data);
    }
}


