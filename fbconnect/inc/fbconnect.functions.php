<?php
/**
 * Auxilliary functions for FaceBook Connect plugin
 *
 * @author Trustmaster
 * @copyright (с) 2009-2013 Vladimir Sibirov
 */

/**
 * Logs a Cotonti user in
 *
 * @param array $row User record
 */
function fb_autologin($row)
{
	global $db, $usr, $sys, $cfg, $redirect, $db_users, $db_online;

	$rusername = $row['user_name'];
	$rmdpass = $row['user_password'];
	$rremember = false;
	if ($row['user_maingrp']==-1)
	{
		$env['status'] = '403 Forbidden';
		cot_log("Log in attempt, user inactive : ".$rusername, 'usr');
		cot_redirect(cot_url('message', 'msg=152', '', true));
	}
	if ($row['user_maingrp']==2)
	{
		$env['status'] = '403 Forbidden';
		cot_log("Log in attempt, user inactive : ".$rusername, 'usr');
		cot_redirect(cot_url('message', 'msg=152', '', true));
	}
	elseif ($row['user_maingrp']==3)
	{
		if ($sys['now'] > $row['user_banexpire'] && $row['user_banexpire']>0)
		{
			$db->update($db_users, array('user_maingrp' => '4'),  "user_id={$row['user_id']}");
		}
		else
		{
			$env['status'] = '403 Forbidden';
			cot_log("Log in attempt, user banned : ".$rusername, 'usr');
			cot_redirect(cot_url('message', 'msg=153&num='.$row['user_banexpire'], '', true));
		}
	}

	$ruserid = $row['user_id'];
	// $rdeftheme = $row['user_theme'];
	// $rdefscheme = $row['user_scheme'];

	$token = cot_unique(16);

	$sid = hash_hmac('sha256', $rmdpass . $row['user_sidtime'], $cfg['secret_key']);

	if (empty($row['user_sid']) || $row['user_sid'] != $sid
		|| $row['user_sidtime'] + $cfg['cookielifetime'] < $sys['now_offset'])
	{
		// Generate new session identifier
		$sid = hash_hmac('sha256', $rmdpass . $sys['now_offset'], $cfg['secret_key']);
		$update_sid = ", user_sid = " . $db->quote($sid) . ", user_sidtime = " . $sys['now_offset'];
	}
	else
	{
		$update_sid = '';
	}

	$db->query("UPDATE $db_users SET user_lastip='{$usr['ip']}', user_lastlog = {$sys['now_offset']}, user_logcount = user_logcount + 1, user_token = '$token' $update_sid WHERE user_id={$row['user_id']}");

	// Hash the sid once more so it can't be faked even if you  know user_sid
	$sid = hash_hmac('sha1', $sid, $cfg['secret_key']);

	$u = base64_encode($ruserid.':'.$sid);

	if ($rremember)
	{
		cot_setcookie($sys['site_id'], $u, time()+$cfg['cookielifetime'], $cfg['cookiepath'], $cfg['cookiedomain'], $sys['secure'], true);
		unset($_SESSION[$sys['site_id']]);
	}
	else
	{
		$_SESSION[$sys['site_id']] = $u;
	}

	/* === Hook === */
	foreach (cot_getextplugins('fbconnect.autologin') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$res = $db->query("SHOW TABLES LIKE '$db_online'");
	if ($res->rowCount() == 1)
	{
		$db->delete($db_online, "online_userid='-1' AND online_ip='".$usr['ip']."' LIMIT 1");
	}

	cot_uriredir_apply($cfg['redirbkonlogin']);
	cot_uriredir_redirect(empty($redirect) ? cot_url('index') : base64_decode($redirect));
}

/**
 * Completes user profile fields with data received from Facebook
 * @param  array  $me    Facebook profile
 * @param  array  $ruser Profile fields
 * @return array         Completed $ruser
 */
function fb_complete_profile($me, $ruser = array())
{
	global $cot_extrafields, $db, $db_users;

	if (empty($ruser['user_name']))
	{
		$user_name = empty($me['username']) ? $me['name'] : $me['username'];
		$name = $user_name;
		while ($db->query("SELECT COUNT(*) FROM $db_users WHERE user_name = ?", $name)->fetchColumn() > 0)
		{
			// Name is busy, generate a random prefix
			$name = $user_name . mt_rand(2,9999);
		}
		$ruser['user_name'] = $name;
	}

	$username = empty($me['username']) ? $me['id'] : $me['username'];

	if (empty($ruser['user_email']))
	{
		$ruser['user_email'] = empty($me['email']) ? "$username@facebook.com" : $me['email'];
	}

	if (empty($ruser['user_birthdate']) && !empty($me['birthday']))
	{
		list($month, $day, $year) = explode('/', $me['birthday']);
		$ruser['user_birthdate'] = cot_mktime(1, 0, 0, $month, $day, $year);
	}

	if (empty($ruser['user_lang']) && !empty($me['locale']))
	{
		$lang = $me['locale'];
		if (file_exists("lang/$lang"))
		{
			$ruser['user_lang'] = $lang;
		}
		elseif (strpos($lang, '_') !== false)
		{
			$locale = explode('_', $lang);
			$ruser['user_lang'] = strtolower($locale[0]);
			$ruser['user_country'] = strtolower($locale[1]);
		}
	}

	if (empty($ruser['user_gender']) && !empty($me['gender']))
		$ruser['user_gender'] = $me['gender'] == 'female' ? 'F' : 'M';

	if ($db->fieldExists($db_users, 'user_avatar'))
		$ruser['user_avatar'] = "//graph.facebook.com/$username/picture";

	if ($db->fieldExists($db_users, 'user_photo'))
		$ruser['user_photo'] = "//graph.facebook.com/$username/picture?type=large";

	// Some extra fields
	if (isset($cot_extrafields[$db_users]['first_name']) && empty($ruser['user_first_name']))
		$ruser['user_first_name'] = $me['first_name'];

	if (isset($cot_extrafields[$db_users]['last_name']) && empty($ruser['user_last_name']))
		$ruser['user_last_name'] = $me['last_name'];

	if ($db->fieldExists($db_users, 'user_location'))
	{
		$ruser['user_location'] = $me['location']['name'];
	}

	$ruser['user_fbid'] = $me['id'];
	$ruser['user_fb_url'] = $me['link'];

	return $ruser;
}
