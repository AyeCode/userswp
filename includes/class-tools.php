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
    
    public function uwp_fix_usermeta_table() {
        // If table not available it will be created else synced
        uwp_create_tables();
        uwp101_create_tables();
    }

    public function uwp_fix_userdata($step) {
        $this->uwp_fix_usermeta($step);
    }

    public function uwp_tools_wrap_error_message($message, $class) {
        ob_start();
        ?>
        <div class="notice inline notice-<?php echo $class; ?> notice-alt">
            <p><?php echo $message; ?></p>
        </div>
        <?php
        $html = ob_get_clean();
        return $html;
    }

    public function uwp_fix_field_columns() {
        global $wpdb;
        $errors = new WP_Error();

        $form_type = 'account';
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s ORDER BY sort_order ASC", array($form_type)));
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

    public function uwp_get_field_columns() {
        global $wpdb;
        $form_type = 'account';
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s ORDER BY sort_order ASC", array($form_type)));

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

    public function uwp_fix_usermeta($step) {

        global $wpdb;


        $items_per_page = 10;
        $offset = (int) $step * $items_per_page;
//    $end = $offset + $items_per_page;

        $user_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT $wpdb->users.ID FROM $wpdb->users LIMIT %d OFFSET %d",
            $items_per_page, $offset ));

        $total_users = $this->uwp_tools_total_users_count();

        $max_step = ceil($total_users / $items_per_page) - 1;
        $percent = (($step + 1)/ ($max_step+1)) * 100;

        $columns = $this->uwp_get_field_columns();

        //we got all the IDs, now loop through them to get individual IDs
        $count = 0;
        $message = '';
        $table_sync_error = false;
        $done = false;
        $error = false;

        if ($step == 0) {
            $this->uwp_fix_usermeta_table();

            $output = $this->uwp_fix_field_columns();
            if (is_wp_error($output)) {
                $table_sync_error = true;
                $error = true;
                $message = $this->uwp_tools_wrap_error_message($output->get_error_message(), 'error');
            }
        }

        if (!$table_sync_error) {
            foreach ( $user_ids as $user_id ) {

                // get user info by calling get_userdata() on each id
                $user_data = get_userdata($user_id);
                $first_name = get_user_meta( $user_id, 'first_name', true );
                $last_name = get_user_meta( $user_id, 'last_name', true );
                $bio = get_user_meta( $user_id, 'description', true );
                $usermeta = get_user_meta( $user_id, 'uwp_usermeta', true );

                foreach ($columns as $column) {
                    switch ($column) {
                        case "uwp_account_username":
                            $value = $user_data->user_login;
                            break;
                        case "uwp_account_email":
                            $value = $user_data->user_email;
                            break;
                        case "uwp_account_first_name":
                            $value = $first_name;
                            break;
                        case "uwp_account_last_name":
                            $value = $last_name;
                            break;
                        case "uwp_account_bio":
                            $value = $bio;
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


                //avatar and banner
                $avatar_url = isset( $usermeta[ 'uwp_account_avatar_thumb' ] ) ? $usermeta[ 'uwp_account_avatar_thumb' ] : false;
                if ($avatar_url !== false) {
                    uwp_update_usermeta($user_id, 'uwp_account_avatar_thumb', $avatar_url);
                }
                $banner_url = isset( $usermeta[ 'uwp_account_banner_thumb' ] ) ? $usermeta[ 'uwp_account_banner_thumb' ] : false;
                if ($banner_url !== false) {
                    uwp_update_usermeta($user_id, 'uwp_account_banner_thumb', $banner_url);
                }




                $count++;
            }

            if ($step >= $max_step) {
                $done = true;
                $message = __("Processed Successfully", 'userswp');
                $message = $this->uwp_tools_wrap_error_message($message, 'success');
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

    public function uwp_tools_total_users_count() {
        global $wpdb;
        $sort= "user_registered";
        $total_users = $wpdb->get_var( $wpdb->prepare(
            "SELECT count(*) FROM $wpdb->users ORDER BY %s ASC"
            , $sort ));
        return $total_users;
    }

    public function uwp_tools_process_dummy_users() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!isset($_GET['uwp_dummy_users']) || empty($_GET['uwp_dummy_users'])) {
            return;
        }

        global $wpdb;

        if ($_GET['uwp_dummy_users'] == 'create') {

            $users_data = $this->uwp_dummy_users_data();
            foreach ( $users_data as $user ) {
                if ( username_exists( $user['login'] ) ) {
                    continue;
                }
                $user_id = wp_insert_user( array(
                    'user_login'      => $user['login'],
                    'user_pass'       => $user['pass'],
                    'display_name'    => $user['display_name'],
                    'user_email'      => $user['email'],
                    'user_registered' => uwp_get_random_date( 45, 1 ),
                ) );
                $query[] = $wpdb->last_query;

                $name = explode( ' ', $user['display_name'] );
                update_user_meta( $user_id, 'first_name', $name[0] );
                update_user_meta( $user_id, 'uwp_dummy_user', '1' );
                update_user_meta( $user_id, 'last_name', isset( $name[1] ) ? $name[1] : '' );

                $users[] = $user_id;
            }

            wp_redirect(admin_url('users.php'));
            exit();
        }

        if ($_GET['uwp_dummy_users'] == 'delete') {
            //todo: add this feature
        }
    }

    /**
     * Get a site specific password for dummy users.
     *
     * @return string
     */
    private static function get_dummy_user_passowrd(){
        return substr(hash( 'SHA256', AUTH_KEY . site_url() ), 0, 15);
    }

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
     * Adds the tools settings page menu as submenu.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       callable   $settings_page    The function to be called to output the content for this page.
     *
     * @return      void
     */
    public function uwp_add_admin_tools_sub_menu($settings_page) {

        add_submenu_page(
            "userswp",
            __('Tools', 'userswp'),
            __('Tools', 'userswp'),
            'manage_options',
            'uwp_tools',
            $settings_page
        );

    }

    public function uwp_tools_main_tab_content() {
        ?>
        <table class="form-table gd-tools-table">
            <tbody>
            <tr>
                <td><strong><?php _e('Tool', 'userswp');?></strong></td>
                <td><strong><?php _e('Description', 'userswp');?></strong></td>
                <td style="text-align: right"><strong><?php _e('Action', 'userswp');?></strong></td>
            </tr>


            <?php if (defined('USERSWP_VERSION')) { ?>
                <tr>
                    <td><?php _e('Fix User Data', 'userswp');?></td>
                    <td>
                        <div style="margin-bottom: 10px"><?php _e('Fixes User Data if you were using the Beta version.', 'userswp');?></div>
                    </td>
                    <td style="text-align: right">
                        <?php
                        $total_users = $this->uwp_tools_total_users_count();
                        $items_per_page = 10;
                        if ($total_users > $items_per_page) {
                            $multiple = 'data-step="1"';
                        } else {
                            $multiple = "";
                        }
                        ?>
                        <input type="button" value="<?php _e('Run', 'userswp');?>"
                               class="button-primary uwp_diagnosis_button" <?php echo $multiple; ?> data-diagnose="fix_user_data"/>
                    </td>
                </tr>

                <tr>
                    <td colspan="3">
                        <div id="uwp_diagnose_pb_fix_user_data" class="uwp-pb-wrapper">
                            <div id="progressBar" style="display: none;"><div></div></div>
                        </div>
                        <div id="uwp_diagnose_fix_user_data"></div>
                    </td>
                </tr>

                <tr>
                    <td><?php _e('Create Dummy Users', 'userswp');?></td>
                    <td>
                        <div><?php _e('Dummy Users will be created for Testing. You can delete them later. Password for all dummy users:', 'userswp'); echo " ".self::get_dummy_user_passowrd();?></div>
                    </td>
                    <td style="text-align: right">
                        <?php
                        $dummy_users_create_url = add_query_arg( array(
                            'uwp_dummy_users' => 'create',
                        ));

                        ?>
                        <a href="<?php echo $dummy_users_create_url; ?>" class="button-primary"><?php _e('Run', 'userswp');?></a>
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

                        $("#uwp_diagnose_pb_" + type).find('#progressBar').show().progressbar({value: 0});

                        // start the process
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
                        type: type
                    },
                    beforeSend: function() {},
                    success: function(response, textStatus, xhr) {
                        if(response.done === true || response.error === true ) {
                            jQuery("#uwp_diagnose_pb_" + type).find('#progressBar').hide();
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
                    $element = jQuery("#uwp_diagnose_pb_" + type).find('#progressBar');
                    var progressBarWidth = percent * $element.width() / 100;
                    $element.find('div').animate({ width: progressBarWidth }, 500).html(percent + "% ");
                }
            }
        </script>
        <?php
    }

    function uwp_process_diagnosis_ajax() {

        if (!is_user_logged_in()) {
            return;
        }

        $type = strip_tags(esc_sql($_POST['type']));
        $step = isset($_POST['step']) ? strip_tags(esc_sql($_POST['step'])) : 0;


        if (!current_user_can('manage_options')) {
            return;
        }

        $this->uwp_process_diagnosis($type, $step);

        die();

    }

    function uwp_process_diagnosis($type, $step) {
        switch ($type) {
            case 'fix_user_data':
                $this->uwp_fix_userdata($step);
        }
    }

}