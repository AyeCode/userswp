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
    global $uwp_widget_args;

    $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

    $args = array(
        'post_status' => 'publish',
        'posts_per_page' => uwp_get_option('profile_no_of_items', 10),
        'author' => $user->ID,
        'paged' => $paged,
    );

    if ($post_type) {
        $args['post_type'] = $post_type;
    }

    if (is_array($post_ids)) {
        if (!empty($post_ids)) {
            $args['post__in'] = $post_ids;
        } else {
            // no posts found
	        echo aui()->alert(array(
		        'type'=>'info',
		        'content'=> sprintf(__('No %s found', 'userswp'), strtolower($title))
	        ));
            return;
        }
    }
    // The Query
    $the_query = new WP_Query($args);

    $uwp_widget_args['template_args']= array(
        'the_query' => $the_query,
        'user'      => $user,
        'post_type' => $post_type,
        'title'     => $title,
        'post_ids'  => $post_ids,
    );

    $design_style = !empty($uwp_widget_args['design_style']) ? esc_attr($uwp_widget_args['design_style']) : uwp_get_option("design_style",'bootstrap');
    $template = $design_style ? $design_style."/loop-posts" : "loop-posts";
    uwp_locate_template($template);
    
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
        'notifications' => array(
	        'title' => __( 'Notifications', 'userswp' ),
	        'icon' => 'fas fa-bell',
        ),
        'privacy' => array(
	        'title' => __('Privacy', 'userswp'),
	        'icon' => 'fas fa-lock',
        ),
    );

    return apply_filters( 'uwp_account_available_tabs', $tabs );
}

function uwp_profile_add_tabs($tab_data){

	$obj = new UsersWP_Settings_Profile_Tabs();
	$obj->tabs_field_save($tab_data);

}