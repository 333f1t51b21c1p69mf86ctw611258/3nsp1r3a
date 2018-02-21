$(document).ready( function() {
    $( ".btn_editablelist" ).click( function() {
        var context = this.id.split("_"),
            subject_id = context[0],
            state = context[1],
            state_text = context[2];

        $( "#wf_editablelist_subject_id" ).val(subject_id);
        $( "#wf_editablelist_state" ).val(state);
        $( "#wf_editablelist_state_text" ).val(state_text);
        $( "#wf_editablelist_dialog" ).dialog( "open" );
    });

    $( "#wf_editablelist_dialog" ).dialog({ 
        autoOpen: false 
    });

});
