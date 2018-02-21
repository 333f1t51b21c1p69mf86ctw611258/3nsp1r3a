<?php
App::uses('AppHelper', 'View/Helper');

class UserSettingHelper extends AppHelper {
    private function input_hidden($username, $key, $value){
        $capitalized = ucfirst($key);
        $hidden =<<<END
<input type="hidden" name="all$capitalized$username" id="all$capitalized$username" value="$value">
END;
        return $hidden;
    }

    private function form_end($resourceValue, $buttonType, $buttonName){
        $end = <<<END
        <input type="hidden" name="resourceflag" value="$resourceValue" id="resourceflag">
        <hr>
        <div class="form-actions">
            <button class="btn btn-primary" type="$buttonType">$buttonName</button>
        </div>
    </form>
END;
        return $end;
    }

    private function submenu_title($title, $comment){
        $submenu =<<<END
    <div align="left" class="paddingLeft paddingTop">
        <h3>$title</h3>
        <h5 style="font-weight:normal;">$comment</h5>
    </div>
    <hr>
END;
        //return $submenu;
        return '';
    }

    private function user_detail_hidden($username, $usertype, $name, $email, $department, $title, $manager, $activeFlag){
        $detail = '';
        $paramSet = array('usertype'=>$usertype, 
                          'name'=>$name,
                          'email'=>$email,
                          'department'=>$department,
                          'title'=>$title,
                          'manager'=>$manager,
                          'activeFlag'=>$activeFlag, );
        foreach( $paramSet as $key=>$value){
            $detail .= $this->input_hidden($username, $key, $value);
        }
        return $detail;
    }

    public function user_details_hidden($usertype, $userCount, $allUsername, $allUsertype, $allName, $allEmail, $allDepartment, $allTitle, $allManager, $allActiveFlag){
        // FIXME 1 is hardcoded
        if ($usertype != 1) return;

        for($i=0; $i<$userCount; $i++){
            $this->user_detail_hidden($allUsername[$i], $allUsertype[$i], $allName[$i],
                                $allEmail[$i], $allDepartment[$i], $allTitle[$i],
                                $allManager[$i], $allActiveFlag[$i]);
        }
    }

    public function user_detail_left($activeTab, $usertype, $enableProfilePicture){
        $breadcrumb   = array('#'=>array('Profile', 'active'));
        $itemToUrlMap = array(
                    //array('Account',        'user_setting'),
                    array('Password',       'password_change'), 
        );
        $itemToUrl_profile_picture = array(
                    array('ProfilePicture', 'profile_upload'),
        );
        $itemToUrl_admin = array(
                    // array('AddUser',        'add'),
        );
        if( $enableProfilePicture ){
            $itemToUrlMap = array_merge($itemToUrlMap, $itemToUrl_profile_picture);
        }
        if( $usertype == 1 ){
            $itemToUrlMap = array_merge($itemToUrlMap, $itemToUrl_admin);
        }
        return $this->left_submenu($breadcrumb, $activeTab, $itemToUrlMap);
    }

    private function genFieldset($key, $value, $label, $type="text", $disabled=NULL){
        $disabled = '';
        if( $disabled != NULL ) $disabled = 'disabled="$disabled"';
        if( $type == "password" ) $value = '';
        $field =<<<END
<fieldset class="form-group">
    <label class="control-label">$label</label>
    <div class="controls">
        <input type="$type" name="$key" value="$value" $disabled" id="$key" class="form-control">
    </div>
</fieldset>
END;
        return $field;
    } 

