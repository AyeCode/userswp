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

        global $wpdb;
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

        if (uwp_str_ends_with($key, '_privacy')) {
	        if (uwp_str_ends_with($key, '_tab_privacy')) {
		        $row = $wpdb->get_row($wpdb->prepare("SELECT tabs_privacy FROM {$meta_table} WHERE user_id = %d", $user_id), ARRAY_A);
		        $value = false;
		        if (!empty($row)) {
			        $public_fields = isset($row['tabs_privacy']) ? maybe_unserialize($row['tabs_privacy']) : $default;
			        $public_fields_keys = is_array($public_fields) ? array_keys($public_fields) : $public_fields;
			        if (is_array($public_fields) && in_array($key, $public_fields_keys)) {
				        $value = $public_fields[$key];
			        }
		        }
	        } else {
		        $row = $wpdb->get_row($wpdb->prepare("SELECT user_privacy FROM {$meta_table} WHERE user_id = %d", $user_id), ARRAY_A);
		        $value = 'yes';
		        if (!empty($row)) {
			        $output = isset($row['user_privacy']) ? $row['user_privacy'] : $default;
			        $public_fields = explode(',', $output);
			        if (in_array($key, $public_fields)) {
				        $value = 'no';
			        }
		        }
	        }
        } else {
            $value = null;
            $user_data = get_userdata($user_id);

            switch ($key){
                case 'email': $value = $user_data->user_email; break;
                case 'username': $value = $user_data->user_login; break;
                case 'user_nicename': $value = $user_data->user_nicename; break;
                case 'bio': $value = $user_data->description; break;
                default :
                    $row = $wpdb->get_row($wpdb->prepare("SELECT {$key} FROM {$meta_table} WHERE user_id = %d", $user_id), ARRAY_A);
                    if (!empty($row)) {
                        $value = isset($row[$key]) ? $row[$key] : $default;
                    } else {
                        $value = $default;
                    }
                    break;
            }
        }

        $value = uwp_maybe_unserialize($key, $value);
        $value = wp_unslash($value);
        $value = apply_filters( 'uwp_get_usermeta', $value, $user_id, $key, $default );
        return apply_filters( 'uwp_get_usermeta_' . $key, $value, $user_id, $key, $default );
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
        $user_meta_info = $wpdb->get_col( $wpdb->prepare( "SELECT $key FROM $meta_table WHERE user_id = %d", $user_id ) );

        $value = apply_filters( 'uwp_update_usermeta', $value, $user_id, $key, $user_meta_info );
        $value =  apply_filters( 'uwp_update_usermeta_' . $key, $value, $user_id, $key, $user_meta_info );

        do_action( 'uwp_before_update_usermeta', $user_id, $key, $value, $user_meta_info );

        $value = uwp_maybe_serialize($key, $value);

        if (!empty($user_meta_info)) {
            $wpdb->update(
                $meta_table,
                array($key => $value),
                array('user_id' => $user_id),
                array('%s'),
                array('%d')
            );
        } else {
            $wpdb->insert(
                $meta_table,
                array('user_id' => $user_id, $key => $value)
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

        global $wpdb;
        $user_data = get_userdata($user_id);
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

        $user_meta = array(
            'username' => $user_data->user_login,
            'email' => $user_data->user_email,
            'display_name' => $user_data->display_name,
            'first_name' => $user_data->first_name,
            'last_name' => $user_data->last_name,
            'user_url' => $user_data->user_url,
            'bio' => $user_data->description,
        );

        foreach ($user_meta as $key => $meta){
            do_action('sync_usermeta_on_register', $user_id, $key, $meta); // for adding points via mycred add on.
        }

        $users = $wpdb->get_var($wpdb->prepare("SELECT COUNT(user_id) FROM {$meta_table} WHERE user_id = %d", $user_id));

        if($users){
            $wpdb->update(
                $meta_table,
                $user_meta,
                array('user_id' => $user_id)
            );
        } else {
            $user_meta['user_id'] = $user_id;
            $wpdb->insert(
                $meta_table,
                $user_meta
            );
        }

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
        if (isset($user->ID)) {
	        $ip = uwp_get_ip();
            uwp_update_usermeta($user->ID, 'user_ip', $ip);    
        }
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
    public function modify_datepicker_value_on_get($value, $user_id, $key, $default) {
        // modify date to timestamp
        if (is_string($value) && (strpos($value, '-') !== false) && $value != '0000-00-00') {
            $field_info = uwp_get_custom_field_info($key);
            if (isset($field_info->field_type) && $field_info->field_type == 'datepicker') {
                $value = strtotime($value);
            }
        }elseif($value == '0000-00-00'){$value = '';}
        return $value;
    }

	/**
	 * Returns user row actions
	 *
	 * @package     userswp
	 *
	 * @param       array      $actions          Date string.
	 * @param       object     $user_object      The User ID.
	 *
	 * @return      array   Row actions.
	 */
    public function user_row_actions($actions, $user_object){
        $user_id = $user_object->ID;
        $mod_value = get_user_meta( $user_id, 'uwp_mod', true );
        $resend_link = add_query_arg(
            array(
                'user_id' => $user_id,
                'action'    => 'uwp_resend',
                '_nonce'  => wp_create_nonce('uwp_resend'),
            ),
            admin_url( 'users.php' )
        );

        $activate_link = add_query_arg(
            array(
                'user_id' => $user_id,
                'action'    => 'uwp_activate_user',
                '_nonce'  => wp_create_nonce('uwp_activate_user'),
            ),
            admin_url( 'users.php' )
        );

        if ($mod_value == 'email_unconfirmed') {
            $actions['uwp_resend_activation'] = "<a class='' href='" . $resend_link . "'>" . __( 'Resend Activation','userswp') . "</a>";
            $actions['uwp_auto_activate'] = "<a class='' href='" . $activate_link . "'>" . __( 'Activate User','userswp') . "</a>";
        }

        return $actions;
    }

	/**
	 * Processes user action
	 *
	 * @package     userswp
	 *
	 * @return      mixed
	 */
    public function process_user_actions(){
        $user_id = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0;
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
        $nonce = isset($_REQUEST['_nonce']) ? $_REQUEST['_nonce'] : false;

        if($user_id && 'uwp_resend' == $action && wp_verify_nonce( $nonce,'uwp_resend')){
            $send_result = $this->resend_activation_mail($user_id);
            if(!is_admin()){
                global $uwp_notices;

                if($send_result) {
                    $message = __('Activation email has been sent!', 'userswp');
	                $uwp_notices[] = aui()->alert(array(
		                'type'=>'success',
		                'content'=> $message
	                ));
                } else {
                    $message = __('Error while processing request. Please contact site admin.', 'userswp');
	                $uwp_notices[] = aui()->alert(array(
		                'type'=>'error',
		                'content'=> $message
	                ));
                }
                //$uwp_notices[] = '<div class="uwp-alert-success text-center">'.$message.'</div>';
                return;
            }
            if(!$send_result){
                wp_redirect( add_query_arg( 'update', 'err_uwp_resend', admin_url('users.php') ) );
            }
            wp_redirect( add_query_arg( 'update', 'uwp_resend', admin_url('users.php') ) );
            exit();
        } elseif($user_id && 'uwp_activate_user' == $action && wp_verify_nonce( $nonce,'uwp_activate_user')){
            if(is_admin() && current_user_can('edit_users')){
                $this->activate_user($user_id);
                wp_redirect( add_query_arg( 'update', 'uwp_activate_user', admin_url('users.php') ) );
            }
        }
    }

	/**
	 * Returns users bulk actions
	 *
	 * @package     userswp
	 *
	 * @param       array      $bulk_actions    Bulk actions.
	 *
	 * @return      array   Bulk actions.
	 */
    public function users_bulk_actions($bulk_actions){
        $bulk_actions['uwp_resend'] = __( 'Resend Activation', 'userswp');
        $bulk_actions['uwp_activate_user'] = __( 'Approve User(s)', 'userswp');
        return $bulk_actions;
    }

	/**
	 * Handles users bulk actions
	 *
	 * @package     userswp
	 *
	 * @param       string      $redirect_to    Bulk actions.
	 * @param       string      $doaction    Current action.
	 * @param       array      $user_ids    User IDs to process.
	 *
	 * @return      string   Redirect URL.
	 */
    public function handle_users_bulk_actions($redirect_to, $doaction, $user_ids){
        if ( 'uwp_resend' == $doaction ) {
            foreach ( $user_ids as $user_id ) {
                $this->resend_activation_mail($user_id);
            }

            $redirect_to = add_query_arg( 'update', 'uwp_resend', $redirect_to );
        } elseif('uwp_activate_user' == $doaction){
            foreach ( $user_ids as $user_id ) {
                $this->activate_user($user_id);
            }
            $redirect_to = add_query_arg( 'update', 'uwp_activate_user', $redirect_to );
        }

        return $redirect_to;
    }

	/**
	 * Sends activation email to user
	 *
	 * @package     userswp
	 *
	 * @param       int      $user_id    User ID.
	 *
	 * @return      bool
	 */
    public function resend_activation_mail($user_id = 0){
        if(!$user_id){
            return false;
        }
        if( 'email_unconfirmed' == get_user_meta( $user_id, 'uwp_mod', true )){
	        $user_data = get_userdata($user_id);

	        $activation_link = uwp_get_activation_link($user_id);

	        if($activation_link){

		        $message = __('To activate your account, visit the following address:', 'userswp') . "\r\n\r\n";

		        $message .= "<a href='".esc_url($activation_link)."' target='_blank'>".esc_url($activation_link)."</a>" . "\r\n";

		        $activate_message = '<p><b>' . __('Please activate your account :', 'userswp') . '</b></p><p>' . $message . '</p>';

		        $activate_message = apply_filters('uwp_activation_mail_message', $activate_message, $user_id);

		        $email_vars = array(
			        'user_id' => $user_id,
			        'login_details' => $activate_message,
			        'activation_link' => $activation_link,
		        );

		        $send_result = UsersWP_Mails::send($user_data->user_email, 'registration_activate', $email_vars);

		        return $send_result;
	        }
        }
        return true;
    }

	/**
	 * Activates user
	 *
	 * @param int $user_id User ID
	 *
	 * @return bool
	 */
    public function activate_user($user_id = 0){
        if(!$user_id){
            return false;
        }

        if( 'email_unconfirmed' == get_user_meta( $user_id, 'uwp_mod', true )){
            update_user_meta( $user_id, 'uwp_mod', '' );
        }

        return true;
    }

	/**
	 * Displays update messages
	 */
    public function show_update_messages(){
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
            case 'uwp_activate_user':
                $messages['msg'] = __('User(s) has been activated!','userswp');
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