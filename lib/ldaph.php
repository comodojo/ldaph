<?php

/**
 * ldaph.php
 * 
 * poor man's php ldap class
 * 
 * @package 	Comodojo Spare Parts
 * @author		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
 *
 * @tutorial please see README file
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

define("COMODOJO_GLOBAL_DEBUG_ENABLED", false);
define("COMODOJO_GLOBAL_DEBUG_LEVEL", "INFO");
define("COMODOJO_GLOBAL_DEBUG_FILE", null);

require_once("comodojo_debug.php");
require_once("comodojo_exceptions.php");

class ldap {
	
/********************** PRIVATE VARS *********************/

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

/********************** PRIVATE VARS *********************/

/********************* PUBLIC METHODS ********************/
	/**
	 * Constructor class
	 * 
	 * Prepare environment for connection and bind
	 */
	public function __construct($server, $port) {
		
		if ( empty($server) OR empty($port) ) {
			comodojo_debug('Invalid LDAP parameters','ERROR','ldap');
			throw new comodojoException("Invalid LDAP parameters", 1401);
		}
		
		if (!function_exists("ldap_connect")) {
			throw new comodojoException("PHP ldap extension not available", 1407);
		}

		$this->server = $server;
		$this->port = filter_var($port, FILTER_VALIDATE_INT);

		return $this;
		
	}

	public final function base($dcs) {

		if ( empty($dcs) ) {
			comodojo_debug('Invalid dc','ERROR','ldap');
			throw new comodojoException($dcs, 1410);
		}

		$pDc = str_replace(' ', '', $dcs);

		$this->dc = $pDc;
		
		return $this;

	}

	public final function dn($dn) {

		if ( empty($dn) ) {
			comodojo_debug('Invalid dn','ERROR','ldap');
			throw new comodojoException($dns, 1411);
		}

		$this->dn = str_replace(' ', '', $dn);

		return $this;

	}

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

	public final function sso($mode=true) {

		$mode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

		if ($mode === true) {
			if ( !function_exists('ldap_sasl_bind') ) {
				comodojo_debug('No LDAP SSO support','ERROR','ldap');
				throw new comodojoException("No LDAP SSO support", 1408);
			}
			$this->sso = true;
		}
		else {
			$this->sso = false;
		}

		return $this;

	}

	public final function account($user, $pass) {
		
		if ( empty($user) OR empty($pass)) {
			comodojo_debug('Invalid LDAP user/pass','ERROR','ldap');
			throw new comodojoException("Invalid LDAP user/pass", 1402);
		}
		
		$this->user = $user;
		$this->pass = $pass;
		
		return $this;

	}
	
	public final function searchbase($s) {
		
		if ( empty($s) ) {
			$this->searchbase = false;
		}
		else {
			$this->searchbase = str_replace(' ', '', $s);
		}

		return $this;

	}

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
	 * @param	string	$userName	The user to auth
	 * @param	string	$userPass	The password for user
	 * 
	 * @return	bool
	 */
	public function auth($userName, $userPass) {
		
		if( empty($userName) OR empty($userPass) ) { 
			comodojo_debug('Invalid LDAP user/pass','ERROR','ldap');
			throw new comodojoException("Invalid LDAP user/pass", 1402);
		}
		
		comodojo_debug('Starting LDAP auth','INFO','ldap');
		
		try {
			$auth = $this->setupConnection($userName, $userPass);
		} catch (Exception $e) {
			$this->unsetConnection();
			throw $e;
		}

		$this->unsetConnection();

		return $auth;
			
	}
	
	/**
	 * List the directory
	 */
	public function search($what="*", $clean=false) {
			
		comodojo_debug('Starting LDAP directory search','INFO','ldap');
		
		try {
			$this->setupConnection($this->user, $this->pass);
			ldap_set_option($this->ldaph, LDAP_OPT_SIZELIMIT, 0);
			$result = $this->search_helper($what, filter_var($clean, FILTER_VALIDATE_BOOLEAN));
		} catch (Exception $e) {
			$this->unsetConnection();
			throw $e;
		}

		$this->unsetConnection();

		return $result;

	}

/********************* PUBLIC METHODS ********************/

/********************* PRIVATE METHODS *******************/	
	/**
	 * Setup an LDAP connection to server
	 */
	private function setupConnection($user=null, $pass=null) {

		if ($this->ssl) {
			$this->ldaph = ldap_connect("ldaps://".$this->server, $this->port);
		}
		else {
			$this->ldaph = ldap_connect($this->server, $this->port);
		}
		
		if (!$this->ldaph) {
			comodojo_debug('Unable to connect to ldap server: '.ldap_error($this->ldaph),'ERROR','ldap');
			throw new comodojoException(ldap_error($this->ldaph), 1403);
		}
		
		comodojo_debug('Connected to LDAP server '.$this->server.':'.$this->port.($this->ssl ? ' using SSL' : ''),'INFO','ldap');
		
		ldap_set_option($this->ldaph, LDAP_OPT_PROTOCOL_VERSION, $this->version);
		ldap_set_option($this->ldaph, LDAP_OPT_REFERRALS, 0);

		if ($this->tls) {

			$tls = @ldap_start_tls($this->ldaph);

			if ($tls) {
				comodojo_debug('Connection is using TLS','INFO','ldap');
			}
			else {
				comodojo_debug('Ldap error, TLS does not start correctly: '.ldap_error($this->ldaph),'ERROR','ldap');
				throw new comodojoException(ldap_error($this->ldaph), 1403);
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
			comodojo_debug('Ldap error, server refuse to bind: '.ldap_error($this->ldaph),'ERROR','ldap');
			throw new comodojoException(ldap_error($this->ldaph), 1402);
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
	 * 
	 */
	private function search_helper($what, $clean) {

		//$base = !$this->searchbase ? $this->dn : $this->searchbase;

		$base = $this->dc;
		$search = str_replace('PATTERN', $what, $this->searchbase);

		if ( empty($this->fields) ) {
			$result = ldap_search($this->ldaph, $base, $search);
		}
		else {
			$result = ldap_search($this->ldaph, $base, $search, $this->fields);
		}

		if (!$result) {
			comodojo_debug('Unable to search through ldap directory','ERROR','ldap');
			throw new comodojoException(ldap_error($this->ldaph), 1404);
		}

		$to_return = ldap_get_entries($this->ldaph, $result);

		if (!$to_return) {
			comodojo_debug('Unable to get ldap entries','ERROR','ldap');
			throw new comodojoException(ldap_error($this->ldaph), 1412);
		}
		
		if ($clean) {
			return $this->search_cleaner($to_return);
		}
		else {
			return $to_return;
		}
		
	}

	private function search_cleaner($results) {

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

	
/********************* PRIVATE METHODS *******************/
	
}

?>