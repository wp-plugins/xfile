<?php
$XAPP_BASE_DIRECTORY =  realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'xapp') . DIRECTORY_SEPARATOR;
$XAPP_LOG_DIRECTORY =  realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'log');
$XAPP_SERVICE_DIRECTORY =  realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'  . DIRECTORY_SEPARATOR  . '..' . DIRECTORY_SEPARATOR .  'server');


$XAPP_WORKSPACE_DIRECTORY = realpath($XAPP_BASE_DIRECTORY . '..' .DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .'/user/';
$XAPP_VFS_CONFIG_PATH = realpath($XAPP_BASE_DIRECTORY . '..' .DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .'maqetta' . DIRECTORY_SEPARATOR .'vfs.json';


/***
 * Framework minimal includes, ignore!
 */
define('XAPP_BASEDIR',$XAPP_BASE_DIRECTORY); //important !

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

/***
 * Build bootstrap config for the RPC service
 */
$opt = array(

    XApp_Bootstrap::BASEDIR                 =>  XAPP_BASEDIR,
    XApp_Bootstrap::FLAGS                   =>  array(

        XAPP_BOOTSTRAP_SETUP_XAPP,              //takes care about output encoding and compressing
        XAPP_BOOTSTRAP_SETUP_RPC,               //setup a RPC server
        //XAPP_BOOTSTRAP_SETUP_LOGGER,            //setup a logger
        XAPP_BOOTSTRAP_SETUP_GATEWAY,           //setup a gateway,
        XAPP_BOOTSTRAP_SETUP_SERVICES           //setup services
    ),
    XApp_Bootstrap::RPC_TARGET              =>  '../server/xide/service.php?view=smdCall',
    XApp_Bootstrap::SIGNED_SERVICE_TYPES    =>  array(

    ),
    XApp_Bootstrap::GATEWAY_CONF            =>  array(
        Xapp_Rpc_Gateway::OMIT_ERROR        => true
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

        XApp_Service::ServiceItem('xapp.Directory.Service',array(

            XApp_Directory_Service::REPOSITORY_ROOT     => $XAPP_WORKSPACE_DIRECTORY,
            XApp_Directory_Service::FILE_SYSTEM         => 'XApp_VFS_Local',
            XApp_Directory_Service::VFS_CONFIG_PATH     => $XAPP_VFS_CONFIG_PATH,
            XApp_Directory_Service::FILE_SYSTEM_CONF    => array(

                XApp_VFS_Base::ABSOLUTE_VARIABLES=>array('WS_ABS_PATH' => $XAPP_WORKSPACE_DIRECTORY),
                XApp_VFS_Base::RELATIVE_VARIABLES=>array()
            )/*
            /*XApp_Directory_Service::DEFAULT_NODE_FIELDS =>(XAPP_XFILE_SHOW_MIME|XAPP_XFILE_SHOW_SIZE
                |XAPP_XFILE_SHOW_PERMISSIONS|XAPP_XFILE_SHOW_ISREADONLY|XAPP_XFILE_SHOW_ISDIR)*/
        ))
    )
);

$xappBootrapper = new XApp_Bootstrap($opt);

$xappBootrapper->init();

$xappBootrapper->run();

