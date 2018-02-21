<?php
App::uses('BriodeFormHelper', 'View/Helper');

class WorkflowFormHelper extends BriodeFormHelper {
    protected function action_to_label($action){
        $map = array("prev"     => "Send to Previous",
                     "next"     => "Send to Next",
                     "approve"  => "Approve",
                     "cond_approve"  => "Conditional Approve",
                     "reject"   => "Reject", 
                     "modify_after_close"   => "Save", 
                     "review"               => "Confirm",
                     //"create_as"            => "Create as this person",
                     "approve_as"           => "Approve as the assignee",
                     "next_as"              => "Send to Next as the assignee",
                     "prev_as"              => "Send to Previous as the assignee",
                     "undo"                 => "Cancel(undo)",
                     //"update"               => "Edit()",
        );
        if( array_key_exists($action, $map) ){ 
            return $map[$action];
        }

        return parent::action_to_label($action);
    }

    protected function gen_button($action){
        return parent::gen_button_html($action, $this->action_to_label($action));
    }

    private function gen_attachment_list($pluginName, $source_id, $options){
        if( !empty($options) &&
            array_key_exists('attachments', $options) ){
            $edit_mode = array_key_exists('choose_your_attachment', $options);
            return $this->list_attachments($pluginName, $source_id, $edit_mode, $options['attachments']);
        }
        return '';
    }

    private function gen_create_as_selection($create_as_users, $create_as_manager){
        $head =<<<END
<div class="form-group">
    <label for="create_as_user">Create this ticket as the following person and have it assigned to you. <BR>(Next Approver will be ignored unless your name is chosen)</label>
    <select class="form-control" id="create_as_user" name="create_as_user">
END;
        // set default create_as user self
        $users = '<option selected>'.$create_as_manager.'</option>';
        foreach($create_as_users as $user){
            $users .= "<option>$user</option>";
        }
        $tail =<<<END
    </select>
</div>
END;
        return $head. $users. $tail;
    }

    private function gen_workflow_area($pluginName, $source_id, $options, $nextapprovers){
        
        //$header = "<div class=\"text-center\"  id=\"routing\">";
        //$footer = "</div>";
        $head =<<<END
<div class="form-group">
    <label for="addcomment">Add Comment</label>
    <textarea class="form-control" maxlength="1024" id="addcomment" name="addcomment"></textarea>
</div>
<div class="form-group">
    <label for="assignee">Next Approver</label>
    <select class="form-control" id="assignee" name="assignee">
END;
        $approvers = '';
        foreach($nextapprovers as $next){
            $approvers .= "<option>$next</option>";
        }
        $tail =<<<END
    </select>
</div>
END;
        $attachment = $this->gen_attachment_list($pluginName, $source_id, $options);
        $attachment .= $this->gen_attachment();

        return $head. $approvers. $tail. $attachment;
    }

    private function gen_comments($comments){
        $head =<<<END
<div class="form-group"></div>
<div class="form-group">
    <label for="comments">Comments</label>
    <div class="panel panel-default">
        <ul class="list-group">
END;
        $list = '';
        foreach($comments as $idx => $tableAndAttr){ 
            $c = array_values($tableAndAttr)[0];
            $msg = $c['created_at']. ':'. $c['creator_id']. ':'. $c['comment'];
            $list .= "<li class=\"list-group-item\">$msg</li>";
        }

        $tail =<<<END
        </ul>
    </div>
</div>
END;
        return $head. $list. $tail;
    }

    public function display_form($pluginName, $source_id, $doc, $actions, $logs=NULL, $options=NULL, $wf_options=NULL){
        $header =<<<END
<form id="aForm" name="aForm" method="post" enctype="multipart/form-data" onsubmit="return OnSubmitForm(document.pressed, document.aForm);">
END;
        $hidden = '';
        foreach( array('creator_id', 'created_at', 'id') as $col ){
            if( array_key_exists($col, $options) ){
                $hidden .= $this->gen_hidden($col, $options[$col]);
            }
        }
        foreach( array('mandatory_flag', 'validation_flag' ) as $col ){
            if( array_key_exists($col, $wf_options) ){
                $hidden .= $this->gen_hidden($col, $wf_options[$col]);
            }
        }       
        $hidden .= "<input type=\"hidden\" id=\"load_checker\" name=\"load_checker\" value=\"true\">";
        $hidden .= "<input type=\"hidden\" id=\"cond_approve_res\" name=\"cond_approve_res\" value=\"\">";

        $comments = '';
        if( array_key_exists('comments', $wf_options) ){
            $comments = $this->gen_comments($wf_options['comments']);
        }

        $attrlog = $this->gen_log_history($logs);

        $approval = '';
        if( array_key_exists('nextapprovers', $wf_options) ){
            $approval = $this->gen_workflow_area($pluginName, $source_id, $options, $wf_options['nextapprovers']);
        }else{
            $approval = $this->gen_attachment_list($pluginName, $source_id, $options);
        }

        $attachment = '';
        if( array_key_exists('formid_for_download', $wf_options) ){
            $form_id = $wf_options['formid_for_download'];
            $attachment = $this->gen_formid_field($form_id);
        }

        // FIXME: both id and _id exists
        $hiddenId = '';
        //if( array_key_exists('id', $_GET) ){
        //    $hiddenId = "<input type=\"hidden\" name=\"_id\" value=\"". $_GET['id']. "\">";
        //}

        $excelUpload = '';
        if( $actions && in_array('upload_single_data', $actions) ){
            $excelUpload = $this->gen_excel_upload();
        }

        $create_as = '';
        $create_as_users_key = 'create_as_users';
        $create_as_manager_key = 'create_as_manager';
        if( array_key_exists($create_as_users_key, $wf_options) &&
            array_key_exists($create_as_manager_key, $wf_options) ){
            $create_as = $this->gen_create_as_selection(
                                        $wf_options[$create_as_users_key], 
                                        $wf_options[$create_as_manager_key]);
        }

        $buttons = $this->gen_form_buttons($actions);
 
        $footer = "</form>";

        //$skype = $this->enable_skype_payment();
 
        return $header. $hiddenId. $doc. $hidden. $comments. $attrlog. $approval. $attachment. $excelUpload. $create_as. $buttons. $footer;
    }
}
