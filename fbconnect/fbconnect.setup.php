<?php
/* ====================
 * [BEGIN_COT_EXT]
 * Code=fbconnect
 * Name=FaceBook Connect
 * Description=Basic FaceBook account integration
 * Version=2.2
 * Date=2013-02-06
 * Author=Trustmaster
 * Copyright=&copy; Vladimir Sibirov 2009-2013
 * Notes=You must register your site as FaceBook App and obtain your own AppId and Secret to use FaceBook integration
 * SQL=
 * Auth_guests=R
 * Lock_guests=W12345A
 * Auth_members=RW
 * Lock_members=12345A
 * [END_COT_EXT]

 * [BEGIN_COT_EXT_CONFIG]
 * locale=01:string::en_US:Default FaceBook interface locale
 * app_id=02:string:::Application ID
 * secret_key=03:string:::Secret key
 * autoactiv=04:radio::1:Automatic activation of FaceBook registrants
 * autoreg=05:radio::1:Redirect FaceBook users to Quick Registration
 * autoadd=06:radio::0:Automatically add new facebook users to system
 * scope=06:string:::Custom access perimissions, comma separated
 * [END_COT_EXT_CONFIG]
==================== */

defined('COT_CODE') or die('Wrong URL');
