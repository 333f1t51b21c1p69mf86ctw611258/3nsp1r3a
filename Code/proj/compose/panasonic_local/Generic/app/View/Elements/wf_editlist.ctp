<div id="wf_editablelist_dialog" title="Edit Case Status">

<?php
echo $this->Form->create(null, array(
    'url' => $absolute_url. '/save_state'
));
?>

<H4>Subject_id</H4>
<?php
echo $this->Form->text('state_dlg_subject_id', array(
    'id' => 'wf_editablelist_subject_id',
    'class' => 'dialog_text',
    'readonly' => 'readonly'
));
?>

<H4>case_state</H4>
<?php
// $state_options calculated in _set_dialog_options
echo $this->Form->select('state_dlg_case_state', $state_options, array(
    'id' => 'wf_editablelist_state'
));
?>

<H4>case_state_text</H4>
<?php
echo $this->Form->text('state_dlg_case_state_text', array(
    'id' => 'wf_editablelist_state_text',
    'class' => 'dialog_text'
));
?>

<BR>
<BR>
<?php
echo $this->Form->button('Save', array(
    'type' => 'submit',
    'escape' => true
));
?>
</div>
