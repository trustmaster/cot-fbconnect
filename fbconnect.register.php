<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=fbconnect
Part=register
File=fbconnect.register
Hooks=users.register.main
Tags=
Order=10
[END_SED_EXTPLUGIN]
==================== */

defined('SED_CODE') or die('Wrong URL');

// Auto-complete some fields with values got from FaceBook.
// We are not allowed to store any personal information received from FaceBook
// but we only store what a user has filled in his registration form himself,
// saving him a bit of time by making these assumptions

if ($fb_uid > 0 && $usr['id'] == 0)
{
	if (empty($a))
	{
		// Auto-complete fields on first run
		if (empty($rusername)) $rusername = $fb_usr['name'];
		// Cannot use proxy email as it won't pass validation (contains + character)
		//if (empty($ruseremail)) $ruseremail = $fb_usr['proxied_email'];
		if (empty($rcountry))
		{
			$rcountry = strtolower(substr($fb_usr['locale'], 3));
			if ($rcountry == 'gb') $rcountry = 'uk';
		}
		if (empty($rlocation))
		{
			$rlocation = $fb_usr['current_location']['city'];
			if (is_array($fb['usr']['hometown_location']) && !empty($fb['usr']['hometown_location']['city']))
			{
				$rlocation = $fb['usr']['hometown_location']['city'];
			}
		}
		if (empty($rtimezone))
		{
			// FIXME have to duplicate this because of hook order
			$rtimezone = $fb_usr['timezone'];
			$form_timezone = "<select name=\"rtimezone\" size=\"1\">";
			foreach ($timezonelist as $x)
			{
				$f = (float) $x;
				$selected = ($f == $rtimezone) ? "selected=\"selected\"" : '';
				$form_timezone .= "<option value=\"$f\" $selected>GMT ".$x."</option>";
			}
			$form_timezone .= "</select> ".$usr['gmttime']." / ".date($cfg['dateformat'], $sys['now_offset'] + $usr['timezone']*3600)." ".$usr['timetext'];
		}
		// TODO untranslate gender latter from $fb_usr['sex'] somehow
		/*if (empty($rusergender))
		{
			$rusergender = $fb_usr['sex'];
			$form_usergender = sed_selectbox_gender($rusergender, 'rusergender');
		}*/
		if (empty($rmonth) && empty($rdate) && empty($ryear))
		{
			if (preg_match('#(\d+)/(\d+)/(\d+)#', $fb_usr['birthday_date'], $mt))
			{
				$rmonth = (int) $mt[1];
				$rday = (int) $mt[2];
				$ryear = (int) $mt[3];
			}
			$form_birthdate = sed_selectbox_date(sed_mktime(1, 0, 0, $rmonth, $rday, $ryear), 'short', '', date('Y', $sys['now_offset']));
		}
		if (empty($ruserwebsite)) $ruserwebsite = $fb_usr['website'];
		// TODO add extra fields support
	}
}
?>
