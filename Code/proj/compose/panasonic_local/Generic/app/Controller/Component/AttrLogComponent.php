<?php
App::uses('Component', 'Controller');

class AttrLogComponent extends Component {

    public function get_attr_logs($pluginName, $form_id){
        $attrEventModel = ClassRegistry::init('AttributeEventLog');
        $ret = $attrEventModel->find('all', array(
            'conditions' => array(
                        'AttributeEventLog.subject_id'=>$form_id,
                        'AttributeEventLog.plugin_name'=>$pluginName)
        ));
        if( isset($ret) ){
            return $ret;
        }
        return NULL;
    }

    public function delete_attr_logs($pluginName, $form_id){
        $attrEventModel = ClassRegistry::init('AttributeEventLog');
        $logs = $attrEventModel->find('all', array(
            'conditions' => array(
                        'AttributeEventLog.subject_id'=>$form_id,
                        'AttributeEventLog.plugin_name'=>$pluginName)
        ));

        foreach ($logs as $l) {
            $attrEventModel->delete($l['AttributeEventLog']['id']);
        }
    }
}