    private function update_user_account_admin($username, $usertype, $userCount, $name, $email, $department, $title, $manager, $activeflag, $usertype_count, $usertypeOption, $usertypeMeaning, $allUsername, $allUsertype, $allName, $allEmail, $allDepartment, $allTitle, $allManager, $allActiveFlag){
        $submenu = $this->submenu_title("Account", "Change Profile for Users");
    
        $admin_right1 =<<<END
    <form class="form-horizontal" action="user_setting" method="post" onsubmit="return inputCheckAdmin()" accept-charset="utf-8">
        <fieldset class="form-group">
            <label class="control-label">Username</label>
            <div class="controls">
                <select name="users" id="users" onchange="UpdateInput();" class="form-control">
END;
    
        $admin_right2 = '';
        if($userCount>0){
            for ($i = 0; $i < $userCount; $i++){
                $admin_right2 .= '<option ';
                if ($username == $allUsername[$i]){
                    $admin_right2 .= 'selected ';
                }
                $admin_right2 .= 'value="'.$allUsername[$i].'">'.$allUsername[$i].'</option>';
            }
        }

        $checked = ($activeflag==1) ? 'checked' : '';
        $admin_right3 =<<<END
                </select>
                <p style="margin:0;"><input type="checkbox" name="activeFlag" value="$activeflag" id="activeFlag" $checked> Check the box to active the user.</p>
            </div>
        </fieldset>
        <fieldset class="form-group">
            <label class="control-label">Usertype</label>
            <div class="controls">
                <select name="usertypes" id="usertypes" class="form-control">
END;
        
        $admin_right4 = '';
        for ($i = 0 ; $i < $usertype_count; $i ++){
            $admin_right4 .= '<option id="usertypes'.($i+1).'"';
            if ($usertype == $usertypeOption[$i]){
                $admin_right4 .= 'selected ';
            }
            $admin_right4 .= 'value='.$usertypeOption[$i].'>'.$usertypeMeaning[$i].'</option>';
        }
        
        $admin_right5 =<<<END
                </select>
            </div>
        </fieldset>
END;

        $fieldSet = array('name'=>array('Full Name', $name),
                          'email'=>array('Email', $email),
                          'manager'=>array('Manager', $manager),
                          'title'=>array('Title', $title), );
        $admin_right6 = '';
        foreach($fieldSet as $key=>$params){
            $label = $params[0];
            $value = $params[1];
            $admin_right6 .= $this->genFieldset($key, $value, $label);
        }

        $admin_right7 = $this->form_end("admin", "submit", "Save");

        return $this->wrap_well($submenu. $admin_right1 . $admin_right2 . $admin_right3 . $admin_right4 . $admin_right5 . $admin_right6 . $admin_right7);
    }

    public function update_user_account($username, $usertype, $userCount, $name, $email, $department, $title, $manager, $activeflag, $usertype_count, $usertypeOption, $usertypeMeaning, $allUsername, $allUsertype, $allName, $allEmail, $allDepartment, $allTitle, $allManager, $allActiveFlag){
        if( $usertype == 1 ){
            return $this->update_user_account_admin($username, $usertype, $userCount, $name, $email, $department, $title, $manager, $activeflag, $usertype_count, $usertypeOption, $usertypeMeaning, $allUsername, $allUsertype, $allName, $allEmail, $allDepartment, $allTitle, $allManager, $allActiveFlag);
        } 

        $submenu = $this->submenu_title("Account", "Change Your Name and Email Address.");

        $nonadmin_right1 =<<<END
    <form class="form-horizontal" action="user_setting" method="post" onsubmit="return inputCheckUser()" accept-charset="utf-8">
END;
    
        $fieldSet = array(//'username'=>array('Username', $username, 'disabled'),
                          //'userFullName'=>array('Full Name', $name, NULL),
                          'userEmail'=>array('Email', $email, NULL), );
        $nonadmin_right2 = '';
        foreach($fieldSet as $key=>$params){
            $label = $params[0];
            $value = $params[1];
            $disabled = $params[2];
            $nonadmin_right2 .= $this->genFieldset($key, $value, $label, 'text', $disabled);
        }
       
        $nonadmin_right3 = $this->form_end("user", "submit", "Save Changes");

        return $this->wrap_well($submenu. $nonadmin_right1 . $nonadmin_right2 . $nonadmin_right3);
    }

    public function update_profile_picture($picture){
        $submenu = $this->submenu_title("Profile", "Update Your Profile Picture.");

        $profile =<<<END
    <form class="form-horizontal" action="profile_upload" method="post" enctype="multipart/form-data" name="form1" id="form1"  accept-charset="utf-8">
        <fieldset class="form-group">
            <label class="control-label"><img class="img80" src="/Generic/app/webroot/img/$picture.jpg"></label>
            <div class="controls">
                <input name="ufile" type="file" id="ufile" size="50px" /></td>
            </div>
        </fieldset>
        <hr>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </form>
END;

        return $this->wrap_well($submenu. $profile);
    }

