<?php
/**
 * UsersWP Meta functions
 *
 * All UsersWP related User Settings and Site settings can be created, updated and accessed via this class.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Meta {

    /**
     * Gets UsersWP setting value using key.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       string          $key        Setting Key.
     * @param       bool|string     $default    Default value.
     * @param       bool            $cache      Use cache to retrieve the value?.
     * 
     * @return      string                      Setting Value.
     */
    public function get_option( $key = '', $default = false, $cache = true ) {
        if ($cache) {
            global $uwp_options;
        } else {
            $uwp_options = get_option( 'uwp_settings' );
        }
        $value = ! empty( $uwp_options[ $key ] ) ? $uwp_options[ $key ] : $default;
        $value = apply_filters( 'uwp_get_option', $value, $key, $default );
        return apply_filters( 'uwp_get_option_' . $key, $value, $key, $default );
    }

    /**
     * Updates UsersWP setting value using key.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       string|bool     $key        Setting Key.
     * @param       string          $value      Setting Value.
     * 
     * @return      bool                        Update success or not?.
     */
    public function update_option( $key = false, $value = '') {

        if (!$key ) {
            return false;
        }

        $settings = get_option( 'uwp_settings', array());

        if( !is_array( $settings ) ) {
            $settings = array();
        }

        $settings[ $key ] = $value;

        $settings = apply_filters( 'uwp_update_option', $settings, $key, $value );
        $settings =  apply_filters( 'uwp_update_option_' . $key, $settings, $key, $value );

        update_option( 'uwp_settings', $settings );

        return true;
    }

    /**
     * Gets UsersWP user meta value using key.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       int|bool        $user_id        User ID.
     * @param       string          $key            User meta Key.
     * @param       bool|string     $default        Default value.
     * 
     * @return      string                          User meta Value.
     */
    public function get_usermeta( $user_id = false, $key = '', $default = false ) {
        if (!$user_id) {
            return $default;
        }

        $user_data = get_userdata($user_id);
        $value = null;
        $usermeta = false;

        if (!uwp_str_ends_with($key, '_privacy')) {
            if ($key == 'uwp_account_email') {
                $value = $user_data->user_email;
            } else {
                $usermeta = uwp_get_usermeta_row($user_id);
                if (!empty($usermeta)) {
                    $value = $usermeta->{$key} ? $usermeta->{$key} : $default;
                } else {
                    $value = $default;
                }

            }
        } else {
            $usermeta = uwp_get_usermeta_row($user_id);
        }


        $value = uwp_maybe_unserialize($key, $value);
        $value = wp_unslash($value);
        $value = apply_filters( 'uwp_get_usermeta', $value, $user_id, $key, $default, $usermeta );
        return apply_filters( 'uwp_get_usermeta_' . $key, $value, $user_id, $key, $default, $usermeta );
    }

    /**
     * Updates UsersWP user meta value using key.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       int|bool        $user_id        User ID.
     * @param       string|bool     $key            User meta Key.
     * @param       string          $value          User meta Value.
     * 
     * @return      bool                            Update success or not?.
     */
    public function update_usermeta( $user_id = false, $key, $value ) {

        if (!$user_id || !$key ) {
            return false;
        }

        global $wpdb;
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
        $user_meta_info = uwp_get_usermeta_row($user_id);

        $value = apply_filters( 'uwp_update_usermeta', $value, $user_id, $key, $user_meta_info );
        $value =  apply_filters( 'uwp_update_usermeta_' . $key, $value, $user_id, $key, $user_meta_info );

        do_action( 'uwp_before_update_usermeta', $user_id, $key, $value, $user_meta_info );


        $value = uwp_maybe_serialize($key, $value);

        if (uwp_str_ends_with($key, '_privacy')) {
            $key = 'user_privacy';
        }

        if (!empty($user_meta_info)) {
            $wpdb->query(
                $wpdb->prepare(

                    "update " . $meta_table . " set {$key} = %s where user_id = %d",
                    array(
                        $value,
                        $user_id
                    )
                )
            );
        } else {
            $wpdb->query(
                $wpdb->prepare(

                    "insert into " . $meta_table . " set {$key} = %s, user_id = %d",
                    array(
                        $value,
                        $user_id
                    )
                )
            );
        }

        return true;
    }

    /**
     * Gets UsersWP user meta row using user ID.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       int|bool            $user_id    User ID.
     * 
     * @return      object|bool                     User meta row object.
     */
    public function get_usermeta_row($user_id = false) {
        if (!$user_id) {
            return false;
        }

        global $wpdb;
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$meta_table} WHERE user_id = %d", $user_id));

        return $row;
    }

    /**
     * Deletes a UsersWP meta row using the user ID.
     *
     * @since       1.0.5
     * @package     UsersWP
     * 
     * @param       int|bool            $user_id        User ID.
     * 
     * @return      void
     */
    public function delete_usermeta_row($user_id = false) {
        if (!$user_id) {
            return;
        }

        global $wpdb;
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
        $wpdb->query($wpdb->prepare("DELETE FROM {$meta_table} WHERE user_id = %d", $user_id));
    }

    /**
     * Syncs WP usermeta with UsersWP usermeta.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       int            $user_id        User ID.
     * 
     * @return      void
     */
    public function sync_usermeta($user_id) {

        $user_data = get_userdata($user_id);

        uwp_update_usermeta($user_id, 'uwp_account_username',       $user_data->user_login);
        uwp_update_usermeta($user_id, 'uwp_account_email',          $user_data->user_email);
        uwp_update_usermeta($user_id, 'uwp_account_display_name',   $user_data->display_name);
        uwp_update_usermeta($user_id, 'uwp_account_first_name',     $user_data->first_name);
        uwp_update_usermeta($user_id, 'uwp_account_last_name',      $user_data->last_name);
        uwp_update_usermeta($user_id, 'uwp_account_bio',            $user_data->description);

    }

    /**
     * Delete UsersWP meta when user get deleted.
     *
     * @since       1.0.5
     * @package     UsersWP
     * 
     * @param       int            $user_id        User ID.
     * 
     * @return      void
     */
    public function delete_usermeta_for_user($user_id) {
        $this->delete_usermeta_row($user_id);
    }

    /**
     * Delete UsersWP meta when user get deleted from subsite of multisite network.
     *
     * @package     UsersWP
     *
     * @param       int            $user_id        User ID.
     *
     * @return      void
     */
    public function remove_user_from_blog($user_id, $blog_id) {
        switch_to_blog( $blog_id );
        $this->delete_usermeta_row($user_id);
        restore_current_blog();
    }

    /**
     * Saves User IP during registration. 
     *
     * @since       1.0.5
     * @package     userswp
     * 
     * @param       array       $result         Validated form result.
     * @param       string      $type           Form Type. 
     * @param       int         $user_id        User ID.
     * 
     * @return      array                       Validated form result.
     */
    public function save_user_ip_on_register($result, $type, $user_id) {
        if ($type == 'register') {
            $ip = uwp_get_ip();
            uwp_update_usermeta($user_id, 'user_ip', $ip);
        }
        return $result;
    }

    /**
     * Saves the users IP on login.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       string      $user_login     The users username.
     * @param       object      $user           The user object WP_User.
     * 
     * @return      void
     */
    public function save_user_ip_on_login( $user_login, $user ) {

        $ip = uwp_get_ip();
        if (isset($user->ID)) {
            uwp_update_usermeta($user->ID, 'user_ip', $ip);    
        }
    }

    /**
     * Modifies privacy value while updating into the db.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $value          Privacy value.
     * 
     * @param       int         $user_id        The User ID.
     * @param       string      $key            Custom field key.
     * @param       object      $user_meta_info User meta row.
     * 
     * @return      string                      Modified privacy field string.
     */
    public function modify_privacy_value_on_update($value, $user_id, $key, $user_meta_info) {
        if (uwp_str_ends_with($key, '_privacy')) {
            $old_value = $user_meta_info->user_privacy;
            if (!empty($old_value)) {
                // Existing serialized value
                if ($value == 'no') {
                    $public_fields = explode(',', $old_value);
                    if (!in_array($key, $public_fields)) {
                        $public_fields[] = $key;
                    }
                    $value = implode(',', $public_fields);
                } else {
                    // Yes value
                    $public_fields = explode(',', $old_value);
                    if(($key = array_search($key, $public_fields)) !== false) {
                        unset($public_fields[$key]);
                    }
                    $value = implode(',', $public_fields);
                }

            } else {
                // New Serialized value
                if ($value == 'no') {
                    $public_fields = array();
                    $public_fields[] = $key;
                    $value = implode(',', $public_fields);
                } else {
                    // For yes values no need to update since its a public field.
                    // We store only the private fields.
                }

            }
        }
        return $value;
    }

    /**
     * Modifies privacy value while fetching from the db.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       string      $value          Privacy value.
     * @param       int         $user_id        The User ID.
     * @param       string      $key            Custom field key.
     * @param       string      $default        Default value.
     * 
     * @return      string                      Modified privacy value.
     */
    public function modify_privacy_value_on_get($value, $user_id, $key, $default, $usermeta) {
        if (uwp_str_ends_with($key, '_privacy')) {
            $value = 'yes';
            if (!empty($usermeta)) {
                $output = $usermeta->user_privacy ? $usermeta->user_privacy : $default;
                if ($output) {
                    $public_fields = explode(',', $output);
                    if (in_array($key, $public_fields)) {
                        $value = 'no';
                    }
                }
            }
        }
        return $value;
    }

    /**
     * Modifies date value from unix timestamp to string while updating into the db.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       int         $value          Unix Timestamp.
     * @param       int         $user_id        The User ID.
     * @param       string      $key            Custom field key.
     * 
     * @return      string                      Date string.
     */
    public function modify_datepicker_value_on_update($value, $user_id, $key) {
        // modify timestamp to date
        if (is_int($value)) {
            $field_info = uwp_get_custom_field_info($key);
            if ($field_info->field_type == 'datepicker') {
                $value = date('Y-m-d', $value);
            }
        }
        return $value;
    }

    /**
     * Modifies date value from string to unix timestamp while fetching from the db.
     *
     * @since       1.0.0
     * @package     userswp
     * 
     * @param       string      $value          Date string.
     * @param       int         $user_id        The User ID.
     * @param       string      $key            Custom field key.
     * 
     * @return      int                         Unix Timestamp.
     */
    public function modify_datepicker_value_on_get($value, $user_id, $key, $default, $usermeta) {
        // modify date to timestamp
        if (is_string($value) && (strpos($value, '-') !== false)) {
            $field_info = uwp_get_custom_field_info($key);
            if (isset($field_info->field_type) && $field_info->field_type == 'datepicker') {
                $value = strtotime($value);
            }
        }
        return $value;
    }

    public function uwp_user_row_actions($actions, $user_object){
        $user_id = $user_object->ID;
        $mod_value = get_user_meta( $user_id, 'uwp_mod', true );
        $delete_link = add_query_arg(
            array(
                'user_id' => $user_id,
                'action'    => 'uwp_resend',
                '_nonce'  => wp_create_nonce('uwp_resend'),
            ),
            admin_url( 'users.php' )
        );
        if ($mod_value == 'email_unconfirmed') {
            $actions['uwp_resend_activation'] = "<a class='' href='" . $delete_link . "'>" . __( 'Resend Activation','ultimate-member') . "</a>";
        }

        return $actions;
    }

    public function uwp_process_user_actions(){
        $user_id = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0;
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
        $nonce = isset($_REQUEST['_nonce']) ? $_REQUEST['_nonce'] : false;

        if($user_id && 'uwp_resend' == $action && wp_verify_nonce( $nonce,'uwp_resend')){
            $send_result = $this->uwp_resend_activation_mail($user_id);
            if(!is_admin()){
                global $uwp_notices;

                if($send_result) {
                    $message = __('Activation email has been sent!', 'userswp');
                } else {
                    $message = __('Error while processing request. Please contact site admin.', 'userswp');
                }
                $uwp_notices[] = '<div class="uwp-alert-success text-center">'.$message.'</div>';
                return;
            }
            if(!$send_result){
                wp_redirect( add_query_arg( 'update', 'err_uwp_resend', admin_url('users.php') ) );
            }
            wp_redirect( add_query_arg( 'update', 'uwp_resend', admin_url('users.php') ) );
            exit();
        }
    }

    public function uwp_users_bulk_actions($bulk_actions){
        $bulk_actions['uwp_resend'] = __( 'Resend Activation', 'userswp');
        return $bulk_actions;
    }

    public function uwp_handle_users_bulk_actions($redirect_to, $doaction, $user_ids){
        if ( 'uwp_resend' !== $doaction ) {
            return $redirect_to;
        }

        foreach ( $user_ids as $user_id ) {
            $this->uwp_resend_activation_mail($user_id);
        }

        $redirect_to = add_query_arg( 'update', 'uwp_resend', $redirect_to );
        return $redirect_to;
    }

    public function uwp_resend_activation_mail($user_id = 0){
        if(!$user_id){
            return false;
        }
        if( 'email_unconfirmed' == get_user_meta( $user_id, 'uwp_mod', true )){
            $email = new UsersWP_Mails();
            $send_result = $email->send( 'activate', $user_id );
            return $send_result;
        }
        return true;
    }

    public function uwp_show_update_messages(){
        if ( !isset($_REQUEST['update']) ) return;

        $update = $_REQUEST['update'];
        $messages = array();

        switch($update) {
            case 'uwp_resend':
                $messages['msg'] = __('Activation email has been sent!','userswp');
                break;
            case 'err_uwp_resend':
                $messages['err_msg'] = __('Error while sending activation email. Please try again.','userswp');
                break;
        }

        if ( !empty( $messages ) ) {
            if ( isset($messages['err_content'])) {
                echo '<div class="notice notice-error"><p>' . $messages['err_msg'] . '</p></div>';
            } else {
                echo '<div class="notice notice-success is-dismissible"><p>' . $messages['msg'] . '</p></div>';
            }
        }
    }

}