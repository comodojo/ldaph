<?php

class LdaphTest extends \PHPUnit_Framework_TestCase {

    protected $ldap_server = "ldap.forumsys.com";
    protected $ldap_port  = 389;
    protected $ldap_verion = 3;
    protected $dn = "uid=USERNAME,dc=example,dc=com";
    protected $use_ssl = false;
    protected $use_tls = false;
    protected $auth_login = 'einstein';
    protected $auth_password = 'password';
    protected $base = "dc=example,dc=com";
    protected $searchbase = "(uid=PATTERN)";
    protected $fields = array("mail", "sn", "cn");

    /**
     * @expectedException        Comodojo\Exception\LdaphException
     */
    public function testInvalidServer() {
        
        $ldap = new \Comodojo\Ldaph\Ldaph('');

    }

    public function testProperties() {

        $ldap = new \Comodojo\Ldaph\Ldaph($this->ldap_server, $this->ldap_port);
    
        $result = $ldap->dn($this->dn)
            ->version($this->ldap_verion)
            ->ssl($this->use_ssl)
            ->tls($this->use_tls)
            ->base($this->base)
            ->searchbase($this->searchbase)
            ->sso(false)
            ->account($this->auth_login, $this->auth_password)
            ->fields($this->fields);

        $this->assertInstanceOf('\Comodojo\Ldaph\Ldaph', $result);

    }

}
