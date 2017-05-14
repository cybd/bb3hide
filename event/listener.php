<?php

/**
*
* @package BB3Hide
* @copyright (c) 2015 PPK
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*
*/

namespace ppk\bb3hide\event;

/**
 * @ignore
 */
if(!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	var $posters_groups=array();
	var $posters_defgroups=array();
	var $posters_posts=array();
 	var $posts_data=array();
 	var $posts_posters=array();
	var $register_link='';

	protected $is_admod;
	protected $ignore_groups;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	protected $is_quickreply;

	public function __construct(\phpbb\template\template $template, \phpbb\config\config $config, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request, $php_ext, $root_path, \phpbb\extension\manager $phpbb_extension_manager)
	{
		$this->template = $template;
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->request = $request;
		$this->php_ext = $php_ext;
		$this->root_path = $root_path;
		$this->phpbb_extension_manager = $phpbb_extension_manager;

// 		define('ADAPT_HIDE_LIMIT_POSTS', 200);

		$this->is_quickreply=$this->phpbb_extension_manager->is_enabled('boardtools/quickreply') && $this->config['qr_full_quote'] ? true : false;
	}

	static public function getSubscribedEvents()
	{
		return array(
			//подключение языковых файлов
			'core.user_setup'							=> 'bb3hide_add_lang',
			'core.user_setup_after'							=> 'bb3hide_init',

			//обработка текста для отображения, после обработки системных бб-кодов /includes/functions_content.php 460 generate_text_for_display() -> /viewtopic.php 1550
// 			'core.modify_text_for_display_after'		=> 'bb3hide_displaytext',
			//обработка текста для отображения, до обработки системных бб-кодов /includes/functions_content.php 460 generate_text_for_display() -> /viewtopic.php 1550
			'core.modify_text_for_display_before'		=> 'bb3hide_displaytext',

			'core.viewtopic_modify_post_data' => 'bb3hide_get_posts_data',
			'core.mcp_topic_modify_post_data' => 'bb3hide_get_posts_data',
			'core.search_modify_rowset' => 'bb3hide_get_posts_data',
			'core.topic_review_modify_post_list' => 'bb3hide_get_posts_data',
			'core.viewtopic_modify_post_row'            => array('bb3hide_quickreply', -3),

			//изменение кэша бб-кода для парсинга /includes/bbcode.php 408 bbcode_cache_init()
			'core.bbcode_cache_init_end' => 'bb3hide_cache_init',

			'core.decode_message_before' => 'bb3hide_decode_message',
			'core.modify_format_display_text_before' => 'bb3hide_preview',
			'core.submit_pm_before' => 'bb3hide_submit_pm',

			//изменение бб-кода добавленного через адм. раздел /includes/functions_display.php 1058 display_custom_bbcodes()
			'core.display_custom_bbcodes_modify_row'				=> 'bb3hide_bbtag',
		);
	}

	function bb3hide_quickreply($event)
	{
		$post_row = $event['post_row'];

		if ($this->is_quickreply && isset($post_row['DECODED_MESSAGE']))
		{
			$hide_search_ary = array();
			$hide_search_ary[] = '!\[hide\](.*?)\[/hide\]!s';
			if($this->config['bb3hide_hideplus'])
			{
				$hide_search_ary[] = '!\[hide\=([0-9]+)\](.*?)\[/hide\]!s';
			}
			if($this->config['bb3hide_uhide'])
			{
				$hide_search_ary[] = '!\[uhide\=([-a-zA-Z0-9+.,_ ]+)\](.*?)\[/uhide\]!s';
			}
			if($this->config['bb3hide_rhide'])
			{
				$hide_search_ary[] = '!\[rhide\=(\-?[0-9]+)\](.*?)\[/rhide\]!s';
			}
			if($this->config['bb3hide_ghide'])
			{
				$hide_search_ary[] = '!\[ghide\](.*?)\[/ghide\]!s';
			}
			if($this->config['bb3hide_ghideplus'])
			{
				$hide_search_ary[] = '!\[ghide\=([a-zA-Z0-9-+.,_ ]+)\](.*?)\[/ghide\]!s';
			}

			$post_row['DECODED_MESSAGE'] = preg_replace($hide_search_ary, $this->user->lang['BB3HIDE_QUOTE'], $post_row['DECODED_MESSAGE']);

			$event['post_row']=$post_row;

		}
	}
	function bb3hide_submit_pm($event)
	{
		$data=$event['data'];
		$bbcode_uid=$data['bbcode_uid'];

		$hide_search_ary = array();
		$hide_search_ary[] = '!\[hide:' . $bbcode_uid . '\](.*?)\[/hide:' . $bbcode_uid . '\]!s';
		if($this->config['bb3hide_hideplus'])
		{
			$hide_search_ary[] = '!\[hide\=(?:[0-9]+):' . $bbcode_uid . '\](.*?)\[/hide:' . $bbcode_uid . '\]!s';
		}
		if($this->config['bb3hide_uhide'])
		{
			$hide_search_ary[] = '!\[uhide\=(?:[,0-9]+):' . $bbcode_uid . '\](.*?)\[/uhide:' . $bbcode_uid . '\]!s';
		}
		if($this->config['bb3hide_ghide'])
		{
			$hide_search_ary[] = '!\[ghide:' . $bbcode_uid . '\](.*?)\[/ghide:' . $bbcode_uid . '\]!s';
		}
		if($this->config['bb3hide_ghideplus'])
		{
			$hide_search_ary[] = '!\[ghide\=(?:[a-zA-Z0-9-+.,_ ]+):' . $bbcode_uid . '\](.*?)\[/ghide:' . $bbcode_uid . '\]!s';
		}
		$data['message'] = preg_replace($hide_search_ary, '$1', $data['message']);//'<div class="bb3hide">$1</div>'

		$event['data']=$data;

	}

	function bb3hide_get_posts_data($event)
	{

		if(isset($event['show_results']) && $event['show_results']=='topics')
		{
			return false;
		}

		$rowset=$event['rowset'];

		foreach($rowset as $post_id => $row)
		{
			if($row['bbcode_bitfield'] && $row['bbcode_uid'])
			{
				$poster_id = isset($row['user_id']) ? $row['user_id'] : (isset($row['poster_id']) ? $row['poster_id'] : 2);
				if(!isset($row['forum_id']))
				{
					$row['forum_id']=0;
				}
				$this->posts_data[$row['bbcode_bitfield']][$row['bbcode_uid']]=array($poster_id, $row['forum_id']);
				$this->posts_posters[$poster_id]=$poster_id;
			}

		}

		$this->posts_posters[$this->user->data['user_id']]=$this->user->data['user_id'];

	}

	public function bb3hide_decode_message($event)
	{
		$mode=$this->request->variable('mode', '');
		$action=$this->request->variable('action', '');
		$preview=$this->request->variable('preview', '', true);

		if(!$preview && (in_array($mode, array('quote', 'reply')) || $action=='quotepost'))
		{
			$bbcode_uid=$event['bbcode_uid'];
			$message=$event['message_text'];

			$hide_search_ary = array();
			$hide_search_ary[] = '!\[hide:' . $bbcode_uid . '\](.*?)\[/hide:' . $bbcode_uid . '\]!s';
			if($this->config['bb3hide_hideplus'])
			{
				$hide_search_ary[] = '!\[hide\=([0-9]+):' . $bbcode_uid . '\](.*?)\[/hide:' . $bbcode_uid . '\]!s';
			}
			if($this->config['bb3hide_uhide'])
			{
				$hide_search_ary[] = '!\[uhide\=([,0-9]+):' . $bbcode_uid . '\](.*?)\[/uhide:' . $bbcode_uid . '\]!s';
			}
			if($this->config['bb3hide_ghide'])
			{
				$hide_search_ary[] = '!\[ghide:' . $bbcode_uid . '\](.*?)\[/ghide:' . $bbcode_uid . '\]!s';
			}
			if($this->config['bb3hide_ghideplus'])
			{
				$hide_search_ary[] = '!\[ghide\=([a-zA-Z0-9-+.,_ ]+):' . $bbcode_uid . '\](.*?)\[/ghide:' . $bbcode_uid . '\]!s';
			}
			$message = preg_replace($hide_search_ary, $this->user->lang['BB3HIDE_QUOTE'], $message);

			$event['message_text']=$message;

		}
	}

	public function bb3hide_preview($event)
	{
		$text=$event['text'];

		if ($this->is_quickreply)
		{
			$hide_search_ary = array();
			$hide_search_ary[] = '!\[hide\](.*?)\[/hide\]!s';
			if($this->config['bb3hide_hideplus'])
			{
				$hide_search_ary[] = '!\[hide\=([0-9]+)\](.*?)\[/hide\]!s';
			}
			if($this->config['bb3hide_uhide'])
			{
				$hide_search_ary[] = '!\[uhide\=([,0-9]+)\](.*?)\[/uhide\]!s';
			}
			if($this->config['bb3hide_ghide'])
			{
				$hide_search_ary[] = '!\[ghide\](.*?)\[/ghide\]!s';
			}
			if($this->config['bb3hide_ghideplus'])
			{
				$hide_search_ary[] = '!\[ghide\=([a-zA-Z0-9-+.,_ ]+)\](.*?)\[/ghide\]!s';
			}
		}
		else
		{
			$uid=$event['uid'];

			$hide_search_ary = array();
			$hide_search_ary[] = '!\[hide:' . $uid . '\](.*?)\[/hide:' . $uid . '\]!s';
			if($this->config['bb3hide_hideplus'])
			{
				$hide_search_ary[] = '!\[hide\=(?:[0-9]+):' . $uid . '\](.*?)\[/hide:' . $uid . '\]!s';
			}
			if($this->config['bb3hide_uhide'])
			{
				$hide_search_ary[] = '!\[uhide\=(?:[,0-9]+):' . $uid . '\](.*?)\[/uhide:' . $uid . '\]!s';
			}
			if($this->config['bb3hide_ghide'])
			{
				$hide_search_ary[] = '!\[ghide:' . $uid . '\](.*?)\[/ghide:' . $uid . '\]!s';
			}
			if($this->config['bb3hide_ghideplus'])
			{
				$hide_search_ary[] = '!\[ghide\=(?:[a-zA-Z0-9-+.,_ ]+):' . $uid . '\](.*?)\[/ghide:' . $uid . '\]!s';
			}
		}

		$text = preg_replace($hide_search_ary, '<div class="bb3hide">$1</div>', $text);

		$event['text']=$text;

	}

	public function bb3hide_bbtag($event)
	{
		$custom_tags=$event['custom_tags'];

		if($custom_tags['BBCODE_TAG']=='hide')
		{
			if($this->config['bb3hide_hideplus'])
			{
				$helpline=$this->user->lang['BB3HIDE_HIDES_HELPLINE'];
			}
			else
			{
				$helpline=$this->user->lang['BB3HIDE_HIDE_HELPLINE'];
			}
			$custom_tags['BBCODE_HELPLINE']=$custom_tags['A_BBCODE_HELPLINE']=$helpline;

			$event['custom_tags']=$custom_tags;
		}
		else if($custom_tags['BBCODE_TAG']=='ghide')
		{
			if($this->config['bb3hide_ghide'] && $this->config['bb3hide_ghideplus'])
			{
				$helpline=$this->user->lang['BB3HIDE_GHIDES_HELPLINE'];
			}
			else if($this->config['bb3hide_ghide'])
			{
				$helpline=$this->user->lang['BB3HIDE_GHIDE_HELPLINE'];
			}
			else if($this->config['bb3hide_ghideplus'])
			{
				$helpline=$this->user->lang['BB3HIDE_GHIDEPLUS_HELPLINE'];
			}
			else
			{
				$helpline='';
			}

			$custom_tags['BBCODE_HELPLINE']=$custom_tags['A_BBCODE_HELPLINE']=$helpline;

			$event['custom_tags']=$custom_tags;
		}

	}

	public function bb3hide_cache_init($event)
	{
		$bbcode_cache=$event['bbcode_cache'];
		$bbcode_uid=$event['bbcode_uid'];

// 		if(!sizeof($this->posts_data))
// 		{

			foreach($bbcode_cache as $k => $v)
			{
				if(isset($v['preg']['!\[hide:$uid\](.*?)\[/hide:$uid\]!s']))
				{
					$bbcode_cache[$k]['preg']=array(
						'!\[hide:$uid\](.*?)\[/hide:$uid\]!s' => '<div class="bb3hide">'.$this->user->lang['BB3HIDE_QUOTE_PREVIEW'].'</div>'
					);
				}
				else if($this->config['bb3hide_hideplus'] && isset($v['preg']['!\[hide\=([0-9]+):$uid\](.*?)\[/hide:$uid\]!s']))
				{
					$bbcode_cache[$k]['preg']=array(
						'!\[hide\=(?:[0-9]+):$uid\](.*?)\[/hide:$uid\]!s' => '<div class="bb3hide">'.$this->user->lang['BB3HIDE_QUOTE_PREVIEW'].'</div>'
					);
				}
				else if($this->config['bb3hide_uhide'] && isset($v['preg']['!\[uhide\=([,0-9]+):$uid\](.*?)\[/uhide:$uid\]!s']))
				{
					$bbcode_cache[$k]['preg']=array(
						'!\[uhide\=(?:[,0-9]+):$uid\](.*?)\[/uhide:$uid\]!s' => '<div class="bb3hide">'.$this->user->lang['BB3HIDE_QUOTE_PREVIEW'].'</div>'
					);
				}
				else if($this->config['bb3hide_ghide'] && isset($v['preg']['!\[ghide:$uid\](.*?)\[/ghide:$uid\]!s']))
				{
					$bbcode_cache[$k]['preg']=array(
						'!\[ghide:$uid\](.*?)\[/ghide:$uid\]!s' => '<div class="bb3hide">'.$this->user->lang['BB3HIDE_QUOTE_PREVIEW'].'</div>'
					);
				}
				else if($this->config['bb3hide_ghideplus'] && isset($v['preg']['!\[ghide\=([a-zA-Z0-9-+.,_ ]+):$uid\](.*?)\[/ghide:$uid\]!s']))
				{
					$bbcode_cache[$k]['preg']=array(
						'!\[ghide\=(?:[a-zA-Z0-9-+.,_ ]+):$uid\](.*?)\[/ghide:$uid\]!s' => '<div class="bb3hide">'.$this->user->lang['BB3HIDE_QUOTE_PREVIEW'].'</div>'
					);
				}
			}
// 		}
// 		else
// 		{
// 			foreach($bbcode_cache as $k => $v)
// 			{
// 				if(isset($v['preg']['!\[hide:$uid\](.*?)\[/hide:$uid\]!s']))
// 				{
// 					$bbcode_cache[$k]['preg']=array(
// 						'!\[hide:($uid)\](.*?)\[/hide:$uid\]!s' => '[hide : $1]$2[/hide : $1]'
// 					);
// 				}
// 				else if($this->config['bb3hide_hideplus'] && isset($v['preg']['!\[hide\=([0-9]+):$uid\](.*?)\[/hide:$uid\]!s']))
// 				{
// 					$bbcode_cache[$k]['preg']=array(
// 						'!\[hide\=([0-9]+):($uid)\](.*?)\[/hide:$uid\]!s' => '[hide=$1 : $2]$3[/hide : $2]'
// 					);
// 				}
// 				else if($this->config['bb3hide_ghide'] && isset($v['preg']['!\[ghide:$uid\](.*?)\[/ghide:$uid\]!s']))
// 				{
// 					$bbcode_cache[$k]['preg']=array(
// 						'!\[ghide:($uid)\](.*?)\[/ghide:$uid\]!s' => '[ghide : $1]$2[/ghide : $1]'
// 					);
// 				}
// 				else if($this->config['bb3hide_ghideplus'] && isset($v['preg']['!\[ghide\=([a-zA-Z0-9-+.,_ ]+):$uid\](.*?)\[/ghide:$uid\]!s']))
// 				{
// 					$bbcode_cache[$k]['preg']=array(
// 						'!\[ghide\=([a-zA-Z0-9-+.,_ ]+):($uid)\](.*?)\[/ghide:$uid\]!s' => '[ghide=$1 : $2]$3[/ghide : $2]'
// 					);
// 				}
// 			}
//
// 		}

		$event['bbcode_cache']=$bbcode_cache;

	}


	public function bb3hide_displaytext($event)
	{
		if(!sizeof($this->posts_data))
		{
			return false;
		}

		$uid=$event['uid'];
		$bitfield=$event['bitfield'];

		if(isset($this->posts_data[$bitfield][$uid]))
		{
			$text=$event['text'];

			$poster_id=$this->posts_data[$bitfield][$uid][0];
			$forum_id=$this->posts_data[$bitfield][$uid][1];

			$hide_search_ary = array();
			$hide_search_ary['hide'] = '!\[hide:$uid\](.*?)\[/hide:$uid\]!s';
			if($this->config['bb3hide_hideplus'])
			{
				$hide_search_ary['hide='] = '!\[hide\=([0-9]+):$uid\](.*?)\[/hide:$uid\]!s';
			}
			if($this->config['bb3hide_uhide'])
			{
				$hide_search_ary['uhide='] = '!\[uhide\=([,0-9]+):$uid\](.*?)\[/uhide:$uid\]!s';
			}
			if($this->config['bb3hide_ghide'])
			{
				$hide_search_ary['ghide'] = '!\[ghide:$uid\](.*?)\[/ghide:$uid\]!s';
			}
			if($this->config['bb3hide_ghideplus'])
			{
				$hide_search_ary['ghide='] = '!\[ghide\=([a-zA-Z0-9-+.,_ ]+):$uid\](.*?)\[/ghide:$uid\]!s';
			}

			$hide_found=false;
			foreach($hide_search_ary as $k=>$v)
			{

				$hide_search=str_replace('$uid', $uid, $v);
				$preg=array();
				preg_match_all($hide_search, $text, $preg);

				if(isset($preg[0]) && sizeof($preg[0]))
				{
					foreach($preg[0] as $id=>$p)
					{
						$replaced=false;
// 						if($this->is_admod)
// 						{
// 							$replaced=$hide_found=true;
// 							$text = str_replace($p, '<div class="bb3hide">' . (isset($preg[2][$id]) ? $preg[2][$id] : $preg[1][$id]) . '</div>', $text);
// 							continue;
// 						}
						if($k=='hide' && ($this->user->data['user_id'] == ANONYMOUS || $this->user->data['is_bot'] == 1))
						{
							$replaced=true;
							$text=str_replace($p, '<div class="bb3hide">' . sprintf($this->user->lang['BB3HIDE_GUEST'], $this->register_link) . '</div>', $text);
						}
						else if($k=='hide=' && !$this->auth->acl_get('m_edit', $forum_id) && $this->user->data['user_id'] != $poster_id)
						{
							if(!isset($this->posters_groups[$poster_id]))
							{
								$sql = 'SELECT group_id, user_id
									FROM ' . USER_GROUP_TABLE . '
									WHERE ' . $this->db->sql_in_set('user_id', $this->posts_posters) . '
									AND user_pending = 0';
								$result = $this->db->sql_query($sql);
								while($sql_row = $this->db->sql_fetchrow($result))
								{
									$this->posters_groups[$sql_row['user_id']][$sql_row['group_id']] = $sql_row['group_id'];
								}
								$this->db->sql_freeresult($result);
							}
							$ignore_limit = false;
							foreach($this->posters_groups[$this->user->data['user_id']] as $poster_group)
							{
								if(in_array($poster_group, $this->ignore_groups))
								{
									$ignore_limit = true;
									break;
								}
							}
							if(!$ignore_limit)
							{
								if(!isset($this->posters_posts[$poster_id]))
								{
									$sql = 'SELECT user_posts, user_id, group_id
										FROM ' . USERS_TABLE . '
										WHERE ' . $this->db->sql_in_set('user_id', $this->posts_posters);
									$result = $this->db->sql_query($sql);
									while($sql_row = $this->db->sql_fetchrow($result))
									{
										$this->posters_posts[$sql_row['user_id']] = $sql_row['user_posts'];
										$this->posters_defgroups[$sql_row['user_id']] = $sql_row['group_id'];
									}
									$this->db->sql_freeresult($result);
								}

								$posts=$preg[1][$id];

// 								if(!$ignore_limit)
// 								{
// 									$posts = min($posts, $this->posters_posts[$poster_id], ADAPT_HIDE_LIMIT_POSTS);
// 								}
								$hide_cause = false;
								if($this->user->data['user_id'] == ANONYMOUS || $this->user->data['is_bot'] == 1)
								{
									if($posts == 0)
									{
										$hide_cause = $this->user->lang['BB3HIDE_GUEST'];
									}
									else
									{
										$hide_cause = sprintf($this->user->lang['BB3HIDE_GUEST_POSTS'], $this->register_link, $posts);
									}
								}
								else if($this->user->data['user_posts'] < $posts)
								{
									$hide_cause = sprintf($this->user->lang['BB3HIDE_POSTS'], $posts);
								}
								if($hide_cause)
								{
									$replaced=true;
									$text = str_replace($p, '<div class="bb3hide">' . $hide_cause . '</div>', $text);
								}

							}
						}
						else if($k=='uhide=' && !$this->auth->acl_get('m_edit', $forum_id) && $this->user->data['user_id'] != $poster_id)
						{
							$users_ary = explode(',', $reputations=$preg[1][$id]);
							$uhide = true;
							foreach ($users_ary as $user_id)
							{
								if ($user_id==$this->user->data['user_id'])
								{
									$uhide = false;
									break;
								}
							}
							if($uhide)
							{
								$replaced=true;
								$text=str_replace($p, '<div class="bb3hide">' . $this->user->lang['BB3HIDE_USERS'] . '</div>', $text);
							}
						}
						else if(in_array($k, array('ghide', 'ghide=')) && !$this->auth->acl_get('m_edit', $forum_id) && $this->user->data['user_id'] != $poster_id)
						{
							if($k=='ghide')
							{
								if(!isset($this->posters_defgroups[$poster_id]))
								{
									$sql = 'SELECT user_posts, user_id, group_id
										FROM ' . USERS_TABLE . '
										WHERE ' . $this->db->sql_in_set('user_id', $this->posts_posters);
									$result = $this->db->sql_query($sql);
									while($sql_row = $this->db->sql_fetchrow($result))
									{
										$this->posters_posts[$sql_row['user_id']] = $sql_row['user_posts'];
										$this->posters_defgroups[$sql_row['user_id']] = $sql_row['group_id'];
									}
									$this->db->sql_freeresult($result);
								}
// 								if(!in_array($this->posters_defgroups[$poster_id], $this->posters_groups[$this->user->data['user_id']]))
								if($this->posters_defgroups[$poster_id]!=$this->posters_defgroups[$this->user->data['user_id']])
								{
									$replaced=true;
									$text = str_replace($p, '<div class="bb3hide">' . $this->user->lang['BB3HIDE_GROUP'] . '</div>', $text);
								}
							}
							else if($k=='ghide=')
							{
								if(!isset($this->posters_groups[$this->user->data['user_id']]))
								{
									$sql = 'SELECT group_id, user_id
										FROM ' . USER_GROUP_TABLE . '
										WHERE ' . $this->db->sql_in_set('user_id', $this->posts_posters) . '
										AND user_pending = 0';
									$result = $this->db->sql_query($sql);
									while($sql_row = $this->db->sql_fetchrow($result))
									{
										$this->posters_groups[$sql_row['user_id']][$sql_row['group_id']] = $sql_row['group_id'];
									}
									$this->db->sql_freeresult($result);
								}

								$groups=explode(',', $preg[1][$id]);

								$ghide = true;
								foreach($groups as $group)
								{
									if(in_array(intval($group), $this->posters_groups[$this->user->data['user_id']]))
									{
										$ghide = false;
										break;
									}
								}
								if($ghide)
								{
									$replaced=true;
									$text = str_replace($p, '<div class="bb3hide">' . $this->user->lang['BB3HIDE_GROUPS'] . '</div>', $text);
								}

							}
						}
						if(!$replaced)
						{
							$replaced=true;
							$text = str_replace($p, '<div class="bb3hide">' . (isset($preg[2][$id]) ? $preg[2][$id] : $preg[1][$id]) . '</div>', $text);
						}
						if($replaced)
						{
							$hide_found=true;
						}
					}
				}
			}
			if($hide_found)
			{
				$event['text']=$text;
			}
		}

	}

	public function bb3hide_add_lang($event)
	{

		$lang_set_ext=$event['lang_set_ext'];

		$lang_set_ext[]=array(
			'ext_name' => 'ppk/bb3hide',
			'lang_set' => 'bb3hide',
		);

		$event['lang_set_ext']=$lang_set_ext;
	}

	public function bb3hide_init()
	{

		$this->is_admod=$this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_') ? 1 : 0;
		$this->ignore_groups = explode(',', $this->config['bb3hide_ignorelimit_groups']);

		$this->register_link=append_sid("{$this->root_path}ucp.{$this->php_ext}", 'mode=register');


	}

}
