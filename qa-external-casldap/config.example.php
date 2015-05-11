<?php

 /*
  * Path of PHPCAS librairy
  *
  * You have to specified the path of CAS.php file.
  * 
  * On Debian (since wheezy), install php-cas package and specify
  * /usr/share/php/CAS.php
  */
define('PHPCAS_PATH','/usr/share/php/CAS.php');

// Loading PHP CAS librairy
require_once PHPCAS_PATH;

/*
 * CAS Configuration
 */

// Hostname of CAS server (ex: cas.example.com)
define('CAS_HOST','cas.example.com');

// HTTP (or HTTPS) port of CAS Server (ex: 443)
define('CAS_PORT',443);

// URL context of CAS Server (ex: /cas)
define('CAS_CTX','/cas');

// CAS protocol version
// Possible values : CAS_VERSION_1_0 or CAS_VERSION_2_0
define('CAS_VER',CAS_VERSION_2_0);

// SSL certificate path of CAS server
// If empty, CAS server's certificate will not be validated.
define('CAS_CA_CERT_FILE','');

// List user's role
// Roles : QA_USER_LEVEL_BASIC, QA_USER_LEVEL_EDITOR, QA_USER_LEVEL_ADMIN, QA_USER_LEVEL_SUPER
$CAS_USERS_ROLE=array(
	'qauser1' => QA_USER_LEVEL_SUPER,
	'qauser2' => QA_USER_LEVEL_EDITOR,
);

// CAS default user role
define('CAS_DEFAULT_USER_ROLE',QA_USER_LEVEL_BASIC);

/*
 * LDAP Configuration
 */

// LDAP URI (Ex : ldap://ldapserver:389). 
// Moreover, this constant permit to configure LDAPS connection using URI ldaps://ldapserver:636.
define('LDAP_SERVER_URI','ldap://ldap.exemple.com:389');

// LDAP Authentification
// Anonymous connection when LDAP_SERVER_BIND_DN is empty (no ldap_bind)
define('LDAP_SERVER_BIND_DN', '');
define('LDAP_SERVER_BIND_PASSWORD', '');

// LDAP basedn to search user
define('LDAP_USER_BASEDN','dc=example,dc=com');

// LDAP filter to search user (compose with %%user%% pattern replace by userid)
define('LDAP_USER_FILTER','(&(objectClass=posixAccount)(|(uid=%%user%%)(mail=%%user%%)))');

// LDAP filter to search user by publicname (compose with %%user%% pattern replace by publicname)
define('LDAP_USER_FILTER_BY_PUBLIC_NAME','(&(objectClass=posixAccount)(|(cn=%%user%%)(displayname=%%user%%)))');

// LDAP login attribute
define('LDAP_USERID_ATTR','uid');

// LDAP mail attribute
define('LDAP_MAIL_ATTR','mail');

// LDAP mail alternative attribute
define('LDAP_ALTERNATE_MAIL_ATTR','supannMailPerso');

// LDAP public name attribute
define('LDAP_PUBLIC_NAME_ATTR','cn');
