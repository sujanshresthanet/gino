<?php

include('configuration.php');

define('OS', '/');

/*
	Absolute Paths
*/
define('CACHE_DIR', SITE_ROOT.OS.'cache');
define('LIB_DIR', SITE_ROOT.OS.'lib');
define('CLASSES_DIR', LIB_DIR.OS.'classes');
define('APP_DIR', SITE_ROOT.OS.'app');
define('INCLUDE_DIR', LIB_DIR.OS.'include');
define('CSS_DIR', SITE_ROOT.OS.'css');
define('TPL_DIR', SITE_ROOT.OS.'templates');
define('EXTRA_DIR', SITE_ROOT.OS.'extra');
define('GRAPHICS_DIR', SITE_ROOT.OS.'graphics');
define('CONTENT_DIR', SITE_ROOT.OS.'contents');
define('DOC_DIR', CONTENT_DIR.OS.'documents');
define('TMP_DIR', '/tmp');
define('TMP_SITE_DIR', SITE_ROOT.OS.'tmp');

/*
	Home Site
*/
$home_file = basename($_SERVER['SCRIPT_NAME']);
define('HOME_FILE', SITE_WWW.'/'.$home_file);

/*
	Web Path Directories
*/
define('CSS_WWW', SITE_WWW.'/css');
define('CSS_BASE', CSS_WWW.'/main.css');

define('SITE_APP', SITE_WWW.'/app');
define('SITE_IMG', SITE_WWW.'/img');
define('SITE_GRAPHICS', SITE_WWW.'/graphics');
define('SITE_EXTRA', SITE_WWW.'/extra');
define('SITE_LIB', SITE_WWW.'/lib');
define('SITE_JS', SITE_LIB.'/js');
define('SITE_CUSTOM_CKEDITOR', SITE_LIB.'/custom_ckeditor');
//define('SITE_FCK', SITE_LIB.'/fck');
//define('FCK_FILE', SITE_FCK.'/fckconfig.js');

define('CONTENT_WWW', SITE_WWW.'/contents');
define('DOC_WWW', CONTENT_WWW.'/documents');

define('EVT_NAME', 'evt');
define('EVT_CONTROLLER', LIB_DIR.OS.'evt.php');
define('CORE', LIB_DIR.OS.'core.php');
define('METHOD_POINTER', LIB_DIR.OS.'methodPointer.php');
define('CLASS_PREFIX', 'class_');
define('INSTANCE_PREFIX', '_instance_');

?>
