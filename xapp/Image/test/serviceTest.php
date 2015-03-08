<?php
$XAPP_BASE_DIRECTORY =  realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
//exit;
define('XAPP_BASEDIR',$XAPP_BASE_DIRECTORY); //important !

/***
 * A log directory, must be writable !!
 */
$XAPP_LOG_DIRECTORY =  realpath(XAPP_BASEDIR . '..' . DIRECTORY_SEPARATOR . 'log');
/***
 * Dojo's JSON-RPC classes need an url to the entry here :
 */
$XAPP_SMD_TARGET = '../xapp/xide/Service/entry.php?view=smdCall';

/***
 * Framework minimal includes, ignore!
 */
require_once(XAPP_BASEDIR . '/Bootstrap.php');

XApp_Bootstrap::loadMin();
XApp_Bootstrap::loadRPC();
xapp_setup_language_standalone();

xapp_import('xapp.Service');
xapp_import('xapp.File.Utils');
xapp_import('xapp.Directory.Utils');
xapp_import('xapp.Directory.Service');
xapp_import('xapp.Path.Utils');
xapp_import('xapp.VFS.Interface.Access');
xapp_import('xapp.VFS.Base');
xapp_import('xapp.VFS.Local');
xapp_import('xapp.Resource.Renderer');
xapp_import('xapp.Xapp.Hook');
xapp_import('xapp.Option.*');
xapp_import("xapp.xide.Models.User");
xapp_import('xapp.xide.Controller.UserManager');
xapp_import('xapp.xide.Controller.UserService');
xapp_import('xapp.xide.Workbench.Service');
xapp_import('xapp.Store.Json.Json');
xapp_import('xapp.Image.Service');


/***
 * Bootstrap config
 */

/***
 * JSON-P SMD : http://localhost/xide/code/php/xide-php/xapp/Image/test/entry.php?callback=tre
 * JSON-RPP SMD : http://localhost/xide/code/php/xide-php/xapp/Image/test/entry.php
 *
 * JSON-P-Call : http://localhost/xide/code/php/xide-php/xapp/Image/test/serviceTest.php?service=XApp_Image_Service.test
 * RAW call : http://localhost/xide/code/php/xide-php/xapp/Image/test/serviceTest.php?service=XApp_Image_Service.test&callback=tre&raw=html
 *
 * RAW call image resize
 * http://localhost/xide/code/php/xide-php/xapp/Image/test/serviceTest.php?service=XApp_Image_Service.resize&callback=tre&raw=html&src=http://www.x4mm.net/current/xide-php/xapp/tests/data/Images/adidas.gif&width=50
 * http://localhost/xide/code/php/xide-php/xapp/Image/test/serviceTest.php?service=XApp_Image_Service.resize&callback=tre&raw=html&src=http://www.x4mm.net/current/xide-php/xapp/tests/data/Images/adidas.gif&width=50
 * http://localhost:81/xapp-commander-standalone/docroot/xapp/Image/test/serviceTest.php?service=XApp_Image_Service.resize&callback=tre&raw=html&src=http://www.x4mm.net/current/xide-php/xapp/tests/data/Images/adidas.gif&width=50
 */

//require_once(XAPP_BASEDIR . '/classmixer/ClassMixer.php');

xapp_import('xapp.Commons.ClassMixer');
xapp_import('xapp.Commons.VariableMixin');
xapp_import('xapp.xide.NodeJS.Service');
xapp_import('xapp.xide.NodeJS.ServiceManager');

class LawyerMixin {
    function greet($name) {
        return "I'm going to sue $name!";
    }

    static function kind() {
        return "Lawyer";
    }

    public function testop(){
        echo('test op');
    }
}
/*
XApp_ClassMixer::create_mixed_class('XApp_NodeJS_ServiceManager2', 'XIDE_NodeJS_Service_Manager', array('XApp_Variable_Mixin'));
//XApp_ClassMixer::create_mixed_class('XImageService', 'XApp_Image_Service', array('LawyerMixin'));
$l = new XApp_NodeJS_ServiceManager2();
$class = new ReflectionClass('XApp_NodeJS_ServiceManager2');
if($method !== null)
{
    $methods = array($class->getMethod($method));
}else{
    $methods = $class->getMethods();
}*/

/*xapp_dump($methods);*/
//exit;
//xapp_dump($l);
//exit;

$opt = array(

    XApp_Bootstrap::BASEDIR                 =>  XAPP_BASEDIR,
    XApp_Bootstrap::FLAGS                   =>  array(

        XAPP_BOOTSTRAP_SETUP_XAPP,              //takes care about output encoding and compressing
        XAPP_BOOTSTRAP_SETUP_RPC,               //setup a RPC server
        //XAPP_BOOTSTRAP_SETUP_LOGGER,            //setup a logger
        XAPP_BOOTSTRAP_SETUP_GATEWAY,           //setup a gateway,
        XAPP_BOOTSTRAP_SETUP_SERVICES           //setup services
    ),
    XApp_Bootstrap::RPC_TARGET              =>  $XAPP_SMD_TARGET,
    XApp_Bootstrap::SIGNED_SERVICE_TYPES    =>  array(

    ),
    XApp_Bootstrap::GATEWAY_CONF            =>  array(
        Xapp_Rpc_Gateway::OMIT_ERROR        => XApp_Service_Entry_Utils::isDebug()
    ),
    XApp_Bootstrap::LOGGING_FLAGS           =>  array(
    ),
    XApp_Bootstrap::LOGGING_CONF            =>  array(
        Xapp_Log::PATH                      => $XAPP_LOG_DIRECTORY,
        Xapp_Log::EXTENSION                 => 'log',
        Xapp_Log::NAME                      => 'xide'
    ),
    XApp_Bootstrap::XAPP_CONF               => array(
        XAPP_CONF_DEBUG_MODE                => null,
        XAPP_CONF_AUTOLOAD                  => false,
        XAPP_CONF_DEV_MODE                  => XApp_Service_Entry_Utils::isDebug(),
        XAPP_CONF_HANDLE_BUFFER             => true,
        XAPP_CONF_HANDLE_SHUTDOWN           => false,
        XAPP_CONF_HTTP_GZIP                 => true,
        XAPP_CONF_CONSOLE                   => false,
        XAPP_CONF_HANDLE_ERROR              => true,
        XAPP_CONF_HANDLE_EXCEPTION          => true
    ),
    XApp_Bootstrap::SERIVCE_CONF             => array(

        XApp_Service::factoryEx('XApp_Image_Service',array(
            XApp_Service::MANAGED_CLASS_OPTIONS => array()
        ))/*
        XApp_Service::factoryEx('XIDE_NodeJS_Service',array(
            XApp_Service::MANAGED_CLASS         =>'XApp_NodeJS_ServiceManager',//rpc auto wrapping
        ))*/
    )
);

$xappBootrapper = new XApp_Bootstrap($opt);
$xappBootrapper->init();

$imageService = XApp_Image_Service::instance();

$xappBootrapper->run();



