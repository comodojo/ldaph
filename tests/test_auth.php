<?php

use comodojo;
require("../lib/comodojo/ldaph.php");

$ldap_server = "ldap.forumsys.com";
$ldap_port 	= 389;
$ldap_verion = 3;

$dn = "uid=USERNAME,dc=example,dc=com";

$use_ssl = false;
$use_tls = false;

$auth_login = 'einstein';
$auth_password = 'password';

try {
	$ldap = new comodojo\ldaph($ldap_server, $ldap_port);
	$lauth = $ldap->dn($dn)
		->version($ldap_verion)
		->ssl($use_ssl)
		->tls($use_tls)
		->auth($auth_login, $auth_password);
}
catch (comodojoException $ce) {
	die($ce->getMessage());
}
catch (Exception $e){
	die($e->getMessage());
}

if ($lauth) {
	echo 'User '.$auth_login.' authenticated via ldap';
}
else {
	echo 'User '.$auth_login.' unknown or wrong password';
}

?>