<?php
if (!function_exists('redux_init')) :
    function redux_init() {

        /**

        Most of your editing will be done in this section.

        Here you can override default values, uncomment args and change their values.
        No $args are required, but they can be overridden if needed.

         **/
        $args = array();


        // For use with a tab example below
        $tabs = array();

        ob_start();

        $ct = wp_get_theme();
        $theme_data = $ct;
        $item_name = $theme_data->get('Name');
        $tags = $ct->Tags;
        $screenshot = $ct->get_screenshot();
        $class = $screenshot ? 'has-screenshot' : '';

        $customize_title = sprintf( __( 'Customize &#8220;%s&#8221;','redux-framework-demo' ), $ct->display('Name') );

        ?>

        <?php
        $item_info = ob_get_contents();

        ob_end_clean();

        $sampleHTML = '';
        if( file_exists( dirname(__FILE__).'/info-html.html' )) {
            /** @global WP_Filesystem_Direct $wp_filesystem  */
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once(ABSPATH .'/wp-admin/includes/file.php');
                WP_Filesystem();
            }
            /*$sampleHTML = $wp_filesystem->get_contents(dirname(__FILE__).'/info-html.html');*/
        }

        // BEGIN Sample Config

        // Setting dev mode to true allows you to view the class settings/info in the panel.
        // Default: true
        $args['dev_mode'] = false;

        // Set the icon for the dev mode tab.
        // If $args['icon_type'] = 'image', this should be the path to the icon.
        // If $args['icon_type'] = 'iconfont', this should be the icon name.
        // Default: info-sign
        $args['dev_mode_icon'] = 'info-sign';

        // Set the class for the dev mode tab icon.
        // This is ignored unless $args['icon_type'] = 'iconfont'
        // Default: null
        //$args['dev_mode_icon_class'] = '';

        // Set a custom option name. Don't forget to replace spaces with underscores!
        $args['opt_name'] = 'xcommanderOptions';

        // Setting system info to true allows you to view info useful for debugging.
        // Default: false
        $args['system_info'] = false;


        // Set the icon for the system info tab.
        // If $args['icon_type'] = 'image', this should be the path to the icon.
        // If $args['icon_type'] = 'iconfont', this should be the icon name.
        // Default: info-sign
        $args['system_info_icon'] = 'info-sign';

        // Set the class for the system info tab icon.
        // This is ignored unless $args['icon_type'] = 'iconfont'
        // Default: null
        $args['system_info_icon_class'] = 'icon-large';

        $theme = wp_get_theme();

        $args['display_name'] = 'XFile Settings';//$theme->get('Name');
        //$args['database'] = "theme_mods_expanded";
        $args['display_version'] = $theme->get('Version');

        // If you want to use Google Webfonts, you MUST define the api key.
        $args['google_api_key'] = 'AIzaSyAX_2L_UzCDPEnAHTG7zhESRVpMPS4ssII';

        // Define the starting tab for the option panel.
        // Default: '0';
        //$args['last_tab'] = '0';

        // Define the option panel stylesheet. Options are 'standard', 'custom', and 'none'
        // If only minor tweaks are needed, set to 'custom' and override the necessary styles through the included custom.css stylesheet.
        // If replacing the stylesheet, set to 'none' and don't forget to enqueue another stylesheet!
        // Default: 'standard'
        //$args['admin_stylesheet'] = 'standard';

        // Setup custom links in the footer for share icons
        /*
        $args['share_icons']['twitter'] = array(
            'link' => 'http://twitter.com/ghost1227',
            'title' => 'Follow me on Twitter',
            'img' => ReduxFramework::$_url . 'assets/img/social/Twitter.png'
        );
        $args['share_icons']['linked_in'] = array(
            'link' => 'http://www.linkedin.com/profile/view?id=52559281',
            'title' => 'Find me on LinkedIn',
            'img' => ReduxFramework::$_url . 'assets/img/social/LinkedIn.png'
        );
        */

        // Enable the import/export feature.
        // Default: true
        //$args['show_import_export'] = false;

        // Set the icon for the import/export tab.
        // If $args['icon_type'] = 'image', this should be the path to the icon.
        // If $args['icon_type'] = 'iconfont', this should be the icon name.
        // Default: refresh
        //$args['import_icon'] = 'refresh';

        // Set the class for the import/export tab icon.
        // This is ignored unless $args['icon_type'] = 'iconfont'
        // Default: null
        //$args['import_icon_class'] = '';

        /**
         * Set default icon class for all sections and tabs
         * @since 3.0.9
         */
        //$args['default_icon_class'] = '';


        // Set a custom menu icon.
        //$args['menu_icon'] = '';

        // Set a custom title for the options page.
        // Default: Options
        $args['menu_title'] = __('XFile', 'redux-framework-demo');

        // Set a custom page title for the options page.
        // Default: Options
        $args['page_title'] = __('XFile Options', 'redux-framework-demo');

        // Set a custom page slug for options page (wp-admin/themes.php?page=***).
        // Default: redux_options
        $args['page_slug'] = 'xcommander_options';

        $args['default_show'] = false;
        $args['default_mark'] = '*';

        // Set a custom page capability.
        // Default: manage_options
        //$args['page_cap'] = 'manage_options';

        // Set the menu type. Set to "menu" for a top level menu, or "submenu" to add below an existing item.
        // Default: menu
        $args['page_type'] = 'submenu';

        // Set the parent menu.
        // Default: themes.php
        // A list of available parent menus is available at http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
        $args['page_parent'] = 'options-general.php';


        // Set a custom page location. This allows you to place your menu where you want in the menu order.
        // Must be unique or it will override other items!
        // Default: null
        //$args['page_position'] = null;

        // Set a custom page icon class (used to override the page icon next to heading)
        //$args['page_icon'] = 'icon-themes';

        // Set the icon type. Set to "iconfont" for Elusive Icon, or "image" for traditional.
        // Redux no longer ships with standard icons!
        // Default: iconfont
        //$args['icon_type'] = 'image';

        // Disable the panel sections showing as submenu items.
        // Default: true
        //$args['allow_sub_menu'] = false;

        // Set ANY custom page help tabs, displayed using the new help tab API. Tabs are shown in order of definition.

        $controlsStr = '<h2>Keyboard </h2>';
        $controlsStr .='<ul>'.
                '<li><strong>F2</strong> : Rename</li>'.
                '<li><strong>CTRL/CMD + ENTER</strong> : Open selection in main window</li>'.
                '<li><strong>F5</strong> : Copy (If main window is open, the destination is set automatically)</li>'.
                '<li><strong>F6</strong> : Move</li>'.
                '<li><strong>F7</strong> : Delete</li>'.
                '<li><strong>BACKSPACE</strong> (Firefox) : Go back in history</li>'.
                '<li><strong>SHIFT + BACKSPACE</strong> (Chrome) : Go back in history</li>'.
                '<li><strong>DEL</strong> : Delete selection</li>'.
                '<li><strong>CTRL+W</strong> (Firefox) : Close last window</li>'.
                '<li><strong>SHIFT+W</strong> (Chrome) : Close last window</li>'.
                '<li><strong>SHIFT+UP/DOWN</strong> : Multi-Selection</li>'.
                '<li><strong>CTRL+A</strong> : Select all</li>'.
                '<li><strong>CTRL+C</strong> : Copy selection to clipboard</li>'.
                '<li><strong>CTRL+X</strong> : Cut selection to clipboard</li>'.
                '<li><strong>CTRL+V</strong> : Paste selection</li>'.
            '</ul>'.

            '<h2>Drag n Drop </h2>'.
            '<ul>'.
                '<li>CTRL                : Enable copy modus</li>'.
            '</ul>'.

            '<h2>Mouse </h2>'.
            '<ul>'.
                '<li>Right-Click         : Open context menu</li>'.
            '</ul>'.

            '<h2>How to Upload</h2>'.
            '<ul>'.
                '<li>Simply drag files from your file manager into the file panel</li>'.
                '<li>Only files are allowed! Not folders!</li>'.
            '</ul>';

        $args['help_tabs'][] = array(
            'id' => 'redux-opts-1',
            'title' => __('Keyboard & Mouse controls', 'redux-framework-demo'),
            'content' => __('<p>Controls</p>'.$controlsStr, 'redux-framework-demo')
        );
        // Add HTML before the form.
        if (!isset($args['global_variable']) || $args['global_variable'] !== false ) {
            if (!empty($args['global_variable'])) {
                $v = $args['global_variable'];
            } else {
                $v = str_replace("-", "_", $args['opt_name']);
            }

        } else {

        }

        $args['footer_credit']='';
        $sections = array();

        //Background Patterns Reader
        $sample_patterns_path = ReduxFramework::$_dir . '../sample/patterns/';
        $sample_patterns_url  = ReduxFramework::$_url . '../sample/patterns/';
        $sample_patterns      = array();

        if ( is_dir( $sample_patterns_path ) ) :

            if ( $sample_patterns_dir = opendir( $sample_patterns_path ) ) :
                $sample_patterns = array();

                while ( ( $sample_patterns_file = readdir( $sample_patterns_dir ) ) !== false ) {

                    if( stristr( $sample_patterns_file, '.png' ) !== false || stristr( $sample_patterns_file, '.jpg' ) !== false ) {
                        $name = explode(".", $sample_patterns_file);
                        $name = str_replace('.'.end($name), '', $sample_patterns_file);
                        $sample_patterns[] = array( 'alt'=>$name,'img' => $sample_patterns_url . $sample_patterns_file );
                    }
                }
            endif;
        endif;

        $sections[] = array(
            'icon' => 'el-icon-cogs',
            'title' => __('General Settings', 'redux-framework-demo'),
            'fields' => array(
                array(
                    'id'=>'PATH',
                    'type' => 'text',
                    'title' => __('Start path', 'redux-framework-demo'),
                    'desc' => __('Set the relative path to your Wordpress installation. You can use also : wp-content/%user%/ to have user folders!', 'redux-framework-demo'),
                    'msg' => 'custom error message',
                    'default' => ''
                ),
                array(
                    'id'=>'UPLOADEXTENSIONS',
                    'type' => 'textarea',
                    'title' => __('Allowed upload extensions', 'redux-framework-demo'),
                    'desc' => __('Place here a comma separated list of file extensions', 'redux-framework-demo'),
                    'default' => 'js,css,less,bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS'
                )
            )
        );

        $sections[] = array(
            'icon' => 'el-icon-th-large',
            'title' => __('Visual Settings', 'redux-framework-demo'),
            'fields' => array(
                array(
                    'id'=>'LAYOUTPRESET',
                    'type' => 'image_select',
                    'compiler'=>true,
                    'title' => __('Main Layout', 'redux-framework-demo'),
                    'subtitle' => __('Select main content and sidebar alignment. Choose between dual and single layout', 'redux-framework-demo'),
                    'options' => array(
                        '1' => array('alt' => '2 Column Left', 'img' => ReduxFramework::$_url.'assets/img/2cl.png'),
                        '2' => array('alt' => '1 Column', 'img' => ReduxFramework::$_url.'assets/img/1col.png')
                    ),
                    'default' => '1'
                ),
                array(
                    'id'=>'PANELOPTIONS',
                    'type' => 'checkbox',
                    'title' => __('Allowed Panels', 'redux-framework-demo'),
                    'options' => array(
                        '0' => 'Allow new tabs',
                        '1' => 'Allow info view',
                        '2' => 'Allow breadcrumbs',
                        '3' => 'Allow log view (upload progress)',
                        '4' => 'Allow context menu',
                        '5' => 'Allow source selector',
                        '6' => 'Allow layout selector'),
                    'default' => array(
                        '0' => '1',
                        '1' => '1',
                        '2' => '1',
                        '3' => '1')//See how std has changed? you also don't need to specify opts that are 0.
                ),
                array(
                    'id'=>'JQTHEME',
                    'type' => 'select',
                    'title' => __('Select a jQuery Theme', 'redux-framework-demo'),
                    'options' => array(
	                    'black-tie'=>'black-tie',
                        'cupertino'=>'cupertino',
                        'dot-luv'=>'dot-luv',
                        'excite-bike'=>'excite-bike',
                        'hot-sneaks'=>'hot-sneaks',
                        'le-frog'=>'le-frog',
                        'overcast'=>'redmond',
                        'south-street'=>'south-street',
                        'sunny'=>'trontastic',
                        'ui-lightness'=>'ui-lightness',
                        'blitzer'=>'dark-hive',
                        'eggplant'=>'eggplant',
                        'flick'=>'flick',
                        'humanity'=>'humanity',
                        'mint-choc'=>'mint-choc',
                        'pepper-grinder'=>'pepper-grinder',
                        'smoothness'=>'smoothness',
                        'start'=>'start',
                        'swanky-purse'=>'swanky-purse',
                        'ui-darkness'=>'ui-darkness',
                        'vader'=>'ui-vader'),
                    'default' => 'dot-luv'
                )
            )
        );

        /****
         *
         */



        /****
            Permissions
         */

        $XCOM_ACTIONS = array(
            XC_OPERATION_NONE=>__('None', 'redux-framework-demo'),
            XC_OPERATION_EDIT=>__('Edit', 'redux-framework-demo'),
            XC_OPERATION_COPY=>__('Copy', 'redux-framework-demo'),
            XC_OPERATION_MOVE=>__('Move', 'redux-framework-demo'),
            XC_OPERATION_INFO=>__('Info', 'redux-framework-demo'),
            XC_OPERATION_DOWNLOAD=>__('Download', 'redux-framework-demo'),
            XC_OPERATION_COMPRESS=>__('Compress', 'redux-framework-demo'),
            XC_OPERATION_DELETE=>__('Delete', 'redux-framework-demo'),
            XC_OPERATION_RENAME=>__('Rename', 'redux-framework-demo'),
            XC_OPERATION_DND=>__('Drag and Drop', 'redux-framework-demo'),
            XC_OPERATION_COPY_PASTE=>__('Copy & Paste', 'redux-framework-demo'),
            XC_OPERATION_OPEN=>__('Open', 'redux-framework-demo'),
            XC_OPERATION_RELOAD=>__('Reload', 'redux-framework-demo'),
            XC_OPERATION_NEW_FILE=>__('New File', 'redux-framework-demo'),
            XC_OPERATION_NEW_DIRECTORY=>__('New Directory', 'redux-framework-demo'),
            XC_OPERATION_PLUGINS=>__('Plugins', 'redux-framework-demo'),
            XC_OPERATION_READ=>__('Read', 'redux-framework-demo'),
            XC_OPERATION_WRITE=>__('Write', 'redux-framework-demo'),
            XC_OPERATION_PLUGINS=>__('Plugins', 'redux-framework-demo'),
            XC_OPERATION_UPLOAD=>__('Upload', 'redux-framework-demo'),
	        XC_OPERATION_ADD_MOUNT=>__('Add Mount', 'redux-framework-demo'),
	        XC_OPERATION_REMOVE_MOUNT=>__('Remove Mount', 'redux-framework-demo'),
	        XC_OPERATION_EDIT_MOUNT=>__('Edit Mount', 'redux-framework-demo')
        );


        $sections[] = array(
            'icon' => 'el-icon-wrench',
            'title' => __('Permissions', 'redux-framework-demo'),
            'fields' => array(
                array(
                    'id'=>'XAPP-DEFAULT-ACTIONS',
                    'type' => 'select',
                    'multi'=>true,
                    'title' => __('Set global default actions', 'redux-framework-demo'),
                    'options' =>$XCOM_ACTIONS,
                    'default' => array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27')
                ),
                array(
                    'id'=>'RPC_GATEWAY_ALLOW_IP',
                    'type' => 'textarea',
                    'title' => __('Allowed IP addresses', 'redux-framework-demo'),
                    'desc' => __('Place here a comma separated list of IP adresses', 'redux-framework-demo'),
                    'default' => ''
                ),
                array(
                    'id'=>'RPC_GATEWAY_DENY_IP',
                    'type' => 'textarea',
                    'title' => __('Denied IP addresses', 'redux-framework-demo'),
                    'desc' => __('Place here a comma separated list of IP adresses', 'redux-framework-demo'),
                    'default' => ''
                ),
                array(
                    'id'=>'RPC_GATEWAY_ALLOW_HOST',
                    'type' => 'textarea',
                    'title' => __('Allowed host names', 'redux-framework-demo'),
                    'desc' => __('Place here a comma separated list of host names', 'redux-framework-demo'),
                    'default' => ''
                ),
                array(
                    'id'=>'RPC_GATEWAY_DENY_HOST',
                    'type' => 'textarea',
                    'title' => __('Denied host names', 'redux-framework-demo'),
                    'desc' => __('Place here a comma separated list of host names', 'redux-framework-demo'),
                    'default' => ''
                )

            )
        );


        if (function_exists('wp_get_theme')){
            $theme_data = wp_get_theme();
            $theme_uri = $theme_data->get('ThemeURI');
            $description = $theme_data->get('Description');
            $author = $theme_data->get('Author');
            $version = $theme_data->get('Version');
            $tags = $theme_data->get('Tags');
        }else{
            $theme_data = wp_get_theme(trailingslashit(get_stylesheet_directory()).'style.css');
            $theme_uri = $theme_data['URI'];
            $description = $theme_data['Description'];
            $author = $theme_data['Author'];
            $version = $theme_data['Version'];
            $tags = $theme_data['Tags'];
        }

        $theme_info = '<div class="redux-framework-section-desc">';
        $theme_info .= '<p class="redux-framework-theme-data description theme-uri">'.__('<strong>Theme URL:</strong> ', 'redux-framework-demo').'<a href="'.$theme_uri.'" target="_blank">'.$theme_uri.'</a></p>';
        $theme_info .= '<p class="redux-framework-theme-data description theme-author">'.__('<strong>Author:</strong> ', 'redux-framework-demo').$author.'</p>';
        $theme_info .= '<p class="redux-framework-theme-data description theme-version">'.__('<strong>Version:</strong> ', 'redux-framework-demo').$version.'</p>';
        $theme_info .= '<p class="redux-framework-theme-data description theme-description">'.$description.'</p>';
        if ( !empty( $tags ) ) {
            $theme_info .= '<p class="redux-framework-theme-data description theme-tags">'.__('<strong>Tags:</strong> ', 'redux-framework-demo').implode(', ', $tags).'</p>';
        }

        $sections[] = array(
            'type' => 'divide',
        );

        if(file_exists(trailingslashit(dirname(__FILE__)) . 'README.html')) {
            $tabs['docs'] = array(
                'icon' => 'el-icon-book',
                'title' => __('Documentation', 'redux-framework-demo'),
                'content' => nl2br(file_get_contents(trailingslashit(dirname(__FILE__)) . 'README.html'))
            );
        }

        global $ReduxFramework;
        $ReduxFramework = new ReduxFramework($sections, $args, $tabs);

        // END Sample Config
    }
    add_action('init', 'redux_init');
