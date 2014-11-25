<?php
/**
 * @version 1.6
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

$screen=(array)get_current_screen();
if($screen['base']==='toplevel_page_xfile'){

}else{
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
$XAPP_BASE_DIRECTORY =  $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR . 'xapp' . DIRECTORY_SEPARATOR;
$XAPP_SITE_DIRECTORY =  $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR;

if(!defined('XAPP_BASEDIR')){
    define('XAPP_BASEDIR',$XAPP_BASE_DIRECTORY);
}

include_once XAPP_BASEDIR . '/XApp_Service_Entry_Utils.php';
XApp_Service_Entry_Utils::includeXAppCore();
XApp_Service_Entry_Utils::includeXAppRPC();

require_once(XAPP_BASEDIR . '/app/Renderer.php');
require_once(XAPP_BASEDIR . '/commander/Commander.php');
XApp_App_Commander::loadDependencies();


$XAPP_CLIENT_DIRECTORY = $XAPP_SITE_DIRECTORY . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'src';

//$XAPP_SERVICE_URL  = admin_url('admin-ajax.php?action=xfile-rpc&view=rpc');
$XAPP_SERVICE_URL  = './admin.php?page=xcommander?view=rpc';
$XAPP_APP_URL = $XAPP_PLUGIN_URL . '/client/src/';
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
$XAPP_DOJO_PACKAGES='[]';
$XAPP_DOJO_PACKAGE_LOCATION_PREFIX=$XAPP_PLUGIN_URL.'/xapp/commander/plugins/';

/***
 * Plugin Type, being using as filter for the plugin manager
 */
if(!defined('XAPP_PLUGIN_TYPE')){
    define("XAPP_PLUGIN_TYPE", 'XCOM');
}

/***
 * Minimal bootrap
 */
require_once(XAPP_BASEDIR . '/XApp_Service_Entry_Utils.php');
require_once(XAPP_BASEDIR . '/lib/wordpress/XAppWordpressAuth.php');//auth checker
require_once(XAPP_BASEDIR . '/lib/wordpress/ParameterHelper.php');//auth checker
require_once(XAPP_BASEDIR . '/Utils/Debugging.php');

$xappServicePath=  $XAPP_SITE_DIRECTORY . 'server' .DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'index_wordpress_admin.php';
include_once($XAPP_BASE_DIRECTORY . '/commander/Bootstrap.php');
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
    XApp_Commander_Bootstrap::PLUGIN_DIRECTORY        =>  $XAPP_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'commander' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR,
    XApp_Commander_Bootstrap::PLUGIN_MASK             =>  XAPP_PLUGIN_TYPE,
	XApp_Commander_Bootstrap::FLAGS                   => array(
		XAPP_BOOTSTRAP_ENABLE_GOOGLE_ANALYTICS
	)

);
//create bootstrap
$xappBootrapper = new XApp_Commander_Bootstrap($xappBootrapperOptions);
//http://mc007ibi.dyndns.org:81/wordpress/wp-admin/admin-ajax.php?action=xfile-rpc&service=XCOM_Directory_Service.put&callback=nada&mount=%2Froot&dstDir=.%2Ftest
//do the bootstrap
$xappCommanderRenderer = $xappBootrapper->setup();

//extract resource renderer
$xappResourceRender = xapp_get_option(XApp_App_Commander::RESOURCE_RENDERER,$xappCommanderRenderer);
//queue the jQuery theme
if( isset($_SERVER['HTTPS'] ) ) {
    $jQueryThemeUrl = "https://code.jquery.com/ui/1.10.3/themes/" .  $XAPP_JQUERY_THEME . "/jquery-ui.css ";
}
else{
    $jQueryThemeUrl = "http://code.jquery.com/ui/1.10.3/themes/" .  $XAPP_JQUERY_THEME . "/jquery-ui.css ";
}

wp_enqueue_style(md5($jQueryThemeUrl),$jQueryThemeUrl);
$XAPP_FILE_SERVICE = "../wp-content/plugins/".$XAPP_PLUGIN_DIR_NAME . "/server/service/index_wordpress_admin.php?view=rpc";

//$XAPP_FILE_SERVICE = admin_url('admin-ajax.php?action=xfile-rpc&view=rpc');

$authDelegate = new XAppWordpressAuth();
$authDelegate->setSalt(SECURE_AUTH_SALT);


$XAPP_SERVICE_URL_MIXIN = $XAPP_SITE_URL .'/wp-content/plugins/' . $XAPP_PLUGIN_DIR_NAME . '/server/service/index_wordpress_admin.php?view=rpc';

$xappResourceRender->registerRelative('APP_URL',$XAPP_APP_URL);
$resourceVariables = (array)$xappResourceRender->registryToKeyValues(xapp_get_option(XApp_Resource_Renderer::RELATIVE_REGISTRY_NAMESPACE,$xappResourceRender));
$resourceVariables['HTML_HEADER']=array();
$resourceVariables['XAPP_PLUGIN_RESOURCES']=array();
$resourceVariables['DOJOPACKAGES']=array();
$resourceVariables['XFILE_CONFIG_MIXIN']=array();
$resourceVariables['RESOURCE_VARIABLES']=array();
$xappResourceRender->registerRelative('RESOURCE_VARIABLES',json_encode($resourceVariables,true));


?>

