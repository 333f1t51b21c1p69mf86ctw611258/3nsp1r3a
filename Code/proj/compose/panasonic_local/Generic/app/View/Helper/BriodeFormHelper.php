<?php
App::uses('AppHelper', 'View/Helper');

class BriodeFormHelper extends AppHelper {
    protected function action_to_label($action){
        $map = array("delete"               =>"Delete",
                     "create_check"         =>"Create",
                     "upload_single_data"   =>"Upload Excel",
                     "update"               =>"Edit",
                     "save"                 =>"Save",
                     "preview_layout"       =>"Check Layout",
                     "cancel_preview"       =>"Cancel",
                     "upload_confirmation"  =>"Confirm",
                     "reedit_layout"        =>"Re-edit Layout",
                     "back"                 =>"Back",
                     "view_attachment"      =>"View Attachment",
                     "export_to_excel"      =>"Export To Excel"
        );
        if( array_key_exists($action, $map) ){ 
            return $map[$action];
        }
        return $action;
    }

    protected function gen_hidden($key, $value){
        return "<input type=\"hidden\" name =\"$key\" value=\"$value\">";
    }

    protected function gen_button_html($action, $label){
        return "<button id=\"button_$action\" class=\"btn btn-primary\" style=\"margin:3px;\" onclick=\"document.pressed='$action'\">". $label. "</button>";
    }
    protected function gen_button($action){
        return $this->gen_button_html($action, $this->action_to_label($action));
    }

    protected function gen_form_buttons($buttonsLabel){
        
        $header = "<div class=\"text-center\">";
        $footer = "</div>";

        $content = "";
        foreach($buttonsLabel as $b){
            $content .= $this->gen_button($b);
        }

        return $header. $content. $footer;
    }

    protected function gen_attachment(){
        $header = "<div>";
        $content =<<<END
<label for="file">Choose your attachments to add</label><br>
<input type="file" name="fileToAdd[]" style="line-height: 0; padding: 0px" multiple="multiple">
END;
        $footer = "<br><br></div>";

        return $header. $content. $footer;
    }

    // set formid for attachment download/excel export
    protected function gen_formid_field($form_id){
        $view_attachment =<<<END
<div class="form-group">
    <input type="hidden" name="form_id" value="$form_id" id="form_id">
</div>
END;
        return $view_attachment;
    }

    protected function enable_skype_payment(){
        return $this->Skype->gen_skype_button();
    }

    protected function gen_log_history($logs){
        if( !isset($logs) ) return '';

        $head =<<<END
<div class="form-group"></div>
<div class="form-group">
    <label for="attrlogs">Update History</label>
    <div class="panel panel-default">
        <ul class="list-group">
END;
        $list = '';
        foreach($logs as $idx => $tableAndAttr){
            $ent = array_values($tableAndAttr)[0];
            $msg = $ent['update_time']. ':'. $ent['updated_by']. ':'. $ent['additional_text'];
            $list .= "<li class=\"list-group-item\">$msg</li>";
        }

        $tail =<<<END
        </ul>
    </div>
</div>
END;
        return $head. $list. $tail;
    }

    protected function list_attachments($pluginName, $source_id, $edit_mode, $attachments){
        // $attachments always include . and ..
        if( empty($attachments) || count($attachments)==2 ){
            return "";
        }

        $label = $head = $list = $tail = '';

        if( $edit_mode ){
            $label =<<<END
<label for="attrlogs">Select attachment file(s) to delete</label>
END;
        }else{
            $label =<<<END
<label for="attrlogs">Attachment</label>
END;
        }

        $head =<<<END
<div class="form-group">
<div class="panel panel-default">
    <table class="table">
END;
        foreach($attachments as $file){
            if( strcmp($file, '..')==0 ||
                strcmp($file, '.')==0 ||
                strcmp($file, 'attachment.zip')==0 ) continue;
            $list .= "<tr>";
            if( $edit_mode ){
                $list .= "<td>". $file. "</td>";
                $list .= "<td><input type=\"checkbox\" name=\"FileToDelete_$file\" value=\"$file\"></td>";
            }else{
                if( strpos(strtolower($file), 'pdf')!==false ){
                    $list .= "<td><a target=\"_blank\" href=\"/Generic/".$pluginName."/file_viewer?id=".$source_id."&fn=".$file."\">".$file."</a></td>";
                }else{
                    $list .= "<td>". $file. "</td>";
                }
            }
            $list .= "</tr>";
        }

        $tail =<<<END
     </table>
</div>
</div>
END;
        return $label. $head. $list. $tail;
    }

    protected function gen_excel_upload(){
        $header = "<div class=\"text-left\">";
        $content =<<<END
<label for="xlfile[]" style="left;">Upload Excel for Data Input</label><br>
<input type="file" name="xlfile[]" style="line-height: 0; left-margin:30%;" multiple="multiple">
END;
        $footer = "<br><br></div>";

        return $header. $content. $footer;
    }

    public function display_form($pluginName, $source_id, $doc, $actions, $logs=NULL, $options=NULL){

        $header =<<<END
<form id="aForm" name="aForm" method="post" enctype="multipart/form-data" onsubmit="return OnSubmitForm(document.pressed, document.aForm);">
END;
        // FIXME: both id and _id exists
        $hidden = '';
        foreach( array('creator_id', 'created_at', 'id') as $col ){
            if( !empty($options) && array_key_exists($col, $options) ){
                $hidden .= $this->gen_hidden($col, $options[$col]);
            }
        }
        if( array_key_exists('id', $_GET) ){
            $hidden .= "<input type=\"hidden\" name=\"_id\" value=\"". $_GET['id']. "\">";
        }
        $hidden .= "<input type=\"hidden\" id=\"load_checker\" name=\"load_checker\" value=\"true\">";

        $excelUpload = '';
        if( $actions && in_array('upload_single_data', $actions) ){
            $excelUpload = $this->gen_excel_upload();
        }

        $attrlog = $this->gen_log_history($logs);

        $export = '';
        if( !empty($options) && 
            array_key_exists('formid_for_download', $options) ){
            $form_id = $options['formid_for_download'];
            $export = $this->gen_formid_field($form_id);
        }

        $attachment = '';
        if( !empty($options) && 
            array_key_exists('attachments', $options) ){
            $edit_mode = array_key_exists('choose_your_attachment', $options);
            $attachment .= $this->list_attachments($pluginName, $source_id, $edit_mode, $options['attachments']);
        }
        if( !empty($options) && 
            array_key_exists('choose_your_attachment', $options) ){
            $attachment .= $this->gen_attachment();
        }

        $buttons = $this->gen_form_buttons($actions);
 
        $footer = "</form>";

        //$skype = $this->enable_skype_payment();
 
        return $header. $hidden. $doc. $attrlog. $attachment. $excelUpload. $export. $buttons. $footer;
    }
}
