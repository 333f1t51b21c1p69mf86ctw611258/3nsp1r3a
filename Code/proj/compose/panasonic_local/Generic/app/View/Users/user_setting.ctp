<?php
echo $this->UserSetting->user_details_hidden($usertype, $userCount, $allUsername, $allUsertype, $allName, $allEmail, $allDepartment, $allTitle, $allManager, $allActiveFlag);
?>

<div class="row">
    <div class="tabbable col-sm-12 col-md-12">
        <div class="col-sm-5 col-md-5">
            <?php 
                // FIXME 'Account' is hardcoded
                echo $this->UserSetting->user_detail_left('Account', $usertype,$enableProfilePicture);
            ?> 
        </div>
        <div class="col-sm-7 col-md-7">
            <?php 
                echo $this->UserSetting->update_user_account($username, $usertype, $userCount, $name, $email, $department, $title, $manager, $activeflag, $usertype_count, $usertypeOption, $usertypeMeaning, $allUsername, $allUsertype, $allName, $allEmail, $allDepartment, $allTitle, $allManager, $allActiveFlag); 
            ?>
        </div>
    </div>
</div>    

<script type="text/javascript" charset="utf-8">
function UpdateInput(){
    var index= document.getElementById("users").value;
    var usertype = document.getElementById("allUsertype"+index).value;
    var name = document.getElementById("allName"+index).value;
    var email = document.getElementById("allEmail"+index).value;
    var department = document.getElementById("allDepartment"+index).value;
    var title = document.getElementById("allTitle"+index).value;
    var manager = document.getElementById("allManager"+index).value;
    var activeFlag = document.getElementById("allActiveFlag"+index).value;
    
    document.getElementById("usertypes"+usertype).selected = true;
    document.getElementById("name").value = name;
    document.getElementById("email").value = email;
    document.getElementById("department").value = department;
    document.getElementById("title").value = title;
    document.getElementById("manager").value = manager;
    document.getElementById("activeFlag").checked = (activeFlag == 1)? true:false;
 
}

function inputCheckAdmin(){
    var flag = true;
    
    flag &= nullCheck("manager");
    flag &= nullCheck("title");
    flag &= nullCheck("department");
    flag &= nullCheck("email");
    flag &= nullCheck("name");
    if (flag == false) {
        alert("Fill out all necessary fields");
        return false;
    }
    else {
        return true;
    }
}
function inputCheckUser(){
    var flag = true;
    flag &= nullCheck("userEmail");
    flag &= nullCheck("userFullName");
    if (flag == false) {
        alert("Fill out all necessary fields");
        return false;
    }
    else {
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

</script>

