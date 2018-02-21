<div class="row-fluid">
    <div class="tabbable col-sm-12 col-md-12">
        <div class="col-sm-5 col-md-5">
            <?php
                // FIXME 'Account' is hardcoded
                echo $this->UserSetting->user_detail_left('Password', $usertype,$enableProfilePicture);
            ?>
        </div>
		<div class="col-sm-7 col-md-7" >
			<?php
                echo $this->UserSetting->update_password($usertype, $username, $userCount, $allUsername);
            ?>
        </div>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
	
function inputCheck(){
	var flag = true;
	flag &= nullCheck("verifyPassword");
	flag &= nullCheck("newPassword");
	if (flag == false) {
		alert("Fill out all necessary fields");
		return false;
	}
	flag &= matchCheck("newPassword", "verifyPassword");
	if (flag == false) {
		alert("Password don't match");
		return false;
	}
	return true;
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
