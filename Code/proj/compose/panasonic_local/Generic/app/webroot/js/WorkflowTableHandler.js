$(document).ready(function(){
    $('#APPROVERDEPT_1').change(function(){
        setApproverList($('#APPROVERDEPT_1').val(), 1);
    }).change();
    $('#APPROVERDEPT_2').change(function(){
        setApproverList($('#APPROVERDEPT_2').val(), 2);
    }).change();
    $('#APPROVERDEPT_3').change(function(){
        setApproverList($('#APPROVERDEPT_3').val(), 3);
    }).change();
    $('#APPROVERDEPT_4').change(function(){
        setApproverList($('#APPROVERDEPT_4').val(), 4);
    }).change();
    
});


function setApproverList(dept,layer)
{
    approverId = "#APPROVERID_"+layer;
    departmentId = "#APPROVERDEPT_"+layer;

    // save selected department
    var selectedApprover = $(approverId).find(":selected").text();
    var selectedDepartment = $(departmentId).find(":selected").text();

    var data = {department: dept};

    idForOption = approverId + ' option';
   
    $.ajax({
		async: false,
        type: "POST",
        url: '/AppGenerics/Users/deptmembers.json',
        data: data,
        dataType: "json",
        success: function(result){
            options = '';
			if(result.members.length == 0){
				options = options + "<option value selected></option>"; 
			}
			else{
				for( i=0; i<result.members.length; i++ ){
                	user = result.members[i];
                	options = options + "<option value=" + user;
                	if( selectedApprover==user ){
                   		options = options + " selected=" + user ;
                	}
                	options = options + ">" + user + "</option>";
            	}
			}
            $(approverId).html(options);

            // restore selected user
            $(approverId).val(selectedApprover);
            $(departmentId).val(selectedDepartment);
        }
    });
}



