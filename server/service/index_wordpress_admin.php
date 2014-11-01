<?php
/**
 * Service entry point, uses Wordpress session manager to get some parameters
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

if(!defined('DS')){
    define( 'DS', DIRECTORY_SEPARATOR );
}

//enable this for Joomla code
if( !defined( '_JEXEC' ) ){
    define('_JEXEC',true);
}
//enable this for Joomla code
if( !defined( '_VALID_MOS' ) ){
    define('_VALID_MOS',true);
}
$XAPP_WP_NAME = 'xcom';
$XAPP_APP_FOLDER            = "xfile";
$XAPP_APP_NAME              = "xwordpress";

$XAPP_WP_ROOT_PREFIX = DIRECTORY_SEPARATOR."..". DIRECTORY_SEPARATOR ."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."../";
require_once(realpath( dirname(__FILE__) .   $XAPP_WP_ROOT_PREFIX) . DIRECTORY_SEPARATOR . "wp-load.php");

$XCOM_ROOT = realpath( dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$XAPP_ROOT  = realpath($XCOM_ROOT . "xapp") . DIRECTORY_SEPARATOR;
$XAPP_PLUGIN_URL = plugins_url('',__FILE__);

define('XAPPED', true);
define("XAPP_BASEDIR",$XAPP_ROOT .DIRECTORY_SEPARATOR);
define("XAPP_LIB", XAPP_BASEDIR. DIRECTORY_SEPARATOR . "lib" .DIRECTORY_SEPARATOR);
define("XAPP_ADMIN_SERVICE_ROOT", realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define("XAPP_CONF_DIRECTORY", XAPP_ADMIN_SERVICE_ROOT . '..' . DIRECTORY_SEPARATOR. '..' . DIRECTORY_SEPARATOR. "conf" . DIRECTORY_SEPARATOR);


if(!class_exists('XApp_Service_Entry_Utils')){
    include_once(XAPP_BASEDIR . 'XApp_Service_Entry_Utils.php');//conf data
}
define('XAPP_INDEX',xapp_fix_index());

XApp_Service_Entry_Utils::includeXAppCore();
XApp_Service_Entry_Utils::includeXAppRPC();

require_once(XAPP_BASEDIR . '/app/Renderer.php');
require_once(XAPP_BASEDIR . '/commander/Commander.php');
XApp_App_Commander::loadDependencies();

global $XAPP_FILE_ROOT;
global $XAPP_UPLOAD_EXTENSIONS;

if($XAPP_FILE_ROOT==null){
    $XAPP_FILE_ROOT = '/';
}

if($XAPP_FILE_ROOT==null){
    $XAPP_UPLOAD_EXTENSIONS = 'js,css,less,bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS';
}

include_once (ABSPATH . DIRECTORY_SEPARATOR .  'wp-includes' . DIRECTORY_SEPARATOR . 'l10n.php');
xapp_setup_language_wordpress();

include_once(XAPP_BASEDIR . '/commander/Bootstrap.php');
if(!class_exists('XAppWordpressAuth')){
    include_once(XAPP_LIB . 'wordpress/XAppWordpressAuth.php');
}
include_once(XAPP_LIB . 'wordpress/ParameterHelper.php');//conf data

$XAPP_WP_PATH=realpath( $XCOM_ROOT . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..' ) . DIRECTORY_SEPARATOR;//our plugin absolute directory

//wp_set_auth_cookie( '038d6c8f26523d54d6b8e9dc3661f2c4||1414345008||1414344648', true );
//load wp core stuff: pretty heavy !
require_once( $XAPP_WP_PATH . 'wp-config.php' );
require_once ($XAPP_WP_PATH . 'wp-load.php');


//get our session
include_once ($XCOM_ROOT . 'class-recursive-arrayaccess.php');
include_once ($XCOM_ROOT . 'class-wp-session.php');
$XAPP_WP_SESSION = XApp_WP_Session::get_instance();
$XAPP_PARAMETERS = null;
$XAPP_NEEDS_SESSION = true;

//Setup Wordpress Auth delegate for the file service
$authDelegate = new XAppWordpressAuth();
$authDelegate->setSalt(SECURE_AUTH_SALT);//make token salt site based
$XAPP_USER_NAME = $authDelegate->getUserName();

$xfToken = mysql_real_escape_string(htmlentities($_GET['xfToken']));
$userHash = mysql_real_escape_string(htmlentities($_GET['user']));


if( !$authDelegate->isLoggedIn() && //no logged in
	$XAPP_USER_NAME==='guest' && // just to be sure
	XApp_Service_Entry_Utils::isPictureService2() && //only for special requests
	$xfToken && //we need a token
	$userHash &&//we need a md5 of the user name
	$authDelegate->loginUserByToken($xfToken,$userHash)
)
{

	/**
	 * read config and re-construct session parameters
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
	$XAPP_USER_NAME = $authDelegate->getUserName();//important!

}
if($XAPP_NEEDS_SESSION) {
	//kill switch for non - authorized requests
	if($XAPP_WP_SESSION==null){
		die('have no session');
	}
	$XAPP_PARAMETERS = $XAPP_WP_SESSION['XAPP_PARAMETERS'];
	if ($XAPP_PARAMETERS == null) {
		die('have no session parameters!');
	}
	if (!is_user_logged_in()) {
		die('you must be logged in');
	}
}
/***
 * Now transfer settings
 */
