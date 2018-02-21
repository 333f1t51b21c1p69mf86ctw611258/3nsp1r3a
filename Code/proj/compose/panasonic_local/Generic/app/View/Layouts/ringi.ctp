<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>
	<?php
	echo $this->Html->meta('icon');
	echo $this->Html->css('ringi');
	echo $this->Html->css('cake.generic');
	echo $this->Html->css('bootstrap.min');
	echo $this->Html->css('bootstrap-responsive.min');
	echo $this->fetch('css');
	echo $this->Html->script('libs/jquery');
	echo $this->Html->script('libs/bootstrap.min');
	echo $this->fetch('meta');
	echo $this->fetch('script');
	echo $scripts_for_layout;
	?>
</head>
<body>

</body>
</html>
