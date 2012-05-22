<?php

define('SITE_DIR',		$_SERVER['DOCUMENT_ROOT']);
//Корневая директория сайта
define('DIR_ROOT',		SITE_DIR);
//Директория с изображениями (относительно корневой)
define('DIR_IMAGES',	'/uploads');
//Директория с файлами (относительно корневой)
define('DIR_FILES',		'/uploads/files');


//Высота и ширина картинки до которой будет сжато исходное изображение и создана ссылка на полную версию
define('WIDTH_TO_LINK', 500);
define('HEIGHT_TO_LINK', 500);

//Атрибуты которые будут присвоены ссылке (для скриптов типа lightbox)
define('CLASS_LINK', 'lightview');
define('REL_LINK', 'lightbox');

date_default_timezone_set('Asia/Yekaterinburg');

require_once (is_dir(SITE_DIR . '/framework') ? (SITE_DIR . '/framework') : getenv('BAZALT_FRAMEWORK')) . '/core/include.inc';