$XAPP_FILE_START_PATH='';
if(array_key_exists('XAPP_FILE_START_PATH',$XAPP_PARAMETERS)){
	$XAPP_FILE_START_PATH = $XAPP_PARAMETERS['XAPP_FILE_START_PATH'];
}

$XAPP_UPLOAD_EXTENSIONS=$XAPP_PARAMETERS['XAPP_UPLOAD_EXTENSIONS'];


$repositoryRoot = ABSPATH . DIRECTORY_SEPARATOR;
if($XAPP_FILE_ROOT!=null){
    $repositoryRoot .= DIRECTORY_SEPARATOR. $XAPP_FILE_ROOT . DIRECTORY_SEPARATOR;
}
if($XAPP_FILE_START_PATH!=null){
    $repositoryRoot.=$XAPP_FILE_START_PATH;
}

if($XAPP_UPLOAD_EXTENSIONS==null){
    $XAPP_UPLOAD_EXTENSIONS = 'bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS';
}

xapp_import('xapp.Service.Service');
xapp_import('xapp.commander.Directory.Service');
xapp_import('xapp.VFS.Base');
xapp_import('xapp.VFS.Local');
xapp_import('xapp.Option.Utils');
xapp_import('xapp.Directory.Utils');
xapp_import('xapp.Path.Utils');
xapp_import('xapp.Commons.VariableMixin');
xapp_import('xapp.Resource.Renderer');
xapp_import('xapp.Store.Json.Json');
xapp_import('xapp.Resource.Service');
xapp_import('xapp.Resource.ResourceManager');
xapp_import('xapp.xide.Logging.Service');
xapp_import('xapp.xide.Logging.LogManager');

//redux parameters
$REDUX_OPTIONS=XApp_Wordpress_Parameter_Helper::getComponentParameters();

//vfs config, there is just one entry at /
$XAPP_VFS_CONFIG_PATH = XAPP_BASEDIR . 'commander'. DIRECTORY_SEPARATOR . 'vfs.json';
$XAPP_VFS_CONFIG_PASSWORD = '2K<{K01!k;6484| Q9=VUA#P8FFDcNy u!w_@<gV6zXPJy3yI^g2[:LqwIe6rXO2';
if(defined('AUTH_SALT')){
	$XAPP_VFS_CONFIG_PASSWORD = AUTH_SALT;
}
$repositoryRoot = XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($repositoryRoot));

//xfile - repo root, back-compat :
$xFileRepositoryRoot = XApp_Variable_Mixin::replaceResourceVariables($repositoryRoot,array(
        'root' => $repositoryRoot,
        'user' => $authDelegate->getUserName()
    ));


$XAPP_FILE_SERVICE = admin_url('admin-ajax.php?action=xfile-rpc');//not used anymore, to slow!!!
$XAPP_SETTINGS_FILE  = XAPP_CONF_DIRECTORY . DIRECTORY_SEPARATOR . 'settings.json';
$XIDE_LOG_PATH = realpath(XAPP_BASEDIR . '..' . DIRECTORY_SEPARATOR . 'logs'. DIRECTORY_SEPARATOR . 'all.log');
if(!$XIDE_LOG_PATH){
	$XIDE_LOG_PATH='';
}




require_once(XAPP_BASEDIR. 'lib/standalone/StoreDelegate.php');

