<?php
App::uses('AppHelper', 'View/Helper');

class ApplicationEditorHelper extends AppHelper {
    private function generateButton($action, $buttonName){
        $btnMsg =<<<END
<button class="btn btn-primary" onclick=$action name=$buttonName id=$buttonName>$buttonName</button> 
END;
        return $btnMsg;
    }

    public function showAttachmentAndButtons($buttonsToEnable){
        $headerMsg =<<<END
</div>
    <div class="well">
    <div class="text-center paddingBottom">
    <label for="file">Choose your attachments</label><br>
    <input type="file" name="file[]" style="line-height: 0; padding: 0px" multiple="multiple"><br><br>
END;

        $bottomMsg =<<<END
</div>
END;
        
        $applyButton = $this->generateButton("reapply()", 'Apply');
        $reapplyButton = $this->generateButton("reapply()", 'Reapply');
        $deleteButton = $this->generateButton("delet()", 'Delete'); // has to be delet
        $saveAsEditingButton = $this->generateButton("saveAsEditing()", 'Save');
        $saveAsCancelledButton = $this->generateButton("saveAsCancelled()", 'Save');

        $outMsg = $headerMsg;

        if( in_array('applyButton', $buttonsToEnable) ){
            $outMsg .= $applyButton;
        }
        if( in_array('reapplyButton', $buttonsToEnable) ){
            $outMsg .= $reapplyButton;
        }
        if( in_array('deleteButton', $buttonsToEnable) ){
            $outMsg .= $deleteButton;
        }
        if( in_array('saveAsEditingButton', $buttonsToEnable) ){
            $outMsg .= $saveAsEditingButton;
        }
        if( in_array('saveAsCancelledButton', $buttonsToEnable) ){
            $outMsg .= $saveAsCancelledButton;
        }

        $outMsg .= $bottomMsg;
    
        return $outMsg;
    }
}




