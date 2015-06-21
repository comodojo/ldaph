<?php

class AuthTest extends \PHPUnit_Framework_TestCase {

    protected $ldap_server = "ldap.forumsys.com";
    protected $ldap_port  = 389;
    protected $ldap_verion = 3;
    protected $dn = "uid=USERNAME,dc=example,dc=com";
    protected $use_ssl = false;
    protected $use_tls = false;
    protected $auth_login = 'einstein';
    protected $auth_password = 'password';

    public function testAuthentication() {
        
        $ldap = new \Comodojo\Ldaph\Ldaph($this->ldap_server, $this->ldap_port);
    

        $lauth = $ldap->dn($this->dn)
            ->version($this->ldap_verion)
            ->ssl($this->use_ssl)
            ->tls($this->use_tls)
            ->auth($this->auth_login, $this->auth_password);
        
        $this->assertTrue($lauth);

    }

}
