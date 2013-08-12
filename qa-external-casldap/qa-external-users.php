<?php

/*
	=========================================================================
	THIS FILE ALLOWS YOU TO INTEGRATE WITH AN EXISTING USER MANAGEMENT SYSTEM
	=========================================================================

	It is used if QA_EXTERNAL_USERS is set to true in qa-config.php.
*/

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}

	// Loading configuration
	require_once 'config.php';

/*
	==============
	Initiliaze CAS
	==============
 */
	phpCAS::client(CAS_VER, CAS_HOST, CAS_PORT, CAS_CTX);
	
	// SSL certification validation
	if (constant('CAS_CA_CERT_FILE')!="") {
		phpCAS::setCasServerCACert(CAS_CA_CERT_FILE);
	}
	else {
		phpCAS::setNoCasServerValidation();
	}

/*
	 Return user informations from LDAP
	 
	 Parameter : $user = The user login
	 
	 Return : Array of user informations in QA format
*/
	function get_ldap_user_infos($user) {
		if (isset($_SESSION['UserLdapInfos']) && !isset($_REQUEST['ldap_refresh'])) {
			return $_SESSION['UserLdapInfos'];
		}
		$infos=array(
			'userid'		=> $user,
			'publicusername'	=> $user,
		);
		global $CAS_ADMIN_USERS;
		if (in_array($user,$CAS_ADMIN_USERS)) {
			$infos['level'] = QA_USER_LEVEL_SUPER;
		}
		else {
			$infos['level'] = QA_USER_LEVEL_BASIC;
		}
	
		try {
			$con=ldap_connect(LDAP_SERVER);
			$filter=sprintf(LDAP_USER_FILTER,$user);
			$attrs=array(LDAP_MAIL_ATTR,LDAP_PUBLIC_NAME_ATTR,LDAP_USERID_ATTR);
			if (constant('LDAP_ALTERNATE_MAIL_ATTR') != '') {
				$attrs[]=LDAP_ALTERNATE_MAIL_ATTR;
			}
			$res=ldap_search($con,LDAP_USER_BASEDN,$filter,$attrs);
			$uinfos=ldap_get_entries($con,$res);
			if ($uinfos['count']==1) {
				if (isset($uinfos[0][LDAP_MAIL_ATTR][0])) {
					$infos['email']=$uinfos[0][LDAP_MAIL_ATTR][0];
				}
				elseif (constant('LDAP_ALTERNATE_MAIL_ATTR') != '' && isset($uinfos[0][LDAP_ALTERNATE_MAIL_ATTR][0])) {
					$infos['email']=$uinfos[0][LDAP_ALTERNATE_MAIL_ATTR][0];
				}
				if (isset($uinfos[0][LDAP_PUBLIC_NAME_ATTR][0])) {
					$infos['publicusername']=$uinfos[0][LDAP_PUBLIC_NAME_ATTR][0];
				}
				if (isset($uinfos[0][LDAP_USERID_ATTR][0])) {
					$infos['userid']=$uinfos[0][LDAP_USERID_ATTR][0];
				}
			}
		} catch (Exception $e) {
			error_log('Fail to get user infos from LDAP : '.$e->getMessage());
		}
		error_log("User $user infos : ".print_r($infos,true));
		$_SESSION['UserLdapInfos']=$infos;
		return $infos;
	}


	function get_ldap_userid_by_publicname($publicname) {
		$userid=NULL;
		try {
			$con=ldap_connect(LDAP_SERVER);
			$filter=sprintf(LDAP_USER_FILTER_BY_PUBLIC_NAME,$publicname);
			$attrs=array(LDAP_USERID_ATTR);
			$res=ldap_search($con,LDAP_USER_BASEDN,$filter,$attrs);
			$uinfos=ldap_get_entries($con,$res);
			if (isset($uinfos[0][LDAP_USERID_ATTR][0])) {
				$userid=$uinfos[0][LDAP_USERID_ATTR][0];
			}
			else {
				error_log('User '.$publicname.' does not have '.LDAP_USERID_ATTR.' attribute. Fail to get userid from LDAP.');
			}
		}
		catch (Exception $e) {
			error_log('Fail to get userid from LDAP : '.$e->getMessage());
		}
		return $userid;
	}

	function qa_get_mysql_user_column_type() {
		return 'VARCHAR(32)';
	}


	function qa_get_login_links($relative_url_prefix, $redirect_back_to_url)
