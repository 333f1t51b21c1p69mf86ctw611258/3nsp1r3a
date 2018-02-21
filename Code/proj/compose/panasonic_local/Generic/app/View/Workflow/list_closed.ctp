<div class="row">
    <div class="col-sm-3 col-md-3">
        <?php
            // FIXME Open is hardcoded 
            echo $this->WorkflowAppPortal->submenu_left('Closed', $isApprover, $countsPerState, $enableReview, $enableAssignedToBuddy, $reviewOnly, $isPostApprover, $readOnly);
        ?> 
    </div>
    <div class="col-sm-9 col-md-9">
        <?php 
            echo $this->ListAppPortal->show($attrDataArray, $pluginName, $listModelName, $attrs, $detail_id);
            ?>
    </div>
</div>    

<?php
echo $this->element('wf_editlist');
$this->Html->script('briode.wf.editablelist', array('inline' => false));
?>

