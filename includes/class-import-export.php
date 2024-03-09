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
    private $wp_filesystem;
    private $export_dir;
    private $export_url;
    public $per_page;
    public $meta_table_name;
    public $path;
    public $total_rows;
    public $imp_step;
    public $skipped;
	private $empty;
	private $step;
	private $file;
	private $filename;


    public function __construct() {
        global $wp_filesystem;

        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
            global $wp_filesystem;
        }

        $this->wp_filesystem    = $wp_filesystem;
        $this->export_dir       = $this->export_location();
        $this->export_url       = $this->export_location( true );
        $this->per_page         = apply_filters('uwp_import_export_per_page', 20, $this);
        $this->meta_table_name  = get_usermeta_table_prefix() . 'uwp_usermeta';
        $this->path  = '';

        add_action( 'admin_init', array($this, 'process_settings_export') );
        add_action( 'admin_init', array($this, 'process_settings_import') );
        add_action( 'wp_ajax_uwp_ajax_export_users', array( $this, 'process_users_export' ) );
        add_action( 'wp_ajax_uwp_ajax_import_users', array( $this, 'process_users_import' ) );
        add_action( 'wp_ajax_uwp_ie_upload_file', array( $this, 'ie_upload_file' ) );
        add_action( 'wp_ajax_nopriv_uwp_ie_upload_file', array( $this, 'ie_upload_file' ) );
        add_action( 'admin_notices', array($this, 'ie_admin_notice') );
        add_filter( 'uwp_get_export_users_status', array( $this, 'get_export_users_status' ) );
        add_filter( 'uwp_get_import_users_status', array( $this, 'get_import_users_status' ) );
     }

	/**
	 * Returns export file location
	 *
	 * @package     userswp
	 *
	 * @param       bool     $relative
	 *
	 * @return      string
     *
	 */
    public function export_location( $relative = false ) {
        $upload_dir         = wp_upload_dir();
        $export_location    = $relative ? trailingslashit( $upload_dir['baseurl'] ) . 'cache' : trailingslashit( $upload_dir['basedir'] ) . 'cache';
        $export_location    = apply_filters( 'uwp_export_location', $export_location, $relative );

        return trailingslashit( $export_location );
    }

	/**
	 * Displays notice
	 */
    public function ie_admin_notice(){
        if(isset($_GET['imp-msg']) && 'success' == $_GET['imp-msg']){
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e( 'Settings imported successfully!', 'userswp' ); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Process a settings export that generates a .json file of the settings
     */
    public function process_settings_export() {
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
    public function process_settings_import() {
        if( empty( $_POST['uwp_ie_action'] ) || 'import_settings' != $_POST['uwp_ie_action'] )
            return;
        if( ! wp_verify_nonce( $_POST['uwp_import_nonce'], 'uwp_import_nonce' ) )
            return;
        if( ! current_user_can( 'manage_options' ) )
            return;
        $extension = explode( '.', $_FILES['import_file']['name'] );
        $extension = end( $extension );
        if( $extension != 'json' ) {
            wp_die( esc_html( wp_sprintf( __( 'Please upload a valid .json file. %sGo Back%s' ), '<a href="' . esc_url( admin_url( 'admin.php?page=userswp&tab=import-export&section=settings' ) ) . '">', '</a>' ) ) );
        }
        $import_file = $_FILES['import_file']['tmp_name'];
        if( empty( $import_file ) ) {
            wp_die( esc_html( wp_sprintf( __( 'Please upload a file to import. %sGo Back%s' ), '<a href="' . esc_url( admin_url( 'admin.php?page=userswp&tab=import-export&section=settings' ) ) . '">', '</a>' ) ) );
        }
        // Retrieve the settings from the file and convert the json object to an array.
        $settings = (array) json_decode( file_get_contents( $import_file ), true );
        update_option( 'uwp_settings', $settings );
        wp_safe_redirect( admin_url( 'admin.php?page=userswp&tab=import-export&section=settings&imp-msg=success' ) ); exit;
    }

	/**
	 * Processes users export
	 */
    public function process_users_export(){

        $response               = array();
        $response['success']    = false;
        $response['msg']        = __( 'Invalid export request found.', 'userswp' );

        if ( empty( $_POST['data'] ) || !current_user_can( 'manage_options' ) ) {
            wp_send_json( $response );
        }

        parse_str( $_POST['data'], $data );

        $data['step']   = !empty( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;

        $_REQUEST = (array)$data;
        if ( !( !empty( $_REQUEST['uwp_export_users_nonce'] ) && wp_verify_nonce( $_REQUEST['uwp_export_users_nonce'], 'uwp_export_users_nonce' ) ) ) {
            $response['msg']    = __( 'Security check failed.', 'userswp' );
            wp_send_json( $response );
        }

        if ( ( $error = $this->check_export_location() ) !== true ) {
            $response['msg'] = __( 'Filesystem ERROR: ' . $error, 'userswp' );
            wp_send_json( $response );
        }

        $this->set_export_params( $_REQUEST );

        $return = $this->process_export_step();
        $done   = $this->get_export_status();

        if ( $return ) {
            $this->step += 1;

            $response['success']    = true;
            $response['msg']        = '';

            if ( $done >= 100 ) {
                $this->step     = 'done';
                $new_filename   = 'uwp-users-export-' . date( 'y-m-d-H-i' ) . '.csv';
                $new_file       = $this->export_dir . $new_filename;

                if ( file_exists( $this->file ) ) {
                    $this->wp_filesystem->move( $this->file, $new_file, true );
                }

                if ( file_exists( $new_file ) ) {
                    $response['data']['file'] = array( 'u' => $this->export_url . $new_filename, 's' => size_format( filesize( $new_file ), 2 ) );
                }
            }

            $response['data']['step']   = $this->step;
            $response['data']['done']   = $done;
        } else {
            $response['msg']    = __( 'No data found for export.', 'userswp' );
        }

        wp_send_json( $response );

    }

	/**
	 * Sets export params
	 *
	 * @package     userswp
	 *
	 * @param       array     $request
	 *
	 */
    public function set_export_params( $request ) {
        $this->empty    = false;
        $this->step     = !empty( $request['step'] ) ? absint( $request['step'] ) : 1;
        $this->filename = 'uwp-users-export-temp.csv';
        $this->file     = $this->export_dir . $this->filename;
        $chunk_per_page = !empty( $request['uwp_ie_chunk_size'] ) ? absint( $request['uwp_ie_chunk_size'] ) : 0;
        $this->per_page = $chunk_per_page < 50 || $chunk_per_page > 100000 ? 5000 : $chunk_per_page;

        do_action( 'uwp_export_users_set_params', $request );
    }

	/**
	 * Returns export file location
	 *
	 * @package     userswp
	 *
	 * @return      bool
	 *
	 */
    public function check_export_location() {
        try {
            if ( empty( $this->wp_filesystem ) ) {
                return __( 'Filesystem ERROR: Could not access filesystem.', 'userswp' );
            }

            if ( is_wp_error( $this->wp_filesystem ) ) {
                return __( 'Filesystem ERROR: ' . $this->wp_filesystem->get_error_message(), 'userswp' );
            }

            $is_dir         = $this->wp_filesystem->is_dir( $this->export_dir );
            $is_writeable   = $is_dir && is_writeable( $this->export_dir );

            if ( $is_dir && $is_writeable ) {

            } else if ( $is_dir && !$is_writeable ) {
                if ( !$this->wp_filesystem->chmod( $this->export_dir, FS_CHMOD_DIR ) ) {
                    return wp_sprintf( __( 'Filesystem ERROR: Export location %s is not writable, check your file permissions.', 'userswp' ), $this->export_dir );
                }
            } else {
                if ( !$this->wp_filesystem->mkdir( $this->export_dir, FS_CHMOD_DIR ) ) {
                    return wp_sprintf( __( 'Filesystem ERROR: Could not create directory %s. This is usually due to inconsistent file permissions.', 'userswp' ), $this->export_dir );
                }
            }

	        if(!$this->wp_filesystem->exists( $this->export_dir . '/index.php')){
		        $this->wp_filesystem->copy( USERSWP_PATH . 'assets/index.php', $this->export_dir . '/index.php' );
	        }

	        return true;
        } catch ( Exception $e ) {
            return $e->getMessage();
        }
    }

	/**
	 * Processes export step
	 *
	 * @return      bool
	 *
	 */
    public function process_export_step() {
        if ( $this->step < 2 ) {
            @unlink( $this->file );
            $this->print_columns();
        }

        $return = $this->print_rows();

        if ( $return ) {
            return true;
        } else {
            return false;
        }
    }

	/**
	 * Prints columns
	 *
	 * @return      array
	 *
	 */
    public function print_columns() {
        $column_data    = '';
        $columns        = $this->get_columns();
        $i              = 1;
        foreach( $columns as $key => $column ) {
            $column_data .= '"' . addslashes( $column ) . '"';
            $column_data .= $i == count( $columns ) ? '' : ',';
            $i++;
        }
        $column_data .= "\r\n";

        $this->attach_export_data( $column_data );

        return $column_data;
    }

	/**
	 * Returns export columns
	 *
	 * @return      array
	 *
	 */
    public function get_columns() {
        global $wpdb;
        $columns = array();

        foreach ( $wpdb->get_col( "DESC " . $this->meta_table_name, 0 ) as $column_name ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $columns[] = $column_name;
        }

        return apply_filters( 'uwp_export_users_get_columns', $columns );
    }

	/**
	 * Returns export data
	 *
	 * @package     userswp
	 *
	 * @return      array
	 *
	 */
    public function get_export_data() {
        global $wpdb;
        if(!$this->step){
            $page = 1;
        } else {
            $page = $this->step;
        }

        $page_start = absint( ( $page - 1 ) * $this->per_page );
        $data = $wpdb->get_results( "SELECT * FROM $this->meta_table_name WHERE 1=1 LIMIT $page_start,". $this->per_page); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $i = 0;

        foreach ($data as $u){
            $user = get_userdata($u->user_id);
            $data[$i]->username = $user->user_login;
            $data[$i]->email = $user->user_email;
            $data[$i]->bio = $user->description;
            $i++;
        }

        return apply_filters( 'uwp_export_users_get_data', $data );
    }

	/**
	 * Returns export status
	 *
	 * @return      int
	 *
	 */
    public function get_export_status() {
        $status = 100;
        return apply_filters( 'uwp_get_export_users_status', $status );
    }

	/**
	 * Prints CSV rows
	 *
	 * @return      string
	 *
	 */
    public function print_rows() {
        $row_data   = '';
        $data       = $this->get_export_data();
        $columns    = $this->get_columns();

        if ( is_array($data) && !empty($data) ) {
            foreach ( $data as $row ) {
                $i = 1;
                foreach ( $row as $key => $column ) {
                    $column = $this->escape_data( $column );
                    $row_data .= '"' . addslashes( preg_replace( "/\"/","'", $column ) ) . '"';
                    $row_data .= $i == count( $columns ) ? '' : ',';
                    $i++;
                }
                $row_data .= "\r\n";
            }

            $this->attach_export_data( $row_data );

            return $row_data;
        }

        return false;
    }

	/**
	 * Returns export file content
	 *
	 * @return      string
	 *
	 */
    protected function get_export_file() {
        $file = '';

        if ( $this->wp_filesystem->exists( $this->file ) ) {
            $file = $this->wp_filesystem->get_contents( $this->file );
        } else {
            $this->wp_filesystem->put_contents( $this->file, '' );
        }

        return $file;
    }

	/**
	 * Adds export data to CSV file
	 *
	 * @package     userswp
	 *
	 * @param       string     $data
	 *
	 */
    protected function attach_export_data( $data = '' ) {
        $filedata   = $this->get_export_file();
        $filedata   .= $data;

        $this->wp_filesystem->put_contents( $this->file, $filedata );

        $rows       = file( $this->file, FILE_SKIP_EMPTY_LINES );
        $columns    = $this->get_columns();
        $columns    = empty( $columns ) ? 0 : 1;

        $this->empty = count( $rows ) == $columns ? true : false;
    }

	/**
	 * Returns export user status
	 *
	 * @return      int
	 *
	 */
    public function get_export_users_status() {
        global $wpdb;
        $data       = $wpdb->get_results("SELECT user_id FROM $this->meta_table_name WHERE 1=1"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $total      = !empty( $data ) ? count( $data ) : 0;
        $status     = 100;

        if ( $this->per_page > $total ) {
            $this->per_page = $total;
        }

        if ( $total > 0 ) {
            $status = ( ( $this->per_page * $this->step ) / $total ) * 100;
        }

        if ( $status > 100 ) {
            $status = 100;
        }

        return $status;
    }

	/**
	 * Uploaded file handling
	 *
	 * @return      string
	 *
	 */
    public function ie_upload_file(){

        if ( !(!empty($_REQUEST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'], 'uwp-ie-file-upload-nonce' )) ) {
            echo 'error';return;
        }

        $upload_data = array(
            'name'     => $_FILES['import_file']['name'],
            'type'     => $_FILES['import_file']['type'],
            'tmp_name' => $_FILES['import_file']['tmp_name'],
            'error'    => $_FILES['import_file']['error'],
            'size'     => $_FILES['import_file']['size']
        );

        header('Content-Type: text/html; charset=' . get_option('blog_charset'));

	    add_filter( 'upload_mimes', array( $this, 'allowed_upload_mimes' ) );
        $uploaded_file = wp_handle_upload( $upload_data, array('test_form' => false) );
	    remove_filter( 'upload_mimes', array( $this, 'allowed_upload_mimes' ) );

        if ( isset( $uploaded_file['url'] ) ) {
            $file_loc = $uploaded_file['url'];
            echo esc_url( $file_loc );exit;
        } else {
            echo 'error';
        }
        exit;
    }

	/**
	 * Processes users import
	 *
	 */
    public function process_users_import(){

        $response               = array();
        $response['success']    = false;
        $response['msg']        = __( 'Invalid import request found.', 'userswp' );

        if ( empty( $_POST['data'] ) || !current_user_can( 'manage_options' ) ) {
            wp_send_json( $response );
        }

        parse_str( $_POST['data'], $data );

        $this->imp_step   = !empty( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;

        $_REQUEST = (array)$data;
        if ( !( !empty( $_REQUEST['uwp_import_users_nonce'] ) && wp_verify_nonce( $_REQUEST['uwp_import_users_nonce'], 'uwp_import_users_nonce' ) ) ) {
            $response['msg']    = __( 'Security check failed.', 'userswp' );
            wp_send_json( $response );
        }

        $allowed = array('csv');
        $import_file = $data['uwp_import_users_file'];
        $uploads      = wp_upload_dir();
        $csv_file_array = explode( '/', $import_file );
        $csv_filename = end( $csv_file_array );
        $this->path = $uploads['path'].'/'.$csv_filename;

        $ext = pathinfo($csv_filename, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $response['msg']    = __( 'Invalid file type, please upload .csv file.', 'userswp' );
            wp_send_json( $response );
        }

        $lc_all = setlocale( LC_ALL, 0 );
        setlocale( LC_ALL, 'en_US.UTF-8' );
        if ( ( $handle = fopen( $this->path, "r" ) ) !== false ) {
            while ( ( $data = fgetcsv( $handle, 100000, "," ) ) !== false ) {
                if ( ! empty( $data ) ) {
                    $file[] = $data;
                }
            }
            fclose( $handle );
        }
        setlocale( LC_ALL, $lc_all );

        $this->total_rows = ( ! empty( $file ) && count( $file ) > 1 ) ? count( $file ) - 1 : 0;

        $return = $this->process_import_step();
        $done   = $this->get_import_status();

        if ( $return['success'] ) {
            $response['total']['msg'] = '';
            if($this->total_rows > 0 && $this->imp_step == 1) {
                $response['total']['msg'] = __( 'Total '.$this->total_rows. ' item(s) found.', 'userswp' );
            }

            $this->imp_step += 1;

            $response['success']    = true;
            $response['msg'] = '';

            if ( $done >= 100 ) {
                $this->imp_step     = 'done';
                $response['msg']    = __( 'Users import completed.', 'userswp' );
            }

            $response['data']['msg']    = ! empty( $return['msg'] ) ? $return['msg'] : $response['msg'];
            $response['data']['step']   = $this->imp_step;
            $response['data']['done']   = $done;
        } else {
            $response['msg']    = __( 'No valid data found for import.', 'userswp' );
        }

        wp_send_json( $response );

    }

	/**
	 * Processes import step
	 *
	 * @return      array
	 *
	 */
    public function process_import_step() {

        $errors = new WP_Error();
        if(is_null($this->path)){
            $errors->add('no_csv_file', __('No csv file found.','userswp'));
        }

        set_time_limit(0);

        $return = array('success' => false);

        $rows = $this->get_csv_rows($this->imp_step, 1);

        if ( ! empty( $rows ) ) {
            foreach ( $rows as $row ) {
                if( empty($row) ) {
                    $return['msg'] = sprintf(__('Row - %s Error: '. 'Skipped due to invalid/no data.','userswp'), $this->imp_step);
                    continue;
                }

                $username = isset($row['username']) ? $row['username'] : '';
                $email = isset($row['email']) ? $row['email'] : '';
                $first_name = isset($row['first_name']) ? $row['first_name'] : '';
                $last_name = isset($row['last_name']) ? $row['last_name'] : '';
                $bio = isset($row['bio']) ? $row['bio'] : '';
                $display_name = isset($row['display_name']) ? $row['display_name'] : '';
                $password = wp_generate_password();
                $exclude = array('user_id');
                $exclude = apply_filters('uwp_import_exclude_columns', $exclude, $row);

                if(isset($row['username']) && username_exists($row['username'])){
                    $user = get_user_by('login', $row['username']);
                    $user_id = $user->ID;
                    $email = $row['email'];
                    if( !empty( $email ) && $update_existing = apply_filters('uwp_import_update_users', false, $row, $user_id) ) {
                        $args = array(
                            'ID'         => $user_id,
                            'user_email' => $email,
                            'display_name' => $display_name,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'description' => $bio,
                        );
                        wp_update_user( $args );
                    }
                } elseif(isset($row['email']) && email_exists($row['email'])){
                    $user = get_user_by('email', $row['email']);
                    $user_id = $user->ID;
                } elseif((int)$row['user_id'] > 0){
                    $user = get_user_by('ID', $row['user_id']);
                    if(false === $user){
                        $userdata = array(
                            'user_login'  =>  $row['username'],
                            'user_email'  =>  $email,
                            'user_pass'   =>  $password,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'description' => $bio,
                            'display_name'=>  $display_name
                        );
                        $user_id = wp_insert_user( $userdata );
	                    $notify = apply_filters('uwp_import_user_notify', 'user', $user_id);
	                    wp_new_user_notification($user_id,null, $notify); //send password reset link
                    } else {
                        if( $user->user_login == $row['username'] ) { //check id passed in csv and existing username are same
                            $user_id = $row['user_id'];
                            if( !empty( $email ) && $email != $user->user_email && $update_existing = apply_filters('uwp_import_update_users', false, $row, $user_id)) {
                                $args = array(
                                    'ID'         => $user_id,
                                    'user_email' => $email,
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'description' => $bio,
                                    'display_name' => $display_name
                                );
                                wp_update_user( $args );
                            }
                        } else {
                            $return['msg'] = sprintf(__('Row - %s Error: '. 'User could not be created.','userswp'), $this->imp_step);
                            continue;
                        }
                    }
                } else {
                    $user_id = wp_create_user( $username, $password, $email );
	                if( !is_wp_error( $user_id ) ) {
		                $args = array(
			                'ID'           => $user_id,
			                'first_name'   => $first_name,
			                'last_name'    => $last_name,
			                'description'  => $bio,
			                'display_name' => $display_name
		                );
		                wp_update_user( $args );
	                }
                }

                if( !is_wp_error( $user_id ) ){
                    foreach ($row as $key => $value){
                        if(!in_array($key, $exclude)){
                            $value = maybe_unserialize($value);
                            uwp_update_usermeta($user_id, $key, $value);
                        }
                    }
                } else {
                    $return['msg'] = sprintf(__('Row - %s Error: %s','userswp'), $this->imp_step, $user_id->get_error_message());
                    continue;
                }
            }
            $return['success'] = true;
        }

        return $return;
    }

	/**
	 * Returns CSV row
	 *
	 * @package     userswp
	 *
	 * @param       int     $row
	 * @param       int     $count
	 *
	 * @return      mixed
	 *
	 */
    public function get_csv_rows( $row = 0, $count = 1 ) {

        $lc_all = setlocale( LC_ALL, 0 ); // Fix issue of fgetcsv ignores special characters when they are at the beginning of line
        setlocale( LC_ALL, 'en_US.UTF-8' );
        $l = $f =0;
        $headers = $file = array();
        $userdata_fields = $this->get_columns();
        if ( ( $handle = fopen( $this->path, "r" ) ) !== false ) {
            while ( ( $line = fgetcsv( $handle, 100000, "," ) ) !== false ) {
                // If the first line is empty, abort
                // If another line is empty, just skip it
                if ( empty( $line ) ) {
                    if ( $l === 0 )
                        break;
                    else
                        continue;
                }

                // If we are on the first line, the columns are the headers
                if ( $l === 0 ) {
                    $headers = $line;
                    $l ++;
                    continue;
                }

                // only get the rows needed
                if ( $row && $count ) {

                    // if we have everything we need then break;
                    if ( $l == $row + $count ) {
                        break;

                        // if its less than the start row then continue;
                    } elseif ( $l && $l < $row ) {
                        $l ++;
                        continue;

                        // if we have the count we need then break;
                    } elseif ( $f > $count ) {
                        break;
                    }
                }

                // Separate user data from meta
                $userdata = $usermeta = array();
                foreach ( $line as $ckey => $column ) {
                    $column_name = $headers[$ckey];
                    $column = trim( $column );
                    if ( in_array( $column_name, $userdata_fields ) ) {
                        $userdata[$column_name] = $column;
                    } else {
                        $usermeta[$column_name] = $column;
                    }
                }

                // A plugin may need to filter the data and meta
                $userdata = apply_filters( 'uwp_import_userdata', $userdata, $usermeta );

                if ( ! empty( $userdata ) ) {
                    $file[] = $userdata;
                    $f ++;
                    $l ++;
                }
            }
            fclose( $handle );
        }
        setlocale( LC_ALL, $lc_all );

        return $file;

    }

	/**
	 * Returns import status
	 *
	 * @return      int
	 *
	 */
    public function get_import_status() {
        $status = 100;
        return apply_filters( 'uwp_get_import_users_status', $status );
    }

	/**
	 * Returns import user status
	 *
	 * @return      int
	 *
	 */
    public function get_import_users_status() {

        if ( $this->imp_step >= $this->total_rows ) {
            $status = 100;
        } else {
            $status = ( ( 1 * $this->imp_step ) / $this->total_rows ) * 100;
        }

        return $status;
    }

    public function allowed_upload_mimes($mimes = array()) {
	    $mimes['csv'] = "text/csv";
	    return $mimes;
    }

	/**
	 * Escape a string to be used in a CSV export.
	 *
	 * @see https://hackerone.com/reports/72785
	 *
	 * @since 1.2.3.10
	 *
	 * @param string $data Data to escape.
	 * @return string
	 */
	public function escape_data( $data ) {
		$escape_chars = array( '=', '+', '-', '@' );

		if ( $data && in_array( substr( $data, 0, 1 ), $escape_chars, true ) ) {
			$data = " " . $data;
		}

		return $data;
	}

}
new UsersWP_Import_Export();