/*
	===========================================================================
	YOU MUST MODIFY THIS FUNCTION, BUT CAN DO SO AFTER Q2A CREATES ITS DATABASE
	===========================================================================

	You should return an array containing URLs for the login, register and logout pages on
	your site. These URLs will be used as appropriate within the Q2A site.
	
	You may return absolute or relative URLs for each page. If you do not want one of the links
	to show, omit it from the array, or use null or an empty string.
	
	If you use absolute URLs, then return an array with the URLs in full (see example 1 below).

	If you use relative URLs, the URLs should start with $relative_url_prefix, followed by the
	relative path from the root of the Q2A site to your login page. Like in example 2 below, if
	the Q2A site is in a subdirectory, $relative_url_prefix.'../' refers to your site root.
	
	Now, about $redirect_back_to_url. Let's say a user is viewing a page on the Q2A site, and
	clicks a link to the login URL that you returned from this function. After they log in using
	the form on your main site, they want to automatically go back to the page on the Q2A site
	where they came from. This can be done with an HTTP redirect, but how does your login page
	know where to redirect the user to? The solution is $redirect_back_to_url, which is the URL
	of the page on the Q2A site where you should send the user once they've successfully logged
	in. To implement this, you can add $redirect_back_to_url as a parameter to the login URL
	that you return from this function. Your login page can then read it in from this parameter,
	and redirect the user back to the page after they've logged in. The same applies for your
	register and logout pages. Note that the URL you are given in $redirect_back_to_url is
	relative to the root of the Q2A site, so you may need to add something.
*/
	{

		return array(
			'login' => null,
			'register' => null,
			'logout' => null,
		);

	}
	

	function qa_get_logged_in_user()
/*
	Check if user is currently logged in : If not, return null. If so, return an array 
        with the following elements:

	* userid: a user id appropriate for your response to qa_get_mysql_user_column_type()
	* publicusername: a user description you are willing to show publicly, e.g. the username
	* email: the logged in user's email address
	* level: one of the QA_USER_LEVEL_* values below to denote the user's privileges:
	
	QA_USER_LEVEL_BASIC, QA_USER_LEVEL_EDITOR, QA_USER_LEVEL_ADMIN, QA_USER_LEVEL_SUPER
	
	To indicate that the user is blocked you can also add an element 'blocked' with the value true.
	Blocked users are not allowed to perform any write actions such as voting or posting.
	
	The result of this function will be passed to your other function qa_get_logged_in_user_html()
	so you may add any other elements to the returned array if they will be useful to you.

	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries.
	
	In order to access the admin interface of your Q2A site, ensure that the array element 'level'
	contains QA_USER_LEVEL_ADMIN or QA_USER_LEVEL_SUPER when you are logged in.
*/
	{

		phpCAS::forceAuthentication();

		if (isset($_REQUEST['logout'])) {
			unset($_SESSION['UserLdapInfos']);
			phpCAS::logout();
		}

		$user=phpCAS::getUser();
		if (!empty($user)) {
			return  get_ldap_user_infos($user);
		}
	
		return null;
		
	}

	
	function qa_get_user_email($userid)
/*
	Return the email address for user $userid, or null if you don't know it.
*/
	{

		$infos=get_ldap_user_infos($user);
		return (isset($infos['email'])?$infos['email']:null);

	}
	

	function qa_get_userids_from_public($publicusernames)
/*
	You should take the array of public usernames in $publicusernames, and return an array which
	maps valid usernames to internal user ids. For each element of this array, the username should be
	in the key, with the corresponding user id in the value. If your usernames are case- or accent-
	insensitive, keys should contain the usernames as stored, not necessarily as in $publicusernames.
	
	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries. If you
	access this database or any other, try to use a single query instead of one per user.
*/
	{

		$publictouserid=array();
		
		foreach ($publicusernames as $publicusername)
			$publictouserid[$publicusername]=get_ldap_userid_by_publicname($publicusername);
		
		return $publictouserid;

	}


	function qa_get_public_from_userids($userids)
/*
	This is exactly like qa_get_userids_from_public(), but works in the other direction.
	
	You should take the array of user identifiers in $userids, and return an array which maps valid
	userids to public usernames. For each element of this array, the userid you were given should
	be in the key, with the corresponding username in the value.
	
	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries. If you
	access this database or any other, try to use a single query instead of one per user.
*/
	{

		$useridtopublic=array();
		
		foreach ($userids as $userid)
			$useridtopublic[$userid]=$userid;
		
		return $useridtopublic;

	}


	function qa_get_logged_in_user_html($logged_in_user, $relative_url_prefix)
