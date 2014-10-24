<?php
/*
   Plugin Name: xfile
   Plugin URI: http://wordpress.org/plugins/xfile/
   Version: 1.0
   Author: xfile
   Description: simple file manger
   Author URI: http://www.xappcommander.com
   License: GPLv2
*/

$xcom_minimalRequiredPhpVersion = '5.3';
/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function xcom_noticePhpVersionWrong() {
    global $xcom_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
        __('Error: plugin "xfile" requires a newer version of PHP to be running.',  'xcom').
        '<br/>' . __('Minimal version of PHP required: ', 'xfile') . '<strong>' . $xcom_minimalRequiredPhpVersion . '</strong>' .
        '<br/>' . __('Your server\'s PHP version: ', 'xfile') . '<strong>' . phpversion() . '</strong>' .
        '</div>';
}


function xcom_PhpVersionCheck() {
    global $xcom_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $xcom_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'xcom_noticePhpVersionWrong');
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function xcom_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('xfile', false, $pluginDir . '/languages/');
}

// First initialize i18n
xcom_i18n_init();

// Next, run the version check.
// If it is successful, continue with initialization for this plugin
if (xcom_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('xcom_init.php');
    xcom_init(__FILE__);
}
if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}

function renderXCOMGUI(){
    include(realpath(dirname(__FILE__).'/xcom_Renderer.php'));
}
function renderXCOMGUI_HEAD(){
    include(realpath(dirname(__FILE__).'/xcom_Head_Renderer.php'));
}
function renderRPC(){
	include(realpath(dirname(__FILE__).'/server/service/index_wordpress_admin.php'));
	die();
}

function xcom_admin_menu() {
    add_menu_page('XFile', 'Files', 'administrator', 'xfile','renderXCOMGUI');
}

add_action('admin_menu', 'xcom_admin_menu');
add_action('admin_head', 'renderXCOMGUI_HEAD');
add_action('wp_ajax_xfile-rpc', 'renderRPC');//http://localhost:81/wordpress/wp-admin/admin-ajax.php?action=xfile-rpc


if(file_exists(realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'xcom_Admin_Editor.php')){
	include_once realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'xcom_Admin_Editor.php';
}
include_once realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR. 'class-recursive-arrayaccess.php';
include_once realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR. 'class-wp-session.php';
include_once realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR. 'wp-session.php';
