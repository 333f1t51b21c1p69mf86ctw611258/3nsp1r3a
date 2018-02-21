<?php

	$shellname = "bash ";
	$ext = ".sh";

    // default setting at AWS
    $LDAPBindUser = 'admin';
    $LDAPAddress = 'ldap';
    $LDAPPassword = 'briodeRocks';

    $hostname = gethostname();

	$config['ldap'] = array(
		'DefaultDC' 		=> "dc=enspirea,dc=com",
		'Hostname'			=> $LDAPAddress,
		'BindDN'			=> 'cn='.$LDAPBindUser.',dc=enspirea,dc=com',
		'BindPassword' 		=> $LDAPPassword,
	);
	
	$config['scripts'] = array(
		'CreateBudget' 		=> $shellname. "createBudgetTables". $ext, 
		'CreateFolder' 		=> $shellname. "createFolder". $ext,
		'CreateLdap'		=> $shellname. "createLDAPTree". $ext,
		'CreateRingiTable'	=> $shellname. "createRingiTables". $ext,
		'CreateZip'	        => $shellname. "createZip". $ext,
		'LoadUser'			=> $shellname. "importADToMySql".$ext,
		'ResetPassword'		=> $shellname. "resetPassword".$ext,
		'SynchronizeUser'	=> $shellname. "synchronizeUser".$ext,
		'UpdateBudgetTables'=> $shellname. "updateBudgetTables".$ext,
		'SyncLdapWithDB'    => $shellname. "syncLdapWithDB".$ext,

        'GenerateReport'    => $shellname. "generateReport". $ext,
        'GenerateReportState'  => $shellname. "generateReportState". $ext,

        'LoadPadding'  => $shellname. "load_padding_app25". $ext,

        'RunTrigger'   => $shellname. "runTrigger". $ext,
	);

?>
