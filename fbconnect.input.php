<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=fbconnect
Part=input
File=fbconnect.input
Hooks=input
Tags=
Order=10
[END_SED_EXTPLUGIN]
==================== */

defined('SED_CODE') or die('Wrong URL');

require_once $cfg['plugins_dir'] . '/fbconnect/lib/facebook.php';
require_once $cfg['plugins_dir'] . '/fbconnect/inc/functions.php';

$facebook = new Facebook($cfg['plugin']['fbconnect']['api_key'], $cfg['plugin']['fbconnect']['secret_key']);

$fb_uid = $facebook->get_loggedin_user();

if ($usr['id'] == 0)
{
	sed_sql_query("UPDATE $db_users SET user_fbid = 0 WHERE user_id = 8");
}

if ($fb_uid > 0)
{
	// Provide some user data from FB
	if (empty($_SESSION['fb_usr']))
	{
		$fb_res = $facebook->api_client->users_getInfo($fb_uid, 'current_location, hometown_location, name, last_name, first_name, locale, pic_square, profile_url, proxied_email, timezone, birthday_date, website');
		$fb_usr = $fb_res[0];
		$_SESSION['fb_usr'] = $fb_usr;
	}
	else
	{
		$fb_usr = $_SESSION['fb_usr'];
	}
	
	if ($usr['id'] > 0)
	{
		// Logged in both on FB and Cotonti
		if (empty($usr['user_fbid']))
		{
			sed_sql_query("UPDATE $db_users SET user_fbid = $fb_uid WHERE user_id = " . $usr['id']);
		}
		// continue normal execution
	}
	elseif (!defined('SED_USERS') && !defined('SED_MESSAGE')) // avoid deadlocks and loops
	{
		// Check if this FB user has a native Cotonti account
		$fb_res = sed_sql_query("SELECT * FROM $db_users WHERE user_fbid = $fb_uid");
		if ($row = sed_sql_fetchassoc($fb_res))
		{
			// Load user account and log him in
			fb_autologin($row);
			exit;
		}
		else
		{
			// Forward to quick account registration,
			// except for users module to let existing users log in and have FB UID filled
			sed_redirect(sed_url('users', 'm=register', '', TRUE));
			exit;
		}
		sed_sql_freeresult($fb_res);
	}
}
?>
