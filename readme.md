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

Now you can init ldaph creating an instance of ldaph specifying ldap server address and port.

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
		die($e->getMessage());
	}

Ldaph will try to bind to ldap server using DN; special word USERNAME will be replaced with $username.

Example of DNs:

* "USERNAME@example.com" (for Active Directory)
* "uid=USERNAME,dc=example,dc=com" (for openLDAP)

### Search LDAP tree
