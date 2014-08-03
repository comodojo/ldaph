<?php

require '../src/Exception/LdaphException.php';
require '../src/Ldaph.php';

use Comodojo\Ldaph\Ldaph;
use Comodojo\Exception\LdaphException;

$ldap_server = "ldap.forumsys.com";
$ldap_port  = 389;
$ldap_verion = 3;

$dn = "uid=USERNAME,dc=example,dc=com";

$use_ssl = false;
$use_tls = false;

$auth_login = 'einstein';
$auth_password = 'password';

try {
    $ldap = new Ldaph($ldap_server, $ldap_port);
    $lauth = $ldap->dn($dn)
        ->version($ldap_verion)
        ->ssl($use_ssl)
        ->tls($use_tls)
        ->auth($auth_login, $auth_password);
} catch (LdaphException $ce) {
    die("comodojo exception: ".$ce->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}

if ($lauth) {
    echo 'User '.$auth_login.' authenticated via ldap';
} else {
    echo 'User '.$auth_login.' unknown or wrong password';
}
