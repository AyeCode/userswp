<?php
/**
 * UsersWP Notice display functions.
 *
 * All UsersWP notice display related functions can be found here.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Import_Export {

    public function __construct() {
        add_action( 'userswp_settings_import-export_tab_content', array($this, 'get_ie_content') );
        add_action( 'admin_init', array($this, 'uwp_process_settings_export') );
        add_action( 'admin_init', array($this, 'uwp_process_settings_import') );
        add_action( 'admin_init', array($this, 'uwp_process_users_export') );
        add_action( 'admin_notices', array($this, 'uwp_ie_admin_notice') );
     }

    public function get_ie_content() {
        $subtab = 'ie-users';

        if (isset($_GET['subtab'])) {
            $subtab = $_GET['subtab'];
        }
        ?>
        <div class="item-list-sub-tabs">
            <ul class="item-list-sub-tabs-ul">
                <li class="<?php if ($subtab == 'ie-users') { echo "current selected"; } ?>">
                    <a href="<?php echo add_query_arg(array('tab' => 'import-export', 'subtab' => 'ie-users')); ?>"><?php echo __( 'Users', 'userswp' ); ?></a>
                </li>
                <li class="<?php if ($subtab == 'ie-settings') { echo "current selected"; } ?>">
                    <a href="<?php echo add_query_arg(array('tab' => 'import-export', 'subtab' => 'ie-settings')); ?>"><?php echo __( 'Settings', 'userswp' ); ?></a>
                </li>
            </ul>
        </div>
        <?php
        if ($subtab == 'ie-users') {
            include_once( USERSWP_PATH . '/admin/settings/admin-settings-ie-users.php' );
        } elseif ($subtab == 'ie-settings') {
            include_once( USERSWP_PATH . '/admin/settings/admin-settings-ie-settings.php' );
        }
    }

    public function uwp_ie_admin_notice(){
        if('success' == $_GET['imp-msg']){
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Settings imported successfully!', 'userswp' ); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Process a settings export that generates a .json file of the settings
     */
    public function uwp_process_settings_export() {
        if( empty( $_POST['uwp_ie_action'] ) || 'export_settings' != $_POST['uwp_ie_action'] )
            return;
        if( ! wp_verify_nonce( $_POST['uwp_export_nonce'], 'uwp_export_nonce' ) )
            return;
        if( ! current_user_can( 'manage_options' ) )
            return;
        $settings = get_option( 'uwp_settings' );
        ignore_user_abort( true );
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=uwp-settings-export-' . date( 'm-d-Y' ) . '.json' );
        header( "Expires: 0" );
        echo json_encode( $settings );
        exit;
    }

    /**
     * Process a settings import from a json file
     */
    public function uwp_process_settings_import() {
        if( empty( $_POST['uwp_ie_action'] ) || 'import_settings' != $_POST['uwp_ie_action'] )
            return;
        if( ! wp_verify_nonce( $_POST['uwp_import_nonce'], 'uwp_import_nonce' ) )
            return;
        if( ! current_user_can( 'manage_options' ) )
            return;
        $extension = end( explode( '.', $_FILES['import_file']['name'] ) );
        if( $extension != 'json' ) {
            wp_die( sprintf(__( 'Please upload a valid .json file. %sGo Back%s' ), '<a href="'.admin_url( 'admin.php?page=userswp&tab=import-export&subtab=ie-settings' ).'">', '</a>' ));
        }
        $import_file = $_FILES['import_file']['tmp_name'];
        if( empty( $import_file ) ) {
            wp_die( sprintf(__( 'Please upload a file to import. %sGo Back%s' ), '<a href="'.admin_url( 'admin.php?page=userswp&tab=import-export&subtab=ie-settings' ).'">', '</a>' ));
        }
        // Retrieve the settings from the file and convert the json object to an array.
        $settings = (array) json_decode( file_get_contents( $import_file ), true );
        update_option( 'uwp_settings', $settings );
        wp_safe_redirect( admin_url( 'admin.php?page=userswp&tab=import-export&subtab=ie-settings&imp-msg=success' ) ); exit;
    }

    public function uwp_process_users_export(){
        if( empty( $_POST['uwp_ie_action'] ) || 'export_users' != $_POST['uwp_ie_action'] )
            return;
        if( ! wp_verify_nonce( $_POST['uwp_export_users_nonce'], 'uwp_export_users_nonce' ) )
            return;
        if( ! current_user_can( 'manage_options' ) )
            return;

        global $wpdb;

        ob_start();
        $filename = 'uwp-users-exports-' . time() . '.csv';
        $header_row = array();
        $data_rows = array();

        $settings = get_option( 'uwp_settings' );
        ignore_user_abort( true );
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=uwp-users-export-' . date( 'm-d-Y' ) . '.csv' );
        header( "Expires: 0" );
        echo json_encode( $settings );
        exit;
    }

}
new UsersWP_Import_Export();