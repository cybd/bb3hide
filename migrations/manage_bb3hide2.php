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

class manage_bb3hide2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['bb3hide_version']) && version_compare($this->config['bb3hide_version'], '1.2.0', '>=');
	}

	static public function depends_on()
	{
		return array('\ppk\bb3hide\migrations\manage_bb3hide');
	}

	public function update_data()
	{
		return array(

			// Add new config vars
			array('config.add', array('bb3hide_uhide', '0')),

			array('config.update', array('bb3hide_version', '1.2.0')),

		);
	}

	public function revert_data()
	{
		return array(
			array('config.remove', array('bb3hide_uhide')),
		);
	}
}