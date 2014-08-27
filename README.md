qa-external-casldap
===================

Authentification plugin CAS+LDAP for [Question2Answer](http://www.question2answer.org/)

Requirement
-----------

  * [phpCAS](https://wiki.jasig.org/display/CASC/phpCAS) : On Debian (since Wheezy), install _php-cas_ package

Configuration
-------------

  * **CAS_HOST** : Hostname of CAS server (ex : _cas.example.com_)
  * **CAS_PORT** : HTTP (or HTTPS) port of CAS server (ex : _443_)
  * **CAS_CTX** : URL context path of CAS server (ex: /cas)
  * **CAS_VER** : CAS protocol version. Possible values :  *CAS_VERSION_1_0* or *CAS_VERSION_2_0*
  * **CAS_CA_CERT_FILE** : SSL certificate path of CAS server. If empty, the SSL certificate will not be validated.
  * **$CAS_USERS_ROLE** : PHP array listing specific user role. Users's role must be define using Q2A constant :
    * QA_USER_LEVEL_BASIC
    * QA_USER_LEVEL_EDITOR
    * QA_USER_LEVEL_ADMIN
    * QA_USER_LEVEL_SUPER

	Example : 

	    $CAS_USERS_ROLE = array(
	      'user1' => QA_USER_LEVEL_EDITOR,
	      'user2' => QA_USER_LEVEL_SUPER
	    );
 
  * **CAS_DEFAULT_USER_ROLE** : User default role define using Q2A constant (see _$CAS_USERS_ROLE_)
  * **LDAP_SERVER** : Hostname or IP address of LDAP server (ex: _ldap.example.com_)
  * **LDAP_USER_BASEDN** : basedn to search user in LDAP directory (ex: _dc=example,dc=com_)
  * **LDAP_USER_FILTER** : LDAP filter to search user in LDAP directory. The filter will be composed with user CAS login (remplace by **%s**). (ex: _(&(objectClass=posixAccount)(uid=%s))_)
  * **LDAP_USERID_ATTR** : LDAP attribute corresponding to CAS user login (ex: //uid//)
  * **LDAP_MAIL_ATTR** : LDAP attribute corresponding to user's mail (ex: //mail//)
  * **LDAP_ALTERNATE_MAIL_ATTR** : LDAP attribute corresponding to alternative user's mail (ex: //supannMailPerso//)
  * **LDAP_PUBLIC_NAME_ATTR** : LDAP attribute corresponding to user's full name (ex: //displayName//)
