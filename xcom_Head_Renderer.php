<?php
/**
 * @version 0.1.0
 * @link https://github.com/mc007
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

//////////////////////////////////////////////////////////////////////////////////
//
//  Killswitchs
//
//////////////////////////////////////////////////////////////////////////////////
$screen=(array)get_current_screen();
if(!$screen['base']==='toplevel_page_xfile')
	return;


//////////////////////////////////////////////////////////////////////////////////
//
//  Variables and path substitution
//
//////////////////////////////////////////////////////////////////////////////////

$XAPP_WP_NAME               = 'xfile';//the name of this plugin
$XAPP_APP_FOLDER            = "xfile";//the folder to the client application
$XAPP_APP_NAME              = "xwordpress";//the name of the client application (inherits from 'xfile' for a few customizations)
$WP_PLUGIN_LOCATION_REL     = '/wp-content/plugins/';


$ROOT_DIRECTORY_ABSOLUTE    = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR); // this points to this plugin's directory

//wp related
$XAPP_PLUGIN_DIR_NAME       = basename( $ROOT_DIRECTORY_ABSOLUTE ); //should be 'xfile'
$XAPP_PLUGIN_URL            = plugins_url('',__FILE__);             //url to this folder
$XAPP_SITE_URL              = get_site_url();                       //the site url
$XAPP_SYS_PATH = ABSPATH;

//xapp-php related
$XAPP_BASE_DIRECTORY =  $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR. 'xapp' . DIRECTORY_SEPARATOR;
$XAPP_SITE_DIRECTORY =  $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR;
$XAPP_CLIENT_DIRECTORY = $XAPP_SITE_DIRECTORY . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'src';

$XAPP_SERVICE_URL  = './admin.php?page=xfile?view=rpc';
$XAPP_APP_URL = $XAPP_PLUGIN_URL . '/client/src/';



global $XAPP_FILE_START_PATH;           //this should be 'wp-content'
global $XAPP_FILE_ROOT;                 //this should be ABSPATH
global $XAPP_JQUERY_THEME;              //a jQuery theme
global $XAPP_UPLOAD_EXTENSIONS;         //self explaining
global $XAPP_XFILE_CONFIG;              //the Dojo app config as JSON encoded string
global $XAPP_XFILE_CONFIG_ARRAY;        //the Dojo app config as PHP array
global $XAPP_WP_SESSION;                //the session object



// defaults
$XAPP_FILE_ROOT = '/';
$XAPP_JQUERY_THEME = 'blitzer';
$XAPP_UPLOAD_EXTENSIONS = 'js,css,less,bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls';



define("XAPP_PLUGIN_TYPE", 'XCOM');     //Plugin Type, being using as filter for the plugin manager
define('XAPP_BASEDIR',$XAPP_BASE_DIRECTORY);

//////////////////////////////////////////////////////////////////////////////////
//
//  Load dependencies
//
//////////////////////////////////////////////////////////////////////////////////


include_once XAPP_BASEDIR . '/XApp_Service_Entry_Utils.php';
XApp_Service_Entry_Utils::includeXAppCore();
XApp_Service_Entry_Utils::includeXAppRPC();
require_once(XAPP_BASEDIR . '/app/Renderer.php');
require_once(XAPP_BASEDIR . '/commander/Commander.php');
XApp_App_Commander::loadDependencies();

include(XAPP_BASEDIR . 'lib/wordpress/ParameterHelper.php');
$xappServicePath=  $XAPP_SITE_DIRECTORY . 'server' .DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'index_wordpress_admin.php';

//////////////////////////////////////////////////////////////////////////////////
//
//  Load configuration
//
//////////////////////////////////////////////////////////////////////////////////
$xcomParameters = XApp_Wordpress_Parameter_Helper::getComponentParameters();
if($xcomParameters){
    $xfileConfig =XApp_Wordpress_Parameter_Helper::toXFileConfig($xcomParameters);

	$XAPP_XFILE_CONFIG_ARRAY = $xfileConfig;
    $XAPP_XFILE_CONFIG_ARRAY['XAPP_IS_LOGGED_IN']= is_user_logged_in();

    $xfileConfig = json_encode($xfileConfig);
    $xfileConfig = preg_replace( "/\"(\d+)\"/", '$1', $xfileConfig);
	$XAPP_XFILE_CONFIG = $xfileConfig;
    $XAPP_XFILE_CONFIG_ARRAY['XAPP_FILE_ROOT'] = rtrim(ABSPATH, '/');
    $XAPP_XFILE_CONFIG_ARRAY['XAPP_FILE_START_PATH'] = ''  . $XAPP_XFILE_CONFIG_ARRAY['START_PATH'];
	$XAPP_JQUERY_THEME = $XAPP_XFILE_CONFIG_ARRAY['JQTHEME'];
	$XAPP_WP_SESSION = XApp_WP_Session::get_instance();
    $XAPP_WP_SESSION['XAPP_PARAMETERS'] = $XAPP_XFILE_CONFIG_ARRAY;
}

//////////////////////////////////////////////////////////////////////////////////
//
//  Setup bootstrap
//
//////////////////////////////////////////////////////////////////////////////////

//bootstrap config
$xappBootrapperOptions = array(
    XApp_Commander_Bootstrap::BASEDIR                 =>  $XAPP_BASE_DIRECTORY,
    XApp_Commander_Bootstrap::APPDIR                  =>  $XAPP_CLIENT_DIRECTORY,
    XApp_Commander_Bootstrap::SERVICE                 =>  $XAPP_SERVICE_URL,
    XApp_Commander_Bootstrap::APP_NAME                =>  $XAPP_APP_NAME,
    XApp_Commander_Bootstrap::APP_FOLDER              =>  $XAPP_APP_FOLDER,
    XApp_Commander_Bootstrap::DOC_ROOT                =>  $XAPP_APP_URL,
    XApp_Commander_Bootstrap::RENDER_DELEGATE         =>  new stdClass(),
    XApp_Commander_Bootstrap::RESOURCE_CONFIG_SUFFIX  =>  '-wordpress-admin',
    XApp_Commander_Bootstrap::RESOURCE_RENDERER_PREFIX=>  'wordpress',
    XApp_Commander_Bootstrap::RESOURCE_RENDERER_CLZ   =>  'XApp_Wordpress_Resource_Renderer',
    XApp_Commander_Bootstrap::PLUGIN_DIRECTORY        =>  $XAPP_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'commander' . DIRECTORY_SEPARATOR . 'plugins' .DIRECTORY_SEPARATOR,
    XApp_Commander_Bootstrap::PLUGIN_MASK             =>  XAPP_PLUGIN_TYPE
);

//create bootstrap
$xappBootrapper = new XApp_Commander_Bootstrap($xappBootrapperOptions);

//do the bootstrap
$xappCommanderRenderer = $xappBootrapper->setup();

//extract resource renderer
$xappResourceRender = xapp_get_option(XApp_App_Commander::RESOURCE_RENDERER,$xappCommanderRenderer);

$XAPP_FILE_SERVICE = "../../..'" . $WP_PLUGIN_LOCATION_REL . $XAPP_PLUGIN_DIR_NAME . "/server/service/index_wordpress_admin.php";
if(!XApp_Service_Entry_Utils::isDebug()){
    $XAPP_FILE_SERVICE = ".." .$WP_PLUGIN_LOCATION_REL . $XAPP_PLUGIN_DIR_NAME . "/server/service/index_wordpress_admin.php";
}

//////////////////////////////////////////////////////////////////////////////////
//
//  HTML head rendering
//
//////////////////////////////////////////////////////////////////////////////////

/***
 * Now we render all the application's resources out, using a Wordpress specific resource renderer : xapp/lib/wordpress/ResourceRenderer.php
 */

//store resource variables
$xappResourceRender->registerRelative('WP_PLUGIN',$XAPP_APP_URL);
$xappResourceRender->registerRelative('XCOM_ROOT',$XAPP_PLUGIN_URL);
$xappResourceRender->registerRelative('SITEURL',$XAPP_SITE_URL.'/');
$xappResourceRender->registerRelative('XCOM_PLUGINS_WEB_URL',$XAPP_PLUGIN_URL . '/xapp/commander/plugins/');


$javaScriptHeader = '<script type="application/javascript">';
$javaScriptHeader.=$xappResourceRender->renderJavascriptHeaderTags();
$javaScriptHeader.= '</script>';

echo($javaScriptHeader);

$javascriptPlugins = $xappResourceRender->getJavascriptPlugins();
if($javascriptPlugins && count($javascriptPlugins)){
    $javaScriptHeaderStr = '<script type="application/javascript">';
    $javaScriptHeaderStr .= 'var xappPluginResources=';
    $javaScriptHeaderStr .=json_encode($javascriptPlugins) .';';
    $javaScriptHeaderStr.= '</script>';
    echo($javaScriptHeaderStr);
}
?>