<?php
include_once('../Exception.php');
include_once('Client.php');
include_once('Exception.php');
include_once('Result.php');
include_once('../Shell/Client.php');
include_once('../Shell/Exception.php');
$client = new XApp_Svn_Client(false,false,array(),'svn');
/*$res = $client->update('/htdocs/wordpress/wp-content/plugins/xcom/client');*/
/*error_log('res : '  . $res);*/
$res = $client->info(array('/htdocs/wordpress/wp-content/plugins/xcom/client'));
/*error_log('res : '  . $res);*/


