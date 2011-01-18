<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=fbconnect
Name=FaceBook Connect
Description=Basic FaceBook account integration
Version=2.0.0
Date=2011-01-18
Author=Trustmaster
Copyright=&copy; Vladimir Sibirov 2009-2011
Notes=You must register your site as FaceBook App and obtain your own AppId and Secret to use FaceBook integration
SQL=
Auth_guests=R
Lock_guests=W12345A
Auth_members=RW
Lock_members=12345A
[END_SED_EXTPLUGIN]

[BEGIN_SED_EXTPLUGIN_CONFIG]
locale=01:string::en_US:Default FaceBook interface locale
app_id=02:string:::Application ID
secret_key=03:string:::Secret key
autoactiv=04:radio::1:Automatic activation of FaceBook registrants
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
