<?php namespace Comodojo\Ldaph;

use \Comodojo\Exception\LdaphException;

/**
 * ldaph: poor man's php ldap class
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <info@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
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
     * Constructor method
     * 
     * Prepare environment for connection (bind)
     *
     * @param   string  $server ldap server (ip or FQDN)
     * @param   int     $port   port to connect to
     *
     * @return  Object  $this
     */
    public function __construct($server, $port) {
        
        if ( empty($server) OR empty($port) ) throw new LdaphException("Invalid LDAP parameters", 1401);
        
        if (!function_exists("ldap_connect")) throw new LdaphException("PHP ldap extension not available", 1407);

        $this->server = $server;

        $this->port = filter_var($port, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 1,
                "max_range" => 65535,
                "default" => 389
                )
            )
        );

    }

    /**
     * Set ldap base
     * 
     * @param   string  $dcs    ldap base, comma separated, not spaced
     *
     * @return  Object  $this
     */
    public final function base($dcs) {

        if ( empty($dcs) ) throw new LdaphException($dcs, 1410);

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
     *
     * @return  Object  $this
     */
    public final function dn($dn) {

        if ( empty($dn) )  throw new LdaphException($dns, 1411);

        $this->dn = str_replace(' ', '', $dn);

        return $this;

    }

    /**
     * Set ldap version: 2 or 3 (default)
     * 
     * @param   int $mode   ldap protocol version
     *
     * @return  Object  $this
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
     *
     * @return  Object  $this
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
     *
     * @return  Object  $this
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
     *
     * @return  Object  $this
     */
    public final function sso($mode=true) {

        $mode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        if ($mode === true) {

            if ( !function_exists('ldap_sasl_bind') ) throw new LdaphException("No LDAP SSO support", 1408);

            $this->sso = true;

        } else {
          
            $this->sso = false;

        }

        return $this;

    }

    /**
     * Set user/pass for bind and search
     * 
     * @param   string  $user
     * @param   string  $pass
     *
     * @return  Object  $this
     */
    public final function account($user, $pass) {
        
        if ( empty($user) OR empty($pass)) throw new LdaphException("Invalid LDAP user/pass", 1402);
        
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
     *
     * @return  Object  $this
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
     * @param   mixed    $f
     *
     * @return  Object  $this
     */
    public final function fields($f) {

        if ( empty($f) ) $this->fields = null;
        
        elseif ( is_array($f) ) $this->fields = $f;

        else $this->fields = array($f);

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
        
        if( empty($userName) OR empty($userPass) ) throw new LdaphException("Invalid LDAP user/pass", 1402);
        
        try {

            $auth = $this->setupConnection($userName, $userPass);

        } catch (LdaphException $le) {

            $this->unsetConnection();

            throw $le;

        } catch (\Exception $e) {

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
     *
     * @return  array
     */
    public function search($what="*", $clean=false) {
            
        try {

            $this->setupConnection($this->user, $this->pass);

            ldap_set_option($this->ldaph, LDAP_OPT_SIZELIMIT, 0);
            
            $result = $this->searchHelper($what, filter_var($clean, FILTER_VALIDATE_BOOLEAN));

        }
        catch (LdaphException $le) {

            $this->unsetConnection();

            throw $le;

        } catch (\Exception $e) {

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

        $this->ldaph = $this->ssl ? ldap_connect("ldaps://".$this->server, $this->port) : ldap_connect($this->server, $this->port);

        if (!$this->ldaph) throw new LdaphException(ldap_error($this->ldaph), 1403);
        
        ldap_set_option($this->ldaph, LDAP_OPT_PROTOCOL_VERSION, $this->version);
        ldap_set_option($this->ldaph, LDAP_OPT_REFERRALS, 0);

        if ($this->tls) {

            $tls = @ldap_start_tls($this->ldaph);

            if ( $tls === false ) throw new LdaphException(ldap_error($this->ldaph), 1403);

        }

        if ( $this->sso AND $_SERVER['REMOTE_USER'] AND $_SERVER["REMOTE_USER"] == $user AND $_SERVER["KRB5CCNAME"] ) {

            putenv("KRB5CCNAME=".$_SERVER["KRB5CCNAME"]);

            $bind = @ldap_sasl_bind($this->ldaph, NULL, NULL, "GSSAPI");

        } elseif ( is_null($user) OR is_null($pass) ) {

            $bind = @ldap_bind($this->ldaph);

        } else {

            $user_dn = str_replace('USERNAME', $user, $this->dn);
            $bind = @ldap_bind($this->ldaph, $user_dn, $pass);

        }

        if (!$bind) throw new LdaphException(ldap_error($this->ldaph), 1402);

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

        $result = empty($this->fields) ? ldap_search($this->ldaph, $base, $search) : ldap_search($this->ldaph, $base, $search, $this->fields);

        if (!$result) throw new LdaphException(ldap_error($this->ldaph), 1404);

        $to_return = ldap_get_entries($this->ldaph, $result);

        if (!$to_return) throw new LdaphException(ldap_error($this->ldaph), 1412);
        
        return $clean ? $this->searchCleaner($to_return) : $to_return;

    }

    /**
     * Normalize ldap search result into plain array
     */
    private function searchCleaner($results) {

        $entry = array();

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

                $entry[$key] = array();

            }

            foreach ($result as $subkey => $value) {
                
                if (is_int($subkey) OR $subkey=="count") continue;
                
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