<?php

require '../src/Exception/LdaphException.php';
require '../src/Ldaph.php';

use Comodojo\Ldaph\Ldaph;
use Comodojo\Exception\LdaphException;

$ldap_server = "ldap.forumsys.com";
$ldap_port  = 389;
$ldap_verion = 3;

$dn = "uid=USERNAME,dc=example,dc=com";
$base = "dc=example,dc=com";
$searchbase = "(uid=PATTERN)";

$use_ssl = false;
$use_tls = false;

$auth_login = 'euclid';
$auth_password = 'password';

try {
    $ldap = new Ldaph($ldap_server, $ldap_port);
    $data = $ldap->base($base)
        ->searchbase($searchbase)
        ->dn($dn)
        ->version($ldap_verion)
        ->ssl($use_ssl)
        ->tls($use_tls)
        ->account($auth_login, $auth_password)
        ->search("*",true);
} catch (LdapException $ce) {
    die("comodojo exception: ".$ce->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}

// SHOW ALL DATA
echo '<h1>comodojo ldaph search test - data dump</h1><pre>';
print_r($data);
echo '</pre>';