endif;

/**

Custom function for filtering the sections array. Good for child themes to override or add to the sections.
Simply include this function in the child themes functions.php file.

NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
so you must use get_template_directory_uri() if you want to use any of the built in icons

 **/
if ( !function_exists( 'redux_add_another_section' ) ):
    function redux_add_another_section($sections){
        //$sections = array();
        $sections[] = array(
            'title' => __('Section via hook', 'redux-framework-demo'),
            'desc' => __('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework-demo'),
            'icon' => 'el-icon-paper-clip',
            // Leave this as a blank section, no options just some intro text set above.
            'fields' => array()
        );

        return $sections;
    }
    add_filter('redux/options/redux_demo/sections', 'redux_add_another_section');
    // replace redux_demo with your opt_name
endif;
/**

Filter hook for filtering the args array given by a theme, good for child themes to override or add to the args array.

 **/
if ( !function_exists( 'redux_change_framework_args' ) ):
    function redux_change_framework_args($args){
        //$args['dev_mode'] = true;

        return $args;
    }
    add_filter('redux/options/redux_demo/args', 'redux_change_framework_args');
    // replace redux_demo with your opt_name
endif;
/**

Filter hook for filtering the default value of any given field. Very useful in development mode.

 **/
if ( !function_exists( 'redux_change_option_defaults' ) ):
    function redux_change_option_defaults($defaults){
        $defaults['str_replace'] = "Testing filter hook!";

        return $defaults;
    }
    add_filter('redux/options/redux_demo/defaults', 'redux_change_option_defaults');
    // replace redux_demo with your opt_name
