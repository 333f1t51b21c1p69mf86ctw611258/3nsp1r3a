<?php
echo $this->UserList->usersArrayToTable($users);
?>
<div class="row-fluid">
    <div class="span2 offset4">
        <form action="userupload_confirmation" method="post" align="center">
            <button class="btn btn-primary" name="submit" value="confirm">Confirm</button>
        </form>
    </div>
    <div class="span2">
        <form action="upload_users" method="post" align="center">
            <button class="btn btn-primary" name="submit" value="reupdate">Upload again</button>
        </form>
    </div>
    
</div>


