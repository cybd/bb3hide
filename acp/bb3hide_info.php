<?php

/**
*
* @package BB3Hide
* @copyright (c) 2015 PPK
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*
*/

namespace ppk\bb3hide\acp;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
    exit;
}

class bb3hide_info
{
	function module()
	{
		return array(
			'filename'	=> '\ppk\bb3hide\bb3hide_module',
			'title'		=> 'BB3HIDE',
			'modes'		=> array(
				'bb3hide_config' => array('title' => 'BB3HIDE_CONFIG', 'auth' => 'ext_ppk/bb3hide && acl_a_board', 'cat' => array('BB3HIDE')),
			),
		);
	}
}

?>