endif;

/**

Custom function for the callback referenced above

 */
if ( !function_exists( 'redux_my_custom_field' ) ):
    function redux_my_custom_field($field, $value) {
        print_r($field);
        print_r($value);
    }
endif;

/**

Custom function for the callback validation referenced above

 **/
if ( !function_exists( 'redux_validate_callback_function' ) ):
    function redux_validate_callback_function($field, $value, $existing_value) {
        $error = false;
        $value =  'just testing';
        /*
        do your validation

        if(something) {
            $value = $value;
        } elseif(something else) {
            $error = true;
            $value = $existing_value;
            $field['msg'] = 'your custom error message';
        }
        */

        $return['value'] = $value;
        if($error == true) {
            $return['error'] = $field;
        }
        return $return;
    }
endif;
/**

This is a test function that will let you see when the compiler hook occurs.
It only runs if a field	set with compiler=>true is changed.

 **/
if ( !function_exists( 'redux_test_compiler' ) ):
    function redux_test_compiler($options, $css) {
        echo "<h1>The compiler hook has run!";
        //print_r($options); //Option values
        print_r($css); //So you can compile the CSS within your own file to cache
        $filename = dirname(__FILE__) . '/avada' . '.css';

        global $wp_filesystem;
        if( empty( $wp_filesystem ) ) {
            require_once( ABSPATH .'/wp-admin/includes/file.php' );
            WP_Filesystem();
        }

        if( $wp_filesystem ) {
            $wp_filesystem->put_contents(
                $filename,
                $css,
                FS_CHMOD_FILE // predefined mode settings for WP files
            );
        }

    }
    //add_filter('redux/options/redux_demo/compiler', 'redux_test_compiler', 10, 2);
    // replace redux_demo with your opt_name
endif;


/**

Remove all things related to the Redux Demo mode.

 **/
if ( !function_exists( 'redux_remove_demo_options' ) ):
    function redux_remove_demo_options() {

        // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
        if ( class_exists('ReduxFrameworkPlugin') ) {
            remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::get_instance(), 'plugin_meta_demo_mode_link'), null, 2 );
        }

        // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
        remove_action('admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );

    }
    //add_action( 'redux/plugin/hooks', 'redux_remove_demo_options' );
endif;
