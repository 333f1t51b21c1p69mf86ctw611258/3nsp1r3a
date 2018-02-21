<?php 

function authenticate($user, $pass) {
	
	//ldap credentials
	$ldapConfig 	= Configure::read('ldap');
	$ldap_host 		= $ldapConfig['Hostname'];
	$ldapuser  		= $ldapConfig['BindDN']; 
	$ldappass  		= $ldapConfig['BindPassword'];
	
	//MySQL credentials
	$host = 'mysql';
	$username = 'root';
	$password = '';
	$database = 'genericdata';
	
	// Active Directory DN
	$ldap_dn = "dc=enspirea,dc=com";

	// Active Directory user group
	$ldap_user_group = "WebUsers";

	// Active Directory manager group
	$ldap_manager_group = "WebManagers";

	// Domain, for purposes of constructing $user
	$ldap_usr_dom = "";

	// connect to active directory
	$ldap = ldap_connect($ldap_host) or die("Could not connect to LDAP server.");

	// Connect to MySQL
	$link = mysqli_connect($host, $username, $password);

	if (!$link) {
		die('Could not connect: ' . mysqli_error($link));
	}

	mysqli_select_db($link, $database);	//pointing at the right database

	//change username to DN
	$query = "SELECT DN FROM users WHERE username='".$user."'"; 
	$queryDN = mysqli_query($link, $query) or die(mysqli_error($link));	//does the query, and gets a resource
	$querystring = mysqli_fetch_assoc($queryDN);		//fetches the associated array
	$userDN = $querystring['DN'];

	ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
	
	if($bind = @ldap_bind($ldap, $userDN . $ldap_usr_dom, $pass)) {
		// valid
		// check presence in groups
        /*
		$filter = "(sAMAccountName=" . $user . ")";
		$attr = array("memberof");
		$result = ldap_search($ldap, $ldap_dn, $filter, $attr) or exit("Unable to search LDAP server");
		$entries = ldap_get_entries($ldap, $result);
        */
		ldap_unbind($ldap);
		return true;

	} 
	else {
		// invalid name or password
	    return false;
	}
}

?>
