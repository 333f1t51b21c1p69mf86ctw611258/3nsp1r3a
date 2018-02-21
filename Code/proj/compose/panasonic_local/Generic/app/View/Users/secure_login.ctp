

<div class="users form">
<?php echo $this->Session->flash('auth'); ?>
<?php echo $this->Form->create('User'); ?>
    <fieldset>
        <legend><?php echo __('Please enter your username and password'); ?></legend>
        <?php echo $this->Form->input('username');
        echo $this->Form->input('password');
    ?>
    </fieldset>
<?php echo $this->Form->end(__('ログイン')); ?>
</div>

<!-- <form method="post" action="secure_login">
	Username<br>
	<input type="text" name="userLogin" /><br />
	Password<br>
	<input type="password" name="userPassword" /><br />
	<input type="submit" name="submit" value="Submit" />
</form>

<form method="get" action="login">
	<input type="submit" name="out" value="Logout" />
</form>
-->