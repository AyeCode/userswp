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

        global $wpdb;
        $items_per_page = apply_filters('tools_process_dummy_users_per_page', 10, $step, $type);
        $offset = (int) $step * $items_per_page;
        $message = '';
        $done = false;
        $error = false;
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

            $paged = !$step ? 1 : $step;
            $dummy_users = get_users( array( 'meta_key' => 'uwp_dummy_user', 'meta_value' => '1', 'fields' => array( 'ID' ), 'paged' => $paged, 'number' => $items_per_page ) );

            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->users LEFT JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE meta_key = 'uwp_dummy_user' AND meta_value = 1;", array()));
            $total_users = $count;
            $max_step = ceil($total_users / $items_per_page) - 1;
            $percent = (($step + 1)/ ($max_step+1)) * 100;

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
        <table class="uwp-tools-table widefat">
            <tbody>

            <?php if (defined('USERSWP_VERSION')) { ?>
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
                        <strong class="tool-name"><?php _e('Dummy Users', 'userswp');?></strong>
                        <p class="tool-description"><?php _e('Dummy Users for Testing. Password for all dummy users:', 'userswp'); echo " ".self::get_dummy_user_passowrd();?></p>
                    </th>
                    <td class="run-tool">
                        <?php

                        $dummy_users = get_users( array( 'meta_key' => 'uwp_dummy_user', 'meta_value' => '1', 'fields' => array( 'ID' ) ) );

                        if ( count($dummy_users) > 0 ) {
                            ?>
                            <input type="button" value="<?php _e('Remove', 'userswp');?>" class="button-primary uwp_diagnosis_button" data-diagnose="remove_dummy_users"/>
                            <?php
                        } else {
                            ?>
                            <input type="button" value="<?php _e('Create', 'userswp');?>" class="button-primary uwp_diagnosis_button" data-diagnose="add_dummy_users"/>
                            <?php
                        }

                        ?>
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

            <?php } ?>

            </tbody>
        </table>

        <script type="text/javascript">
            (function( $, window, undefined ) {
                $(document).ready(function () {
                    $('.uwp_diagnosis_button').click(function (e) {
                        e.preventDefault();
                        var type = $(this).data('diagnose');
                        $(this).hide();
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
                            jQuery("#uwp_diagnose_pb_" + type).find('.progressBar').hide();
                            jQuery("#uwp_diagnose_" + type).html(response.message);
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
            case 'add_dummy_users':
                $this->uwp_tools_process_dummy_users($step, 'add');
                break;
            case 'remove_dummy_users':
                $this->uwp_tools_process_dummy_users($step, 'remove');
                break;
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

}