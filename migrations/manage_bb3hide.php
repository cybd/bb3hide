<?php

/**
*
* @package BB3Hide
* @copyright (c) 2015 PPK
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*
*/

namespace ppk\bb3hide\migrations;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
    exit;
}

class manage_bb3hide extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['bb3hide_version']);
	}

	public function update_data()
	{
		return array(

			// Add new config vars
			array('config.add', array('bb3hide_hideplus', '0')),
			array('config.add', array('bb3hide_ghide', '0')),
			array('config.add', array('bb3hide_ghideplus', '0')),
			array('config.add', array('bb3hide_ignorelimit_groups', '4,5')),

			array('config.add', array('bb3hide_version', '1.0.4')),

			// Add new modules
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'BB3HIDE'
			)),

			array('module.add', array(
				'acp',
				'BB3HIDE',
				array(
					'module_basename'	=> '\ppk\bb3hide\acp\bb3hide_module',
					'modes'	=> array('bb3hide_config'),
				),
			)),

		);
	}

	public function revert_data()
	{
		return array(
			array('config.remove', array('bb3hide_hideplus')),
			array('config.remove', array('bb3hide_ghide')),
			array('config.remove', array('bb3hide_ghideplus')),
			array('config.remove', array('bb3hide_ignorelimit_groups')),

			array('config.remove', array('bb3hide_version')),

			array('module.remove', array(
				'acp',
				'BB3HIDE',
				array(
					'module_basename'	=> '\ppk\bb3hide\acp\bb3hide_module',
					'modes'	=> array('bb3hide_config'),
				),
			)),
			array('module.remove', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'BB3HIDE'
			)),

		);
	}
}