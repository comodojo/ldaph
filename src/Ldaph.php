<?php namespace Comodojo\Ldaph;

/**
 * ldaph: poor man's php ldap class
 * 
 * @package     Comodojo ldaph (Spare Parts)
 * @author      comodojo <info@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use \Comodojo\Exception\LdaphException;

/**
 * Comodojo ldaph main class
 *
 * @param string
 */
class Ldaph {
    
    private $ldaph = false;

    private $version = 3;

    private $ssl = false;

    private $tls = false;

    private $sso = false;

    private $dc = '';

    private $dn = 'USER_NAME';

    private $searchbase = false;

    private $user = null;

    private $pass = null;

    private $fields = Array();

    /**
     * Constructor class
     * 
     * Prepare environment for connection (bind)
     *
     * @param   string  $server ldap server (ip or FQDN)
     * @param   int     $port   port to connect to
     */
    public function __construct($server, $port) {
        
        if ( empty($server) OR empty($port) ) {
            // debug('Invalid LDAP parameters','ERROR','ldap');
            throw new LdaphException("Invalid LDAP parameters", 1401);
        }
        
        if (!function_exists("ldap_connect")) {
            throw new LdaphException("PHP ldap extension not available", 1407);
        }

        $this->server = $server;
        $this->port = filter_var($port, FILTER_VALIDATE_INT);

        return $this;
        
    }

    /**
     * Set ldap base
     * 
     * @param   string  $dcs    ldap base, comma separated, not spaced
     */
    public final function base($dcs) {

        if ( empty($dcs) ) {
            // debug('Invalid dc','ERROR','ldap');
            throw new LdaphException($dcs, 1410);
        }

        $pDc = str_replace(' ', '', $dcs);

        $this->dc = $pDc;
        
        return $this;

    }

    /**
     * Set ldap distinguished name (used in ldap bind)
     * 
     * Before bind, special word USERNAME will be substituted by real username
     * 
     * @param   string  $dcs    ldap DN, comma separated, not spaced
     */
    public final function dn($dn) {

        if ( empty($dn) ) {
            // debug('Invalid dn','ERROR','ldap');
            throw new LdaphException($dns, 1411);
        }

        $this->dn = str_replace(' ', '', $dn);

        return $this;

    }

    /**
     * Set ldap version: 2 or 3 (default)
     * 
     * @param   int $mode   ldap protocol version
     */
    public final function version($mode=3) {

        $mode = filter_var($mode, FILTER_VALIDATE_INT);

        if ($mode === 2) {
            $this->version = 2;
        }
        else {
            $this->version = 3;
        }

        return $this;

    }

    /**
     * Enable/disable ssl for connection
     * 
     * @param   bool    $mode
     */
    public final function ssl($mode=true) {

        $mode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        if ($mode === true) {
            $this->ssl = true;
        }
        else {
            $this->ssl = false;
        }

        return $this;

    }

    /**
     * Enable/disable tls for connection
     * 
     * @param   bool    $mode
     */
    public final function tls($mode=true) {

        $mode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        if ($mode === true) {
            $this->tls = true;
        }
        else {
            $this->tls = false;
        }

        return $this;

    }

    /**
     * Enable/disable single sign on
     * 
     * @param   bool    $mode
     */
    public final function sso($mode=true) {

        $mode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        if ($mode === true) {
            if ( !function_exists('ldap_sasl_bind') ) {
                // debug('No LDAP SSO support','ERROR','ldap');
                throw new LdaphException("No LDAP SSO support", 1408);
            }
            $this->sso = true;
        }
        else {
            $this->sso = false;
        }

        return $this;

    }

    /**
     * Set user/pass for bind and search
     * 
     * @param   string  $user
     * @param   string  $pass
     */
    public final function account($user, $pass) {
        
        if ( empty($user) OR empty($pass)) {
            // debug('Invalid LDAP user/pass','ERROR','ldap');
            throw new LdaphException("Invalid LDAP user/pass", 1402);
        }
        
        $this->user = $user;
        $this->pass = $pass;
        
        return $this;

    }
    
    /**
     * Set ldap search base
     *
     * During search, special word PATTERN will be sbstituted by provided pattern
     * 
     * @param   string  $s
     */
    public final function searchbase($s) {
        
        if ( empty($s) ) {
            $this->searchbase = false;
        }
        else {
            $this->searchbase = str_replace(' ', '', $s);
        }

        return $this;

    }

    /**
     * Set fields to query ldap for
     * 
     * @param   array|string    $f
     */
    public final function fields($f) {

        if ( empty($f) ) {
            $this->fields = null;
        }
        elseif ( is_array($f) ) {
            $this->fields = $f;
        }
        else {
            $this->fields = Array($f);
        }

        return $this; 
    }

    /**
     * Authenticate an user via LDAP
     * 
     * @param   string  $userName   The user to auth
     * @param   string  $userPass   The password for user
     * 
     * @return  bool
     */
    public function auth($userName, $userPass) {
        
        if( empty($userName) OR empty($userPass) ) { 
            // debug('Invalid LDAP user/pass','ERROR','ldap');
            throw new LdaphException("Invalid LDAP user/pass", 1402);
        }
        
        // debug('Starting LDAP auth','INFO','ldap');
        
        try {

            $auth = $this->setupConnection($userName, $userPass);

        } catch (LdaphException $le) {

            $this->unsetConnection();
            throw $le;

        } catch (Exception $e) {

            // debug('Error ('.$e->getCode()."): ".$e->getMessage(),'INFO','ldap');
            $this->unsetConnection();
            throw $e;

        }

        $this->unsetConnection();

        return $auth;
            
    }
    