    private function update_password_admin($username, $userCount, $allUsername){
        $submenu = $this->submenu_title("Password", "Reset Password for Users.");

        $password1 =<<<END
    <form class="form-horizontal" action="/Generic/Users/password_change" method="post" onsubmit="return inputCheck()" accept-charset="utf-8">
        <fieldset class="form-group">
            <label class="control-label">Username</label>
            <div class="controls">
                <select name="users" id="users" onchange="UpdateInput();" class="form-control">
END;

        $password2 = '';
        if($userCount>0){
            for ($i = 0; $i < $userCount; $i++){
                $password2 .= '<option ';
                if ($username == $allUsername[$i]){
                    $password2 .= 'selected ';
                }
                $password2 .= 'value="'.$allUsername[$i].'">'.$allUsername[$i].'</option>';
            }
        }
        $password2 .=<<<END
                </select>
            </div>
        </fieldset>
END;
       
        $fieldSet = array('newPassword'=>array('New Password', ''),
                          'verifyPassword'=>array('Verify Password', ''),);
        $password3 = '';
        foreach($fieldSet as $key=>$params){
            $label = $params[0];
            $value = $params[1];
            $password3 .= $this->genFieldset($key, $value, $label, 'password');
        }
        
        $password4 = $this->form_end("admin", "submit", "Save");

        return $this->wrap_well($submenu. $password1. $password2. $password3. $password4);
    }

    public function update_password($usertype, $username, $userCount, $allUsername){
        // FIXME usertype is hardcoded
        if($usertype == 1){
            return $this->update_password_admin($username, $userCount, $allUsername);
        }

        $submenu = $this->submenu_title("Password", "Change Your Password");

        $password1 =<<<END
    <form class="form-horizontal" action="password_change" method="post" onsubmit="return inputCheck()" accept-charset="utf-8">
END;

        $fieldSet = array('currentPassword'=>array('Current Password', ''),
                          'newPassword'=>array('New Password', ''),
                          'verifyPassword'=>array('Verify Password', ''), );
        $password2 = '';
        foreach($fieldSet as $key=>$params){
            $label = $params[0];
            $value = $params[1];
            $password2 .= $this->genFieldset($key, $value, $label, "password");
        }

        $password3 = $this->form_end("user", "submit", "Save");

        return $this->wrap_well($password1 . $password2 . $password3);
    }

    public function add_user($usertype_count, $usertypeOption, $usertypeMeaning){
        $submenu = $this->submenu_title("Add User", "Add New User to System.");

        $adduser1 =<<<END
    <form class="form-horizontal" action="add" method="post" onsubmit="return inputCheck()" accept-charset="utf-8">
        <fieldset class="form-group">
            <label class="control-label">Username</label>
            <div class="controls">
                <input type="text" name="username" value="" id="username" class="form-control">
                <p style="margin:0;">
                    <input type="checkbox" name="activeFlag" value="" id="activeFlag" checked> Check the box to activate the user.
                </p>
            </div>
        </fieldset>
        <fieldset class="form-group">
            <label class="control-label">Usertype</label>
            <div class="controls">
                <select name="usertypes" id="usertypes" class="form-control">
END;

        $adduser2 = '';
        for ($i = 0 ; $i < $usertype_count; $i ++){
            $adduser2 .= '<option id="usertypes'.($i+1).'"';
            $adduser2 .= 'value='.$usertypeOption[$i].'>'.$usertypeMeaning[$i].'</option>';
        }

        $adduser3 =<<<END
                </select>
            </div>
        </fieldset>
END;

        $fieldSet = array('name'=>array('Full Name', ''),
                          'email'=>array('Email', ''),
                          'department'=>array('Department', ''),
                          'title'=>array('Title', ''), 
                          'manager'=>array('Manager', ''),);
        $adduser4 = '';
        foreach($fieldSet as $key=>$params){
            $label = $params[0];
            $value = $params[1];
            $adduser4 .= $this->genFieldset($key, $value, $label);
        }

        $passwdFieldSet = array('newPassword'=>array('Create Password', ''),
                                'verifyPassword'=>array('Verify Password', ''),);
        $adduser5 = '';
        foreach($passwdFieldSet as $key=>$params){
            $label = $params[0];
            $value = $params[1];
            $adduser5 .= $this->genFieldset($key, $value, $label,'password');
        }

        $adduser6 = $this->form_end("admin", "submit", "Create");

        return $this->wrap_well($submenu. $adduser1. $adduser2. $adduser3. $adduser4. $adduser5. $adduser6);
    }
}
