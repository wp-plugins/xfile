<?php
/**
 * Service entry point, uses Wordpress session manager to get some parameters
 * @author mc007
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
//http://mc007ibi.dyndns.org:81/wordpress/wp-admin/admin-ajax.php?action=xfile-rpc&service=XCOM_Directory_Service.put&callback=nada&mount=%2Froot&dstDir=.%2Ftest%2Ftest
//
//"empty or invalid request object
//error_log('rpc url : ' . admin_url('admin-ajax.php'));
//$fileVars = $_FILES;
//error_log('files : ' . json_encode($fileVars));


/***
 * XAPP PATHS
 */
define('XAPPED', true);
define("XAPP_BASEDIR",$XAPP_ROOT .DIRECTORY_SEPARATOR);
define("XAPP_LIB", XAPP_BASEDIR. DIRECTORY_SEPARATOR . "lib" .DIRECTORY_SEPARATOR);
define("XAPP_ADMIN_SERVICE_ROOT", realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define("XAPP_CONF_DIRECTORY", XAPP_ADMIN_SERVICE_ROOT . '..' . DIRECTORY_SEPARATOR. '..' . DIRECTORY_SEPARATOR. "conf" . DIRECTORY_SEPARATOR);


if(!class_exists('XApp_Service_Entry_Utils')){
    include_once(XAPP_BASEDIR . 'XApp_Service_Entry_Utils.php');//conf data
}
define('XAPP_INDEX',xapp_fix_index());
//error_log('xapp index : ' . XAPP_INDEX);

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

/***
 * XApp includes
*/
/*require_once(XAPP_ADMIN_SERVICE_ROOT . "service_includes.php");*/
//require_once (XAPP_ADMIN_SERVICE_ROOT.'../xfile/service/File.php');
include_once(XAPP_BASEDIR . '/commander/Bootstrap.php');
if(!class_exists('XAppWordpressAuth')){
    include_once(XAPP_LIB . 'wordpress/XAppWordpressAuth.php');
}
include_once(XAPP_LIB . 'wordpress/ParameterHelper.php');//conf data

/***
 * Get wordpress config :
 */
$XAPP_WP_PATH=realpath( $XCOM_ROOT . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..' ) . DIRECTORY_SEPARATOR;//our plugin absolute directory

//load wp core stuff: pretty heavy !
require_once( $XAPP_WP_PATH . 'wp-config.php' );
require_once ($XAPP_WP_PATH . 'wp-load.php');

//get our session
include_once ($XCOM_ROOT . 'class-recursive-arrayaccess.php');
include_once ($XCOM_ROOT . 'class-wp-session.php');
$XAPP_WP_SESSION = XApp_WP_Session::get_instance();
$XAPP_PARAMETERS = null;


//error_log('req  :  '  . $_SERVER['REQUEST_METHOD']  . ' : '   . $_SERVER['REQUEST_URI']);

/**
 * take care about Aviary & Pixrl
 */
if(XApp_Service_Entry_Utils::isPictureService()){

	xapp_import('xapp.Utils.Strings');
	$XAPP_WP_SESSION=array(
		'XAPP_PARAMETERS'=>array('
			XAPP_FILE_START_PATH'=>'/tmp/',
			'XAPP_UPLOAD_EXTENSIONS'=>''
		)
	);
}else{
}
//kill switch for non - authorized requests
if($XAPP_WP_SESSION==null){
	die('have no session');
}
$XAPP_PARAMETERS = $XAPP_WP_SESSION['XAPP_PARAMETERS'];
if($XAPP_PARAMETERS==null){
	die('have session parameters!');
}
/***
 * Now transfer settings
 */
$XAPP_FILE_START_PATH=$XAPP_PARAMETERS['XAPP_FILE_START_PATH'];
$XAPP_UPLOAD_EXTENSIONS=$XAPP_PARAMETERS['XAPP_UPLOAD_EXTENSIONS'];

//Setup Wordpress Auth delegate for the file service
$authDelegate = new XAppWordpressAuth();


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


$XAPP_FILE_SERVICE = admin_url('admin-ajax.php?action=xfile-rpc');

//error_log('pass:  ' . $XAPP_VFS_CONFIG_PASSWORD);

try{
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
            XAPP_BOOTSTRAP_SETUP_SERVICES           //setup a logger

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
        XApp_Commander_Bootstrap::RPC_TARGET              =>  $XAPP_FILE_SERVICE .'&view=smdCall',
        XApp_Commander_Bootstrap::SIGNING_KEY             =>  md5($authDelegate->getUserName()),
        XApp_Commander_Bootstrap::SIGNING_TOKEN           =>  md5($authDelegate->getToken()),
        XApp_Commander_Bootstrap::SIGNED_SERVICE_TYPES    =>  array(

            //XAPP_SERVICE_TYPE_SMD_CALL  //client must sign any RPC call
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
        XApp_Commander_Bootstrap::SERIVCE_CONF             => array(

            XApp_Service::factory('XCOM_Directory_Service',array(

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
	        XApp_Service::factory('XApp_Resource_Service',array(
			        XApp_Service::MANAGED_CLASS         =>'XApp_ResourceManager',//rpc auto wrapping class
			        XApp_Service::MANAGED_CLASS_OPTIONS => array(
				        XApp_ResourceManager::STORE_CONF => array(
					        XApp_Store_JSON::CONF_FILE      => $XAPP_VFS_CONFIG_PATH,
					        XApp_Store_JSON::CONF_PASSWORD  => $XAPP_VFS_CONFIG_PASSWORD
				        )
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