<!DOCTYPE html>
<?php $user = $this->Session->read('Auth.User');
      //$pluginName = $this->Session->read('PluginName');
?>
<html lang="en" style="overflow-y: scroll;">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>

	<?php
        echo $this->Html->script(array('jquery.min-1.10.2','jquery.formatCurrency-1.4.0'));
        //FIXME : because of dialog, UI is still dependent on jQuery 
        echo $this->Html->script(array('jquery-ui-1.10.4.custom.min'));
        echo $this->Html->script(array('bootstrap.min', 'bootstrap-datepicker'));
        echo $this->Html->script('timeout_popup', array('inline' => false)); //Li (to show the popup dialog box)
        echo $this->Html->script('reset_session', array('inline' => false)); //Li (to reset sesstion time if user choose yes)
        echo $this->Html->script(array('tinymce/tinymce.min'));
        echo $this->Html->meta('icon');
        echo $this->Html->css(array('bootstrap.min', 'datepicker'));
        //FIXME : because of dialog, UI is still dependent on jQuery 
        echo $this->Html->css(array('jquery-ui-1.10.4.custom.min'));
        echo $this->Html->css(array('excelviewer'));
        echo $this->Html->css(array('briode.ui'));
        echo $this->Html->css(array('typeaheadjs'));
        //echo $this->Html->css(array('jquery.keypad'));
        echo $this->fetch('css');
        echo $this->fetch('meta');
        echo $this->fetch('script');
        ?>

    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="apple-touch-icon" href="/Generic/app/webroot/img/briode-logo120x120.png"/>
    <link rel="apple-touch-icon" sizes="76x76" href="/Generic/app/webroot/img/briode-logo120x120.png"/>
    <link rel="apple-touch-icon" sizes="120x120" href="/Generic/app/webroot/img/briode-logo120x120.png"/>
    <link rel="apple-touch-icon" sizes="152x152" href="/Generic/app/webroot/img/briode-logo152x152.png"/>
</head>
		
<body style="">
    <?php echo $this->ProjParam->setParam($proj_params); ?>
    <!-- FIXME min-height should be set in a better approach -->
    <div class="wrapper" style="min-height: 900px; background-image:url(/Generic/app/webroot/img/bg.png);">
    <?php echo $this->NavBar->getNavBar($user['name'], $pluginName, $pluginModelName, $appName, $urlAndLabel, $operations, $enableReport, $admin_menu, $birt_service, $tomcatBaseUrl, $sessionid); ?>
<button id="resetbutton" hidden="hidden" type="button" onclick="reset_session()">reset session timeout</button><!--add by Li Zhan (to send request to server by trigger resetbutton)-->
    <div>

		<div class="container-liquid" style="position:relative; background-color:rgba(255,255,255,0.3);margin:12px;"> 
			<?php echo $this->Session->flash(); 
                           echo $content_for_layout; ?>
		</div>
		<div class="push"></div>
		
	</div>
<div id="dconfirm"></div> <!--add by Li Zhan (to show the dialog box)-->

<!-- to get the value of session timeout -->
<script type="text/javascript">
	var timeCount = <?php echo Configure::read('Session.timeout'); ?>;
</script> 

</body>
</html>
