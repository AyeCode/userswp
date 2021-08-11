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

        if(!$key){
        	return $default;
        }

        global $wpdb;
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

        if (uwp_str_ends_with($key, '_privacy')) {
	        if (uwp_str_ends_with($key, '_tab_privacy')) {
		        $obj_key = $user_id.'_tabs_privacy';
		        $row = wp_cache_get( $obj_key, 'uwp_usermeta_tabs_privacy' );
		        if ( ! $row ) {
			        $row = $wpdb->get_row($wpdb->prepare("SELECT tabs_privacy FROM {$meta_table} WHERE user_id = %d", $user_id), ARRAY_A);
			        wp_cache_set( $obj_key, $row, 'uwp_usermeta_tabs_privacy' );
		        }

		        $value = false;
		        if (!empty($row)) {
			        $public_fields = isset($row['tabs_privacy']) ? maybe_unserialize($row['tabs_privacy']) : $default;
			        $public_fields_keys = is_array($public_fields) ? array_keys($public_fields) : $public_fields;
			        if (is_array($public_fields) && in_array($key, $public_fields_keys)) {
				        $value = $public_fields[$key];
			        }
		        }
	        } else {
		        $obj_key = $user_id.'_user_privacy';
		        $row = wp_cache_get( $obj_key, 'uwp_usermeta_user_privacy' );
		        if ( ! $row ) {
			        $row = $wpdb->get_row($wpdb->prepare("SELECT user_privacy FROM {$meta_table} WHERE user_id = %d", $user_id), ARRAY_A);
			        wp_cache_set( $obj_key, $row, 'uwp_usermeta_user_privacy' );
		        }

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

	        if (!$user_data) {
		        return $value;
	        }

            switch ($key){
                case 'email': $value = $user_data->user_email; break;
                case 'username': $value = $user_data->user_login; break;
                case 'user_nicename': $value = $user_data->user_nicename; break;
                case 'bio': $value = $user_data->description; break;
                default :
					$obj_key = $user_id.'_'.$key;
	                $row = wp_cache_get( $obj_key, 'uwp_usermeta' );
	                if ( ! $row ) {
	                	if(uwp_column_exist($meta_table, $key)){
			                $row = $wpdb->get_row($wpdb->prepare("SELECT {$key} FROM {$meta_table} WHERE user_id = %d", $user_id), ARRAY_A);
			                wp_cache_set( $obj_key, $row, 'uwp_usermeta' );
		                }
	                }

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
    public function update_usermeta( $user_id, $key, $value ) {

        if (!$user_id || !$key ) {
            return false;
        }

        global $wpdb;
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
	    $cache_group = 'uwp_usermeta';
	    $obj_key = $user_id . '_' . $key;

	    if (uwp_str_ends_with($key, '_privacy')) {
		    if ( 'tabs_privacy' == $key ) {
			    $obj_key = $user_id . '_tabs_privacy';
				$cache_group = 'uwp_usermeta_tab_privacy';
		    } elseif('user_privacy' == $key) {
			    $obj_key = $user_id . '_user_privacy';
			    $cache_group = 'uwp_usermeta_user_privacy';
		    }
	    }

        $user_meta_info = $wpdb->get_col( $wpdb->prepare( "SELECT $key FROM $meta_table WHERE user_id = %d", $user_id ) );

        $value = apply_filters( 'uwp_update_usermeta', $value, $user_id, $key, $user_meta_info );
        $value =  apply_filters( 'uwp_update_usermeta_' . $key, $value, $user_id, $key, $user_meta_info );

        do_action( 'uwp_before_update_usermeta', $user_id, $key, $value, $user_meta_info );

        $value = uwp_maybe_serialize($key, $value);

        if (!empty($user_meta_info)) {
	        $result = $wpdb->update(
                $meta_table,
                array($key => $value),
                array('user_id' => $user_id),
                array('%s'),
                array('%d')
            );

	        if ( ! $result ) {
		        return false;
	        }

        } else {
	        $result = $wpdb->insert(
                $meta_table,
                array('user_id' => $user_id, $key => $value)
            );

	        if ( ! $result ) {
		        return false;
	        }
        }

	    wp_cache_delete( $obj_key, $cache_group );

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

	    $row = wp_cache_get( $user_id, 'uwp_usermeta_row' );
	    if ( ! $row ) {
		    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$meta_table} WHERE user_id = %d", $user_id));
		    wp_cache_set( $user_id, $row, 'uwp_usermeta_row' );
	    }

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

        $count = $wpdb->query($wpdb->prepare("DELETE FROM {$meta_table} WHERE user_id = %d", $user_id));

	    if ( ! $count ) {
		    return false;
	    }

	    wp_cache_delete( $user_id, 'uwp_usermeta_row' );

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
	    $field_info = uwp_get_custom_field_info($key);
	    if (isset($field_info->field_type) && $field_info->field_type == 'datepicker') {
		    if (is_string($value) && (strpos($value, '-') !== false) && $value != '0000-00-00') {
			    $value = strtotime( $value );
		    } elseif($value === '0000-00-00'){$value = '';}
	    }

        return $value;
    }

	/**
	 * Make UWP user meta available through the standard get_user_meta() function if prefixed with `user_`
	 *
	 * @param $metadata
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return bool|mixed|null|string
	 */
	public static function dynamically_add_user_meta( $metadata, $object_id, $meta_key, $single ) {

		if ( strpos( $meta_key, 'uwp_meta_' ) === 0 ) {
			$meta_key = substr( $meta_key, 9 );
			$maybe_meta = uwp_get_usermeta( $object_id, $meta_key, $single );
			if ( ! empty( $maybe_meta ) ) {
				$metadata = $maybe_meta;
			}
		}

		return $metadata;
	}

}