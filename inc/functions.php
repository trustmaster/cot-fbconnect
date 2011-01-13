<?php
/**
 * Auxilliary functions for FaceBook Connect plugin
 * @author Trustmaster
 * @copyright (с) Vladimir Sibirov, Cotonti Team 2009
 */

/**
 * Logs a Cotonti user in
 *
 * @param array $row User record
 */
function fb_autologin($row)
{
	global $facebook, $usr, $sys, $cfg, $redirect, $db_users, $db_online;

	$rusername = $row['user_name'];
	// banned/inactive
	if ($row['user_maingrp'] == -1)
	{
		$facebook->clear_cookie_state();
		sed_log("Log in attempt, user inactive : ".$rusername, 'usr');
		sed_redirect(sed_url('message', 'msg=152', '', true));
		exit;
	}
	if ($row['user_maingrp'] == 2)
	{
		$facebook->clear_cookie_state();
		sed_log("Log in attempt, user inactive : ".$rusername, 'usr');
		sed_redirect(sed_url('message', 'msg=152', '', true));
		exit;
	}
	elseif ($row['user_maingrp'] == 3)
	{
		if ($sys['now'] > $row['user_banexpire'] && $row['user_banexpire']>0)
		{
			$sql = sed_sql_query("UPDATE $db_users SET user_maingrp='4' WHERE user_id={$row['user_id']}");
		}
		else
		{
			$facebook->clear_cookie_state();
			sed_log("Log in attempt, user banned : ".$rusername, 'usr');
			sed_redirect(sed_url('message', 'msg=153&num='.$row['user_banexpire'], '', true));
			exit;
		}
	}

	$ruserid = $row['user_id'];

	$hashsalt = sed_unique(16);

	sed_sql_query("UPDATE $db_users SET user_lastip='{$usr['ip']}', user_lastlog = {$sys['now_offset']}, user_logcount = user_logcount + 1, user_hashsalt = '$hashsalt' WHERE user_id={$row['user_id']}");

	$passhash = md5($row['user_password'].$hashsalt);

	$u = base64_encode($ruserid.':_:'.$passhash);

	sed_setcookie($sys['site_id'], $u, time()+$cfg['cookielifetime'], $cfg['cookiepath'], $cfg['cookiedomain'], $sys['secure'], true);

	$_SESSION['saltstamp'] = $sys['now_offset'];

	$sql = sed_sql_query("DELETE FROM $db_online WHERE online_userid='-1' AND online_ip='".$usr['ip']."' LIMIT 1");
	sed_uriredir_apply($cfg['redirbkonlogin']);
	sed_uriredir_redirect(empty($redirect) ? sed_url('index') : base64_decode($redirect));
}
?>
