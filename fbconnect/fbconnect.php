<?php
/* ====================
 * [BEGIN_COT_EXT]
 * Hooks=standalone
 * [END_COT_EXT]
==================== */

defined('COT_CODE') || die('Wrong URL.');

/**
 * Standalone pages
 *
 * @package fbconnect
 * @version 2.1
 * @author Trustmaster
 * @copyright (c) 2011 Vladimir Sibirov, Skuola.net
 * @license BSD
 */

// FaceBook PHP API
require_once $cfg['plugins_dir'] . '/fbconnect/lib/facebook.php';
require_once $cfg['plugins_dir'] . '/fbconnect/inc/fbconnect.functions.php';

// More APIs
require_once cot_incfile('users', 'module');

if ($m == 'register' && $usr['id'] == 0)
{
	$_SESSION['s_uri_redir'] = $sys['uriredir_prev'];
	// FB register plugin
	if ($_POST['signed_request'])
	{
		$response = parse_signed_request($_POST['signed_request'], $cfg['plugin']['fbconnect']['secret_key']);
		if (!$response)
		{
			cot_die();
		}

		cot_shield_protect();

		/* === Hook for the plugins === */
		foreach (cot_getextplugins('users.register.add.first') as $pl)
		{
			include $pl;
		}
		/* ===== */

		$is_fb_user = isset($response['user_id']);
		$fb_user = $response['user_id'];

		// Check if e-mail exists
		$ruseremail = mb_strtolower($response['registration']['email']);
		$res = $db->query("SELECT * FROM $db_users
			WHERE user_email = '" . $db->prep($ruseremail) . "'");
		if ($res->rowCount() == 1)
		{
			if ($is_fb_user)
			{
				// Attach FB ID to account and log in
				$ruser = $res->fetch();
				$db->query("UPDATE $db_users SET user_fbid = '".$db->prep($fb_user)."'
					WHERE user_id = " . $ruser['user_id']);
				fb_autologin($ruser);
			}
			else
			{
				// Duplicate email
				cot_die();
			}
			exit;
		}

		// Check username and make it unique
		$rusername = empty($fb_me['username']) ? $response['registration']['name'] : $fb_me['username'];
		$bdate = explode('/', $response['registration']['birthday']);
		$tried_bd = false;
		while ($res = $db->query("SELECT COUNT(*) FROM $db_users
			WHERE user_name = '" . $db->prep($rusername) . "'")->fetchColumn() && $res > 0)
		{
			$rusername = empty($fb_me['username']) ? $response['registration']['name'] : $fb_me['username'];
			if ($tried_bd)
			{
				$rusername .= mt_rand(1, 999);
			}
			else
			{
				$rusername .= $bdate[2];
				$tried_bd = true;
			}
		}

		// Detect language
		$ruserlang = mb_substr($response['user']['locale'], 0, 2);
		if (!file_exists('./lang/' . $ruserlang))
		{
			$ruserlang = $cfg['defaultlang'];
		}

		// Fill the fields
		$rpassword1 = $response['registration']['password'];
		$validationkey = md5(microtime());

		$ruser['user_name'] = $rusername;
		$ruser['user_email'] = $ruseremail;
		$ruser['user_password'] = md5($rpassword1);
		$ruser['user_country'] = $response['user']['country'];
		$ruser['user_lang'] = $ruserlang;
		if ($db->fieldExists($db_users, 'user_location'))
		{
			$ruser['user_location'] = $response['registration']['location']['name'];
		}
		$ruser['user_timezone'] = $cfg['defaulttimezone'];
		$ruser['user_gender'] = $response['registration']['gender'] == 'male' ? 'M' : 'F';
		$ruser['user_birthdate'] = $bdate[2] . '-' . $bdate[0] . '-' . $bdate[1];
		$ruser['user_maingrp'] = ($cfg['plugin']['fbconnect']['autoactiv']) ? 4 : 2;
		$ruser['user_hideemail'] = 1;
		$ruser['user_theme'] = $cfg['defaulttheme'];
		$ruser['user_scheme'] = $cfg['defaultscheme'];
		$ruser['user_lang'] = $ruserlang;
		$ruser['user_regdate'] = (int)$sys['now'];
		$ruser['user_logcount'] = 0;
		$ruser['user_lastip'] = $usr['ip'];
		$ruser['user_lostpass'] = $validationkey;

		$ruser['user_fbid'] = $fb_user;

		cot_shield_update(20, 'Registration');

		$db->insert($db_users, $ruser);

		$userid = $db->lastInsertId();
		$ruser['user_id'] = $userid;

		$sql = $db->query("INSERT INTO $db_groups_users (gru_userid, gru_groupid) VALUES (".(int)$userid.", ".(int)$ruser['user_maingrp'].")");

		/* === Hook for the plugins === */
		foreach (cot_getextplugins('users.register.add.done') as $pl)
		{
			include $pl;
		}
		/* ===== */

		if ($cfg['plugin']['fbconnect']['autoactiv'])
		{
			$rsubject = $cfg['maintitle']." - ".$L['Registration'];
			$rbody = sprintf($L['fbconnect_welcome'], $rusername, $rpassword1);
			$rbody .= "\n\n".$L['aut_contactadmin'];
			cot_mail($ruseremail, $rsubject, $rbody);
			fb_autologin($ruser);
			exit;
		}

		if ($cfg['regrequireadmin'])
		{
			$rsubject = $cfg['maintitle']." - ".$L['aut_regrequesttitle'];
			$rbody = sprintf($L['aut_regrequest'], $rusername, $rpassword1);
			$rbody .= "\n\n".$L['aut_contactadmin'];
			cot_mail($ruseremail, $rsubject, $rbody);

			$rsubject = $cfg['maintitle']." - ".$L['aut_regreqnoticetitle'];
			$rinactive = $cfg['mainurl'].'/'.cot_url('users', 'gm=2&s=regdate&w=desc', '', true);
			$rbody = sprintf($L['aut_regreqnotice'], $rusername, $rinactive);
			cot_mail ($cfg['adminemail'], $rsubject, $rbody);
			cot_redirect(cot_url('message', 'msg=118', '', true));
			exit;
		}
		else
		{
			$rsubject = $cfg['maintitle']." - ".$L['Registration'];
			$ractivate = $cfg['mainurl'].'/'.cot_url('users', 'm=register&a=validate&v='.$validationkey.'&y=1', '', true);
			$rdeactivate = $cfg['mainurl'].'/'.cot_url('users', 'm=register&a=validate&v='.$validationkey.'&y=0', '', true);
			$rbody = sprintf($L['aut_emailreg'], $rusername, $rpassword1, $ractivate, $rdeactivate);
			$rbody .= "\n\n".$L['aut_contactadmin'];
			cot_mail($ruseremail, $rsubject, $rbody);
			cot_redirect(cot_url('message', 'msg=105', '', true));
			exit;
		}
	}
	else
	{
		$t = new XTemplate(cot_tplfile('fbconnect.register', 'plug'));
		$t->assign(array(
			'FB_REGISTER_URL' => COT_ABSOLUTE_URL . cot_url('plug', 'e=fbconnect&m=register')
		));
	}
}

function parse_signed_request($signed_request, $secret)
{
	list($encoded_sig, $payload) = explode('.', $signed_request, 2);

	// decode the data
	$sig = base64_url_decode($encoded_sig);
	$data = json_decode(base64_url_decode($payload), true);

	if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
	{
		error_log('Unknown algorithm. Expected HMAC-SHA256');
		return null;
	}

	// check sig
	$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
	if ($sig !== $expected_sig)
	{
		error_log('Bad Signed JSON signature!');
		return null;
	}

	return $data;
}

function base64_url_decode($input)
{
	return base64_decode(strtr($input, '-_', '+/'));
}

?>
