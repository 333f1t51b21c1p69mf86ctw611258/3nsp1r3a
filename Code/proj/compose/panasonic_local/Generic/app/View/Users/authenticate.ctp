<?php 

function authenticate($user, $password) {
	include("credentials.ctp");	
	// Active Directory user group
	$ldap_user_group = "WebUsers";

	// Active Directory manager group
	$ldap_manager_group = "WebManagers";

	// Domain, for purposes of constructing $user
	$ldap_usr_dom = "";

	// connect to active directory
	$ldap = ldap_connect($ldap_host) or die("Could not connect to LDAP server.");

	// verify user and password

	if($bind = @ldap_bind($ldap, $user . $ldap_usr_dom, $password)) {

		// valid
		// check presence in groups
		$filter = "(sAMAccountName=" . $user . ")";
		$attr = array("memberof");
		$result = ldap_search($ldap, $ldap_dn, $filter, $attr) or exit("Unable to search LDAP server");
		$entries = ldap_get_entries($ldap, $result);
		ldap_unbind($ldap);

		$access=1;

		if ($access != 0) {
			// establish session variables
			$_SESSION['user'] = $user;
			$_SESSION['access'] = $access;
			return true;
		} 
		else {
			// user has no rights
			return false;
		}

	} 
	else {
		// invalid name or password
		return false;
	}
}

?>