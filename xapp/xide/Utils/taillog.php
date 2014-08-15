<?php
$logname=$_REQUEST['log'];

/*
if(!ereg('/var/log/apache2/',$logname) || !is_readable($logname)){
        die('<pre style="background:black;color:white;">Access denied to '.$logname.'</pre>');
}
*/
/**
 * Require the library
 */
require 'PHPTail.php';
/**
 * Initilize a new instance of PHPTail
 * @var PHPTail
 */
$tail = new PHPTail('/var/log/apache2/error.log');

/**
 * We're getting an AJAX call
 */
if(isset($_GET['ajax']))  {
	echo $tail->getNewLines($_GET['lastsize'], $_GET['grep'], $_GET['invert']);
	die();
}
/**
 * Regular GET/POST call, print out the GUI
 */
$tail->generateGUI();
?>
