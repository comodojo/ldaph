comodojo.ldaph
==============

> poor man's php ldap class

Ldaph is an extremely simple php class made to handle LDAP/ActiveDirectory authentication and search.

It supports also:

* ssl (ldaps)
* tls
* single sign on (Active Directory)

About
-----

Ldaph is derived from comodojo.core framework as a Spare Part.

You can find more information at http://www.comodojo.org.

Installation
------------

### Using Composer

### Manually

Download ldaph from http://www.comodojo.org or clone from github, then copy lib/comodojo folder inside your project.

Documentation
-------------

Usage
-----

### Include library

First of all, you need to include ldaph class, contained in lib/comodojo folder:

	require("lib/comodojo/ldaph.php");

Now you can create an instance of comodojo\ldaph specifying LDAP server address and port.

Constructor will not init connection but can return exceptions, so wrap it in a try/catch block:

	try {
		$ldap = new comodojo\ldaph($ldap_server, $ldap_port);
	}
	catch (Exception $e){
		// handle exceptions here
	}

### Authenticate an user

To authenticate an user you should specify the Distinguished Name to use and then call the 'auth' method:

	try {
		$ldap = new comodojo\ldaph($ldap_server, $ldap_port);
		$lauth = $ldap->dn($dn)->auth($username, $userpassword);
	}
	catch (Exception $e){
		// handle exceptions here
	}

Ldaph will try to bind to ldap server using DN; special word USERNAME will be replaced with $username.

Examples of DN:

* "USERNAME@example.com" (for Active Directory)
* "uid=USERNAME,dc=example,dc=com" (for openLDAP)

### Search LDAP tree

For searching, you need at least to specify:

* base DN (base)
* search DN (searchbase)
* bind DN (dn)
* account (user/pass)

Then simply call 'search' method:

	try {
		$ldap = new comodojo\ldaph($ldap_server, $ldap_port);
		$lauth = $ldap->base($base)
		->searchbase($searchbase)
		->dn($dn)
		->account($username, $userpassword)
		->search("*",true);
	}
	catch (Exception $e){
		// handle exceptions here
	}

Ldaph will replace the special word 'PATTERN' in searchbase with first parameter and perform query.

Second parameter (if true) will return results in a more convenient, array-based form.

Examples of searchbase (if you are looking for usernames):

* "(&(!(objectClass=computer))(|(anr=PATTERN)))" (for Active Directory)
* "(uid=PATTERN)" (for openLDAP)