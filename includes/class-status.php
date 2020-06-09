<?php
/**
 * UsersWP status functions
 *
 * All UsersWP status related functions can be found here.
 *
 * @since      1.0.20
 */
class UsersWP_Status {

	/**
	 * Outputs status page content
	 *
	 * @since       1.0.0
	 * @package     userswp
	 */
    public static function output() {
        include_once( USERSWP_PATH . '/admin/views/html-admin-page-status.php' );
    }

	/**
	 * Displays status report
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
    public static function status_report() {
	    include_once( USERSWP_PATH . '/admin/views/html-admin-page-status-report.php' );
    }

    /**
     * Get array of environment information. Includes thing like software
     * versions, and various server settings.
     *
     * @return array
     */
    public static function get_environment_info() {
        global $wpdb;

        // Figure out cURL version, if installed.
        $curl_version = '';
        if ( function_exists( 'curl_version' ) ) {
            $curl_version = curl_version();
            $curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
        }

        // WP memory limit
        $wp_memory_limit = uwp_let_to_num( WP_MEMORY_LIMIT );
        if ( function_exists( 'memory_get_usage' ) ) {
            $wp_memory_limit = max( $wp_memory_limit, uwp_let_to_num( @ini_get( 'memory_limit' ) ) );
        }

        // User agent
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

        // Test POST requests
        $post_response = wp_safe_remote_post( 'http://api.wordpress.org/core/browse-happy/1.1/', array(
            'timeout'     => 10,
            'user-agent'  => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
            'httpversion' => '1.1',
            'body'        => array(
                'useragent'	=> $user_agent,
            ),
        ) );

        $post_response_body = NULL;
        $post_response_successful = false;
        if ( ! is_wp_error( $post_response ) && $post_response['response']['code'] >= 200 && $post_response['response']['code'] < 300 ) {
            $post_response_successful = true;
            $post_response_body = json_decode( wp_remote_retrieve_body( $post_response ), true );
        }

        // Test GET requests
        $get_response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/userswp/trunk/readme.txt', array(
            'timeout'     => 10,
            'user-agent'  => 'UsersWP/' . USERSWP_VERSION,
            'httpversion' => '1.1'
        ) );
        $get_response_successful = false;
        if ( ! is_wp_error( $post_response ) && $post_response['response']['code'] >= 200 && $post_response['response']['code'] < 300 ) {
            $get_response_successful = true;
        }

        // Return all environment info. Described by JSON Schema.
        return array(
            'home_url'                  => get_option( 'home' ),
            'site_url'                  => get_option( 'siteurl' ),
            'version'                   => USERSWP_VERSION,
            'wp_version'                => get_bloginfo( 'version' ),
            'wp_multisite'              => is_multisite(),
            'wp_memory_limit'           => $wp_memory_limit,
            'wp_debug_mode'             => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
            'wp_cron'                   => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
            'language'                  => get_locale(),
            'server_info'               => $_SERVER['SERVER_SOFTWARE'],
            'php_version'               => phpversion(),
            'php_post_max_size'         => uwp_let_to_num( ini_get( 'post_max_size' ) ),
            'php_max_execution_time'    => ini_get( 'max_execution_time' ),
            'php_max_input_vars'        => ini_get( 'max_input_vars' ),
            'curl_version'              => $curl_version,
            'suhosin_installed'         => extension_loaded( 'suhosin' ),
            'max_upload_size'           => wp_max_upload_size(),
            'mysql_version'             => ( ! empty( $wpdb->is_mysql ) ? $wpdb->db_version() : '' ),
            'default_timezone'          => date_default_timezone_get(),
            'fsockopen_or_curl_enabled' => ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ),
            'soapclient_enabled'        => class_exists( 'SoapClient' ),
            'domdocument_enabled'       => class_exists( 'DOMDocument' ),
            'gzip_enabled'              => is_callable( 'gzopen' ),
            'gd_library'                => extension_loaded( 'gd' ),
            'mbstring_enabled'          => extension_loaded( 'mbstring' ),
            'remote_post_successful'    => $post_response_successful,
            'remote_post_response'      => ( is_wp_error( $post_response ) ? $post_response->get_error_message() : $post_response['response']['code'] ),
            'remote_get_successful'     => $get_response_successful,
            'remote_get_response'       => ( is_wp_error( $get_response ) ? $get_response->get_error_message() : $get_response['response']['code'] ),
            'platform'       			=> ! empty( $post_response_body['platform'] ) ? $post_response_body['platform'] : '-',
            'browser_name'       		=> ! empty( $post_response_body['name'] ) ? $post_response_body['name'] : '-',
            'browser_version'       	=> ! empty( $post_response_body['version'] ) ? $post_response_body['version'] : '-',
            'user_agent'       			=> $user_agent
        );
    }

	/**
	 * Returns database information
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      array
	 */
    public static function get_database_info(){
        global $wpdb;

        $database_table_sizes = $wpdb->get_results( $wpdb->prepare( "
			SELECT
			    table_name AS 'name',
			    round( ( data_length / 1024 / 1024 ), 2 ) 'data',
			    round( ( index_length / 1024 / 1024 ), 2 ) 'index'
			FROM information_schema.TABLES
			WHERE table_schema = %s
			ORDER BY name ASC;
		", DB_NAME ) );

        $core_tables = array(
            'uwp_usermeta',
            'uwp_form_fields',
            'uwp_form_extras',
            'uwp_profile_tabs',
        );

        $core_tables = apply_filters( 'uwp_database_tables', $core_tables );

        /**
         * Adding the prefix to the tables array, for backwards compatibility.
         *
         * If we changed the tables above to include the prefix, then any filters against that table could break.
         */
        $core_tables = array_map( array( 'UsersWP_Status', 'add_db_table_prefix' ), $core_tables );

        /**
         * Organize UWP and non-UWP tables separately for display purposes later.
         *
         * To ensure we include all UWP tables, even if they do not exist, pre-populate the UWP array with all the tables.
         */
        $tables = array(
            'userswp' => array_fill_keys( $core_tables, false ),
            'other' => array()
        );

        $database_size = array(
            'data' => 0,
            'index' => 0
        );

        foreach ( $database_table_sizes as $table ) {
            $table_type = in_array( $table->name, $core_tables ) ? 'userswp' : 'other';

            $tables[ $table_type ][ $table->name ] = array(
                'data'  => $table->data,
                'index' => $table->index
            );

            $database_size[ 'data' ] += $table->data;
            $database_size[ 'index' ] += $table->index;
        }

        // Return all database info. Described by JSON Schema.
        return array(
            'uwp_db_version'   => get_option( 'uwp_db_version' ),
            'database_prefix'        	=> $wpdb->prefix,
            'database_tables'        	=> $tables,
            'database_size'          	=> $database_size,
        );
    }

	/**
	 * Returns active plugins
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      array
	 */
    public static function get_active_plugins(){
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        require_once( ABSPATH . 'wp-admin/includes/update.php' );

        if ( ! function_exists( 'get_plugin_updates' ) ) {
            return array();
        }

        // Get both site plugins and network plugins
        $active_plugins = (array) get_option( 'active_plugins', array() );
        if ( is_multisite() ) {
            $network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
            $active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
        }

        $active_plugins_data = array();
        $available_updates   = get_plugin_updates();

        foreach ( $active_plugins as $plugin ) {
            $data           = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );

            // convert plugin data to json response format.
            $active_plugins_data[] = array(
                'plugin'            => $plugin,
                'name'              => $data['Name'],
                'version'           => $data['Version'],
                'url'               => $data['PluginURI'],
                'author_name'       => $data['AuthorName'],
                'author_url'        => esc_url_raw( $data['AuthorURI'] ),
                'network_activated' => $data['Network'],
                'latest_verison'	=> ( array_key_exists( $plugin, $available_updates ) ) ? $available_updates[$plugin]->update->new_version : $data['Version']
            );
        }

        return $active_plugins_data;
    }

	/**
	 * Returns theme info
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      array
	 */
    public static function get_theme_info(){
        $active_theme = wp_get_theme();

        // Get parent theme info if this theme is a child theme, otherwise
        // pass empty info in the response.
        if ( is_child_theme() ) {
            $parent_theme      = wp_get_theme( $active_theme->Template );
            $parent_theme_info = array(
                'parent_name'           => $parent_theme->Name,
                'parent_version'        => $parent_theme->Version,
                'parent_latest_verison' => self::get_latest_theme_version( $parent_theme ),
                'parent_author_url'     => $parent_theme->{'Author URI'},
            );
        } else {
            $parent_theme_info = array( 'parent_name' => '', 'parent_version' => '', 'parent_latest_verison' => '', 'parent_author_url' => '' );
        }

        /**
         * Scan the theme directory for all UWP templates to see if our theme
         * overrides any of them.
         */
        $override_files     = array();
        $outdated_templates = false;
        $scan_files         = self::scan_template_files(  USERSWP_PATH . 'templates/' );

        foreach ( $scan_files as $file ) {
            if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
                $theme_file = get_stylesheet_directory() . '/' . $file;
            } elseif ( file_exists( get_stylesheet_directory() . '/userswp/' . $file ) ) {
                $theme_file = get_stylesheet_directory() . '/userswp/' . $file;
            } elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
                $theme_file = get_template_directory() . '/' . $file;
            } elseif ( file_exists( get_template_directory() . '/userswp/' . $file ) ) {
                $theme_file = get_template_directory() . '/userswp/' . $file;
            } else {
                $theme_file = false;
            }

            if ( ! empty( $theme_file ) ) {
                $core_version  = self::get_file_version( USERSWP_PATH . '/templates/' . $file );
                $theme_version = self::get_file_version( $theme_file );
                if ( $core_version && ( empty( $theme_version ) || version_compare( $theme_version, $core_version, '<' ) ) ) {
                    if ( ! $outdated_templates ) {
                        $outdated_templates = true;
                    }
                }
                $override_files[] = array(
                    'file'         => str_replace( WP_CONTENT_DIR . '/themes/', '', $theme_file ),
                    'version'      => $theme_version,
                    'core_version' => $core_version,
                );
            }
        }

        $active_theme_info = array(
            'name'                    => $active_theme->Name,
            'version'                 => $active_theme->Version,
            'latest_verison'          => self::get_latest_theme_version( $active_theme ),
            'author_url'              => esc_url_raw( $active_theme->{'Author URI'} ),
            'is_child_theme'          => is_child_theme(),
            'has_outdated_templates'  => $outdated_templates,
            'overrides'               => $override_files,
        );

        return array_merge( $active_theme_info, $parent_theme_info );
    }

	/**
	 * Returns security info
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      array
	 */
    public static function get_security_info(){
        $check_page = get_home_url();
        return array(
            'secure_connection' => 'https' === substr( $check_page, 0, 5 ),
            'hide_errors'       => ! ( defined( 'WP_DEBUG' ) && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG && WP_DEBUG_DISPLAY ) || 0 === intval( ini_get( 'display_errors' ) ),
        );
    }

	/**
	 * Returns pages
	 *
	 * @since       1.0.0
	 * @package     userswp
     *
	 * @return      array
	 */
    public static function get_pages(){
        $check_pages = array(
            _x( 'Profile Page', 'Page setting', 'userswp' ) => array(
                'option'    => 'profile_page',
                'shortcode' => '[uwp_profile]',
            ),
            _x( 'Register Page', 'Page setting', 'userswp' ) => array(
                'option'    => 'register_page',
                'shortcode' => '[uwp_register]',
            ),
            _x( 'Login Page', 'Page setting', 'userswp' ) => array(
                'option'    => 'login_page',
                'shortcode' => '[uwp_login]',
            ),
            _x( 'Account Page', 'Page setting', 'userswp' ) => array(
                'option'    => 'account_page',
                'shortcode' => '[uwp_account]',
            ),
            _x( 'Change Password Page', 'Page setting', 'userswp' ) => array(
                'option'    => 'change_page',
                'shortcode' => '[uwp_change]',
            ),
            _x( 'Forgot Password Page', 'Page setting', 'userswp' ) => array(
                'option'    => 'forgot_page',
                'shortcode' => '[uwp_forgot]',
            ),
            _x( 'Reset Password Page', 'Page setting', 'userswp' ) => array(
                'option'    => 'reset_page',
                'shortcode' => '[uwp_reset]',
            ),
            _x( 'Users List Page', 'Page setting', 'userswp' ) => array(
                'option'    => 'users_page',
                'shortcode' => '[uwp_users]',
            ),
        );

        $check_pages = apply_filters('uwp_status_check_pages', $check_pages);

        $pages_output = array();
        foreach ( $check_pages as $page_name => $values ) {
            $page_id  = uwp_get_page_id( $values['option'], '' );
            $page_set = $page_exists = $page_visible = false;
            $shortcode_present = $shortcode_required = false;
            $page = false;
            // Page checks
            if ( $page_id ) {
                $page_set = true;
	            $page = get_post( $page_id );
            }
            if ( $page ) {
                $page_exists = true;
            }
            if ( 'publish' === get_post_status( $page_id ) ) {
                $page_visible = true;
            }

            // Shortcode checks
            if ( $values['shortcode']  && $page ) {
                $shortcode_required = true;
                if ( strstr( $page->post_content, $values['shortcode'] ) ) {
                    $shortcode_present = true;
                }
            }

            // Wrap up our findings into an output array
            $pages_output[] = array(
                'page_name'          => $page_name,
                'page_id'            => $page_id,
                'page_set'           => $page_set,
                'page_exists'        => $page_exists,
                'page_visible'       => $page_visible,
                'shortcode'          => $values['shortcode'],
                'shortcode_required' => $shortcode_required,
                'shortcode_present'  => $shortcode_present,
            );
        }

        return $pages_output;
    }

    /**
     * Add prefix to table.
     *
     * @param string $table table name
     * @return string
     */
    protected static function add_db_table_prefix( $table ) {
        return uwp_get_table_prefix() . $table;
    }

    /**
     * Get latest version of a theme by slug.
     *
     * @since 2.0.0
     *
     * @param  object $theme WP_Theme object.
     * @return string Version number if found.
     */
    public static function get_latest_theme_version( $theme ) {
        include_once( ABSPATH . 'wp-admin/includes/theme.php' );

        $api = themes_api( 'theme_information', array(
            'slug'     => $theme->get_stylesheet(),
            'fields'   => array(
                'sections' => false,
                'tags'     => false,
            ),
        ) );

        $update_theme_version = 0;

        // Check .org for updates.
        if ( is_object( $api ) && ! is_wp_error( $api ) ) {
            $update_theme_version = $api->version;
        }

        return $update_theme_version;
    }

    /**
     * Scan the template files.
     *
     * @param  string $template_path Path to the template directory.
     * @return array
     */
    public static function scan_template_files( $template_path ) {
        $files  = @scandir( $template_path ); // @codingStandardsIgnoreLine.
        $result = array();

        if ( ! empty( $files ) ) {

            foreach ( $files as $key => $value ) {

                if ( ! in_array( $value, array( '.', '..' ), true ) ) {

                    if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
                        $sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
                        foreach ( $sub_files as $sub_file ) {
                            $result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
                        }
                    } else {
                        $result[] = $value;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Retrieve metadata from a file. Based on WP Core's get_file_data function.
     *
     * @since  1.0.20
     * @param  string $file Path to the file.
     * @return string
     */
    public static function get_file_version( $file ) {

        // Avoid notices if file does not exist.
        if ( ! file_exists( $file ) ) {
            return '';
        }

        // We don't need to write to the file, so just open for reading.
        $fp = fopen( $file, 'r' ); // @codingStandardsIgnoreLine.

        // Pull only the first 8kiB of the file in.
        $file_data = fread( $fp, 8192 ); // @codingStandardsIgnoreLine.

        // PHP will close file handle, but we are good citizens.
        fclose( $fp ); // @codingStandardsIgnoreLine.

        // Make sure we catch CR-only line endings.
        $file_data = str_replace( "\r", "\n", $file_data );
        $version   = '';

        if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] ) {
            $version = _cleanup_header_comment( $match[1] );
        }

        return $version;
    }
}