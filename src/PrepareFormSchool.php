<?php

namespace ProjectSoft;

class PrepareFormSchool {

	public static function prepareProcess($modx, $data, $fl, $name)
	{
		$cfg = $fl->config->getConfig();
		$site = $modx->config['site_name'];
		$fl->mailConfig['subject']  = $cfg["subject"] = "Робот сайта «" . $site . "»";
		$fl->mailConfig['replyTo']  = $cfg["replyTo"] = $modx->config['email_bot'];
		$fl->mailConfig['fromName']  = $cfg["fromName"] = $modx->config['email_bot_name'];
		$fl->config->setConfig($cfg);
	}

	public static function prepare($modx, $data, $fl, $name)
	{
		$https_port = 443;
		$id = $modx->documentIdentifier;
		$url = $modx->makeUrl($id, '', '');
		$charset = $modx->config['modx_charset'];
		$secured = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
		$port = ((isset ($_SERVER['HTTPS']) && ( (strtolower($_SERVER['HTTPS']) == 'on') || ($_SERVER['HTTPS']) == '1')) || $_SERVER['SERVER_PORT'] == $https_port || $secured) ? 'https://' : 'http://';
		$input = $_SERVER['HTTP_HOST'];
		$idna = new idna_convert();
		$host = $port . $idna->decode($input) . $url;
		$message = $fl->getField('message');
		$message = $message ? htmlspecialchars(trim(strip_tags($message)), ENT_COMPAT, $charset, true) : '';
		$fl->setField('message', $message);
		$fl->setField("pagetitle", $modx->documentObject["pagetitle"]);
		$fl->setField("url", $host);
	}

	public static function prepareAfterProcess($modx, $data, $fl, $name)
	{
		$message = $fl->getField('message');
		$message = $message ? $message : '';
		$url = $fl->getField("pagetitle") . " _" . $fl->getField('url') . "_";
		$arr = array(
			"types" => array(
				'date'		=>'Дата',
				'theme'		=>'Тема',
				'email'		=> 'Email',
				'name'		=>'Имя',
				'phone'		=>'Телефон',
				'message'	=> 'Сообщение',
				'url'		=> 'Страница отправки'
			),
			'fields' => array(
				'date'		=> date('d.m.Y H:i:s', time() + $modx->config['server_offset_time']),
				'theme'		=> $fl->getField('theme'),
				'email'		=> $fl->getField('email'),,
				'name'		=> $fl->getField('first_name'),
				'phone'		=> $fl->getField('phone'),
				'message'	=> $message,
				'url'		=> $url
			),
		);
		$arr["parse_mode"] = "Markdown";
		$modx->invokeEvent('onSendBot', $arr);
	}
}