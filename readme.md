## comodojo.ldaph

[![Build Status](https://api.travis-ci.org/comodojo/ldaph.png)](http://travis-ci.org/comodojo/ldaph) [![Latest Stable Version](https://poser.pugx.org/comodojo/ldaph/v/stable)](https://packagist.org/packages/comodojo/ldaph) [![Total Downloads](https://poser.pugx.org/comodojo/ldaph/downloads)](https://packagist.org/packages/comodojo/ldaph) [![Latest Unstable Version](https://poser.pugx.org/comodojo/ldaph/v/unstable)](https://packagist.org/packages/comodojo/ldaph) [![License](https://poser.pugx.org/comodojo/ldaph/license)](https://packagist.org/packages/comodojo/ldaph)

poor man's php ldap class

Ldaph is a simple library made to handle LDAP/ActiveDirectory authentication and search.

It supports:

* ssl (ldaps)
* tls
* single sign on (Active Directory)

## Installation

- Using Composer

	Install [composer](https://getcomposer.org/), then:

	`` composer require comodojo/ldaph 1.0.* ``

-	Manually

	Download zipball from GitHub, extract it, include `src/LdaphException.php` and `src/Ldaph.php` in your project.

## Basic Usage

-	Creating an instance

	Class constructor expects ldap server and port as parameters. Wrap it in a try/catch block, since it may generate a `LdaphException` in case of wrong parameters or missed php ext.

	```php

	try {
		
		$ldap = new \Comodojo\Ldaph('ldap.exampe.com', 389);

	}
	catch (LdaphException $le){

		// handle exception here

	}

	```

-	User authentication

	```php

	$dn = "uid=john,dc=example,dc=com";

	try {

		$ldap = new \Comodojo\Ldaph('ldap.exampe.com', 389);
		$lauth = $ldap->dn($dn)->auth('john', 'doe');
	
	}
	catch (LdaphException $le){

		// handle exception here

	}

	```

	Defining DN, there is a special word USERNAME that will be replaced with first auth() parameter ($username).

	Examples of DN:

	* "USERNAME@example.com" (for Active Directory)
	* "uid=USERNAME,dc=example,dc=com" (for openLDAP)

-	Search LDAP tree

	Searching into ldap tree requires, at least:

	- base DN (base)
	- search DN (searchbase)
	- bind DN (dn)
	- account (user/pass)

	`search()` method will list ldap tree using this parameters.

	```php

	$dn = "uid=USERNAME,dc=example,dc=com";
    $base = "dc=example,dc=com";
    $searchbase = "(uid=PATTERN)";

	try {

		$ldap = new \Comodojo\Ldaph('ldap.exampe.com', 389);

		$lsearch = $ldap->base($base)
						->searchbase($searchbase)
						->dn($dn)
						->account('john', 'doe')
						->search("*",true);

	}
	catch (LdaphException $le){

		// handle exception here

	}

	```

	Special word 'PATTERN' in searchbase will be replaced with first `search()` parameter.

	Second parameter (if true) will return results in a more convenient, array-based form.

	Examples of searchbase (if you are looking for usernames):

	* "(&(!(objectClass=computer))(|(anr=PATTERN)))" (for Active Directory)
	* "(uid=PATTERN)" (for openLDAP)

## See also

[ldaph api](http://api.comodojo.org/framework/Comodojo/Ldaph/Ldaph.html)