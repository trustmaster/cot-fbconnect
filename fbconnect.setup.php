<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=fbconnect
Name=FaceBook Connect
Description=Various FaceBook Services for your site
Version=0.0.1
Date=2009-oct-23
Author=Trustmaster
Copyright=
Notes=You must obtain your own API key to use FBConnect integration
SQL=
Auth_guests=R
Lock_guests=W12345A
Auth_members=RW
Lock_members=12345A
[END_SED_EXTPLUGIN]

[BEGIN_SED_EXTPLUGIN_CONFIG]
locale=01:string::en_US:Default FaceBook interface locale
app_id=02:string:::Application ID
api_key=03:string:::Public API key
secret_key=04:string:::Secret key
[END_SED_EXTPLUGIN_CONFIG]
==================== */

defined('SED_CODE') or die('Wrong URL');

if($action == 'install')
{
	sed_extrafield_add('users', 'fbid', 'input', '<input class="text" type="text" maxlength="16" size="16" />', '',
		'FaceBook UID');
}
elseif($action == 'uninstall')
{
	// Keep UIDs for later
}

?>
