<?php
/**
 * @version 1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
$screen=(array)get_current_screen();
if($screen['base']==='toplevel_page_xfile'){

}else{
	error_log('skip head!');
	return;
}



$XAPP_WP_NAME = 'xfile';
$XAPP_APP_FOLDER            = "xfile";
$XAPP_APP_NAME              = "xwordpress";

$ROOT_DIRECTORY_ABSOLUTE = realpath(dirname(__FILE__) . DS);

//wp related
$XAPP_PLUGIN_DIR_NAME = basename( $ROOT_DIRECTORY_ABSOLUTE );
$XAPP_PLUGIN_URL = plugins_url('',__FILE__);
$XAPP_SYS_PATH = ABSPATH;

//xapp-php related
$XAPP_BASE_DIRECTORY =  $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR. 'xapp' . DIRECTORY_SEPARATOR;
$XAPP_SITE_DIRECTORY =  $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR;



$XAPP_CLIENT_DIRECTORY = $XAPP_SITE_DIRECTORY . DIRECTORY_SEPARATOR . 'client';

$XAPP_SERVICE_URL  = './admin.php?page=xfile?view=rpc';
$XAPP_APP_URL = $XAPP_PLUGIN_URL . '/client/';
$XAPP_SITE_URL= get_site_url();

/****
 * XCOM Variables
 */
global $XAPP_FILE_START_PATH;           //this should be 'wp-content'
global $XAPP_FILE_ROOT;                 //this should be ABSPATH
global $XAPP_JQUERY_THEME;              //a jQuery theme
global $XAPP_UPLOAD_EXTENSIONS;         //self explaining
global $XAPP_XFILE_CONFIG;              //the Dojo app config as JSON encoded string
global $XAPP_XFILE_CONFIG_ARRAY;        //the Dojo app config as PHP array
global $XAPP_WP_SESSION;                //the session object



// defaults
$XAPP_FILE_ROOT = '/';
$XAPP_JQUERY_THEME = 'dot-luv';
$XAPP_UPLOAD_EXTENSIONS = 'js,css,less,bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS';
/***
 * Plugin Type, being using as filter for the plugin manager
 */
define("XAPP_PLUGIN_TYPE", 'XCOM');

/***
 * Minimal bootrap
 */
define('XAPP_BASEDIR',$XAPP_BASE_DIRECTORY);
include_once XAPP_BASEDIR . '/XApp_Service_Entry_Utils.php';
XApp_Service_Entry_Utils::includeXAppCore();
XApp_Service_Entry_Utils::includeXAppRPC();

require_once(XAPP_BASEDIR . '/app/Renderer.php');
require_once(XAPP_BASEDIR . '/commander/Commander.php');
XApp_App_Commander::loadDependencies();

include(XAPP_BASEDIR . 'lib/wordpress/ParameterHelper.php');//auth checker
$xappServicePath=  $XAPP_SITE_DIRECTORY . 'server' .DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'index_wordpress_admin.php';
/***
 * Pick up options
 */
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

    //store in session, the RPC server will reject any request otherwise
    $XAPP_WP_SESSION = XApp_WP_Session::get_instance();
    $XAPP_WP_SESSION['XAPP_PARAMETERS'] = $XAPP_XFILE_CONFIG_ARRAY;

}


/***
 * Setup xapp app bootstrapper
 */
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

$XAPP_FILE_SERVICE = "../../../wp-content/plugins/".$XAPP_PLUGIN_DIR_NAME . "/server/service/index_wordpress_admin.php";
if(!XApp_Service_Entry_Utils::isDebug()){
    $XAPP_FILE_SERVICE = "../wp-content/plugins/".$XAPP_PLUGIN_DIR_NAME . "/server/service/index_wordpress_admin.php";
}
?>


<?php

/***
 * Now we render all the application's resources out, using a Wordpress specific resource renderer : xapp/lib/wordpress/ResourceRenderer.php
 */

//Setup resource variables
$xappResourceRender->registerRelative('WP_PLUGIN',$XAPP_APP_URL);
$xappResourceRender->registerRelative('XCOM_ROOT',$XAPP_PLUGIN_URL);
$xappResourceRender->registerRelative('SITEURL',$XAPP_SITE_URL.'/');
$xappResourceRender->registerRelative('XCOM_PLUGINS_WEB_URL',$XAPP_PLUGIN_URL . '/xapp/commander/plugins/');

/*
$resourceVariables = (array)$xappResourceRender->registryToKeyValues(xapp_get_option(XApp_Resource_Renderer::RELATIVE_REGISTRY_NAMESPACE,$xappResourceRender));
$resourceVariables['HTML_HEADER']=array();
$resourceVariables['XAPP_PLUGIN_RESOURCES']=array();
$resourceVariables['DOJOPACKAGES']=array();
$resourceVariables['XFILE_CONFIG_MIXIN']=array();
$resourceVariables['RESOURCE_VARIABLES']=array();
$xappResourceRender->registerRelative('RESOURCE_VARIABLES',json_encode($resourceVariables,true));
*/


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
}else{

}
?>
<!--Start of Zopim Live Chat Script-->
<script type="text/javascript">
	window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
		d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
		_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute('charset','utf-8');
		$.src='//v2.zopim.com/?2NTMTET7LTUMmledW9IT55fQkq6DeptG';z.t=+new Date;$.
			type='text/javascript';e.parentNode.insertBefore($,e)})(document,'script');
</script>
<!--End of Zopim Live Chat Script-->