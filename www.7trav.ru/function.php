<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Системный модуль. Общие функции.
 *
 * @package Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 21.01.2010
 */

/**
 *
 * @param unknown_type $var
 */
function pre($var)
{
  print '!<pre>'; print_r($var); print '</pre>';
}

function get_page($url, $postdata = '')
{
  $ch = curl_init($url);
  //  201.55.193.6:3128
  //  202.63.72.10:80
  //  201.55.193.6:3128
  //  curl_setopt($ch,CURLOPT_PROXY,'202.63.72.10:80');
  //  curl_setopt($ch,CURLOPT_PROXYUSERPWD,'user:password');
  //  curl_setopt($ch,CURLOPT_HTTPPROXYTUNNEL,1);
  //	время работы
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);          //	полное время сеанса
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);    //	время ожидания соединения в секундах
  //	Передаем и возвращаем Заголовки и тело страницы
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_NOBODY, 0);
  //	User-Agent
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)");
  //	Referer
  curl_setopt($ch, CURLOPT_REFERER, $url);
  //	Host
  $header_mas = array();
  $url_mas = explode('/', $url);
  $header_mas[] = 'Host: ' . $url_mas[2];
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header_mas);
  //	Cookie
  curl_setopt($ch, CURLOPT_COOKIEFILE, PATH_ADMIN . '/session/' . SESSISON_NAME . '.txt'); //	посылка
  curl_setopt($ch, CURLOPT_COOKIEJAR, PATH_ADMIN . '/session/' . SESSISON_NAME . '.txt');  //	получение
  //	АВТОРИЗАЦИЯ МЕТОДОМ APACHE
  //	переадресация
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	//	переход по редиректу
  curl_setopt($ch, CURLOPT_MAXREDIRS, 3);				//	максимальное количество переадресаций
  //	запрос GET
  if ( '' == $postdata )
  {
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
  }
  //	запрос POST
  else
  {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
  }
  //	возвращаем результат в переменную
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $page = curl_exec($ch);
  //	ошибки
  $error_code = curl_errno($ch);
  $error_subj = curl_error($ch);
  $content_type=curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
  curl_close($ch);
  return $page;
}

//	получение бинарных данны в файл
function get_file($url, $file)
{
  $fp=fopen($file, 'w');
  $ch=curl_init($url);
  //	время работы
  curl_setopt($ch,CURLOPT_TIMEOUT,30);					//	полное время сеанса
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,30);		//	время ожидания соединения в секундах
  //	получение только тела
  curl_setopt($ch,CURLOPT_HEADER,0);
  curl_setopt($ch,CURLOPT_FILE,$fp);
  curl_exec($ch); fclose($fp);
  //	ошибки
  $error_code=curl_errno($ch);
  $error_subj=curl_error($ch);
  curl_close($ch);
  if ( $error_code > 0 ) return false; return true;
}
