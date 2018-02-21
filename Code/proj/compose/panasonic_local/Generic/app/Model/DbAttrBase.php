<?php
App::uses('AppModel', 'Model');
App::uses('CakeEvent', 'Event');
App::uses('AttributeEventLog', 'Event');
 
class DbAttrBase extends AppModel {
    public $useDbConfig = 'genericdata';

    protected function get_diff(){
        $ret = null;
        $this->recursive = -1;
        $this->old = $this->findById($this->id);
        // $changed_fields = array();
        if ($this->old){
            foreach ($this->data[$this->alias] as $key =>$value) {
                $this->log('key:old/new='.$key.':'.$this->old[$this->alias][$key].','.$value, 'debug');
                // FIXME infinity, updated_at ignored
                if ( !strcmp($value, '-Infinity')==0 &&
                     !strcmp($key, 'updated_at')==0 &&
                     $this->old[$this->alias][$key] != $value) {
                    //array_push($changed_fields, $key);
                    $ret .= $key .', ';
                }
            }
        }

        if( !isset($ret) ) return null;
        return "updated attrs: ". $ret;
    }
    protected function get_search_id_constraint(){
        if( isset($this->belongsTo) ){
            foreach( $this->belongsTo as $k=>$v ){
                return $k.'.id = '. $this->alias.'.'.strtolower($k).'_id';
                break;
            }
        }
        else{
            return NULL;
        }
    }
    protected function get_filter_condition($data, $keyword){
        $like_condition = array();
        foreach( array_keys($this->getColumnTypes()) as $col ){
            $key = $this->alias. '.'.$col.' LIKE';
            $value = '%'. $keyword. '%';
            $like_condition[$key] = $value;
        }
        $id_constraint = $this->get_search_id_constraint();
        if( !empty($id_constraint) ){
            return array('AND' => array(
                        array($id_constraint),
                        array('OR' => $like_condition)
                        )
            );
        }
        return array( 'OR' => $like_condition); 
    }

    protected function createCakeEvent($eventName, $dbModelName, $plugin, $text){
        return new CakeEvent($eventName, $this, array(
                    'id' => $this->data[$dbModelName]['id'],
                    'updated_at' => $this->data[$dbModelName]['updated_at'],
                    'updator_id' => $this->data[$dbModelName]['updator_id'],
                    'plugin_name' => $plugin,
                    'additional_text' => $text,
            ));
    }

    /*
    public function afterSave($created) == 0;
    */
}
