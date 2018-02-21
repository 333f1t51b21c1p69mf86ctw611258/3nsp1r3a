<?php
App::uses('AppHelper', 'View/Helper');

class ListAppPortalHelper extends AppHelper {
    var $helpers = array('Paginator');

    private function _add_edit_button($row) {
        $this->log('row=', 'debug');
        $this->log($row, 'debug');
        if (!array_key_exists('subject_id', $row) ||
            !array_key_exists('case_state', $row) ||
            !array_key_exists('case_state_text', $row)) {
            $this->log('_add_edit_button, required param not found', 'debug');
            return '';
        }

        $subject_id = $row['subject_id'];
        $state = $row['case_state'];
        $state_text = $row['case_state_text'];

        $button_id = $subject_id.'_'.$state.'_'.$state_text;
        $btn_add = '<td><button class="btn_editablelist" id="'.$button_id.'">Edit</button></td>';

        return $btn_add;
    }

    public function show_approver_list($attrDataArray, $pluginName, $listModelName, $attrs, $detail_id) {
        $paginatorList = true;
        $enable_edit = false;
        $link_as_edit = true;
        return $this->show($attrDataArray, $pluginName, $listModelName, $attrs, $detail_id, $paginatorList, $enable_edit, $link_as_edit);
    }

    public function show($attrDataArray, $pluginName, $listModelName, $attrs, $detail_id, $paginatorList=true, $enable_edit=false, $link_as_edit=false){
        $list_begin =<<<END
<div class="table-responsive">
    <table class="table table-striped">
END;
        // FIXME this needs to be replaced
        $link_action = 'read';
        if ($link_as_edit) {
            $link_action = 'update';
        }
        $idAndUrl = array($detail_id, '/Generic/'.$pluginName.'/'.$link_action);

        $list_body = '<tr>';
        foreach( $attrs as $a ){
            $list_body .= '<th>'.$this->Paginator->sort($a, $a, array('direction'=>'desc')).'</th>';
        }
        if ($enable_edit) {
            $btn_add = '<th></th>';
            $list_body .= $btn_add;
        }
        $list_body .= '</tr>';

        foreach($attrDataArray as $attr){
            $row = $attr[$listModelName];
            $this->log('ListAppPortalHelper, row=', 'debug');
            $this->log($row, 'debug');
            $list_body .= '<tr>';
            foreach($attrs as $a){
                $displayString = $this->raw_to_label($a, $row[$a]);
                $row[$a] = $displayString;
                if( strcmp($idAndUrl[0],$a)==0 ){
                    $id = $row[$a];
                    $list_body .= '<td><a href='.$idAndUrl[1].'?id='.$id.'>'.$displayString.'</a>';
                }else{
                    $list_body .= '<td>'.$displayString.'</td>';
                }
            }
            // FIXME
            // code below only runs for Workflow app
            if ($enable_edit) {
                $list_body .= $this->_add_edit_button($row);
            }
            $list_body .= '</tr>';
        }
        $list_body .= '</table>';

        // Shows the next and previous links
        $pagination = $this->Paginator->prev(
          '« Previous  ',
          null,
          null,
          array('class' => 'disabled')
        );
        // Shows the page numbers
        $pagination .= $this->Paginator->numbers();

        $pagination .= $this->Paginator->next(
          '   Next »',
          null,
          null,
          array('class' => 'disabled')
        );
        $list_end =<<<END
</div>
END;
        if( !$paginatorList ){
            $pagination = '<BR>*List shows first 100 results*<BR>';
            
        }
        return $list_begin . $list_body . $pagination . $list_end;
    }
}
