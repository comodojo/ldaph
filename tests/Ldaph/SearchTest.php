<?php namespace Comodojo\Ldaph\Tests;

class SearchTest extends \PHPUnit_Framework_TestCase {

    protected $ldap_server = "ldap.forumsys.com";
    protected $ldap_port  = 389;
    protected $ldap_verion = 3;
    protected $dn = "uid=USERNAME,dc=example,dc=com";
    protected $base = "dc=example,dc=com";
    protected $searchbase = "(uid=PATTERN)";
    protected $use_ssl = false;
    protected $use_tls = false;
    protected $auth_login = 'euclid';
    protected $auth_password = 'password';

    public function testAuthentication() {
        
        $ldap = new \Comodojo\Ldaph\Ldaph($this->ldap_server, $this->ldap_port);
    

        $data = $ldap->base($this->base)
            ->searchbase($this->searchbase)
            ->dn($this->dn)
            ->version($this->ldap_verion)
            ->ssl($this->use_ssl)
            ->tls($this->use_tls)
            ->account($this->auth_login, $this->auth_password)
            ->search("*",true);
        
        $this->assertInternalType('array', $data);

        foreach ($data as $result) {
            
            $this->assertInternalType('array', $result);

            $this->assertArrayHasKey("uid", $result);

            $this->assertArrayHasKey("cn", $result);

            $this->assertArrayHasKey("sn", $result);

        }

    }

}
