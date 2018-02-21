<html>
<body>
<div class="users form">
<?php echo $this->Session->flash('auth'); ?>
<?php echo $this->Form->create('User'); ?>
    <fieldset>
        <legend><?php echo __('Please Logout'); ?></legend>
    </fieldset>
<?php echo $this->Form->end(__('Logout')); ?>
</div>
</body>
</html>
