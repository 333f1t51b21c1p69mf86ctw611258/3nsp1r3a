<!DOCTYPE html>
<html lang="en" style="overflow-y: scroll;min-height:720px;">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>

	<?php
    echo $this->Html->script(array('bootstrap', 'bootstrap.min', 'bootstrap-datepicker'));
    echo $this->Html->meta('icon');
	echo $this->Html->css(array('bootstrap.min'));

	echo $this->fetch('css');
	echo $this->fetch('meta');
	echo $this->fetch('script');

	?>
    <link rel="apple-touch-icon" href="/Generic/app/webroot/img/briode-logo120x120.png"/>
    <link rel="apple-touch-icon" sizes="76x76" href="/Generic/app/webroot/img/briode-logo120x120.png"/>
    <link rel="apple-touch-icon" sizes="120x120" href="/Generic/app/webroot/img/briode-logo120x120.png"/>
    <link rel="apple-touch-icon" sizes="152x152" href="/Generic/app/webroot/img/briode-logo152x152.png"/>
</head>
<body style="background-color:#6AA9B0;background-size:142px 720px; background-repeat:repeat-x; background-image:url(/Generic/app/webroot/img/login_back.jpg);">
	<div class="wrapper">
		<div class="container" style="background-image:url(/Generic/app/webroot/img/login_test.jpg);width:0px;min-height:720px;min-width:1120px;">
        <?php echo $this->fetch('content') ?>
        </div>
	</div>
</body>
</html>
