<?php

use comodojo;

require("../lib/comodojo/ldaph.php");

$ldap_server = "ldap.forumsys.com";
$ldap_port 	= 389;
$ldap_verion = 3;

$dn = "uid=USERNAME,dc=example,dc=com";
$base = "dc=example,dc=com";
$searchbase = "(cn=PATTERN)";

$use_ssl = false;
$use_tls = false;

$auth_login = 'euclid';
$auth_password = 'password';

//ou=mathematicians,dc=example,dc=com

try {
	$ldap = new comodojo\ldaph($ldap_server, $ldap_port);
	$data = $ldap->base($base)
		->searchbase($searchbase)
		->dn($dn)
		->version($ldap_verion)
		->ssl($use_ssl)
		->tls($use_tls)
		->account($auth_login, $auth_password)
		->search("*",true);
}
catch (comodojoException $ce) {
	die($ce->getMessage());
}
catch (Exception $e){
	die($e->getMessage());
}

// SHOW ALL DATA
echo '<h1>Dump all data</h1><pre>';
print_r($data);
echo '</pre>';

?>