try{


	Xapp_Rpc_Gateway::setSalt(SECURE_AUTH_SALT);//

    /***
     * Build boostrap configuration
     */
    $opt = array(
        XApp_Commander_Bootstrap::BASEDIR                 =>  XAPP_BASEDIR,
        XApp_Commander_Bootstrap::APP_NAME                =>  'xfile',
        XApp_Commander_Bootstrap::APP_FOLDER              =>  'xwordpress',
        XApp_Commander_Bootstrap::RESOURCE_CONFIG_SUFFIX  =>  '',
        XApp_Commander_Bootstrap::RESOURCE_RENDERER_PREFIX=>  'wordpress',
        XApp_Commander_Bootstrap::RESOURCE_RENDERER_CLZ   =>  'XApp_Wordpress_Resource_Renderer',
        XApp_Commander_Bootstrap::PLUGIN_DIRECTORY        =>  XAPP_BASEDIR . DIRECTORY_SEPARATOR . 'commander' . DIRECTORY_SEPARATOR . 'plugins' .DS,
        XApp_Commander_Bootstrap::PLUGIN_MASK             =>  'XCOM',
        XApp_Commander_Bootstrap::ALLOW_PLUGINS           =>  TRUE,
        XApp_Commander_Bootstrap::PROHIBITED_PLUGINS      =>  '',
        XApp_Commander_Bootstrap::FLAGS                   =>  array(

            XAPP_BOOTSTRAP_LOAD_PLUGIN_RESOURCES,   //ignored when XApp_Commander_Bootstrap::ALLOW_PLUGINS is off
            XAPP_BOOTSTRAP_REGISTER_SERVER_PLUGINS, //ignored when XApp_Commander_Bootstrap::ALLOW_PLUGINS is off
            XAPP_BOOTSTRAP_SETUP_XAPP,              //takes care about output encoding and compressing
            XAPP_BOOTSTRAP_SETUP_RPC,               //the RPC server
            //XAPP_BOOTSTRAP_SETUP_XFILE,             //the File-I/O service class
            XAPP_BOOTSTRAP_SETUP_GATEWAY,           //is our firewall
            XAPP_BOOTSTRAP_SETUP_SERVICES,           //setup a logger,
	        XAPP_BOOTSTRAP_SETUP_STORE             //setup a settings store
        ),
	    XApp_Commander_Bootstrap::XAPP_CONF               => array(
            XAPP_CONF_DEBUG_MODE                => null,
            XAPP_CONF_AUTOLOAD                  => false,
            XAPP_CONF_DEV_MODE                  => true,//XApp_Service_Entry_Utils::isDebug(),
            XAPP_CONF_HANDLE_BUFFER             => true,
            XAPP_CONF_HANDLE_SHUTDOWN           => true,
            XAPP_CONF_HTTP_GZIP                 => true,
            XAPP_CONF_CONSOLE                   => false,//XApp_Bootstrap::getConsoleType(),//XApp_Service_Entry_Utils::isDebug() ? self::getConsoleType() : false,
            XAPP_CONF_HANDLE_ERROR              => true,//XApp_Service_Entry_Utils::isDebug() ? 'console' : true,
            XAPP_CONF_HANDLE_EXCEPTION          => true
        ),
        XApp_Commander_Bootstrap::AUTH_DELEGATE           =>  $authDelegate,
        //XApp_Commander_Bootstrap::RPC_TARGET              =>  $XAPP_FILE_SERVICE .'&view=smdCall',
	    XApp_Commander_Bootstrap::RPC_TARGET              =>  $XAPP_PLUGIN_URL.'/index_wordpress_admin.php?view=smdCall',
        XApp_Commander_Bootstrap::SIGNING_KEY             =>  md5($XAPP_USER_NAME),
        XApp_Commander_Bootstrap::SIGNING_TOKEN           =>  md5($authDelegate->getToken()),
        XApp_Commander_Bootstrap::SIGNED_SERVICE_TYPES    =>  array(

            XAPP_SERVICE_TYPE_SMD_CALL,  //client must sign any RPC call
	        XAPP_SERVICE_TYPE_SMD_GET,
	        XAPP_SERVICE_TYPE_DOWNLOAD
        ),
        XApp_Commander_Bootstrap::GATEWAY_CONF            =>  array(

            Xapp_Rpc_Gateway::ALLOW_IP                  => XApp_Wordpress_Parameter_Helper::getGatewayOption($REDUX_OPTIONS,Xapp_Rpc_Gateway::ALLOW_IP),
            Xapp_Rpc_Gateway::DENY_IP                   => XApp_Wordpress_Parameter_Helper::getGatewayOption($REDUX_OPTIONS,Xapp_Rpc_Gateway::DENY_IP),
            Xapp_Rpc_Gateway::ALLOW_HOST                => XApp_Wordpress_Parameter_Helper::getGatewayOption($REDUX_OPTIONS,Xapp_Rpc_Gateway::ALLOW_HOST),
            Xapp_Rpc_Gateway::DENY_HOST                 => XApp_Wordpress_Parameter_Helper::getGatewayOption($REDUX_OPTIONS,Xapp_Rpc_Gateway::DENY_HOST)
        ),/*,
        XApp_Commander_Bootstrap::XFILE_CONF              =>  array(

            Xapp_FileService::REPOSITORY_ROOT   => $xFileRepositoryRoot,                 // the absolute path to your files
            Xapp_FileService::AUTH_DELEGATE     => $authDelegate,                   // needed!
            Xapp_FileService::UPLOAD_EXTENSIONS => $XAPP_UPLOAD_EXTENSIONS          // allowed upload extensions
        ),*/
	    XApp_Commander_Bootstrap::STORE_CONF                   => array(
		    XApp_Store::READER_CLASS            =>'XApp_Store_Delegate',
		    XApp_Store::WRITER_CLASS            =>'XApp_Store_Delegate',
		    XApp_Store::PRIMARY_KEY             =>trim(preg_replace( '/\s+/', '',$XAPP_USER_NAME)),
		    XApp_Store::IDENTIFIER              =>'',
		    XApp_Store::CONF_FILE               =>$XAPP_SETTINGS_FILE
	    ),
	    XApp_Commander_Bootstrap::SERIVCE_CONF             => array(

		    /**
		     * Register file service
		     */
            XApp_Service::factory('XCOM_Directory_Service',array(

				XApp_Directory_Service::AUTH_DELEGATE       => $authDelegate,
	            XApp_Directory_Service::REPOSITORY_ROOT     => $repositoryRoot,
                XApp_Directory_Service::FILE_SYSTEM         => 'XApp_VFS_Local',
                XApp_Directory_Service::VFS_CONFIG_PATH     => $XAPP_VFS_CONFIG_PATH,
		        XApp_Directory_Service::VFS_CONFIG_PASSWORD => $XAPP_VFS_CONFIG_PASSWORD,
                XApp_Directory_Service::FILE_SYSTEM_CONF    => array(

                    XApp_VFS_Base::ABSOLUTE_VARIABLES=>array(
                        'root' => $repositoryRoot,
                        'user' => $authDelegate->getUserName()
                    ),
                    XApp_VFS_Base::RELATIVE_VARIABLES=>array()
                )
            )),
		    /**
		     * Resources service for mount configs
		     */
	        XApp_Service::factory('XApp_Resource_Service',array(
			        XApp_Service::MANAGED_CLASS         =>'XApp_ResourceManager',//rpc auto wrapping class
			        XApp_Service::MANAGED_CLASS_OPTIONS => array(
				        XApp_ResourceManager::STORE_CONF => array(
					        XApp_Store_JSON::CONF_FILE      => $XAPP_VFS_CONFIG_PATH,
					        XApp_Store_JSON::CONF_PASSWORD  => $XAPP_VFS_CONFIG_PASSWORD
				        )
			        )
	        )),
		    /***
		     * XIDE Logging service
		     */
		    XApp_Service::factoryEx('XIDE_Log_Service',array(

			    XApp_Service::MANAGED_CLASS         => 'XIDE_Log_Manager',
			    XApp_Service::MANAGED_CLASS_OPTIONS => array(
				    XIDE_Log_Manager::LOG_PATH=>$XIDE_LOG_PATH
			    )
		    ))
        )
    );
	//create and run bootstrap
    $xappBootrapper = new XApp_Commander_Bootstrap($opt);
	//processes flags, creates instances and runs the server
	$xappBootrapper->setupService();

}
catch(Exception $e)
{
    Xapp_Rpc_Server_Json::dump($e);
	error_log('xapp failed !' . json_encode($e));
}