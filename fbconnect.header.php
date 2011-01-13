<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=fbconnect
Part=header
File=fbconnect.header
Hooks=header.tags
Tags=header.tpl:{FB_XMLNS},{FB_BODY_JS},{FB_LOGIN}
Order=10
[END_SED_EXTPLUGIN]
==================== */

defined('SED_CODE') or die('Wrong URL');

$fb_locale = empty($usr['fb_locale']) ? $cfg['plugin']['fbconnect']['locale'] : $usr['fb_locale'];

$fb_init_script = '<script type="text/javascript"> FB.init("'.$cfg['plugin']['fbconnect']['api_key'].'", "'
	. $sys['site_uri'] . $cfg['plugins_dir'] . '/fbconnect/xd_receiver.html");</script>
	<script type="text/javascript" src="'.$cfg['plugins_dir'] . '/fbconnect/js/fbconnect.js"></script>';

$t->assign(array(
	'FB_XMLNS' => 'xmlns:fb="http://www.facebook.com/2008/fbml"',
	'FB_BODY_JS' => '<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/'
		. $fb_locale . '" type="text/javascript"></script>',
	'FB_LOGIN' => $fb_uid > 0 ? $fb_init_script : '<fb:login-button onlogin="facebook_onlogin()"></fb:login-button>'
		. $fb_init_script
));
?>