<script type="application/javascript">
    var xFileConfigMixin =<?php echo $XAPP_XFILE_CONFIG?>;
    var xFileConfig={
	    mixins:[
		    {
			    declaredClass:'xide.manager.ServerActionBase',
			    mixin:{
				    serviceUrl:"<?php echo $XAPP_SERVICE_URL_MIXIN?>",
				    singleton:true
			    }
		    },
		    {
			    declaredClass:'xfile.manager.FileManager',
			    mixin:{
				    serviceUrl:"<?php echo $XAPP_SERVICE_URL_MIXIN?>",
				    singleton:true
			    }
		    },
		    {
			    declaredClass:'xide.manager.SettingsManager',
			    mixin:{
				    serviceUrl:"<?php echo $XAPP_SERVICE_URL_MIXIN?>",
				    singleton:true
			    }
		    },
		    {
			    declaredClass:'xide.manager.ResourceManager',
			    mixin: {
				    serviceUrl:"<?php echo $XAPP_SERVICE_URL_MIXIN?>",
				    singleton: true,
				    resourceVariables: <?php echo json_encode($resourceVariables,true)?>
				}
		    }
	    ],
        CODDE_MIRROR:"<?php echo $XAPP_APP_URL?>/xfile/ext/cm/",
        THEME_ROOT:"<?php echo $XAPP_APP_URL?>/themes/",
        WEB_ROOT:"<?php echo $XAPP_APP_URL?>",
        FILE_SERVICE:"<?php echo $XAPP_FILE_SERVICE?>",
        FILE_SERVICE_FULL:"<?php echo $XAPP_FILE_SERVICE?>",
        FILES_STORE_URL:"<?php echo $XAPP_SERVICE_URL_MIXIN?>",
        DOWNLOAD_URL2:"<?php echo $XAPP_FILE_SERVICE .'&service=XCOM_Directory_Service.get'?>",
	    DOWNLOAD_URL:"<?php echo $XAPP_SITE_URL .'/wp-content/plugins/' .$XAPP_PLUGIN_DIR_NAME .'/server/service/index_wordpress_admin.php?service=XCOM_Directory_Service.get'?>",
        REPO_URL:"<?php echo $XAPP_SITE_URL .'/'. $XAPP_XFILE_CONFIG_ARRAY['XAPP_FILE_START_PATH']?>",//obsolete
        FILES_STORE_SERVICE_CLASS:'XCOM_Directory_Service',
        RPC_PARAMS:{
            rpcUserField:'user',
            rpcUserValue:"<?php echo md5(XAppWordpressAuth::getUserName()) ?>",
            rpcSignatureToken:"<?php echo md5(XAppWordpressAuth::getToken()) ?>",
            rpcSignatureField:'sig',
            rpcFixedParams:{

            }
        },
        MEDIA_PICKER2:{
            showPreview:true,
            editorNode:'jform_articletext_parent',
            editorTextNode:'jform_articletext',
            editorNodeAfter:'editor-xtd-buttons',
            toolbarClass:'.ui-state-default',
            editorPreviewTarget:'topTabs',
            editorPreviewLayoutZone:'center',
            editorPreviewLayoutContainerClass:'contentPreviewPane'
        },
        ACTION_TOOLBAR_MODE:'self'
};
</script>

<?php

/***
 * Now we render all the application's resources out, using a Wordpress specific resource renderer : xapp/lib/wordpress/ResourceRenderer.php
 */

//Setup resource variables
$xappResourceRender->registerRelative('WP_PLUGIN',$XAPP_APP_URL);
$xappResourceRender->registerRelative('XCOM_ROOT',$XAPP_PLUGIN_URL);
$xappResourceRender->registerRelative('SITEURL',$XAPP_SITE_URL.'/');
$xappResourceRender->registerRelative('XCOM_PLUGINS_WEB_URL',$XAPP_PLUGIN_URL . '/xapp/commander/plugins/');

/***
 * Now update Dojo's package info
 */
$javascriptPlugins = $xappResourceRender->getJavascriptPlugins();
if($javascriptPlugins && count($javascriptPlugins)){

    if(XApp_Service_Entry_Utils::isDebug()){

        $dojoPackages = array();
        $dojoPackagesStr ='[';
        $pIdx=0;
        foreach($javascriptPlugins as $plugin){

            if($pIdx>0)
            {
                $dojoPackagesStr.=",";
            }
            $dojoPackagesStr.="{name:" . "'"	. $plugin->name . "',";
            $dojoPackagesStr.="location:"	. "'"	. $XAPP_DOJO_PACKAGE_LOCATION_PREFIX . $plugin->name . '/client/' .  "'}";
            if($pIdx<count($javascriptPlugins)-1){
                $dojoPackagesStr.=',';
            }
        }
        $dojoPackagesStr.=']';
        $XAPP_DOJO_PACKAGES=$dojoPackagesStr;

    }else{
        /*$dojoPackages = array();*/
        $dojoPackages=array();
        array_push($dojoPackages,array('name'=>'dojo','location'=>'dojo'));
        array_push($dojoPackages,array('name'=>'dojox','location'=>'dojox'));
        array_push($dojoPackages,array('name'=>'dijit','location'=>'dijit'));
        array_push($dojoPackages,array('name'=>'cbtree','location'=>'cbtree'));
        array_push($dojoPackages,array('name'=>'xfile','location'=>'xfile'));
        array_push($dojoPackages,array('name'=>'xide','location'=>'xide'));
        array_push($dojoPackages,array('name'=>'xwordpress','location'=>'xwordpress'));
        foreach($javascriptPlugins as $plugin){
            array_push($dojoPackages,array('name'=>$plugin->name,'location'=>$XAPP_DOJO_PACKAGE_LOCATION_PREFIX . $plugin->name . '/client/'));
        }
        $XAPP_DOJO_PACKAGES=json_encode($dojoPackages);
    }
}



$xappResourceRender->registerRelative('DOJOPACKAGES',$XAPP_DOJO_PACKAGES);

//Render resources. Resources variables will be resolved by using %XCOM_ROOT%
$xappCommanderRenderer->renderResources();

/***
 * Render HTML
 */
$htmlTemplates = $xappResourceRender->renderHTML();

echo $htmlTemplates;
