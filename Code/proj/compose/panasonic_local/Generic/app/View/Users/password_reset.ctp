<div class="container">
	<div class="surface">
		<div class="account-content">
			<form method="post" action="../users/password_reset">
				<h2 class="form-signin-heading">Password Reset</h2>
				<select name="selection">
				  <option value=""></option>
					<?php
					$i=0;
					while (isset($allusers[$i])) {
						echo '<option value="'. $allusers[$i] . '">' . $allusers[$i] . '</option>';
						$i++; 
					}
					?>
				</select>
				
				<input type="text" placeholder="New pass" name="newpass"><br>
				<button class="btn btn-primary" type="submit">Reset password</button>
			</form>
		</div>
	</div>
</div>