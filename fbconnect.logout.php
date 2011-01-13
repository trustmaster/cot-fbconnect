<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=fbconnect
Part=logout
File=fbconnect.logout
Hooks=users.logout
Tags=
Order=10
[END_SED_EXTPLUGIN]
==================== */

defined('SED_CODE') or die('Wrong URL');

// Applies single sign out policy
if ($fb_uid > 0)
{
	// FIXME: this FaceBook method is buggy and FB doesn't clear cookies in any way!
	$facebook->clear_cookie_state();
}
?>