    /**
     * Search ldap directory for $what
     *
     * @param   string  $what   The pattern to search for (will replace the searcbase PATTERN special word)
     * @param   bool    $clean  If true, raw ldap_get_entries result will be normalized as plain array
     */
    public function search($what="*", $clean=false) {
            
        // debug('Starting LDAP directory search','INFO','ldap');
        
        try {

            $this->setupConnection($this->user, $this->pass);
            ldap_set_option($this->ldaph, LDAP_OPT_SIZELIMIT, 0);
            $result = $this->searchHelper($what, filter_var($clean, FILTER_VALIDATE_BOOLEAN));

        }
        catch (LdaphException $le) {

            $this->unsetConnection();
            throw $le;

        } catch (Exception $e) {

            // debug('Error ('.$e->getCode()."): ".$e->getMessage(),'INFO','ldap');
            $this->unsetConnection();
            throw $e;

        }

        $this->unsetConnection();

        return $result;

    }

    /**
     * Setup LDAP connection
     */
    private function setupConnection($user=null, $pass=null) {

        if ($this->ssl) {
            $this->ldaph = ldap_connect("ldaps://".$this->server, $this->port);
        }
        else {
            $this->ldaph = ldap_connect($this->server, $this->port);
        }
        
        if (!$this->ldaph) {
            // debug('Unable to connect to ldap server: '.ldap_error($this->ldaph),'ERROR','ldap');
            throw new LdaphException(ldap_error($this->ldaph), 1403);
        }
        
        // debug('Connected to LDAP server '.$this->server.':'.$this->port.($this->ssl ? ' using SSL' : ''),'INFO','ldap');
        
        ldap_set_option($this->ldaph, LDAP_OPT_PROTOCOL_VERSION, $this->version);
        ldap_set_option($this->ldaph, LDAP_OPT_REFERRALS, 0);

        if ($this->tls) {

            $tls = @ldap_start_tls($this->ldaph);

            if ($tls) {
                // debug('Connection is using TLS','INFO','ldap');
            }
            else {
                // debug('Ldap error, TLS does not start correctly: '.ldap_error($this->ldaph),'ERROR','ldap');
                throw new LdaphException(ldap_error($this->ldaph), 1403);
            }

        }

        if ($this->sso AND $_SERVER['REMOTE_USER'] AND $_SERVER["REMOTE_USER"] == $user AND $_SERVER["KRB5CCNAME"]) {
            putenv("KRB5CCNAME=".$_SERVER["KRB5CCNAME"]);
            $bind = @ldap_sasl_bind($this->ldaph, NULL, NULL, "GSSAPI");
        }
        elseif ( is_null($user) OR is_null($pass) ) {
            $bind = @ldap_bind($this->ldaph);
        }
        else {
            $user_dn = str_replace('USERNAME', $user, $this->dn);
            $bind = @ldap_bind($this->ldaph, $user_dn, $pass);
        }

        if (!$bind) {
            // debug('Ldap error, server refuse to bind: '.ldap_error($this->ldaph),'ERROR','ldap');
            throw new LdaphException(ldap_error($this->ldaph), 1402);
        }

        return true;

    }
    
    /**
     * Unset a previously opened ldap connection
     */
    private function unsetConnection() {
        @ldap_unbind($this->ldaph);
    }

    /**
     * Helper for $this->search()
     */
    private function searchHelper($what, $clean) {

        $base = $this->dc;
        $search = str_replace('PATTERN', $what, $this->searchbase);

        if ( empty($this->fields) ) {
            $result = ldap_search($this->ldaph, $base, $search);
        }
        else {
            $result = ldap_search($this->ldaph, $base, $search, $this->fields);
        }

        if (!$result) {
            // debug('Unable to search through ldap directory','ERROR','ldap');
            throw new LdaphException(ldap_error($this->ldaph), 1404);
        }

        $to_return = ldap_get_entries($this->ldaph, $result);

        if (!$to_return) {
            // debug('Unable to get ldap entries','ERROR','ldap');
            throw new LdaphException(ldap_error($this->ldaph), 1412);
        }
        
        if ($clean) {
            return $this->searchCleaner($to_return);
        }
        else {
            return $to_return;
        }
        
    }

    /**
     * Normalize ldap search result into plain array
     */
    private function searchCleaner($results) {

        $entry = Array();

        unset($results['count']);

        foreach ($results as $key => $result) {

            unset($result["count"]);

            $valid = true;
            foreach ($this->fields as $field) {
                if (!array_key_exists(strtolower($field), $result)) $valid = false;
            }

            if (!$valid) {
                unset($result[$key]);
                continue;
            }
            else {
                $entry[$key] = Array();
            }

            foreach ($result as $subkey => $value) {
                
                if (is_int($subkey) OR $subkey=="count") {
                    continue;
                }
                else {
                    if (is_scalar($value)) {
                        $entry[$key][$subkey] = $value;
                    }
                    if (is_array($value)) {
                        if ($value["count"] == 1) {
                            $entry[$key][$subkey] = $value[0];
                        }
                        else {
                            unset($value["count"]);
                            $entry[$key][$subkey] = $value;
                        }
                    }
                }

            }

        }

        return $entry;

    }
    
}