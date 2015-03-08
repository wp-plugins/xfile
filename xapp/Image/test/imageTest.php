<?

$XAPP_BASE_DIRECTORY =  realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'  . DIRECTORY_SEPARATOR  . '..') . DIRECTORY_SEPARATOR;
$XAPP_SERVICE_DIRECTORY =  realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'  . DIRECTORY_SEPARATOR  . '..' . DIRECTORY_SEPARATOR . '..' .DIRECTORY_SEPARATOR . 'server');


/***
 * Framework minimal includes, ignore!
 */
define('XAPP_BASEDIR',$XAPP_BASE_DIRECTORY); //important !
define('XAPP_LIB',$XAPP_BASE_DIRECTORY.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR); //important !
require_once(XAPP_BASEDIR . '/XApp_Service_Entry_Utils.php');
XApp_Service_Entry_Utils::includeXAppCore();
XApp_Service_Entry_Utils::includeXAppJSONStoreClasses();
XApp_Service_Entry_Utils::includeXAppJSONTools();
XApp_Service_Entry_Utils::includeXAppRegistry();
define('_VALID_MOS',1);//bypass joomla php security
define('_JEXEC',1);//bypass joomla php security
require_once(XAPP_BASEDIR . '/app/Renderer.php');
require_once(XAPP_BASEDIR . '/commander/Commander.php');
XApp_App_Commander::loadDependencies();
xapp_setup_language_standalone();

xapp_import('xapp.Resource.Renderer');

/*************************************************************************************/
error_reporting(E_ALL);

xapp_import('xapp.Image.Utils');



$jobs = '
[
 {
  "'.XApp_Image_Utils::IMAGE_OPERATION.'" : "'.XApp_Image_Utils::OPERATION_RESIZE.'",
  "'.XApp_Image_Utils::OPERATION_OPTIONS.'" : {
      "'.XApp_Image_Utils::OPTION_HEIGHT.'" : "300",
      "'.XApp_Image_Utils::OPTION_PREVENT_CACHE.'" : "true"

   }
 }
]
';


$errors = array();

$src = XAPP_BASEDIR . DS . 'tests' . DS . 'data' . DS . 'Images' . DS . 'adidas.gif';
$dst = XAPP_BASEDIR . DS . 'tests' . DS . 'data' . DS . 'Images' . DS . 'out.gif';

XApp_Image_Utils::execute($src,$dst,$jobs,$errors);
/*
$src = XAPP_BASEDIR . DS . 'tests' . DS . 'data' . DS . 'Images' . DS . 'Lighthouse.jpg';
$dst = XAPP_BASEDIR . DS . 'tests' . DS . 'data' . DS . 'Images' . DS . 'out.jpg';

XApp_Image_Utils::execute($src,$dst,$jobs,$errors);

$src = XAPP_BASEDIR . DS . 'tests' . DS . 'data' . DS . 'Images' . DS . 'sample.png';
$dst = XAPP_BASEDIR . DS . 'tests' . DS . 'data' . DS . 'Images' . DS . 'out.png';

XApp_Image_Utils::execute($src,$dst,$jobs,$errors);

*/
xapp_dump( $errors );