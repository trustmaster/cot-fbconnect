<?php

defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('extrafields');

global $db_users;

if (!$db->fieldExists($db_users, 'user_fb_url'))
{
	cot_extrafield_add($db_users, 'fb_url', 'input', '<input class="text" type="text" maxlength="255" size="32" />', '', '',
		false, 'Text', 'FaceBook Profile URL');
}

if (!$db->fieldExists($db_users, 'user_first_name'))
{
	cot_extrafield_add($db_users, 'first_name', 'input', '<input class="text" type="text" maxlength="64" size="16" />', '', '',
		false, 'Text', 'FaceBook Profile URL');
}

if (!$db->fieldExists($db_users, 'user_last_name'))
{
	cot_extrafield_add($db_users, 'last_name', 'input', '<input class="text" type="text" maxlength="64" size="16" />', '', '',
		false, 'Text', 'FaceBook Profile URL');
}
