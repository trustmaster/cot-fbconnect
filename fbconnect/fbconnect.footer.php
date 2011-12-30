<?php
/* ====================
 * [BEGIN_COT_EXT]
 * Hooks=footer.tags
 * Tags=footer.tpl:{FB_CONNECT}
 * [END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

/**
 * FBConnect footer javascript for quicker page load
 *
 * @package fbconnect
 * @version 2.0.0
 * @author Trustmaster
 * @copyright (c) 2011 Vladimir Sibirov, Skuola.net
 * @license BSD
 */

$t->assign('FB_CONNECT', $fb_init_script);

?>
