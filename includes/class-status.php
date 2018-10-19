<?php
/**
 * UsersWP status functions
 *
 * All UsersWP status related functions can be found here.
 *
 * @since      1.0.20
 */
class UsersWP_Status {

    public function uwp_status_wrap_error_message($message, $class) {
        ob_start();
        ?>
        <div class="notice inline notice-<?php echo $class; ?> notice-alt">
            <p><?php echo $message; ?></p>
        </div>
        <?php
        $html = ob_get_clean();
        return $html;
    }

    /**
     * Adds the status settings page menu as submenu.
     *
     * @since       1.0.20
     * @package     userswp
     *
     * @param       callable   $settings_page    The function to be called to output the content for this page.
     *
     * @return      void
     */
    public function uwp_add_admin_status_sub_menu($settings_page) {

        add_submenu_page(
            "userswp",
            __('Status', 'userswp'),
            __('Status', 'userswp'),
            'manage_options',
            'uwp_status',
            $settings_page
        );

    }

    public function uwp_status_main_tab_content() {
        global $wpdb;
        $environment      = $this->uwp_get_environment_info();
        $database         = $this->uwp_get_database_info();
        $active_plugins   = $this->uwp_get_active_plugins();
        $theme            = $this->uwp_get_theme_info();
        $security         = $this->uwp_get_security_info();
        $pages            = $this->uwp_get_pages();
        ?>
        <style type="text/css">
            table.uwp-status-table {
                margin-bottom: 1em;
            }
            table.uwp-status-table th {
                font-weight: 700;
                padding: 9px;
            }
            table.uwp-status-table td,
            table.uwp-status-table th {
                font-size: 1.1em;
                font-weight: 400;
            }
            table.uwp-status-table h2 {
                font-size: 14px;
                margin: 0;
            }
            table.uwp-status-table td:first-child {
                width: 33%;
            }
            table.uwp-status-table td mark,
            table.uwp-status-table th mark {
                background: transparent none;
            }
            table.uwp-status-table td mark.yes,
            table.uwp-status-table th mark.yes {
                color: #7ad03a;
            }
             p.submit {
                margin: .5em 0;
                padding: 2px;
            }
            #debug-report {
                display: none;
                margin: 10px 0;
                padding: 0;
                position: relative;
            }
            #debug-report textarea {
                font-family: monospace;
                width: 100%;
                margin: 0;
                height: 300px;
                padding: 20px;
                border-radius: 0;
                resize: none;
                font-size: 12px;
                line-height: 20px;
                outline: 0;
            }
        </style>
        <div class="notice">
            <p><?php _e( 'Please copy and paste this information in your ticket when contacting support:', 'userswp' ); ?> </p>
            <p class="submit"><a href="javascript:void" class="button-primary debug-report"><?php _e( 'Get system report', 'userswp' ); ?></a></p>
            <div id="debug-report">
                <textarea readonly="readonly"></textarea>
                <p class="submit"><button id="copy-for-support" class="button-primary" href="javascript:void" data-tip="<?php esc_attr_e( 'Copied!', 'userswp' ); ?>"><?php _e( 'Select all & copy for support', 'userswp' ); ?></button></p>
                <p class="copy-error hidden"><?php _e( 'Copying to clipboard failed. Please press Ctrl/Cmd+C to copy.', 'userswp' ); ?></p>
            </div>
        </div>
        <table class="uwp-status-table widefat">
            <thead>
            <tr>
                <th colspan="3" data-export-label="WordPress Environment"><h2><?php _e( 'WordPress Environment', 'userswp' ); ?></h2></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-export-label="Home URL"><?php _e( 'Home URL', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['home_url'] ) ?></td>
            </tr>
            <tr>
                <td data-export-label="Site URL"><?php _e( 'Site URL', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['site_url'] ) ?></td>
            </tr>
            <tr>
                <td data-export-label="UWP Version"><?php _e( 'UWP version', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['version'] ) ?></td>
            </tr>
            <tr>
                <td data-export-label="WP Version"><?php _e( 'WP version', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['wp_version'] ) ?></td>
            </tr>
            <tr>
                <td data-export-label="WP Multisite"><?php _e( 'WP multisite', 'userswp' ); ?>:</td>
                <td><?php echo ( $environment['wp_multisite'] ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
            </tr>
            <tr>
                <td data-export-label="WP Memory Limit"><?php _e( 'WP memory limit', 'userswp' ); ?>:</td>
                <td><?php
                    if ( $environment['wp_memory_limit'] < 67108864 ) {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend setting memory to at least 64MB. See: %2$s', 'userswp' ), size_format( $environment['wp_memory_limit'] ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __( 'Increasing memory allocated to PHP', 'userswp' ) . '</a>' ) . '</mark>';
                    } else {
                        echo '<mark class="yes">' . size_format( $environment['wp_memory_limit'] ) . '</mark>';
                    }
                    ?></td>
            </tr>
            <tr>
                <td data-export-label="WP Debug Mode"><?php _e( 'WP debug mode', 'userswp' ); ?>:</td>
                <td>
                    <?php if ( $environment['wp_debug_mode'] ) : ?>
                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                    <?php else : ?>
                        <mark class="no">&ndash;</mark>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="WP Cron"><?php _e( 'WP cron', 'userswp' ); ?>:</td>
                <td>
                    <?php if ( $environment['wp_cron'] ) : ?>
                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                    <?php else : ?>
                        <mark class="no">&ndash;</mark>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="Language"><?php _e( 'Language', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['language'] ) ?></td>
            </tr>
            </tbody>
        </table>

        <table class="uwp-status-table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="Server Environment"><h2><?php _e( 'Server Environment', 'userswp' ); ?></h2></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-export-label="Server Info"><?php _e( 'Server info', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['server_info'] ); ?></td>
            </tr>
            <tr>
                <td data-export-label="PHP Version"><?php _e( 'PHP version', 'userswp' ); ?>:</td>
                <td><?php
                    if ( version_compare( $environment['php_version'], '5.6', '<' ) ) {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend a minimum PHP version of 5.6.', 'userswp' ), esc_html( $environment['php_version'] ) ) . '</mark>';
                    } else {
                        echo '<mark class="yes">' . esc_html( $environment['php_version'] ) . '</mark>';
                    }
                    ?></td>
            </tr>
            <?php if ( function_exists( 'ini_get' ) ) : ?>
                <tr>
                    <td data-export-label="PHP Post Max Size"><?php _e( 'PHP post max size', 'userswp' ); ?>:</td>
                    <td><?php echo esc_html( size_format( $environment['php_post_max_size'] ) ) ?></td>
                </tr>
                <tr>
                    <td data-export-label="PHP Time Limit"><?php _e( 'PHP time limit', 'userswp' ); ?>:</td>
                    <td><?php echo esc_html( $environment['php_max_execution_time'] ) ?></td>
                </tr>
                <tr>
                    <td data-export-label="PHP Max Input Vars"><?php _e( 'PHP max input vars', 'userswp' ); ?>:</td>
                    <td><?php echo esc_html( $environment['php_max_input_vars'] ) ?></td>
                </tr>
                <tr>
                    <td data-export-label="cURL Version"><?php _e( 'cURL version', 'userswp' ); ?>:</td>
                    <td><?php echo esc_html( $environment['curl_version'] ) ?></td>
                </tr>
                <tr>
                    <td data-export-label="SUHOSIN Installed"><?php _e( 'SUHOSIN installed', 'userswp' ); ?>:</td>
                    <td><?php echo $environment['suhosin_installed'] ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
                </tr>
            <?php endif;
            if ( $wpdb->use_mysqli ) {
                $ver = mysqli_get_server_info( $wpdb->dbh );
            } else {
                $ver = mysql_get_server_info();
            }
            if ( ! empty( $wpdb->is_mysql ) && ! stristr( $ver, 'MariaDB' ) ) : ?>
                <tr>
                    <td data-export-label="MySQL Version"><?php _e( 'MySQL version', 'userswp' ); ?>:</td>
                    <td>
                        <?php
                        if ( version_compare( $environment['mysql_version'], '5.6', '<' ) ) {
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'userswp' ), esc_html( $environment['mysql_version'] ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress requirements', 'userswp' ) . '</a>' ) . '</mark>';
                        } else {
                            echo '<mark class="yes">' . esc_html( $environment['mysql_version'] ) . '</mark>';
                        }
                        ?>
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <td data-export-label="Max Upload Size"><?php _e( 'Max upload size', 'userswp' ); ?>:</td>
                <td><?php echo size_format( $environment['max_upload_size'] ) ?></td>
            </tr>
            <tr>
                <td data-export-label="Default Timezone is UTC"><?php _e( 'Default timezone is UTC', 'userswp' ); ?>:</td>
                <td><?php
                    if ( 'UTC' !== $environment['default_timezone'] ) {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'userswp' ), $environment['default_timezone'] ) . '</mark>';
                    } else {
                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                    } ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="fsockopen/cURL"><?php _e( 'fsockopen/cURL', 'userswp' ); ?>:</td>
                <td><?php
                    if ( $environment['fsockopen_or_curl_enabled'] ) {
                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                    } else {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'userswp' ) . '</mark>';
                    } ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="SoapClient"><?php _e( 'SoapClient', 'userswp' ); ?>:</td>
                <td><?php
                    if ( $environment['soapclient_enabled'] ) {
                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                    } else {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not have the %s class enabled - some gateway plugins which use SOAP may not work as expected.', 'userswp' ), '<a href="https://php.net/manual/en/class.soapclient.php">SoapClient</a>' ) . '</mark>';
                    } ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="DOMDocument"><?php _e( 'DOMDocument', 'userswp' ); ?>:</td>
                <td><?php
                    if ( $environment['domdocument_enabled'] ) {
                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                    } else {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'userswp' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' ) . '</mark>';
                    } ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="GZip"><?php _e( 'GZip', 'userswp' ); ?>:</td>
                <td><?php
                    if ( $environment['gzip_enabled'] ) {
                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                    } else {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not support the %s function - this is required to use the GeoIP database from MaxMind.', 'userswp' ), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' ) . '</mark>';
                    } ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="GD Library"><?php _e( 'GD Library', 'userswp' ); ?>:</td>
                <td><?php
                    if ( $environment['gd_library'] ) {
                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                    } else {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not have enabled %s - this is required for image processing.', 'userswp' ), '<a href="https://secure.php.net/manual/en/image.installation.php">GD Library</a>' ) . '</mark>';
                    } ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="Multibyte String"><?php _e( 'Multibyte string', 'userswp' ); ?>:</td>
                <td><?php
                    if ( $environment['mbstring_enabled'] ) {
                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                    } else {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'userswp' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' ) . '</mark>';
                    } ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="Remote Post"><?php _e( 'Remote POST', 'userswp' ); ?>:</td>
                <td><?php
                    if ( $environment['remote_post_successful'] ) {
                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                    } else {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s failed. Contact your hosting provider.', 'userswp' ), 'wp_remote_post()' ) . ' ' . esc_html( $environment['remote_post_response'] ) . '</mark>';
                    } ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="Remote Get"><?php _e( 'Remote GET', 'userswp' ); ?>:</td>
                <td><?php
                    if ( $environment['remote_get_successful'] ) {
                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                    } else {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s failed. Contact your hosting provider.', 'userswp' ), 'wp_remote_get()' ) . ' ' . esc_html( $environment['remote_get_response'] ) . '</mark>';
                    } ?>
                </td>
            </tr>
            <?php
            $rows = apply_filters( 'uwp_system_status_environment_rows', array() );
            if(count($rows) > 0) {
                foreach ($rows as $row) {
                    if (!empty($row['success'])) {
                        $css_class = 'yes';
                        $icon = '<span class="dashicons dashicons-yes"></span>';
                    } else {
                        $css_class = 'error';
                        $icon = '<span class="dashicons dashicons-no-alt"></span>';
                    }
                    ?>
                    <tr>
                    <td data-export-label="<?php echo esc_attr($row['name']); ?>"><?php echo esc_html($row['name']); ?>
                        :
                    </td>
                    <td>
                        <mark class="<?php echo esc_attr($css_class); ?>">
                            <?php echo $icon; ?><?php echo !empty($row['note']) ? wp_kses_data($row['note']) : ''; ?>
                        </mark>
                    </td>
                    </tr><?php
                }
            }?>
            </tbody>
        </table>
        <table class="widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="User Platform"><h2><?php _e( 'User Platform', 'userswp' ); ?></h2></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-export-label="Platform"><?php _e( 'Platform', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['platform'] ); ?></td>
            </tr>
            <tr>
                <td data-export-label="Browser name"><?php _e( 'Browser name', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['browser_name'] ); ?></td>
            </tr>
            <tr>
                <td data-export-label="Browser version"><?php _e( 'Browser version', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['browser_version'] ); ?></td>
            </tr>
            <tr>
                <td data-export-label="User agent"><?php _e( 'User agent', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $environment['user_agent'] ); ?></td>
            </tr>
            </tbody>
        </table>
        <table class="uwp-status-table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="Database"><h2><?php _e( 'Database', 'userswp' ); ?></h2></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-export-label="UWP Database Version"><?php _e( 'UWP database version', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $database['uwp_db_version'] ); ?></td>
            </tr>
            <tr>
                <td data-export-label="UWP Database Prefix"><?php _e( 'Database Prefix', 'userswp' ); ?></td>
                <td><?php
                    if ( strlen( $database['database_prefix'] ) > 20 ) {
                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend using a prefix with less than 20 characters.', 'userswp' ), esc_html( $database['database_prefix'] ) ) . '</mark>';
                    } else {
                        echo '<mark class="yes">' . esc_html( $database['database_prefix'] ) . '</mark>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><?php _e( 'Total Database Size', 'userswp' ); ?></td>
                <td><?php printf( '%.2fMB', $database['database_size']['data'] + $database['database_size']['index'] ); ?></td>
            </tr>

            <tr>
                <td><?php _e( 'Database Data Size', 'userswp' ); ?></td>
                <td><?php printf( '%.2fMB', $database['database_size']['data'] ); ?></td>
            </tr>

            <tr>
                <td><?php _e( 'Database Index Size', 'userswp' ); ?></td>
                <td><?php printf( '%.2fMB', $database['database_size']['index'] ); ?></td>
            </tr>

            <?php foreach ( $database['database_tables']['userswp'] as $table => $table_data ) { ?>
                <tr>
                    <td><?php echo esc_html( $table ); ?></td>
                    <td>
                        <?php if( ! $table_data ) {
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Table does not exist', 'userswp' ) . '</mark>';
                        } else {
                            printf( __( 'Data: %.2fMB + Index: %.2fMB', 'userswp' ), uwp_format_decimal( $table_data['data'], 2 ), uwp_format_decimal( $table_data['index'], 2 ) );
                        } ?>
                    </td>
                </tr>
            <?php } ?>

            <?php foreach ( $database['database_tables']['other'] as $table => $table_data ) { ?>
                <tr>
                    <td><?php echo esc_html( $table ); ?></td>
                    <td>
                        <?php printf( __( 'Data: %.2fMB + Index: %.2fMB', 'userswp' ), uwp_format_decimal( $table_data['data'], 2 ), uwp_format_decimal( $table_data['index'], 2 ) ); ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <table class="uwp-status-table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="Security"><h2><?php _e( 'Security', 'userswp' ); ?></h2></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-export-label="Secure connection (HTTPS)"><?php _e( 'Secure connection (HTTPS)', 'userswp' ); ?>:</td>
                <td>
                    <?php if ( $security['secure_connection'] ) : ?>
                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                    <?php else : ?>
                        <mark class="error"><span class="dashicons dashicons-warning"></span><?php echo __( 'Your site is not using HTTPS.', 'userswp' ); ?></mark>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="Hide errors from visitors"><?php _e( 'Hide errors from visitors', 'userswp' ); ?></td>
                <td>
                    <?php if ( $security['hide_errors'] ) : ?>
                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                    <?php else : ?>
                        <mark class="error"><span class="dashicons dashicons-warning"></span><?php _e( 'Error messages should not be shown to visitors.', 'userswp' ); ?></mark>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="uwp-status-table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="Active Plugins (<?php echo count( $active_plugins ) ?>)"><h2><?php _e( 'Active Plugins', 'userswp' ); ?> (<?php echo count( $active_plugins ) ?>)</h2></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ( $active_plugins as $plugin ) {
                if ( ! empty( $plugin['name'] ) ) {
                    $dirname = dirname( $plugin['plugin'] );

                    // Link the plugin name to the plugin url if available.
                    $plugin_name = esc_html( $plugin['name'] );
                    if ( ! empty( $plugin['url'] ) ) {
                        $plugin_name = '<a href="' . esc_url( $plugin['url'] ) . '" aria-label="' . esc_attr__( 'Visit plugin homepage' , 'userswp' ) . '" target="_blank">' . $plugin_name . '</a>';
                    }

                    $version_string = '';
                    $network_string = '';
                    if ( ! empty( $plugin['latest_verison'] ) && version_compare( $plugin['latest_verison'], $plugin['version'], '>' ) ) {
                        /* translators: %s: plugin latest version */
                        $version_string = ' &ndash; <strong style="color:red;">' . sprintf( esc_html__( '%s is available', 'userswp' ), $plugin['latest_verison'] ) . '</strong>';
                    }

                    if ( false != $plugin['network_activated'] ) {
                        $network_string = ' &ndash; <strong style="color:black;">' . __( 'Network enabled', 'userswp' ) . '</strong>';
                    }
                    ?>
                    <tr>
                        <td><?php echo $plugin_name; ?></td>
                        <td><?php
                            /* translators: %s: plugin author */
                            printf( __( 'by %s', 'userswp' ), $plugin['author_name'] );
                            echo ' &ndash; ' . esc_html( $plugin['version'] ) . $version_string . $network_string;
                            ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>

        <table class="uwp-status-table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="UWP Pages"><h2><?php _e( 'UWP Pages', 'userswp' ); ?></h2></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ( $pages as $page ) {
                $error   = false;

                if ( $page['page_id'] ) {
                    $page_name = '<a href="' . get_edit_post_link( $page['page_id'] ) . '" aria-label="' . sprintf( __( 'Edit %s page', 'userswp' ), esc_html( $page['page_name'] ) ) . '">' . esc_html( $page['page_name'] ) . '</a>';
                } else {
                    $page_name = esc_html( $page['page_name'] );
                }

                echo '<tr><td data-export-label="' . esc_attr( $page_name ) . '">' . $page_name . ':</td><td>';
                // Page ID check.
                if ( ! $page['page_set'] ) {
                    echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Page not set', 'userswp' ) . '</mark>';
                    $error = true;
                } elseif ( ! $page['page_exists'] ) {
                    echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Page ID is set, but the page does not exist', 'userswp' ) . '</mark>';
                    $error = true;
                } elseif ( ! $page['page_visible'] ) {
                    echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Page visibility should be <a href="%s" target="_blank">public</a>', 'userswp' ), 'https://codex.wordpress.org/Content_Visibility' ) . '</mark>';
                    $error = true;
                } else {
                    // Shortcode check
                    if ( $page['shortcode_required'] ) {
                        if ( ! $page['shortcode_present'] ) {
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Page does not contains the shortcode.', 'userswp' ), $page['shortcode'] ) . '</mark>';
                            $error = true;
                        }
                    }
                }

                if ( ! $error ) {
                    echo '<mark class="yes">#' . absint( $page['page_id'] ) . ' - ' . str_replace( home_url(), '', get_permalink( $page['page_id'] ) ) . '</mark>';
                }

                echo '</td></tr>';
            }
            ?>
            </tbody>
        </table>
        <table class="uwp-status-table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="Theme"><h2><?php _e( 'Theme', 'userswp' ); ?></h2></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-export-label="Name"><?php _e( 'Name', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $theme['name'] ) ?></td>
            </tr>
            <tr>
                <td data-export-label="Version"><?php _e( 'Version', 'userswp' ); ?>:</td>
                <td><?php
                    echo esc_html( $theme['version'] );
                    if ( version_compare( $theme['version'], $theme['latest_verison'], '<' ) ) {
                        /* translators: %s: theme latest version */
                        echo ' &ndash; <strong style="color:red;">' . sprintf( __( '%s is available', 'userswp' ), esc_html( $theme['latest_verison'] ) ) . '</strong>';
                    }
                    ?></td>
            </tr>
            <tr>
                <td data-export-label="Author URL"><?php _e( 'Author URL', 'userswp' ); ?>:</td>
                <td><?php echo esc_html( $theme['author_url'] ) ?></td>
            </tr>
            <tr>
                <td data-export-label="Child Theme"><?php _e( 'Child theme', 'userswp' ); ?>:</td>
                <td><?php
                    echo $theme['is_child_theme'] ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<span class="dashicons dashicons-no-alt"></span> &ndash; ' . sprintf( __( 'If you are modifying userswp on a parent theme that you did not build personally we recommend using a child theme. See: <a href="%s" target="_blank">How to create a child theme</a>', 'userswp' ), 'https://codex.wordpress.org/Child_Themes' );
                    ?></td>
            </tr>
            <?php
            if ( $theme['is_child_theme'] ) :
                ?>
                <tr>
                    <td data-export-label="Parent Theme Name"><?php _e( 'Parent theme name', 'userswp' ); ?>:</td>
                    <td><?php echo esc_html( $theme['parent_name'] ); ?></td>
                </tr>
                <tr>
                    <td data-export-label="Parent Theme Version"><?php _e( 'Parent theme version', 'userswp' ); ?>:</td>
                    <td><?php
                        echo esc_html( $theme['parent_version'] );
                        if ( version_compare( $theme['parent_version'], $theme['parent_latest_verison'], '<' ) ) {
                            /* translators: %s: parant theme latest version */
                            echo ' &ndash; <strong style="color:red;">' . sprintf( __( '%s is available', 'userswp' ), esc_html( $theme['parent_latest_verison'] ) ) . '</strong>';
                        }
                        ?></td>
                </tr>
                <tr>
                    <td data-export-label="Parent Theme Author URL"><?php _e( 'Parent theme author URL', 'userswp' ); ?>:</td>
                    <td><?php echo esc_html( $theme['parent_author_url'] ) ?></td>
                </tr>
            <?php endif ?>
            </tbody>
        </table>
        <table class="uwp-status-table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="Templates"><h2><?php _e( 'Templates', 'userswp' ); ?></h2></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ( ! empty( $theme['overrides'] ) ) { ?>
                <tr>
                    <td data-export-label="Overrides"><?php _e( 'Overrides', 'userswp' ); ?></td>
                    <td>
                        <?php
                        $total_overrides = count( $theme['overrides'] );
                        for ( $i = 0; $i < $total_overrides; $i++ ) {
                            $override = $theme['overrides'][ $i ];
                            if ( $override['core_version'] && ( empty( $override['version'] ) || version_compare( $override['version'], $override['core_version'], '<' ) ) ) {
                                $current_version = $override['version'] ? $override['version'] : '-';
                                printf(
                                    __( '%1$s version %2$s is out of date. The core version is %3$s', 'userswp' ),
                                    '<code>' . $override['file'] . '</code>',
                                    '<strong style="color:red">' . $current_version . '</strong>',
                                    $override['core_version']
                                );
                            } else {
                                echo esc_html( $override['file'] );
                            }
                            if ( ( count( $theme['overrides'] ) - 1 ) !== $i ) {
                                echo ', ';
                            }
                            echo '<br />';
                        }
                        ?>
                    </td>
                </tr>
                <?php
            } else {
                ?>
                <tr>
                    <td data-export-label="Overrides"><?php _e( 'Overrides', 'userswp' ); ?>:</td>
                    <td>&ndash;</td>
                </tr>
                <?php
            }

            if ( true === $theme['has_outdated_templates'] ) {
                ?>
                <tr>
                    <td data-export-label="Outdated Templates"><?php _e( 'Outdated templates', 'userswp' ); ?>:</td>
                    <td><mark class="error"><span class="dashicons dashicons-warning"></span></mark></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Get array of environment information. Includes thing like software
     * versions, and various server settings.
     *
     * @return array
     */
    public function uwp_get_environment_info() {
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

    public function uwp_get_database_info(){
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
            'uwp_activity',
            'uwp_followers',
            'uwp_friends',
        );

        $core_tables = apply_filters( 'uwp_database_tables', $core_tables );

        /**
         * Adding the prefix to the tables array, for backwards compatibility.
         *
         * If we changed the tables above to include the prefix, then any filters against that table could break.
         */
        $core_tables = array_map( array( $this, 'uwp_add_db_table_prefix' ), $core_tables );

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

    public function uwp_get_active_plugins(){
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

    public function uwp_get_theme_info(){
        $active_theme = wp_get_theme();

        // Get parent theme info if this theme is a child theme, otherwise
        // pass empty info in the response.
        if ( is_child_theme() ) {
            $parent_theme      = wp_get_theme( $active_theme->Template );
            $parent_theme_info = array(
                'parent_name'           => $parent_theme->Name,
                'parent_version'        => $parent_theme->Version,
                'parent_latest_verison' => $this->get_latest_theme_version( $parent_theme ),
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
        $scan_files         = $this->scan_template_files(  USERSWP_PATH . 'templates/' );

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
                $core_version  = $this->get_file_version( USERSWP_PATH . '/templates/' . $file );
                $theme_version = $this->get_file_version( $theme_file );
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
            'latest_verison'          => $this->get_latest_theme_version( $active_theme ),
            'author_url'              => esc_url_raw( $active_theme->{'Author URI'} ),
            'is_child_theme'          => is_child_theme(),
            'has_outdated_templates'  => $outdated_templates,
            'overrides'               => $override_files,
        );

        return array_merge( $active_theme_info, $parent_theme_info );
    }

    public function uwp_get_security_info(){
        $check_page = get_home_url();
        return array(
            'secure_connection' => 'https' === substr( $check_page, 0, 5 ),
            'hide_errors'       => ! ( defined( 'WP_DEBUG' ) && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG && WP_DEBUG_DISPLAY ) || 0 === intval( ini_get( 'display_errors' ) ),
        );
    }

    public function uwp_get_pages(){
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
            $page = get_post( $page_id );
            // Page checks
            if ( $page_id ) {
                $page_set = true;
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
    protected function uwp_add_db_table_prefix( $table ) {
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
    public function get_latest_theme_version( $theme ) {
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
    public function scan_template_files( $template_path ) {
        $files  = @scandir( $template_path ); // @codingStandardsIgnoreLine.
        $result = array();

        if ( ! empty( $files ) ) {

            foreach ( $files as $key => $value ) {

                if ( ! in_array( $value, array( '.', '..' ), true ) ) {

                    if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
                        $sub_files = $this->scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
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
    public function get_file_version( $file ) {

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