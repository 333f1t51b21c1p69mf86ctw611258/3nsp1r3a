<?php
if ($usertype != 1){
    echo '<h1> You are not an Admin. You don\'t have access to this page. </h1>';
}else{
?>
<div class="row">
    <div class="tabbable col-sm-12 col-md-12">
        <div class="col-sm-5 col-md-5">
            <?php echo $this->UserSetting->user_detail_left('AddUser',$usertype,$enableProfilePicture); ?>
        </div>
        <div class="col-sm-7 col-md-7">
            <?php echo $this->UserSetting->add_user($usertype_count, $usertypeOption, $usertypeMeaning); ?>
		</div>
	</div>
</div>
<?php } // else END ?>

<script type="text/javascript" charset="utf-8">
function inputCheck(){
	var flag = true;
        
        flag &= nullCheck("verifyPassword");
	flag &= nullCheck("newPassword");
        flag &= nullCheck("manager");
	flag &= nullCheck("title");
	flag &= nullCheck("department");
	flag &= nullCheck("email");
	flag &= nullCheck("name");
        flag &= nullCheck("username");
	if (flag == false) {
		alert("Fill out all necessary fields");
		return false;
	}
	flag &= matchCheck("newPassword", "verifyPassword");
	if (flag == false) {
		alert("Password don't match");
		return false;
	}
        
	return existingCheck("username");
}

function existingCheck(var1){
        var x=document.getElementById(var1).value;
        var count = <?php echo $userCount;?>;
        switch(var1){
            case "username":
                var jUsername= <?php echo json_encode($allUsername); ?>;
                for (var i = 0; i < count; i ++){
                    if (x == jUsername[i]) {
                        document.getElementById(var1).style.border = "2px solid #ff0000";
                        document.getElementById(var1).focus();
                        document.getElementById(var1).select();
                        alert("The username has been used. Please pick up an another username!");
                        return false;
                    }
                }
                break;
            default:
                return true;
        }
       
}
function nullCheck(var1){

	var x=document.getElementById(var1).value;
	if (x==null || x=="") {
		document.getElementById(var1).style.border = "2px solid #ff0000";
		document.getElementById(var1).focus();
		document.getElementById(var1).select();
		return false;
	}
	return true;
}

function matchCheck(var1, var2){
	var x = document.getElementById(var1).value;
	var y = document.getElementById(var2).value;
	if (x === y) {
		return true;
	}
	else {
		document.getElementById(var1).style.border = "2px solid #ff0000";
		document.getElementById(var1).focus();
		document.getElementById(var1).select();
		document.getElementById(var2).style.border = "2px solid #ff0000";
		return false;
	}
}

</script>
