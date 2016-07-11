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

/**
 * @package acp
 */
class bb3hide_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang('acp/common');
		$user->add_lang_ext('ppk/bb3hide', 'bb3hide_acp');

		$this->tpl_name = 'acp_bb3hide';
		$this->page_title = $user->lang['BB3HIDE'];

		$form_key = 'acp_bb3hide';
		add_form_key($form_key);

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$bb3hide_hideplus = $request->variable('bb3hide_hideplus', 0);
			$config->set('bb3hide_hideplus', $bb3hide_hideplus);

			$bb3hide_ghide = $request->variable('bb3hide_ghide', 0);
			$config->set('bb3hide_ghide', $bb3hide_ghide);

			$bb3hide_uhide = $request->variable('bb3hide_uhide', 0);
			$config->set('bb3hide_uhide', $bb3hide_uhide);

			$bb3hide_ghideplus = $request->variable('bb3hide_ghideplus', 0);
			$config->set('bb3hide_ghideplus', $bb3hide_ghideplus);

			$bb3hide_ignorelimit_groups = $request->variable('bb3hide_ignorelimit_groups', array(0=>''));
			$config->set('bb3hide_ignorelimit_groups', implode(',', $bb3hide_ignorelimit_groups));

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'BB3HIDE_HIDEPLUS'       => isset($config['bb3hide_hideplus']) ? $config['bb3hide_hideplus'] : 0,
			'BB3HIDE_GHIDE'       => isset($config['bb3hide_ghide']) ? $config['bb3hide_ghide'] : 0,
			'BB3HIDE_UHIDE'       => isset($config['bb3hide_uhide']) ? $config['bb3hide_uhide'] : 0,
			'BB3HIDE_GHIDEPLUS'       => isset($config['bb3hide_ghideplus']) ? $config['bb3hide_ghideplus'] : 0,
			'BB3HIDE_IGNORELIMIT_GROUPS'       => $this->get_ignorelimit_groups(isset($config['bb3hide_ignorelimit_groups']) ? $config['bb3hide_ignorelimit_groups'] : array()),

			'U_ACTION'       => $this->u_action,

			'L_TITLE_EXPLAIN'	=> $user->lang['BB3HIDE_EXPLAIN'],
		));
	}

	function get_ignorelimit_groups($current=array())
	{
		global $user, $db;

		$current=array_map('intval', explode(',', trim($current)));
		$group_form='';

		$sql = 'SELECT g.group_id, g.group_name, g.group_type
			FROM ' . GROUPS_TABLE . ' g
			ORDER BY g.group_type ASC, g.group_name';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$group_form.='<option'.($row['group_type'] == GROUP_SPECIAL ? ' style="font-weight:bold;"' : '').' value="'.$row['group_id'].'"'.(in_array($row['group_id'], $current) ? ' selected="selected"' : '').'>'.(isset($user->lang['G_'.$row['group_name']]) ? $user->lang['G_'.$row['group_name']] : $row['group_name']).'</option>';

		}
		$db->sql_freeresult($result);

		return $group_form;

	}
}

?>