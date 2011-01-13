<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=fbconnect
Part=register.validate
File=fbconnect.register.validate
Hooks=users.register.add.done
Tags=
Order=10
[END_SED_EXTPLUGIN]
==================== */

defined('SED_CODE') or die('Wrong URL');

// Automatically validates and logs in a user who has registered with FBConnect
if ($fb_uid > 0 && !$cfg['regrequireadmin'])
{
	sed_sql_query("UPDATE $db_users SET user_fbid = $fb_uid WHERE user_id = $userid");
	if (!$cfg['regnoactivation'])
	{
		$sql = sed_sql_query("UPDATE $db_users SET user_maingrp=4 WHERE user_id='$userid'");
		$sql = sed_sql_query("UPDATE $db_groups_users SET gru_groupid=4 WHERE gru_groupid=2 AND gru_userid='$userid'");
		sed_auth_clear($userid);
	}
	// Automatically log user in
	$row['user_id'] = $userid;
	$row['user_name'] = $rusername;
	$row['user_password'] = $mdpass;
	$row['user_maingrp'] = 4;
	fb_autologin($row);
	exit;
}
?>
