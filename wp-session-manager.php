<?php
/**
 * @version 1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @Author:      Eric Mann
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
// let users change the session cookie name
if( ! defined( 'WP_SESSION_COOKIE' ) )
	define( 'WP_SESSION_COOKIE', '_wp_session' );

if ( ! class_exists('XApp_WPS_Recursive_ArrayAccess') ) {
	require_once( 'class-recursive-arrayaccess.php' );
}

// Only include the functionality if it's not pre-defined.
if ( ! class_exists('XApp_WP_Session') ) {
	require_once( 'class-wp-session.php' );
	require_once( 'wp-session.php' );
}
