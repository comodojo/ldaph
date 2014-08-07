comodojo.ldaph
==============

poor man's php ldap class

## About

Ldaph is an extremely simple php class made to handle LDAP/ActiveDirectory authentication and search.

It supports also:

* ssl (ldaps)
* tls
* single sign on (Active Directory)

It is derived from comodojo.core framework as a Spare Part.

You can find more information at http://www.comodojo.org.

## Installation

- Using Composer

	Install [composer](https://getcomposer.org/), then:

	`` composer require comodojo/ldaph dev-master ``

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

	To authenticate an user you should specify the Distinguished Name to use and then call the 'auth' method, as in example. Ldaph will try to bind to ldap server using DN.

	```php

	try {

		$ldap = new \Comodojo\Ldaph('ldap.exampe.com', 389);
		$lauth = $ldap->dn($dn)->auth('username', 'userpassword');
	
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

	try {

		$ldap = new \Comodojo\Ldaph('ldap.exampe.com', 389);

		$lsearch = $ldap->base($base)
						->searchbase($searchbase)
						->dn($dn)
						->account($username, $userpassword)
						->search("*",true);

	}
	catch (LdaphException $le){

		// handle exception here

	}

	```

	Special word 'PATTERN' in searchbase will be replaced with first `search()` parameter and perform query.

	Second parameter (if true) will return results in a more convenient, array-based form.

	Examples of searchbase (if you are looking for usernames):

	* "(&(!(objectClass=computer))(|(anr=PATTERN)))" (for Active Directory)
	* "(uid=PATTERN)" (for openLDAP)

## Documentation

docs.comodojo.org

api.comodojo.org