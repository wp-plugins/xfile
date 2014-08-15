<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('_JEXEC') && !defined('_VALID_MOS')) die('Restricted access');

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)) . '/lib/');

require_once (XAPP_LIB .'utils/StringUtils.php');
require_once (XAPP_LIB .'utils/JSONUtils.php');
include (XAPP_BASEDIR.'connect/RpcError.php');
require_once(XAPP_BASEDIR . "Utils/Debugging.php");
/***
 * XApp-PHP related
 */
/*
require_once (XAPP_LIB.'/rpc/lib/vendor/autoload.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Core/core.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Cache/Cache.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Cache/Driver.php');
*/
/***
 * Import JSON-Store classes from 'xapp/Util'
 */
/*
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Storage.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Std.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Query.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Store.php');

require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Json.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Query.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Store.php');

require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Rpc/Interface/Callable.php');
*/