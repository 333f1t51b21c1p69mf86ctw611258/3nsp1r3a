<div class="row">
	<div class="tabbable col-sm-12 col-md-12">
		<div class="col-sm-5 col-md-5">
            <?php
                // FIXME 'Account' is hardcoded
                echo $this->UserSetting->user_detail_left('ProfilePicture', $usertype, $enableProfilePicture);
            ?>
		</div>
		<div class="col-sm-7 col-md-7" >
            <?php 
                echo $this->UserSetting->update_profile_picture($picture); 
            ?>
		</div>
	</div>
</div>

