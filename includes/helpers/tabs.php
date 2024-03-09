<?php
/**
 * Checks the current page is a UsersWP profile tab page.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string|bool        $tab     Tab slug.
 * @return      bool                        True when success. False when failure.
 */
function is_uwp_profile_tab($tab = false) {
    global $wp_query;
    if (is_uwp_profile_page()) {
        if (isset($wp_query->query_vars['uwp_tab']) && !empty($wp_query->query_vars['uwp_tab'])) {
            if ($wp_query->query_vars['uwp_tab'] == $tab) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Checks the current page is a UsersWP profile subtab page.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string|bool        $subtab     Subtab slug.
 * @return      bool                           True when success. False when failure.
 */
function is_uwp_profile_subtab($subtab = false) {
    global $wp_query;
    if (is_uwp_profile_page()) {
        if (isset($wp_query->query_vars['uwp_subtab']) && !empty($wp_query->query_vars['uwp_subtab'])) {
            if ($wp_query->query_vars['uwp_subtab'] == $subtab) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Prints the tab content based on post type.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       object          $user       User object.
 * @param       bool            $post_type  Post type.
 * @param       string          $title      Tab title
 * @param       array|bool      $post_ids   Post ids for post__in. Optional
 *
 * @return      void
 */
function uwp_generic_tab_content($user, $post_type = false, $title = '', $post_ids = false) {

    $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

    $query_args = array(
        'post_status' => 'publish',
        'posts_per_page' => uwp_get_option('profile_no_of_items', 10),
        'author' => $user->ID,
        'paged' => $paged,
    );

    if ($post_type) {
	    $query_args['post_type'] = $post_type;
    }

	if (is_array($post_ids)) {
		if (!empty($post_ids)) {
			$query_args['post__in'] = $post_ids;
		} else {
			// no posts found
			echo aui()->alert( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'type'=>'info',
				'content'=> esc_html( wp_sprintf( __( 'No %s found', 'userswp' ), strtolower( $title ) ) )
			) );

			return;
		}
	}

    // The Query
    $the_query = new WP_Query($query_args);

    $args = array();
	$args['template_args']= array(
        'the_query' => $the_query,
        'user'      => $user,
        'post_type' => $post_type,
        'title'     => $title,
        'post_ids'  => $post_ids,
    );

    $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
    $template = $design_style ? $design_style."/loop-posts.php" : "loop-posts.php";
	uwp_get_template($template, $args);
    
}


/**
 * Adds Privacy tab to available account tabs if privacy enabled.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Account Tabs.
 */
function uwp_account_get_available_tabs() {

    $tabs = array(
        'account' => array(
            'title' => __( 'Edit Account', 'userswp' ),
            'icon' => 'fas fa-user',
        ),
        'change-password' => array(
	        'title' => __( 'Change Password', 'userswp' ),
	        'icon' => 'fas fa-asterisk',
        ),
        'notifications' => array(
	        'title' => __( 'Notifications', 'userswp' ),
	        'icon' => 'fas fa-envelope',
        ),
        'privacy' => array(
	        'title' => __('Privacy', 'userswp'),
	        'icon' => 'fas fa-lock',
        ),
    );

	$tabs = apply_filters( 'uwp_account_available_tabs', $tabs );

	if(class_exists('\WP2FA\WP2FA')){
		if(1 != uwp_get_option('disable_wp_2fa')) {
			$tabs['wp2fa'] = array(
				'title' => __( 'WP - 2FA', 'userswp' ),
				'icon'  => 'fas fa-user-lock',
			);
		}
	}

	// Keep delete account and logout last
	if(1 != uwp_get_option('disable_account_delete') && !current_user_can('administrator')){
		$tabs['delete-account'] = array(
			'title' => __('Delete Account', 'userswp'),
			'icon' => 'fas fa-user-times',
		);
	}

	$template = new UsersWP_Templates();
	$logout_url = $template->uwp_logout_url();

	$tabs['logout'] = array(
		'title' => __('Logout', 'userswp'),
		'icon' => 'fas fa-sign-out-alt',
		'link' => $logout_url,
	);

	return apply_filters( 'uwp_account_all_tabs', $tabs );
}

function uwp_profile_add_tabs($tab_data){

	$obj = new UsersWP_Settings_Profile_Tabs();
	$obj->tabs_field_save($tab_data);

}

function uwp_get_tabs_privacy_by_user($user){
	global $wpdb;

	if(is_integer($user)){
		$user = get_userdata($user);
	}

	$tabs_privacy = array();
	$meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
	$obj_key = $user->ID.'_tabs_privacy';
	$user_meta_info = wp_cache_get( $obj_key, 'uwp_usermeta_tabs_privacy' );

	if ( ! $user_meta_info ) {
		$user_meta_info = $wpdb->get_row($wpdb->prepare("SELECT tabs_privacy FROM {$meta_table} WHERE user_id = %d", $user->ID), ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		wp_cache_set( $obj_key, $user_meta_info, 'uwp_usermeta_tabs_privacy' );
	}

	if(isset($user_meta_info['tabs_privacy']) && !empty($user_meta_info['tabs_privacy'])){
		$tabs_privacy = maybe_unserialize($user_meta_info['tabs_privacy']);
	}

	return $tabs_privacy;
}