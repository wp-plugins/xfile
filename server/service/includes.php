<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)) . '/');
/***
 * XApp-PHP related
 */
require_once (XAPP_LIB.'/vendor/autoload.php');
require_once (XAPP_LIB.'/vendor/xapp/Core/core.php');

/*require_once (XAPP_LIB.'/vendor/xapp/Util/Storage.php');*/

//json store std
/*
require_once (XAPP_LIB.'/vendor/xapp/Util/Std/Std.php');
require_once (XAPP_LIB.'/vendor/xapp/Util/Std/Query.php');


require_once (XAPP_LIB.'/vendor/xapp/Util/Json/Json.php');
require_once (XAPP_LIB.'/vendor/xapp/Util/Json/Query.php');*/
//require_once (XAPP_LIB.'/vendor/xapp/Util/Json/Store.php');



require_once (XAPP_BASEDIR.'../xide/storage/JsonStore.php');
require_once (XAPP_BASEDIR.'../xide/service/Settings.php');
/**
 * JSON Store
 */
/*require_once (XAPP_BASEDIR .'../xide/json/JSON.php');*/
/**
 * JSON Store
 */
/*require_once (XAPP_BASEDIR.'../xide/json/jsonstore.php');*/
/**
 * JSON PATH
 */
/*require_once (XAPP_BASEDIR .'../xide/json/jsonpath-0.8.1.php');*/

