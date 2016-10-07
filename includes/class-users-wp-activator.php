<?php
/**
 * Fired during plugin activation
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Activator {

    /**
     * @since    1.0.0
     */
    public static function activate() {
        self::generate_pages();
        self::add_default_options();
        self::uwp_create_tables();
        self::uwp_create_default_fields();
    }

    public static function generate_pages() {
        self::uwp_create_page(esc_sql(_x('register', 'page_slug', 'users-wp')), 'uwp_register_page', __('Register', 'users-wp'), '[uwp_register]');
        self::uwp_create_page(esc_sql(_x('login', 'page_slug', 'users-wp')), 'uwp_login_page', __('Login', 'users-wp'), '[uwp_login]');
        self::uwp_create_page(esc_sql(_x('account', 'page_slug', 'users-wp')), 'uwp_account_page', __('Account', 'users-wp'), '[uwp_account]');
        self::uwp_create_page(esc_sql(_x('forgot', 'page_slug', 'users-wp')), 'uwp_forgot_pass_page', __('Forgot Password?', 'users-wp'), '[uwp_forgot]');
        self::uwp_create_page(esc_sql(_x('profile', 'page_slug', 'users-wp')), 'uwp_user_profile_page', __('Profile', 'users-wp'), '[uwp_profile]');
        self::uwp_create_page(esc_sql(_x('users', 'page_slug', 'users-wp')), 'uwp_users_list_page', __('Users', 'users-wp'), '[uwp_users]');
    }

    public static function add_default_options() {
        $forgot_password_subject = __('[#site_name#] - Your new password', 'users-wp');
        $forgot_password_content = __("<p>Dear [#client_name#],<p><p>You requested a new password for [#site_name_url#]</p><p>[#login_details#]</p><p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>",'users-wp');

        $register_success_subject = __('Your Log In Details', 'users-wp');
        $register_success_content = __("<p>Dear [#client_name#],</p><p>You can log in  with the following information:</p><p>[#login_details#]</p><p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>",'users-wp');

        update_option('uwp_forgot_password_subject', $forgot_password_subject);
        update_option('uwp_forgot_password_content', $forgot_password_content);
        update_option('uwp_register_success_subject', $register_success_subject);
        update_option('uwp_register_success_content', $register_success_content);
    }

    public static function uwp_create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0, $status = 'publish') {
        global $wpdb, $current_user;

        $option_value = get_option($option);

        if ($option_value > 0) :
            if (get_post($option_value)) :
                // Page exists
                return;
            endif;
        endif;

        $page_found = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;",
                array($slug)
            )
        );

        if ($page_found) :
            // Page exists
            if (!$option_value) update_option($option, $page_found);
            return;
        endif;

        $page_data = array(
            'post_status' => $status,
            'post_type' => 'page',
            'post_author' => $current_user->ID,
            'post_name' => $slug,
            'post_title' => $page_title,
            'post_content' => $page_content,
            'post_parent' => $post_parent,
            'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);

        add_option($option, $page_id);

    }

    public static function uwp_create_tables()
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_custom_fields';

        $wpdb->hide_errors();

        $collate = '';
        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
        }

        /**
         * Include any functions needed for upgrades.
         *
         * @since 1.0.0
         */
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $custom_fields = "CREATE TABLE " . $table_name . " (
							  id int(11) NOT NULL AUTO_INCREMENT,
							  form_type varchar(100) NULL,
							  data_type varchar(100) NULL DEFAULT NULL,
							  field_type varchar(255) NOT NULL COMMENT 'text,checkbox,radio,select,textarea',
							  field_type_key varchar(255) NOT NULL,
							  site_title varchar(255) NULL DEFAULT NULL,
							  htmlvar_name varchar(255) NULL DEFAULT NULL,
							  default_value text NULL DEFAULT NULL,
							  sort_order int(11) NOT NULL,
							  option_values text NULL DEFAULT NULL,
							  is_active enum( '0', '1' ) NOT NULL DEFAULT '1',
							  is_default enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_admin enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_required enum( '0', '1' ) NOT NULL DEFAULT '0',
							  required_msg varchar(255) NULL DEFAULT NULL,
							  show_in text NULL DEFAULT NULL,
							  extra_fields text NULL DEFAULT NULL,
							  field_icon varchar(255) NULL DEFAULT NULL,
							  css_class varchar(255) NULL DEFAULT NULL,
							  decimal_point varchar( 10 ) NOT NULL,
							  validation_pattern varchar( 255 ) NOT NULL,
							  validation_msg text NULL DEFAULT NULL,
							  PRIMARY KEY  (id)
							  ) $collate";

        $custom_fields = apply_filters('uwp_before_custom_field_table_create', $custom_fields);

        dbDelta($custom_fields);


        $register_data_table = $wpdb->prefix . 'uwp_register_form_data';
        $register_data = "CREATE TABLE " . $register_data_table . " (
						user_id int(11) NOT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

        $register_data = apply_filters('uwp_before_register_form_data_table_create', $register_data);

        dbDelta($register_data);


        $login_data_table = $wpdb->prefix . 'uwp_login_form_data';
        $login_data = "CREATE TABLE " . $login_data_table . " (
						user_id int(11) NOT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

        $login_data = apply_filters('uwp_before_login_form_data_table_create', $login_data);

        dbDelta($login_data);

        $forgot_data_table = $wpdb->prefix . 'uwp_forgot_form_data';
        $forgot_data = "CREATE TABLE " . $forgot_data_table . " (
						user_id int(11) NOT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

        $forgot_data = apply_filters('uwp_before_forgot_form_data_table_create', $forgot_data);

        dbDelta($forgot_data);

        $account_data_table = $wpdb->prefix . 'uwp_account_form_data';
        $account_data = "CREATE TABLE " . $account_data_table . " (
						user_id int(11) NOT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

        $account_data = apply_filters('uwp_before_account_form_data_table_create', $account_data);

        dbDelta($account_data);
    }

    public static function uwp_create_default_fields()
    {

        $fields = self::uwp_default_custom_fields();

        $fields = apply_filters('uwp_before_default_custom_fields_saved', $fields);

        foreach ($fields as $field_index => $field) {
            self::uwp_custom_field_save($field);
        }
    }

    public static function uwp_default_custom_fields(){

        $register = self::uwp_default_custom_fields_register();
        $login = self::uwp_default_custom_fields_login();
        $forgot = self::uwp_default_custom_fields_forgot();
        $account = self::uwp_default_custom_fields_account();

        $fields = array_merge($register, $login, $forgot, $account);

        $fields = apply_filters('uwp_default_custom_fields', $fields);

        return $fields;

    }

    public static function uwp_default_custom_fields_register(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'register',
            'data_type' => 'VARCHAR',
            'field_type' => 'text',
            'site_title' => __('Username', 'users-wp'),
            'htmlvar_name' => 'username',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'register',
            'data_type' => 'VARCHAR',
            'field_type' => 'text',
            'site_title' => __('First Name', 'users-wp'),
            'htmlvar_name' => 'first_name',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'register',
            'data_type' => 'VARCHAR',
            'field_type' => 'text',
            'site_title' => __('Last Name', 'users-wp'),
            'htmlvar_name' => 'last_name',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'register',
            'data_type' => 'VARCHAR',
            'field_type' => 'email',
            'site_title' => __('Email', 'users-wp'),
            'htmlvar_name' => 'email',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'register',
            'data_type' => 'VARCHAR',
            'field_type' => 'password',
            'site_title' => __('Password', 'users-wp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'register',
            'data_type' => 'VARCHAR',
            'field_type' => 'password',
            'site_title' => __('Confirm Password', 'users-wp'),
            'htmlvar_name' => 'confirm_password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );


        $fields = apply_filters('uwp_default_custom_fields_register', $fields);

        return  $fields;
    }

    public static function uwp_default_custom_fields_login(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'login',
            'data_type' => 'VARCHAR',
            'field_type' => 'text',
            'site_title' => __('Username', 'users-wp'),
            'htmlvar_name' => 'username',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'login',
            'data_type' => 'VARCHAR',
            'field_type' => 'password',
            'site_title' => __('Password', 'users-wp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields = apply_filters('uwp_default_custom_fields_login', $fields);

        return  $fields;
    }

    public static function uwp_default_custom_fields_forgot(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'forgot',
            'data_type' => 'VARCHAR',
            'field_type' => 'email',
            'site_title' => __('Email', 'users-wp'),
            'htmlvar_name' => 'email',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields = apply_filters('uwp_default_custom_fields_forgot', $fields);

        return  $fields;
    }

    public static function uwp_default_custom_fields_account(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'account',
            'data_type' => 'VARCHAR',
            'field_type' => 'text',
            'site_title' => __('First Name', 'users-wp'),
            'htmlvar_name' => 'first_name',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'account',
            'data_type' => 'VARCHAR',
            'field_type' => 'text',
            'site_title' => __('Last Name', 'users-wp'),
            'htmlvar_name' => 'last_name',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'account',
            'data_type' => 'VARCHAR',
            'field_type' => 'email',
            'site_title' => __('Email', 'users-wp'),
            'htmlvar_name' => 'email',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'account',
            'data_type' => 'VARCHAR',
            'field_type' => 'text',
            'site_title' => __('Password', 'users-wp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'account',
            'data_type' => 'VARCHAR',
            'field_type' => 'text',
            'site_title' => __('Confirm Password', 'users-wp'),
            'htmlvar_name' => 'confirm_password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields = apply_filters('uwp_default_custom_fields_account', $fields);

        return  $fields;
    }

    public static function uwp_custom_field_save($request_field = array(), $default = false)
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_custom_fields';

        $old_html_variable = '';

        $result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';

        // some servers fail if a POST value is VARCHAR so we change it.
        if(isset($request_field['data_type']) && $request_field['data_type']=='XVARCHAR'){
            $request_field['data_type'] = 'VARCHAR';
        }

        $cf = trim($result_str, '_');


        /*-------- check duplicate validation --------*/

        $cehhtmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
        $form_type = $request_field['form_type'];

        if ($request_field['field_type'] != 'fieldset') {
            $cehhtmlvar_name = 'geodir_' . $cehhtmlvar_name;
        }

        $check_html_variable = $wpdb->get_var(
            $wpdb->prepare(
                "select htmlvar_name from " . $table_name . " where id <> %d and htmlvar_name = %s and form_type = %s ",
                array($cf, $cehhtmlvar_name, $form_type)
            )
        );


        if (!$check_html_variable || $request_field['field_type'] == 'fieldset') {

            if ($cf != '') {

                $post_meta_info = $wpdb->get_row(
                    $wpdb->prepare(
                        "select * from " . $table_name . " where id = %d",
                        array($cf)
                    )
                );

            }

            if (!empty($post_meta_info)) {
                $post_val = $post_meta_info;
                $old_html_variable = $post_val->htmlvar_name;

            }

            $data_table = $wpdb->prefix . 'uwp_'.$form_type.'_form_data';


            $site_title = $request_field['site_title'];
            $data_type = $request_field['data_type'];
            $field_type = $request_field['field_type'];
            $field_type_key = isset($request_field['field_type_key']) ? $request_field['field_type_key'] : $field_type;
            $htmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
            $default_value = isset($request_field['default_value']) ? $request_field['default_value'] : '';
            $sort_order = isset($request_field['sort_order']) ? $request_field['sort_order'] : '';
            $is_active = isset($request_field['is_active']) ? $request_field['is_active'] : '';
            $is_required = isset($request_field['is_required']) ? $request_field['is_required'] : '';
            $required_msg = isset($request_field['required_msg']) ? $request_field['required_msg'] : '';
            $css_class = isset($request_field['css_class']) ? $request_field['css_class'] : '';
            $field_icon = isset($request_field['field_icon']) ? $request_field['field_icon'] : '';
            $show_in = isset($request_field['show_in']) ? $request_field['show_in'] : '';
            $decimal_point = isset($request_field['decimal_point']) ? trim($request_field['decimal_point']) : ''; // decimal point for DECIMAL data type
            $decimal_point = $decimal_point > 0 ? ($decimal_point > 10 ? 10 : $decimal_point) : '';
            $validation_pattern = isset($request_field['validation_pattern']) ? $request_field['validation_pattern'] : '';
            $validation_msg = isset($request_field['validation_msg']) ? $request_field['validation_msg'] : '';


            if(is_array($show_in)){
                $show_in = implode(",", $request_field['show_in']);
            }

            if ($field_type != 'fieldset') {
                $htmlvar_name = 'uwp_' . $htmlvar_name;
            }

            $option_values = '';
            if (isset($request_field['option_values']))
                $option_values = $request_field['option_values'];

            if (isset($request_field['extra']) && !empty($request_field['extra']))
                $extra_fields = $request_field['extra'];

            if (isset($request_field['is_default']) && $request_field['is_default'] != '')
                $is_default = $request_field['is_default'];
            else
                $is_default = '0';

            if ($is_active == '') $is_active = 1;
            if ($is_required == '') $is_required = 0;


            if ($sort_order == '') {

                $last_order = $wpdb->get_var("SELECT MAX(sort_order) as last_order FROM " . $table_name);

                $sort_order = (int)$last_order + 1;
            }


            if (!empty($post_meta_info)) {
                switch ($field_type):

                    case 'checkbox':
                    case 'multiselect':
                    case 'select':

                        $op_size = '500';

                        // only make the field as big as it needs to be.
                        if(isset($option_values) && $option_values && $field_type=='select'){
                            $option_values_arr = explode(',',$option_values);
                            if(is_array($option_values_arr)){
                                $op_max = 0;
                                foreach($option_values_arr as $op_val){
                                    if(strlen($op_val) && strlen($op_val)>$op_max){$op_max = strlen($op_val);}
                                }
                                if($op_max){$op_size =$op_max; }
                            }
                        }elseif(isset($option_values) && $option_values && $field_type=='multiselect'){
                            if(strlen($option_values)){
                                $op_size =  strlen($option_values);
                            }
                        }

                        $meta_field_add = "ALTER TABLE " . $data_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "`VARCHAR( $op_size ) NULL";

                        if ($default_value != '') {
                            $meta_field_add .= " DEFAULT '" . $default_value . "'";
                        }

                        $alter_result = $wpdb->query($meta_field_add);
                        if($alter_result===false){
                            return __('Column change failed, you may have too many columns.','users-wp');
                        }

                        if (isset($request_field['cat_display_type']))
                            $extra_fields = $request_field['cat_display_type'];

                        if (isset($request_field['multi_display_type']))
                            $extra_fields = $request_field['multi_display_type'];


                        break;

                    case 'textarea':
                    case 'html':
                    case 'url':
                    case 'file':

                        $alter_result = $wpdb->query("ALTER TABLE " . $data_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` TEXT NULL");
                        if($alter_result===false){
                            return __('Column change failed, you may have too many columns.','users-wp');
                        }
                        if (isset($request_field['advanced_editor']))
                            $extra_fields = $request_field['advanced_editor'];

                        break;

                    case 'fieldset':
                        // Nothing happen for fieldset
                        break;

                    default:
                        if ($data_type != 'VARCHAR' && $data_type != '') {
                            if ($data_type == 'FLOAT' && $decimal_point > 0) {
                                $default_value_add = "ALTER TABLE " . $data_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` DECIMAL(11, " . (int)$decimal_point . ") NULL";
                            } else {
                                $default_value_add = "ALTER TABLE " . $data_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` " . $data_type . " NULL";
                            }

                            if (is_numeric($default_value) && $default_value != '') {
                                $default_value_add .= " DEFAULT '" . $default_value . "'";
                            }
                        } else {
                            $default_value_add = "ALTER TABLE " . $data_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` VARCHAR( 254 ) NULL";
                            if ($default_value != '') {
                                $default_value_add .= " DEFAULT '" . $default_value . "'";
                            }
                        }

                        $alter_result = $wpdb->query($default_value_add);
                        if($alter_result===false){
                            return __('Column change failed, you may have too many columns.','users-wp');
                        }
                        break;
                endswitch;

                $extra_field_query = '';
                if (!empty($extra_fields)) {
                    $extra_field_query = serialize($extra_fields);
                }

                $decimal_point = $field_type == 'text' && $data_type == 'FLOAT' ? $decimal_point : '';

                $wpdb->query(

                    $wpdb->prepare(

                        "update " . $table_name . " set
					form_type = %s,
					site_title = %s,
					field_type = %s,
					field_type_key = %s,
					htmlvar_name = %s,
					default_value = %s,
					sort_order = %s,
					is_active = %s,
					is_default  = %s,
					is_required = %s,
					required_msg = %s,
					css_class = %s,
					field_icon = %s,
					field_icon = %s,
					show_in = %s,
					option_values = %s,
					data_type = %s,
					extra_fields = %s,
					decimal_point = %s,
					validation_pattern = %s,
					validation_msg = %s,
					where id = %d",

                        array(
                            $form_type,
                            $site_title,
                            $field_type,
                            $field_type_key,
                            $htmlvar_name,
                            $default_value,
                            $sort_order,
                            $is_active,
                            $is_default,
                            $is_required,
                            $required_msg,
                            $css_class,
                            $field_icon,
                            $field_icon,
                            $show_in,
                            $option_values,
                            $data_type,
                            $extra_field_query,
                            $decimal_point,
                            $validation_pattern,
                            $validation_msg,
                            $cf
                        )
                    )

                );

                $lastid = trim($cf);


                do_action('geodir_after_custom_fields_updated', $lastid);

            } else {

                switch ($field_type):

                    case 'checkbox':
                        $data_type = 'TINYINT';

                        $meta_field_add = $data_type . "( 1 ) NOT NULL ";
                        if ((int)$default_value === 1) {
                            $meta_field_add .= " DEFAULT '1'";
                        }

                        $add_result = self::uwp_add_column_if_not_exist($data_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'users-wp');
                        }
                        break;
                    case 'multiselect':
                    case 'select':
                        $data_type = 'VARCHAR';
                        $op_size = '500';

                        // only make the field as big as it needs to be.
                        if (isset($option_values) && $option_values && $field_type == 'select') {
                            $option_values_arr = explode(',', $option_values);

                            if (is_array($option_values_arr)) {
                                $op_max = 0;

                                foreach ($option_values_arr as $op_val) {
                                    if (strlen($op_val) && strlen($op_val) > $op_max) {
                                        $op_max = strlen($op_val);
                                    }
                                }

                                if ($op_max) {
                                    $op_size = $op_max;
                                }
                            }
                        } elseif (isset($option_values) && $option_values && $field_type == 'multiselect') {
                            if (strlen($option_values)) {
                                $op_size =  strlen($option_values);
                            }

                            if (isset($request_field['multi_display_type'])) {
                                $extra_fields = $request_field['multi_display_type'];
                            }
                        }

                        $meta_field_add = $data_type . "( $op_size ) NULL ";
                        if ($default_value != '') {
                            $meta_field_add .= " DEFAULT '" . $default_value . "'";
                        }

                        $add_result = self::uwp_add_column_if_not_exist($data_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'users-wp');
                        }
                        break;
                    case 'textarea':
                    case 'html':
                    case 'url':
                    case 'file':

                        $data_type = 'TEXT';

                        $meta_field_add = $data_type . " NULL ";

                        $add_result = self::uwp_add_column_if_not_exist($data_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'users-wp');
                        }

                        break;

                    case 'datepicker':

                        $data_type = 'DATE';

                        $meta_field_add = $data_type . " NULL ";

                        $add_result = self::uwp_add_column_if_not_exist($data_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value must have in valid date format.', 'users-wp');
                        }

                        break;

                    case 'time':

                        $data_type = 'TIME';

                        $meta_field_add = $data_type . " NULL ";

                        $add_result = self::uwp_add_column_if_not_exist($data_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value must have in valid time format.', 'users-wp');
                        }

                        break;

                    default:

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

                        $add_result = self::uwp_add_column_if_not_exist($data_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'users-wp');
                        }
                        break;
                endswitch;

                $extra_field_query = '';
                if (!empty($extra_fields)) {
                    $extra_field_query = serialize($extra_fields);
                }

                $decimal_point = $field_type == 'text' && $data_type == 'FLOAT' ? $decimal_point : '';

                $wpdb->query(

                    $wpdb->prepare(

                        "insert into " . $table_name . " set
					form_type = %s,
					site_title = %s,
					field_type = %s,
					field_type_key = %s,
					htmlvar_name = %s,
					default_value = %s,
					sort_order = %d,
					is_active = %s,
					is_default  = %s,
					is_required = %s,
					required_msg = %s,
					css_class = %s,
					field_icon = %s,
					show_in = %s,
					option_values = %s,
					data_type = %s,
					extra_fields = %s,
					decimal_point = %s,
					validation_pattern = %s,
					validation_msg = %s ",

                        array(
                            $form_type,
                            $site_title,
                            $field_type,
                            $field_type_key,
                            $htmlvar_name,
                            $default_value,
                            $sort_order,
                            $is_active,
                            $is_default,
                            $is_required,
                            $required_msg,
                            $css_class,
                            $field_icon,
                            $show_in,
                            $option_values,
                            $data_type,
                            $extra_field_query,
                            $decimal_point,
                            $validation_pattern,
                            $validation_msg
                        )

                    )

                );

                $lastid = $wpdb->insert_id;

                $lastid = trim($lastid);

            }

            return (int)$lastid;


        } else {
            return 'HTML Variable Name should be a unique name';
        }

    }

    public static function uwp_add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NOT NULL")
    {
        global $wpdb;
        $result = 0;// no rows affected
        if (!self::uwp_column_exist($db, $column)) {
            if (!empty($db) && !empty($column))
                $result = $wpdb->query("ALTER TABLE `$db` ADD `$column`  $column_attr");
        }
        return $result;
    }

    public static function uwp_column_exist($db, $column)
    {
        global $wpdb;
        $exists = false;
        $columns = $wpdb->get_col("show columns from $db");
        foreach ($columns as $c) {
            if ($c == $column) {
                $exists = true;
                break;
            }
        }
        return $exists;
    }

}