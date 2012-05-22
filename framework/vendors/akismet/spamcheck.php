<?php

error_reporting(E_ALL & ~E_DEPRECATED | E_STRICT);
    ini_set('display_errors', 'on');
    ini_set('display_startup_errors', 'on');
require_once 'Akismet.php';

$WordPressAPIKey = '661ba60b0e5f';
$MyBlogURL = 'http://equalteam.org.ua/';

$name = 'Лора';
$email = 'k_larissa@bk.ru';
$url = '';
$comment = 'гинекологу обнаружили трихомонаду. Чуть семья не распалась. Пошли с мужем в кожвен, сдали оба там анализ - все чисто, нет трихомонады? Как им после этого верить?! А уже готовы были лечить, причем не бесплатно. Бедная печень и горе деньгам с клиниками!!!!';

   $akismet = new Akismet($MyBlogURL, $WordPressAPIKey);
   
   if($akismet->isKeyValid()) {
     echo 'valid';
   } else {
     echo 'invalid';
   }

$akismet = new Akismet($MyBlogURL, $WordPressAPIKey);
$akismet->setCommentAuthor($name);
$akismet->setCommentAuthorEmail($email);
$akismet->setCommentAuthorURL($url);
$akismet->setCommentContent($comment);
$akismet->setPermalink('http://www.example.com/blog/alex/someurl/');
if($akismet->isCommentSpam())
    echo 'spam';
else
    echo 'no spam';