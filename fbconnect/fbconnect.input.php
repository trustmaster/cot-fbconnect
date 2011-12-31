<?php
/* ====================
 * [BEGIN_COT_EXT]
 * Hooks=input
 * [END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

require_once $cfg['plugins_dir'] . '/fbconnect/lib/facebook.php';
require_once $cfg['plugins_dir'] . '/fbconnect/inc/fbconnect.functions.php';

$facebook = new Facebook(array(
  'appId'  => $cfg['plugin']['fbconnect']['app_id'],
  'secret' => $cfg['plugin']['fbconnect']['secret_key'],
  'cookie' => true,
));

$fb_user = $facebook->getUser();

$fb_connected = false;
$fb_me = null;

if ($fb_user)
{
	try
	{
		$fb_me = $facebook->api('/me');
		$fb_connected = true;
	}
	catch (FacebookApiException $fb_e)
	{
		error_log($fb_e);
		$fb_connected = false;
	}
}

if ($fb_connected)
{
	if ($usr['id'] > 0)
	{
		// Logged in both on FB and Cotonti
		if (empty($usr['profile']['user_fbid']))
		{
			$db->query("UPDATE $db_users SET user_fbid = '".$db->prep($fb_user)."'
				WHERE user_id = " . $usr['id']);
		}
		// continue normal execution
	}
	elseif (!defined('COT_USERS') && !defined('COT_AUTH') && !defined('COT_MESSAGE')
		&& !($_GET['e'] == 'fbconnect'
			&& $_GET['m'] == 'register')) // avoid deadlocks and loops
	{
		// Remember this URL
		cot_uriredir_store();
		// Check if this FB user has a native Cotonti account
		$fb_res = $db->query("SELECT * FROM $db_users WHERE user_fbid = '".$db->prep($fb_user)."'");
		if ($row = $fb_res->fetch())
		{
			// Load user account and log him in
			fb_autologin($row);
			exit;
		}
		elseif ($cfg['plugin']['fbconnect']['autoreg'])
		{
			// Forward to quick account registration,
			// except for users module to let existing users log in and have FB UID filled
			cot_redirect(cot_url('plug', 'e=fbconnect&m=register', '', TRUE));
			exit;
		}
		$fb_res->closeCursor();
	}
}

// Disable Anti-CSRF for built-in registration
if ($_GET['e'] == 'fbconnect' && $_GET['m'] == 'register')
{
	define('COT_NO_ANTIXSS', true);
	$sys['uriredir_prev'] = $_SESSION['s_uri_redir'];
}

?>
