<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
define('XFILE_ROOTPATH', plugins_url('', __FILE__));
define('XFILE_ROOTDIR', __DIR__);

/**
 * Admin_Editor class.
 *
 * @since 2.0
 */
class xcom_Admin_Editor {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action('admin_head', array( $this, 'add_shortcode_button' ) );
		add_action('wp_ajax_xfile-getpopup', array(&$this, 'UseyourDrive_GetPopup'));
		///wordpress/wp-admin/admin-ajax.php?action=xfile-getpopup&type=imagePicker
		//UseyourDrive_GetPopup


		add_filter( 'tiny_mce_version', array( $this, 'refresh_mce' ) );
		add_filter( 'mce_external_languages', array( $this, 'add_tinymce_lang' ), 10, 1 );
	}

	public function UseyourDrive_GetPopup() {
		include XFILE_ROOTDIR . '/xcom_Popup.php';
		die();
	}

	/**
	 * Add a button for shortcodes to the WP editor.
	 */
	public function add_shortcode_button() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', array( $this, 'add_shortcode_tinymce_plugin' ) );
			add_filter( 'mce_buttons', array( $this, 'register_shortcode_button' ) );
			/* Add a callback to add our button to the TinyMCE toolbar */

		}
	}

	/**
	 * woocommerce_add_tinymce_lang function.
	 *
	 * @param array $arr
	 * @return array
	 */
	public function add_tinymce_lang( $arr ) {
		$XAPP_PLUGIN_URL = plugins_url('',__FILE__);
	    $arr['wc_shortcodes_button'] = $XAPP_PLUGIN_URL . '/assets/js/admin/editor_plugin_lang.php';
	    return $arr;
	}

	/**
	 * Register the shortcode button.
	 *
	 * @param array $buttons
	 * @return array
	 */
	public function register_shortcode_button( $buttons ) {
		//array_push( $buttons, '|', 'wc_shortcodes_button' );

		/*register_shortcode_button
		return $buttons;
		*/
		/* Add the button ID to the $button array */
		$buttons[] = "xfile";
		return $buttons;
	}

	/**
	 * Add the shortcode button to TinyMCE
	 *
	 * @param array $plugin_array
	 * @return array
	 */
	public function add_shortcode_tinymce_plugin( $plugin_array ) {

		$XAPP_PLUGIN_URL = plugins_url('',__FILE__);
		$wp_version = get_bloginfo( 'version' );
		$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$suffix = '';

		if ( version_compare( $wp_version, '3.9', '>=' ) ) {
			$plugin_array['wc_shortcodes_button'] = $XAPP_PLUGIN_URL . '/assets/js/admin/editor_plugin' . $suffix . '.js';
			$plugin_array['xfile'] = $XAPP_PLUGIN_URL . '/assets/js/admin/editor_plugin' . $suffix . '.js';
			//error_log('adding ' . $XAPP_PLUGIN_URL . '/assets/js/admin/editor_plugin' . $suffix . '.js');
		} else {
			$plugin_array['wc_shortcodes_button'] = $XAPP_PLUGIN_URL . '/assets/js/admin/editor_plugin_legacy' . $suffix . '.js';
		}

		return $plugin_array;
	}

	/**
	 * Force TinyMCE to refresh.
	 *
	 * @param int $ver
	 * @return int
	 */
	public function refresh_mce( $ver ) {
		$ver += 3;
		return $ver;
	}
}
//new xcom_Admin_Editor();

