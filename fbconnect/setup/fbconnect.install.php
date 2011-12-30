<?php

defined('COT_CODE') or die('Wrong URL');

global $db_users;

if ($db->query("SHOW COLUMNS FROM `$db_users` WHERE `Field` = 'user_fbid'")->rowCount() == 0)
{
	cot_extrafield_add('users', 'fbid', 'input', '<input class="text" type="text" maxlength="16" size="16" />', '', '',
		false, 'Text', 'FaceBook UID');
}
?>
