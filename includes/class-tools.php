<?php
/**
 * UsersWP tools functions
 *
 * All UsersWP tools related functions can be found here.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Tools {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter('uwp_load_db_language', array($this,'load_custom_field_translation') );
		add_filter('uwp_load_db_language', array($this,'load_uwp_options_text_translation') );
	}

	/**
	 * Fixes usermeta table
	 *
	 * @package     userswp
	 *
	 */
    public function fix_usermeta_table() {
        uwp_create_tables();
    }

	/**
	 * Wraps message
	 *
	 * @package     userswp
	 *
	 * @param       string   $message   Message to wrap
	 * @param       string   $class   Class for wrapper
	 *
	 * @return string
	 *
	 */
    public function tools_wrap_error_message($message, $class) {
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
	 * Fixes field columns
	 *
	 * @package     userswp
	 *
	 * @return object|bool
	 *
	 */
    public function fix_field_columns() {
        global $wpdb;
        $errors = new WP_Error();

        $form_type = 'account';
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT htmlvar_name FROM " . $table_name . " WHERE form_type = %s ORDER BY sort_order ASC", array($form_type)));
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

        $excluded = uwp_get_excluded_fields();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $htmlvar_name = $field->htmlvar_name;
                if (in_array($htmlvar_name, $excluded)) {
                    continue;
                }
                $is_exists = uwp_column_exist($meta_table, $htmlvar_name);
                if (!$is_exists) {
                    $meta_field_add = $this->uwp_sql_datatype_from_field($field);
                    $add_result = uwp_add_column_if_not_exist($meta_table, $htmlvar_name, $meta_field_add);
                    if ($add_result === false) {
                        $errors->add('creation_failed', __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'userswp'));
                        return $errors;
                    }
                }
            }
        }
        return true;
    }

	/**
	 * Returns columns for field
	 *
	 * @package     userswp
	 *
	 * @return array
	 *
	 */
    public function get_field_columns() {
        global $wpdb;
        $form_type = 'account';
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT htmlvar_name FROM " . $table_name . " WHERE form_type = %s ORDER BY sort_order ASC", array($form_type)));

        $excluded = uwp_get_excluded_fields();

        $columns = array();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $htmlvar_name = $field->htmlvar_name;
                if (in_array($htmlvar_name, $excluded)) {
                    continue;
                }
                $columns[] = $htmlvar_name;
            }
        }

        return $columns;

    }

	/**
	 * Fixes users meta
	 *
	 * @package     userswp
	 *
	 * @param       int   $step   Step for processing
	 *
	 */
    public function fix_usermeta($step) {

        global $wpdb;


        $items_per_page = apply_filters('tools_process_fix_usermeta_per_page', 10, $step);
        $offset = (int) $step * $items_per_page;

        $user_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT $wpdb->users.ID FROM $wpdb->users LIMIT %d OFFSET %d",
            $items_per_page, $offset ));

        $users_count = count_users();
        $total_users = $users_count['total_users'];

        $max_step = ceil($total_users / $items_per_page) - 1;
        $percent = (($step + 1)/ ($max_step+1)) * 100;

        $columns = $this->get_field_columns();

        //we got all the IDs, now loop through them to get individual IDs
        $count = 0;
        $message = '';
        $table_sync_error = false;
        $done = false;
        $error = false;

        if ($step == 0) {
            $this->fix_usermeta_table();

            $output = $this->fix_field_columns();
            if (is_wp_error($output)) {
                $table_sync_error = true;
                $error = true;
                $message = $this->tools_wrap_error_message($output->get_error_message(), 'error');
            }
        }

        if (!$table_sync_error) {
            foreach ( $user_ids as $user_id ) {

                // get user info by calling get_userdata() on each id
                $user_data = get_userdata($user_id);
                $usermeta = get_user_meta( $user_id, 'uwp_usermeta', true );

                foreach ($columns as $column) {
                    switch ($column) {
                        case "username":
                            $value = $user_data->user_login;
                            break;
                        case "email":
                            $value = $user_data->user_email;
                            break;
                        case "first_name":
                            $value = $user_data->first_name;
                            break;
                        case "last_name":
                            $value = $user_data->last_name;
                            break;
                        case "bio":
                            $value = $user_data->description;
                            break;
                        default:
                            if ($usermeta === false) {
                                $value = false;
                            } else {
                                $value = isset( $usermeta[ $column ] ) ? $usermeta[ $column ] : false;
                            }

                    }
                    if ($value !== false) {
                        uwp_update_usermeta($user_id, $column, $value);
                    }
                }

                $count++;
            }

            if ($step >= $max_step) {
                $done = true;
                $message = __("Processed Successfully", 'userswp');
                $message = $this->tools_wrap_error_message($message, 'success');
            } else {
                $done = false;
                $step = $step + 1;
            }
        }

        $output = array(
            'done' => $done,
            'error' => $error,
            'message' => $message,
            'step' => $step,
            'percent' => intval($percent)
        );
        echo json_encode($output);

    }

	/**
	 * Exports DB texts for translation.
	 *
	 * @package     userswp
	 *
	 * @param       int   $step   Step for processing
     *
     * @return bool
	 *
	 */
    public function export_db_texts($step){

	    $error = false;
	    $percent = 100;

        $wp_filesystem = UsersWP_Files::uwp_init_filesystem();

	    $language_file = USERSWP_PATH . 'db-language.php';

	    if ( is_file( $language_file ) && ! is_writable( $language_file ) ) {
		    return false;
	    } // Not possible to create.

	    if ( ! is_file( $language_file ) && ! is_writable( dirname( $language_file ) ) ) {
		    return false;
	    } // Not possible to create.

	    $contents_strings = array();

	    /**
	     * Filter the language string from database to translate via po editor
	     *
	     * @since 1.2.2
	     *
	     * @param array $contents_strings Array of strings.
	     */
	    $contents_strings = apply_filters( 'uwp_load_db_language', $contents_strings );

	    $contents_strings = array_unique( $contents_strings );

	    $contents_head   = array();
	    $contents_head[] = "<?php";
	    $contents_head[] = "/**";
	    $contents_head[] = " * Translate language string stored in database. Ex: Custom Fields";
	    $contents_head[] = " *";
	    $contents_head[] = " * @package userswp";
	    $contents_head[] = " * @since ".USERSWP_VERSION;
	    $contents_head[] = " */";
	    $contents_head[] = "";

	    $contents_foot   = array();
	    $contents_foot[] = "";
	    $contents_foot[] = "";

	    $contents = implode( PHP_EOL, $contents_head );

	    if ( ! empty( $contents_strings ) ) {
		    foreach ( $contents_strings as $string ) {
			    if ( is_scalar( $string ) && $string != '' ) {
				    $string = str_replace( "'", "\'", $string );

				    do_action( 'uwp_language_file_add_string', $string );

				    $contents .= PHP_EOL . "__('" . $string . "', 'userswp');";
			    }
		    }
	    }

	    $contents .= implode( PHP_EOL, $contents_foot );

	    if ( ! $wp_filesystem->put_contents( $language_file, $contents, FS_CHMOD_FILE ) ) {
		    return false;
	    } // Failure; could not write file.

	    $done = true;
	    $message = __("Processed Successfully", 'userswp');
	    $message = $this->tools_wrap_error_message($message, 'success');

	    $output = array(
		    'done' => $done,
		    'error' => $error,
		    'message' => $message,
		    'step' => $step,
		    'percent' => intval($percent)
	    );
	    echo json_encode($output);
    }

	/**
	 * Get the custom fields texts for translation
	 *
	 * @since   1.2.2
	 * @package userswp
	 *
	 * @global object $wpdb WordPress database abstraction object.
	 *
	 * @param  array $translation_texts Array of text strings.
	 *
	 * @return array Translation texts.
	 */
	public function load_custom_field_translation( $translation_texts = array() ) {
		global $wpdb;

		$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		// Custom fields table
		$sql  = "SELECT site_title, form_label, help_text, required_msg, default_value, option_values, validation_msg FROM " . $table_name . " where form_type = 'account'";
		$rows = $wpdb->get_results( $sql );

		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( ! empty( $row->site_title ) ) {
					$translation_texts[] = stripslashes_deep( $row->site_title );
				}

				if ( ! empty( $row->form_label ) ) {
					$translation_texts[] = stripslashes_deep( $row->form_label );
				}

				if ( ! empty( $row->help_text ) ) {
					$translation_texts[] = stripslashes_deep( $row->help_text );
				}

				if ( ! empty( $row->required_msg ) ) {
					$translation_texts[] = stripslashes_deep( $row->required_msg );
				}

				if ( ! empty( $row->validation_msg ) ) {
					$translation_texts[] = stripslashes_deep( $row->validation_msg );
				}

				if ( ! empty( $row->default_value ) ) {
					$translation_texts[] = stripslashes_deep( $row->default_value );
				}

				if ( ! empty( $row->placeholder_value ) ) {
					$translation_texts[] = stripslashes_deep( $row->placeholder_value );
				}

				if ( ! empty( $row->option_values ) ) {
					$option_values = uwp_string_values_to_options( stripslashes_deep( $row->option_values ) );

					if ( ! empty( $option_values ) ) {
						foreach ( $option_values as $option_value ) {
							if ( ! empty( $option_value['label'] ) ) {
								$translation_texts[] = $option_value['label'];
							}
						}
					}
				}
			}
		}

		$translation_texts = ! empty( $translation_texts ) ? array_unique( $translation_texts ) : $translation_texts;

		return $translation_texts;
	}

	/**
	 * Get the userswp notification subject & content texts for translation.
	 *
	 * @since 1.2.2
	 * @package userswp
	 *
	 * @param  array $translation_texts Array of text strings.
	 * @return array Translation texts.
	 */
	public function load_uwp_options_text_translation($translation_texts = array()) {
		$translation_texts = !empty( $translation_texts ) && is_array( $translation_texts ) ? $translation_texts : array();

		$uwp_options = array(
			'email_name',
			'email_footer_text',
			'registration_activate_email_subject',
			'registration_activate_email_content',
			'registration_success_email_subject',
			'registration_success_email_content',
			'forgot_password_email_subject',
			'forgot_password_email_content',
			'change_password_email_subject',
			'change_password_email_content',
			'reset_password_email_subject',
			'reset_password_email_content',
			'account_update_email_subject',
			'account_update_email_content',
			'account_delete_email_subject',
			'account_delete_email_content',
			'registration_success_email_subject_admin',
			'registration_success_email_content_admin',
			'account_delete_email_subject_admin',
			'account_delete_email_content_admin',
			'wp_new_user_notification_email_subject',
			'wp_new_user_notification_email_content',
			'wp_new_user_notification_email_subject_admin',
			'wp_new_user_notification_email_content_admin',
		);

		/**
		 * Filters the userswp option names that requires to add for translation.
		 *
		 * @since 1.2.2
		 * @package userswp
		 *
		 * @param  array $uwp_options Array of option names.
		 */
		$uwp_options = apply_filters('uwp_options_for_translation', $uwp_options);
		$uwp_options = array_unique($uwp_options);

		if (!empty($uwp_options)) {
			foreach ($uwp_options as $uwp_option) {
				if ($uwp_option != '' && $option_value = uwp_get_option($uwp_option)) {
					$option_value = is_string($option_value) ? stripslashes_deep($option_value) : '';

					if ($option_value != '' && !in_array($option_value, $translation_texts)) {
						$translation_texts[] = stripslashes_deep($option_value);
					}
				}
			}
		}

		$translation_texts = !empty($translation_texts) ? array_unique($translation_texts) : $translation_texts;

		return $translation_texts;
	}

	/**
	 * Returns SQL data type for field
	 *
	 * @package     userswp
	 *
	 * @param       object   $field   Field object
	 *
	 * @return string
	 *
	 */
    public function uwp_sql_datatype_from_field($field) {

        switch ($field->field_type) {
            case 'checkbox':
                $data_type = 'TINYINT';

                $meta_field_add = $data_type . "( 1 ) NOT NULL ";
                if ((int)$field->default_value === 1) {
                    $meta_field_add .= " DEFAULT '1'";
                }
                break;
            case 'multiselect':
            case 'select':
                $data_type = 'VARCHAR';
                $op_size = '500';
                $meta_field_add = $data_type . "( $op_size ) NULL ";
                if ($field->default_value != '') {
                    $meta_field_add .= " DEFAULT '" . $field->default_value . "'";
                }
                break;
            case 'textarea':
            case 'html':
            case 'url':
            case 'file':
                $data_type = 'TEXT';
                $meta_field_add = $data_type . " NULL ";
                break;
            case 'datepicker':
                $data_type = 'DATE';
                $meta_field_add = $data_type . " NULL ";
                break;
            case 'time':
                $data_type = 'TIME';
                $meta_field_add = $data_type . " NULL ";
                break;
            default:
                $data_type = $field->data_type;
                $decimal_point = $field->decimal_point;
                $default_value = $field->default_value;
                if ($data_type != 'VARCHAR' && $data_type != '') {
                    $meta_field_add = $data_type . " NULL ";

                    if ($data_type == 'FLOAT' && $decimal_point > 0) {
                        $meta_field_add = "DECIMAL(11, " . (int)$decimal_point . ") NULL ";
                    }

                    if (is_numeric($default_value) && $default_value != '') {
                        $meta_field_add .= " DEFAULT '" . $default_value . "'";
                    }
                } else {
                    $meta_field_add = " VARCHAR( 254 ) NULL ";

                    if ($default_value != '') {
                        $meta_field_add .= " DEFAULT '" . $default_value . "'";
                    }
                }
        }

        return $meta_field_add;
    }

	/**
	 * Process add or remove dummy users
	 *
	 * @package     userswp
	 *
	 * @param       int   $step   Current step
	 * @param       string   $type   Action type add or remove
	 *
	 */
    public function uwp_tools_process_dummy_users($step, $type = 'add') {
	    $items_per_page = apply_filters('tools_process_dummy_users_per_page', 10, $step, $type);
	    $offset = (int) $step * $items_per_page;
	    $message = '';
	    $error = false;
	    $percent = 100;
	    $max_step = 0;
	    if ('add' == $type) {
		    $users_data = $this->uwp_dummy_users_data();
		    $total_users = count($users_data);
		    $max_step = ceil($total_users / $items_per_page) - 1;
		    $percent = (($step + 1)/ ($max_step+1)) * 100;
		    $dummy_users = array_slice($users_data, $offset, $items_per_page, true);
		    foreach ( $dummy_users as $user ) {
			    if ( username_exists( $user['login'] ) ) {
				    continue;
			    }
			    $name = explode( ' ', $user['display_name'] );
			    $user_id = wp_insert_user( array(
				    'user_login'      => $user['login'],
				    'user_pass'       => $user['pass'],
				    'first_name'      => isset( $name[0] ) ? $name[0] : '',
				    'last_name'       => isset( $name[1] ) ? $name[1] : '',
				    'display_name'    => $user['display_name'],
				    'user_email'      => $user['email'],
				    'user_registered' => uwp_get_random_date( 45, 1 ),
			    ) );
			    update_user_meta( $user_id, 'uwp_dummy_user', '1' );
		    }
	    }
	    if ('remove' == $type) {
		    $dummy_users = get_users( array( 'meta_key' => 'uwp_dummy_user', 'meta_value' => '1', 'fields' => array( 'ID' )) );
		    $max_step = $step;
		    foreach ( $dummy_users as $user ) {
			    wp_delete_user($user->ID);
		    }
	    }
	    if ($step >= $max_step) {
		    $done = true;
		    $message = __("Processed Successfully", 'userswp');
		    $message = $this->tools_wrap_error_message($message, 'success');
	    } else {
		    $done = false;
		    $step = $step + 1;
	    }
	    $output = array(
		    'done' => $done,
		    'error' => $error,
		    'message' => $message,
		    'step' => $step,
		    'percent' => intval($percent)
	    );
	    echo json_encode($output);

    }

    /**
     * Get a site specific password for dummy users.
     *
     * @return string
     */
    private static function get_dummy_user_passowrd(){
        return substr(hash( 'SHA256', AUTH_KEY . site_url() ), 0, 15);
    }

	/**
	 * Returns array of dummy users
	 *
	 * @package     userswp
	 *
	 * @return array
	 *
	 */
    public function uwp_dummy_users_data() {

        return array(
            0  => array(
                'login' => 'antawn',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Antawn Jamison',
                'email' => 'uwp.dummy.user+1@gmail.com',
            ),
            1  => array(
                'login' => 'chynna',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Chynna Phillips',
                'email' => 'uwp.dummy.user+2@gmail.com',
            ),
            2  => array(
                'login' => 'kiki',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Kiki Cuyler',
                'email' => 'uwp.dummy.user+3@gmail.com',
            ),
            3  => array(
                'login' => 'malivai',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'MaliVai Washington',
                'email' => 'uwp.dummy.user+4@gmail.com',
            ),
            4  => array(
                'login' => 'matraca',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Matraca Berg',
                'email' => 'uwp.dummy.user+5@gmail.com',
            ),
            5  => array(
                'login' => 'ron',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Ron Faucheux',
                'email' => 'uwp.dummy.user+6@gmail.com',
            ),
            6  => array(
                'login' => 'michellie',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Michellie Jones',
                'email' => 'uwp.dummy.user+7@gmail.com',
            ),
            7  => array(
                'login' => 'monta',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Monta Ellis',
                'email' => 'uwp.dummy.user+8@gmail.com',
            ),
            8  => array(
                'login' => 'picabo',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Picabo Street',
                'email' => 'uwp.dummy.user+9@gmail.com',
            ),
            9  => array(
                'login' => 'ralph',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Ralph Fiennes',
                'email' => 'uwp.dummy.user+10@gmail.com',
            ),
            10 => array(
                'login' => 'seamus',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Seamus',
                'email' => 'uwp.dummy.user+11@gmail.com',
            ),
            11 => array(
                'login' => 'shan',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Shan Foster',
                'email' => 'uwp.dummy.user+12@gmail.com',
            ),
            12 => array(
                'login' => 'siobhan',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Siobhan',
                'email' => 'uwp.dummy.user+13@gmail.com',
            ),
            13 => array(
                'login' => 'stephen',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Stephen Curry',
                'email' => 'uwp.dummy.user+14@gmail.com',
            ),
            14 => array(
                'login' => 'wynonna',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Wynonna Judd',
                'email' => 'uwp.dummy.user+15@gmail.com',
            ),
            15 => array(
                'login' => 'john',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'John Caius',
                'email' => 'uwp.dummy.user+16@gmail.com',
            ),
            16 => array(
                'login' => 'thomas',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Thomas Carew',
                'email' => 'uwp.dummy.user+17@gmail.com',
            ),
            17 => array(
                'login' => 'jason',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Jason Chaffetz',
                'email' => 'uwp.dummy.user+18@gmail.com',
            ),
            18 => array(
                'login' => 'mamah',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Mamah Cheney',
                'email' => 'uwp.dummy.user+19@gmail.com',
            ),
            19 => array(
                'login' => 'cecelia',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Cecelia Cichan ',
                'email' => 'uwp.dummy.user+20@gmail.com',
            ),
            20 => array(
                'login' => 'dan',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Dan Cortese ',
                'email' => 'uwp.dummy.user+21@gmail.com',
            ),
            21 => array(
                'login' => 'vernon',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Vernon Dahmer',
                'email' => 'uwp.dummy.user+22@gmail.com',
            ),
            22 => array(
                'login' => 'andre',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Andre Dubus',
                'email' => 'uwp.dummy.user+23@gmail.com',
            ),
            23 => array(
                'login' => 'justin',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Justin Duchscherer',
                'email' => 'uwp.dummy.user+24@gmail.com',
            ),
            24 => array(
                'login' => 'keir',
                'pass'  => self::get_dummy_user_passowrd(),
                'display_name' => 'Keir Dullea ',
                'email' => 'uwp.dummy.user+25@gmail.com',
            ),
        );

    }

	/**
	 * Outputs tools form
	 *
	 * @package     userswp
	 *
	 */
    public static function output() {
        ob_start();
        ?>
        <div class="wrap userswp">
            <h1><?php echo get_admin_page_title(); ?></h1>
        <table class="uwp-tools-table widefat">
            <tbody>

            <?php if (defined('USERSWP_VERSION')) {
	                do_action('uwp_tools_output_start');
                ?>
                <tr>
                    <th>
                        <strong class="tool-name"><?php _e('Clear version numbers', 'userswp');?></strong>
                        <p class="tool-description"><?php _e('This will force install/upgrade functions to run.', 'userswp');?></p>
                    </th>
                    <td class="run-tool">
                        <input type="button" value="<?php _e('Run', 'userswp');?>" class="button-primary uwp_diagnosis_button" data-diagnose="clear_version_numbers"/>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="has-pbar">
                        <div id="uwp_diagnose_pb_clear_version_numbers" class="uwp-pb-wrapper">
                            <div class="progressBar" style="display: none;"><div></div></div>
                        </div>
                        <div id="uwp_diagnose_clear_version_numbers"></div>
                    </td>
                </tr>

                <tr>
                    <th>
                        <strong class="tool-name"><?php _e('Fix User Data', 'userswp');?></strong>
                        <p class="tool-description"><?php _e('Fixes User Data if you were using the Beta version.', 'userswp');?></p>
                    </th>
                    <td class="run-tool">
                        <input type="button" value="<?php _e('Run', 'userswp');?>" class="button-primary uwp_diagnosis_button" data-diagnose="fix_user_data"/>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="has-pbar">
                        <div id="uwp_diagnose_pb_fix_user_data" class="uwp-pb-wrapper">
                            <div class="progressBar" style="display: none;"><div></div></div>
                        </div>
                        <div id="uwp_diagnose_fix_user_data"></div>
                    </td>
                </tr>

                <tr>
                    <th>
                        <strong class="tool-name"><?php _e('DB text translation', 'userswp');?></strong>
                        <p class="tool-description"><?php _e('This tool will collect any texts stored in the DB and put them in the file db-language.php so they can then be used to translate them by translations tools.', 'userswp');?></p>
                    </th>
                    <td class="run-tool">
                        <input type="button" value="<?php _e('Run', 'userswp');?>" class="button-primary uwp_diagnosis_button" data-diagnose="export_db_texts"/>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="has-pbar">
                        <div id="uwp_diagnose_pb_export_db_texts" class="uwp-pb-wrapper">
                            <div class="progressBar" style="display: none;"><div></div></div>
                        </div>
                        <div id="uwp_diagnose_export_db_texts"></div>
                    </td>
                </tr>

                <tr>
                    <th>
                        <strong class="tool-name"><?php _e('Dummy Users', 'userswp');?></strong>
                        <p class="tool-description"><?php _e('Dummy Users for Testing. Password for all dummy users:', 'userswp'); echo " ".self::get_dummy_user_passowrd();?></p>
                    </th>
                    <td class="run-tool">
	                    <?php
	                    $dummy_users = get_users( array( 'meta_key' => 'uwp_dummy_user', 'meta_value' => '1', 'fields' => array( 'ID' ) ) );
	                    $total_dummy_users = !empty( $dummy_users ) ? count($dummy_users) : 0;
	                    ?>
                        <input style="display: <?php echo ( $total_dummy_users > 0 ) ? 'none' :'block'; ?>" type="button" value="<?php _e('Create', 'userswp');?>" class="button-primary uwp_diagnosis_button uwp_add_dummy_users_button" data-diagnose="add_dummy_users"/>
                        <input style="display: <?php echo ( $total_dummy_users > 0 ) ? 'block' :'none'; ?>" type="button" value="<?php _e('Remove', 'userswp');?>" class="button-primary uwp_diagnosis_button uwp_remove_dummy_users_button" data-diagnose="remove_dummy_users"/>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="has-pbar">
                        <div id="uwp_diagnose_pb_add_dummy_users" class="uwp-pb-wrapper">
                            <div class="progressBar" style="display: none;"><div></div></div>
                        </div>
                        <div id="uwp_diagnose_add_dummy_users"></div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="has-pbar">
                        <div id="uwp_diagnose_pb_remove_dummy_users" class="uwp-pb-wrapper">
                            <div class="progressBar" style="display: none;"><div></div></div>
                        </div>
                        <div id="uwp_diagnose_remove_dummy_users"></div>
                    </td>
                </tr>

            <?php
	            do_action('uwp_tools_output_end');
            } ?>

            </tbody>
        </table>
        </div>

        <script type="text/javascript">
            (function( $, window, undefined ) {
                $(document).ready(function () {
                    $('.uwp_diagnosis_button').click(function (e) {
                        e.preventDefault();
                        var type = $(this).data('diagnose');
                        $(this).hide();
                        jQuery("#uwp_diagnose_add_dummy_users,#uwp_diagnose_remove_dummy_users").html('');
                        $("#uwp_diagnose_pb_" + type).find('.progressBar').show().progressbar({value: 0});
                        uwp_process_diagnose_step( 0, type );

                    });
                });
            }( jQuery, window ));

            function uwp_process_diagnose_step(step, type) {
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'uwp_process_diagnosis',
                        step: step,
                        type: type,
                        security: '<?php echo wp_create_nonce('uwp_process_diagnosis'); ?>',
                    },
                    beforeSend: function() {},
                    success: function(response, textStatus, xhr) {
                        if(response.done === true || response.error === true ) {
                            tools_progress(response.percent, type);
                            setTimeout(function(){
                                jQuery("#uwp_diagnose_pb_" + type).find('.progressBar').hide();
                                jQuery("#uwp_diagnose_" + type).html(response.message);
                                if( 'add_dummy_users' === type ) {
                                    jQuery('.uwp_remove_dummy_users_button').show();
                                    jQuery('.uwp_add_dummy_users_button').hide();
                                } else{
                                    jQuery('.uwp_add_dummy_users_button').show();
                                    jQuery('.uwp_remove_dummy_users_button').hide();
                                }
                            }, 1500);
                        } else {
                            tools_progress(response.percent, type);
                            setTimeout(function(){
                                uwp_process_diagnose_step(parseInt( response.step ), type)
                            }, 500);
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        alert(textStatus);
                    }
                }); // end of ajax


                function tools_progress(percent, type) {
                    $element = jQuery("#uwp_diagnose_pb_" + type).find('.progressBar');
                    var progressBarWidth = percent * $element.width() / 100;
                    $element.find('div').animate({ width: progressBarWidth }, 500).html(percent + "% ");
                }
            }
        </script>
        <?php

        echo ob_get_clean();
    }

	/**
	 * Process diagnosis AJAX call
	 */
    public function uwp_process_diagnosis_ajax() {

        if (!is_user_logged_in()) {
            return;
        }

        check_ajax_referer( 'uwp_process_diagnosis', 'security' );

        $type = strip_tags(esc_sql($_POST['type']));
        $step = isset($_POST['step']) ? strip_tags(esc_sql($_POST['step'])) : 0;


        if (!current_user_can('manage_options')) {
            return;
        }

        $this->uwp_process_diagnosis($type, $step);

        die();

    }

	/**
	 * Process diagnosis step
	 *
	 * @package     userswp
	 *
	 * @param       string   $type   Action type
	 * @param       int   $step   Current step
	 *
	 */
    public function uwp_process_diagnosis($type, $step) {
        switch ($type) {
            case 'clear_version_numbers':
                $this->clear_version_numbers();
                break;
            case 'fix_user_data':
                $this->fix_usermeta($step);
                break;
            case 'export_db_texts':
                $this->export_db_texts($step);
                break;
            case 'add_dummy_users':
                $this->uwp_tools_process_dummy_users($step, 'add');
                break;
            case 'remove_dummy_users':
                $this->uwp_tools_process_dummy_users($step, 'remove');
                break;
            default :
                do_action('uwp_process_diagnosis', $type, $step);
        }
    }


    /**
     * Clear version numbers so install/upgrade functions will run.
     */
    public function clear_version_numbers(){
        delete_option( 'uwp_db_version' );
        do_action( 'uwp_clear_version_numbers'); // used by addons to clear their version numbers.
        $message = aui()->alert(array(
                'type'=>'success',
                'content'=> __( 'Version numbers cleared. Install/upgrade functions will run on next page load.', 'userswp' )
            )
        );
        $output = array(
            'done' => true,
            'message' => "<div class='bsui'>".$message."</div>",
        );
        echo json_encode($output);
    }

	public static function setup_menu( $menu_id = '',$menu_location = '' ) {

		$menu_id = sanitize_title_with_dashes($menu_id);
		$menu_location = sanitize_title_with_dashes($menu_location);

		// confirm the sidebar_id is valid
		if(!$menu_id && !$menu_location){
			return new WP_Error( 'uwp-wizard-setup-menu', __( "The menu is not valid.", "userswp" ) );
		}

		$items_added = 0;
		$items_exist= 0;

		if($menu_id){

			$menu_exists = wp_get_nav_menu_object( $menu_id );

			if(!$menu_exists){
				return new WP_Error( 'uwp-wizard-setup-menu', __( "The menu is not valid.", "userswp" ) );
			}

			$current_menu_items = wp_get_nav_menu_items( $menu_id );

			$current_menu_titles = array();
			// get a list of current slugs so we don't add things twice.
			if(!empty($current_menu_items)){
				foreach($current_menu_items as $current_menu_item){
					if(!empty($current_menu_item->post_name)){
						$current_menu_titles[] = $current_menu_item->title;
					}
				}
			}

			$uwp_menus = new UsersWP_Menus();
			$uwp_menu_items = $uwp_menus->get_endpoints();

			if(!empty($uwp_menu_items)){
				foreach($uwp_menu_items as $menu_item_type){
					if(!empty($menu_item_type)){

						$menu_item_type = array_map('wp_setup_nav_menu_item', $menu_item_type);

						foreach($menu_item_type as $menu_item){

							if(!empty($current_menu_titles) && (in_array($menu_item->title,$current_menu_titles) || in_array(str_replace(" page",'',$menu_item->title),$current_menu_titles))){
								$items_exist++; continue 2;
							}

							// setup standard menu stuff
							$menu_item->{'menu-item-object-id'} = $menu_item->object_id;
							$menu_item->{'menu-item-object'} = $menu_item->object;
							$menu_item->{'menu-item-type'} = $menu_item->type;
							$menu_item->{'menu-item-status'} = 'publish';
							$menu_item->{'menu-item-classes'} = !empty($menu_item->classes) ? implode(" ",$menu_item->classes) : 'uwp-menu-item';
							if($menu_item->type=='custom'){
								$menu_item->{'menu-item-url'} = $menu_item->url;
								$menu_item->{'menu-item-title'} = $menu_item->title;
							}

							wp_update_nav_menu_item($menu_id, 0, $menu_item);
							$items_added++;
						}
					}
				}
			}

		} elseif($menu_location){

			$menuname = "UsersWP Menu";

			$menu_exists = wp_get_nav_menu_object( $menuname );

			// If it doesn't exist, let's create it.
			if( !$menu_exists) {
				$menu_id = wp_create_nav_menu( $menuname );

				$locations = get_theme_mod( 'nav_menu_locations' );

				if($menu_id){
					$locations[$menu_location] = $menu_id;
					set_theme_mod('nav_menu_locations', $locations);
					return self::setup_menu($menu_id);
				}

			}else{
				return new WP_Error( 'uwp-wizard-setup-menu', __( "Menu already exists.", "userswp" ) );
			}

		}

		if($items_added == 0 && $items_exist > 0){
			return __( 'Menu items already exist, none added.' , 'userswp' );
		}elseif($items_added  > 0){
			return __( 'Menu items added successfully.' , 'userswp' );
		}else{
			return __( 'Something went wrong, you can manually add items in Appearance > Menus' , 'userswp' );
		}

	}

}