<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=fbconnect
Part=register.tags
File=fbconnect.register.tags
Hooks=users.register.tags
Tags=
Order=10
[END_SED_EXTPLUGIN]
==================== */

defined('SED_CODE') or die('Wrong URL');

// Just an extension for e-mail input, because Facebook proxy emails are more than 64
// characters

// FIXME: this will be obsolete in Siena 0.7.0 because it should be in templates there

$t->assign('USERS_REGISTER_EMAIL', '<input type="text" class="text" name="ruseremail" value="'
	. htmlspecialchars($ruseremail) . '" size="24" maxlength="255" />');

?>
