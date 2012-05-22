<?php

define('CMS_DIR', dirname(__FILE__));

using('Framework.System.Config');
using('Framework.System.Metadata');
using('Framework.System.Html');
using('Framework.System.ORM');

/**
 * Файл з ключом, для шифрування даних
 */
if (!defined('KEYPASS_FILE')) {
    define('KEYPASS_FILE', SITE_DIR . '/.keypass');
}
/**
 * Збереження ІД сайту в сесіях, для того щоб в адмінці можна було перемикати поточний сайт
 */
if (!defined('IDENTIFY_SITE_BY_SESSION')) {
    define('IDENTIFY_SITE_BY_SESSION', false);
}

if (!defined('USE_THEMES')) {
    define('USE_THEMES', true);
}

if (!defined('USE_DEFAULT_THEME')) {
    define('USE_DEFAULT_THEME', true);
}

if (!defined('WEBCONFIG_FILE')) {
    define('WEBCONFIG_FILE', SITE_DIR . '/bazalt.config');
}

/*
if(!extension_loaded('zlib')) {
    throw new Exception('Extension zlib is not loaded');
}*/

Core_Autoload::registerNamespace('CMS', CMS_DIR);

CMS_ErrorCatcher::startCatch();

Html_Form::getView()->addFolder(dirname(__FILE__) . '/templates', 'CMS');

Html_Form::registerElement('alert',  'CMS_Form_Element_Alert');
Html_Form::registerElement('cms_table', 'CMS_Form_Element_Table');
Html_Form::registerElement('wysiwyg',   'CMS_Form_Element_Wysiwyg');
Html_Form::registerElement('uploader',  'CMS_Form_Element_Uploader');
Html_Form::registerElement('imageuploader',  'CMS_Form_Element_ImageUploader');
Html_Form::registerElement('languageTabs',  'CMS_Form_Language_Tabs');


Event::register('CMS_Application', 'BeforeRouteInit', array('CMS_Webservice', 'initWebserviceRoutes'));
Event::register('CMS_Application', 'BeforeRouteInit', array('CMS_User', 'initWebserviceRoutes'));

CMS_View::register('php', 'CMS_View_PhpEngine');
CMS_View::register('twg', 'CMS_View_TwigEngine');
CMS_View::register('tpl', 'CMS_View_SmartyEngine');
CMS_View::register('inc', 'CMS_View_PhpEngine');