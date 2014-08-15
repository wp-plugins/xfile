<?php
/**
 * @version 1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * Return the current cache expire setting.
 *
 * @return int
 */
function xapp_wp_session_cache_expire() {
	$wp_session = XApp_WP_Session::get_instance();

	return $wp_session->cache_expiration();
}

/**
 * Alias of xapp_wp_session_write_close()
 */
function xapp_wp_session_commit() {
	xapp_wp_session_write_close();
}

/**
 * Load a JSON-encoded string into the current session.
 *
 * @param string $data
 */
function xapp_wp_session_decode( $data ) {
	$wp_session = XApp_WP_Session::get_instance();

	return $wp_session->json_in( $data );
}

/**
 * Encode the current session's data as a JSON string.
 *
 * @return string
 */
function xapp_wp_session_encode() {
	$wp_session = XApp_WP_Session::get_instance();

	return $wp_session->json_out();
}

/**
 * Regenerate the session ID.
 *
 * @param bool $delete_old_session
 *
 * @return bool
 */
function xapp_wp_session_regenerate_id( $delete_old_session = false ) {
	$wp_session = XApp_WP_Session::get_instance();

	$wp_session->regenerate_id( $delete_old_session );

	return true;
}

/**
 * Start new or resume existing session.
 *
 * Resumes an existing session based on a value sent by the _wp_session cookie.
 *
 * @return bool
 */
function xapp_wp_session_start() {
	$wp_session = XApp_WP_Session::get_instance();
	do_action( 'xapp_wp_session_start' );

	return $wp_session->session_started();
}
add_action( 'plugins_loaded', 'xapp_wp_session_start' );

/**
 * Return the current session status.
 *
 * @return int
 */
function xapp_wp_session_status() {
	$wp_session = XApp_WP_Session::get_instance();

	if ( $wp_session->session_started() ) {
		return PHP_SESSION_ACTIVE;
	}

	return PHP_SESSION_NONE;
}

/**
 * Unset all session variables.
 */
function xapp_wp_session_unset() {
	$wp_session = XApp_WP_Session::get_instance();

	$wp_session->reset();
}

/**
 * Write session data and end session
 */
function xapp_wp_session_write_close() {
	$wp_session = XApp_WP_Session::get_instance();

	$wp_session->write_data();
	do_action( 'xapp_wp_session_commit' );
}
add_action( 'shutdown', 'xapp_wp_session_write_close' );

/**
 * Clean up expired sessions by removing data and their expiration entries from
 * the WordPress options table.
 *
 * This method should never be called directly and should instead be triggered as part
 * of a scheduled task or cron job.
 */
function xapp_wp_session_cleanup() {
	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( ! defined( 'WP_INSTALLING' ) ) {
		$expiration_keys = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_wp_session_expires_%'" );

		$now = time();
		$expired_sessions = array();

		foreach( $expiration_keys as $expiration ) {
			// If the session has expired
			if ( $now > intval( $expiration->option_value ) ) {
				// Get the session ID by parsing the option_name
				$session_id = substr( $expiration->option_name, 20 );

				$expired_sessions[] = $expiration->option_name;
				$expired_sessions[] = "_wp_session_$session_id";
			}
		}

		// Delete all expired sessions in a single query
		if ( ! empty( $expired_sessions ) ) {
			$option_names = implode( "','", $expired_sessions );
			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')" );
		}
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action( 'xapp_wp_session_cleanup' );
}
add_action( 'wp_session_garbage_collection', 'xapp_wp_session_cleanup' );

/**
 * Register the garbage collector as a twice daily event.
 */
function xapp_wp_session_register_garbage_collection() {
	if ( ! wp_next_scheduled( 'xapp_wp_session_garbage_collection' ) ) {
		wp_schedule_event( time(), 'hourly', 'wp_session_garbage_collection' );
	}
}
add_action( 'wp', 'xapp_wp_session_register_garbage_collection' );