/*
	You should return HTML code which identifies the logged in user, to be displayed next to the
	logout link on the Q2A pages. This HTML will only be shown to the logged in user themselves.

	$logged_in_user is the array that you returned from qa_get_logged_in_user(). Hopefully this
	contains enough information to generate the HTML without another database query, but if not,
	call qa_db_connection() to get the connection to the Q2A database.

	$relative_url_prefix is a relative URL to the root of the Q2A site, which may be useful if
	you want to include a link that uses relative URLs. If the Q2A site is in a subdirectory of
	your site, $relative_url_prefix.'../' refers to your site root (see example 1).

	If you don't know what to display for a user, you can leave the default below. This will
	show the public username, linked to the Q2A profile page for the user.
*/
	{
	
	//	By default, show the public username linked to the Q2A profile page for the user

		$publicusername=$logged_in_user['publicusername'];
		
		return '<A HREF="'.htmlspecialchars($relative_url_prefix.'user/'.urlencode($publicusername)).
			'" CLASS="qa-user-link">'.htmlspecialchars($publicusername).'</A>';

	}


	function qa_get_users_html($userids, $should_include_link, $relative_url_prefix)
/*

	You should return an array of HTML to display for each user in $userids. For each element of
	this array, the userid should be in the key, with the corresponding HTML in the value.
	
	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries. If you
	access this database or any other, try to use a single query instead of one per user.
	
	If $should_include_link is true, the HTML may include links to user profile pages.
	If $should_include_link is false, links should not be included in the HTML.
	
	$relative_url_prefix is a relative URL to the root of the Q2A site, which may be useful if
	you want to include links that uses relative URLs. If the Q2A site is in a subdirectory of
	your site, $relative_url_prefix.'../' refers to your site root (see example 1).
	
	If you don't know what to display for a user, you can leave the default below. This will
	show the public username, linked to the Q2A profile page for each user.
*/
	{

	//	By default, show the public username linked to the Q2A profile page for each user

		$useridtopublic=qa_get_public_from_userids($userids);
		
		$usershtml=array();

		foreach ($userids as $userid) {
			$publicusername=$useridtopublic[$userid];
			
			$usershtml[$userid]=htmlspecialchars($publicusername);
			
			if ($should_include_link)
				$usershtml[$userid]='<A HREF="'.htmlspecialchars($relative_url_prefix.'user/'.urlencode($publicusername)).
					'" CLASS="qa-user-link">'.$usershtml[$userid].'</A>';
		}
			
		return $usershtml;

	}


	function qa_avatar_html_from_userid($userid, $size, $padding)
/*
	
	You should return some HTML for displaying the avatar of $userid on the page.
	If you do not wish to show an avatar for this user, return null.
	
	$size contains the maximum width and height of the avatar to be displayed, in pixels.

	If $padding is true, the HTML you return should render to a square of $size x $size pixels,
	even if the avatar is not square. This can be achieved using CSS padding - see function
	qa_get_avatar_blob_html(...) in qa-app-format.php for an example. If $padding is false,
	the HTML can render to anything which would fit inside a square of $size x $size pixels.
	
	Note that this function may be called many times to render an individual page, so it is not
	a good idea to perform a database query each time it is called. Instead, you can use the fact
	that before qa_avatar_html_from_userid(...) is called, qa_get_users_html(...) will have been
	called with all the relevant users in the array $userids. So you can pull out the information
	you need in qa_get_users_html(...) and cache it in a global variable, for use in this function.
*/
	{
		return null; // show no avatars by default

	}
	
	
	function qa_user_report_action($userid, $action)
/*
	Informs you about an action by user $userid that modified the database, such as posting,
	voting, etc... If you wish, you may use this to log user activity or monitor for abuse.
	
	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries.
	
	$action will be a string (such as 'q_edit') describing the action. These strings will match the
	first $event parameter passed to the process_event(...) function in event modules. In fact, you might
	be better off just using a plugin with an event module instead, since you'll get more information.
	
	FYI, you can get the IP address of the user from qa_remote_ip_address().
*/
	{
		// do nothing by default
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/
