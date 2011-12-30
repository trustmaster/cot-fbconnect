<?php
/**
 * Auxilliary functions for FaceBook Connect plugin
 *
 * @author Trustmaster
 * @copyright (с) 2009-2011 Vladimir Sibirov
 */

/**
 * Logs a Cotonti user in
 *
 * @param array $row User record
 */
function fb_autologin($row)
{
	global $db, $facebook, $usr, $sys, $cfg, $redirect, $db_users, $db_online;

	$rusername = $row['user_name'];
	$rmdpass = $row['user_password'];
	$rremember = true;
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
			$sql = $db->update($db_users, array('user_maingrp' => '4'),  "user_id={$row['user_id']}");
		}
		else
		{
			$env['status'] = '403 Forbidden';
			cot_log("Log in attempt, user banned : ".$rusername, 'usr');
			cot_redirect(cot_url('message', 'msg=153&num='.$row['user_banexpire'], '', true));
		}
	}

	$ruserid = $row['user_id'];
	$rdeftheme = $row['user_theme'];
	$rdefscheme = $row['user_scheme'];

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

	$u = base64_encode($ruserid.':'.$sid);

	if ($rremember)
	{
		cot_setcookie($sys['site_id'], $u, time()+$cfg['cookielifetime'], $cfg['cookiepath'], $cfg['cookiedomain'], $sys['secure'], true);
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

	$sql = $db->delete($db_online, "online_userid='-1' AND online_ip='".$usr['ip']."' LIMIT 1");
	cot_uriredir_apply($cfg['redirbkonlogin']);
	cot_uriredir_redirect(empty($redirect) ? cot_url('index') : base64_decode($redirect));
}
?>
