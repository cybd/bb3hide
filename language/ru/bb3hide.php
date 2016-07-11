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
	'BB3HIDE_CONFIG'				=> 'Настройки',

	'BB3HIDE_GUEST'			=>	'<i>Скрытый текст. Необходимо <a href="%s" class="postlink">зарегистрироваться</a></i>',
	'BB3HIDE_GUEST_POSTS'	=>	'<i>Скрытый текст. Нужно быть <a href="%s" class="postlink">зарегистрированным</a> и иметь сообщений: %d</i>',
	'BB3HIDE_POSTS'			=>	'<i>Скрытый текст. Нужно иметь сообщений: %d</i>',
	'BB3HIDE_USERS'			=>	'<i>Скрытый текст. Доступен только определённым пользователям</i>',
	'BB3HIDE_GROUP'			=>	'<i>Скрытый текст. Вы не состоите в группе по умолчанию, которой доступен этот текст</i>',
	'BB3HIDE_GROUPS'			=>	'<i>Скрытый текст. Вы не состоите в группах, которым доступен этот текст</i>',
	'BB3HIDE_QUOTE'			=>	'[i]Скрытый текст[/i]',
	'BB3HIDE_QUOTE_PREVIEW'			=>	'<i>Скрытый текст</i>',

	'BB3HIDE_HIDES_HELPLINE' => 'Скрыть текст от гостей и ботов: [hide]текст[/hide], скрыть текст от пользователей с числом сообщений меньше указанного [hide=число]текст[/hide]',
	'BB3HIDE_HIDE_HELPLINE' => 'Скрыть текст от гостей и ботов: [hide]текст[/hide]',
	'BB3HIDE_HIDEPLUS_HELPLINE' => 'Скрыть текст от пользователей с числом сообщений меньше указанного [hide=число]текст[/hide]',
	'BB3HIDE_GHIDES_HELPLINE' => 'Скрыть текст от пользователей не из вашей группы по умолчанию: [ghide]текст[/ghide], скрыть текст от пользователей не из указанных групп [ghide=номер_группы_1,номер_группы_2]текст[/ghide]',
	'BB3HIDE_GHIDE_HELPLINE' => 'Скрыть текст от пользователей не из вашей группы по умолчанию: [ghide]текст[/ghide]',
	'BB3HIDE_GHIDEPLUS_HELPLINE' => 'Скрыть текст от пользователей не из указанных групп [ghide=номер_группы_1,номер_группы_2]текст[/ghide]',
));
