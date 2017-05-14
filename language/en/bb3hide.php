<?php

/**
*
* @package BB3Hide
* @copyright (c) 2015 PPK
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
    'BB3HIDE_CONFIG'				=> 'Settings',

    'BB3HIDE_GUEST'			=>	'<i>Hidden text. <a href="%s" class="postlink">Registration</a> required.</i>',
    'BB3HIDE_GUEST_POSTS'	=>	'<i>Hidden text. <a href="%s" class="postlink">Registration</a> and a minimum of %d post(s) are required.</i>',
    'BB3HIDE_POSTS'			=>	'<i>Hidden text. A minimum of %d posts are required.</i>',
    'BB3HIDE_GROUPS'		=>	'<i>Hidden text. None of the groups you are a member of have been granted access.</i>',
    'BB3HIDE_QUOTE'			=>	'[i]Hidden text[/i]',

	'BB3HIDE_USERS'			=>	'<i>Скрытый текст. Доступен только определённым пользователям</i>',
	'BB3HIDE_GROUP'			=>	'<i>Скрытый текст. Вы не состоите в группе по умолчанию, которой доступен этот текст</i>',
	'BB3HIDE_QUOTE_PREVIEW'			=>	'<i>Скрытый текст</i>',

	'BB3HIDE_HIDES_HELPLINE' => 'Скрыть текст от гостей и ботов: [hide]текст[/hide], скрыть текст от пользователей с числом сообщений меньше указанного [hide=число]текст[/hide]',
	'BB3HIDE_HIDE_HELPLINE' => 'Скрыть текст от гостей и ботов: [hide]текст[/hide]',
	'BB3HIDE_HIDEPLUS_HELPLINE' => 'Скрыть текст от пользователей с числом сообщений меньше указанного [hide=число]текст[/hide]',
	'BB3HIDE_GHIDES_HELPLINE' => 'Скрыть текст от пользователей не из вашей группы по умолчанию: [ghide]текст[/ghide], скрыть текст от пользователей не из указанных групп [ghide=номер_группы_1,номер_группы_2]текст[/ghide]',
	'BB3HIDE_GHIDE_HELPLINE' => 'Скрыть текст от пользователей не из вашей группы по умолчанию: [ghide]текст[/ghide]',
	'BB3HIDE_GHIDEPLUS_HELPLINE' => 'Скрыть текст от пользователей не из указанных групп [ghide=номер_группы_1,номер_группы_2]текст[/ghide]',
));
