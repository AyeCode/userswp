<?php
/**
 * UsersWP Page related functions
 *
 * All UsersWP page related functions can be found here.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Pages {
    
    /**
     * Checks whether the current page is of given page type or not.
     *
     * @since   1.0.0
     * @package userswp
     * @param   string|bool $type Page type.
     * @return bool
     */
    public function is_page($type = false) {
        if (is_page()) {
            global $post;
            $current_page_id = isset($post->ID) ? absint($post->ID) : '';
            if($current_page_id){
                if ($type) {
                    $uwp_page = uwp_get_page_id($type, false);
                    if ( $uwp_page && ((int) $uwp_page ==  $current_page_id ) ) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if ($this->is_register_page() ||
                        $this->is_login_page() ||
                        $this->is_forgot_page() ||
                        $this->is_change_page() ||
                        $this->is_reset_page() ||
                        $this->is_account_page() ||
                        $this->is_profile_page() ||
                        $this->is_users_page() ||
                        $this->is_user_item_page()) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        } else {
            return false;
        }

        return false;
    }

    /**
     * Checks whether the current page is register page or not.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function is_register_page() {
        return $this->is_page('register_page');
    }

    /**
     * Checks whether the current page is login page or not.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function is_login_page() {
        return $this->is_page('login_page');
    }

    /**
     * Checks whether the current page is forgot password page or not.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function is_forgot_page() {
        return $this->is_page('forgot_page');
    }

    /**
     * Checks whether the current page is change password page or not.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function is_change_page() {
        return $this->is_page('change_page');
    }

    /**
     * Checks whether the current page is reset password page or not.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function is_reset_page() {
        return $this->is_page('reset_page');
    }

    /**
     * Checks whether the current page is account page or not.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function is_account_page() {
        return $this->is_page('account_page');
    }

    /**
     * Checks whether the current page is profile page or not.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function is_profile_page() {
        return $this->is_page('profile_page');
    }

    /**
     * Checks whether the current page is users page or not.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function is_users_page() {
        return $this->is_page('users_page');
    }

    /**
     * Checks whether the current page is users page or not.
     *
     * @since       1.1.2
     * @package     userswp
     * @return      bool
     */
    public function is_user_item_page() {
        return $this->is_page('user_list_item_page');
    }

    /**
     * Checks whether the current page is logged in user profile page or not.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function is_current_user_profile_page() {
        if (is_user_logged_in() &&
            $this->is_profile_page()
        ) {
            $author_slug = get_query_var('uwp_profile');
            if ($author_slug) {
                $url_type = apply_filters('uwp_profile_url_type', 'slug');
                if ($url_type == 'id') {
                    $user = get_user_by('id', $author_slug);
                } else {
                    $user = get_user_by('slug', $author_slug);
                }

                if ($user && $user->ID == get_current_user_id()) {
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
     * Returns all available pages as array to use in select dropdown.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      array                      Page array.
     */
    public function get_pages() {
        $pages_options = array( '' => __( 'Select a Page', 'userswp' ) ); // Blank option

        $pages = get_pages();
        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_options[ $page->ID ] = $page->post_title;
            }
        }
        return $pages_options;
    }

    /**
     * Gets the page slug using the given page type.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $page_type      Page type.
     * @return      string                      Page slug.
     */
    public function get_page_slug($page_type = 'register_page') {
        $page_id = uwp_get_page_id($page_type, 0);
        if ($page_id) {
            $slug = get_post_field( 'post_name', get_post($page_id) );
        } else {
            $slug = false;
        }
        return $slug;

    }

    /**
     * Creates UsersWP page if not exists.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $slug           Page slug.
     * @param       string      $option         Page setting key.
     * @param       string      $page_title     The post title.  Default empty.
     * @param       mixed       $page_content   The post content. Default empty.
     * @param       int         $post_parent    Set this for the post it belongs to, if any. Default 0.
     * @param       string      $status         The post status. Default 'draft'.
     *
     * @return int Page ID
     */
    public function create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0, $status = 'publish') {
        global $wpdb, $current_user;

        $settings = get_option( 'uwp_settings', array());
        if (isset($settings[$option])) {
            $option_value = $settings[$option];
        } else {
            $option_value = false;
        }

        if ($option_value > 0 && ( $page_object = get_post( $option_value ) )) {
            if ('page' === $page_object->post_type && !in_array($page_object->post_status, array('pending', 'trash', 'future', 'auto-draft'))) {
                // Valid page is already in place
                return $page_object->ID;
            }
        }

        if(!empty($post_parent)){
            $page = get_page_by_path($post_parent);
            if ($page) {
                $post_parent = $page->ID;
            } else {
                $post_parent = '';
            }
        }

        if ( strlen( $page_content ) > 0 ) {
            // Search for an existing page with the specified page content (typically a shortcode)
            $valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
            $trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
        } else {
            // Search for an existing page with the specified page slug
            $valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
            $trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
        }

        $valid_page_found = apply_filters( 'uwp_create_page_id', $valid_page_found, $slug, $page_content );

        if ( $valid_page_found ) {
            if ( $option ) {
                $settings[$option] = $valid_page_found;
                update_option( 'uwp_settings', $settings );
            }
            return $valid_page_found;
        }

        if ( $trashed_page_found ) {
            $page_id   = $trashed_page_found;
            $page_data = array(
                'ID'             => $page_id,
                'post_status'    => 'publish',
                'post_parent'    => $post_parent,
            );
            wp_update_post( $page_data );
        } else {
            $page_data = array(
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_author'    => $current_user->ID,
                'post_name'      => $slug,
                'post_title'     => $page_title,
                'post_content'   => $page_content,
                'post_parent'    => $post_parent,
                'comment_status' => 'closed',
            );
            $page_id = wp_insert_post( $page_data );
        }

        if ( $option ) {
            $settings[$option] = $page_id;
            update_option('uwp_settings', $settings);
        }

        return $page_id;
    }

    /**
     * Generates default UsersWP pages. Usually called during plugin activation.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function generate_default_pages() {
        
        $this->create_page(esc_sql(_x('register',   'page_slug', 'userswp')), 'register_page',  __('Register',          'userswp'), '[uwp_register]');
        $this->create_page(esc_sql(_x('login',      'page_slug', 'userswp')), 'login_page',     __('Login',             'userswp'), '[uwp_login]');
        $this->create_page(esc_sql(_x('account',    'page_slug', 'userswp')), 'account_page',   __('Account',           'userswp'), '[uwp_account]');
        $this->create_page(esc_sql(_x('forgot',     'page_slug', 'userswp')), 'forgot_page',    __('Forgot Password?',  'userswp'), '[uwp_forgot]');
        $this->create_page(esc_sql(_x('reset',      'page_slug', 'userswp')), 'reset_page',     __('Reset Password',    'userswp'), '[uwp_reset]');
        $this->create_page(esc_sql(_x('change',     'page_slug', 'userswp')), 'change_page',    __('Change Password',   'userswp'), '[uwp_change]');
        $this->create_page(esc_sql(_x('profile',    'page_slug', 'userswp')), 'profile_page',   __('Profile',           'userswp'), '[uwp_profile]');
        $this->create_page(esc_sql(_x('users',      'page_slug', 'userswp')), 'users_page',     __('Users',             'userswp'), '[uwp_users]');
        $this->create_page(esc_sql(_x('user-list-item',  'page_slug', 'userswp')), 'user_list_item_page', __('Users List Item', 'userswp'), '[uwp_users_item]');

    }

    
    /**
     * Generates default UsersWP pages on new wpmu blog creation.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       int         $blog_id        Blog ID.
     */
    public function wpmu_generate_default_pages_on_new_site( $blog_id ) {

        if (uwp_get_installation_type() != 'multi_na_all') {
            return;
        }

        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

        // Bail if plugin is not network activated.
        if ( ! is_plugin_active_for_network( 'userswp/userswp.php' ) ) {
            return;
        }

        // Switch to the new blog.
        switch_to_blog( $blog_id );

	    $this->generate_default_pages();

        // Restore original blog.
        restore_current_blog();
    }

    /**
     * Gets the UsersWP page permalink based on page type.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string     $page       Page type.
     * @return      string                 Page permalink.
     */
    public function get_page_link($page) {
        $link = "";
        switch ($page) {
            case 'register':
                $page_id = uwp_get_page_id('register_page', false);
                break;

            case 'login':
                $page_id = uwp_get_page_id('login_page', false);
                break;

            case 'forgot':
                $page_id = uwp_get_page_id('forgot_page', false);
                break;

            case 'account':
                $page_id = uwp_get_page_id('account_page', false);
                break;

            case 'profile':
                $page_id = uwp_get_page_id('profile_page', false);
                break;

            case 'users':
                $page_id = uwp_get_page_id('users_page', false);
                break;

	        default:
		        $page_id = uwp_get_page_id($page, false);
		        break;
        }

        if ($page_id) {
            $link = get_permalink($page_id);
        }

	    if ( ! empty( $link ) && function_exists( 'PLL' ) ) {
		    $link = PLL()->links_model->add_language_to_link( $link, PLL()->curlang );
	    }

        return $link;
    }

    /**
     * Builds the profile page url based on the tab and sub tab given
     * yoursite.com/profile/username
     * yoursite.com/profile/username/tab
     * yoursite.com/profile/username/tab/subtab
     * 
     * @since       1.0.0
     * @package     userswp
     * @param       int             $user_id            User ID.
     * @param       string|bool     $tab                Optional. Main tab
     * @param       string|bool     $subtab             Optional. Sub tab.
     * @return      string                              Built profile page link.
     */
    public function build_profile_tab_url($user_id, $tab = false, $subtab = false) {

	    $link = apply_filters('uwp_profile_link', get_author_posts_url($user_id), $user_id);

        if ($link != '') {
            if (isset($_REQUEST['page_id'])) {
                $permalink_structure = 'DEFAULT';
            } else {
                $permalink_structure = 'CUSTOM';
                $link = rtrim($link, '/') . '/';
            }

            if ('DEFAULT' == $permalink_structure) {
                $link = add_query_arg(
                    array(
                        'uwp_tab' => $tab,
                        'uwp_subtab' => $subtab
                    ),
                    $link
                );
            } else {
                if ($tab) {
                    $link = $link . $tab;
                }

                if ($subtab) {
                    $link = $link .'/'.$subtab;
                }
            }
        }

        return trailingslashit( $link );
    }

	/**
	 * To get the page ID
	 *
	 * @param      $type
	 * @param bool $get_link
	 *
	 * @return bool|false|int|NULL|string
	 */
    public function get_page_id($type, $get_link = false) {

        $link = false;
        $page_id = uwp_get_option($type, false, false);

        if ($page_id && $page_id > 0) {
            if (uwp_is_wpml()) {
                $wpml_page_id = uwp_wpml_object_id($page_id, 'page', true);
                if (!empty($wpml_page_id)) {
                    $page_id = $wpml_page_id;
                }
            }
            $link = $page_id;

            if($get_link){
                $link = get_permalink($page_id);
            }
        }

        return $link;
    }

    /**
     * Add a post display state for special UWP pages in the page list table.
     *
     * @param array   $post_states An array of post display states.
     * @param WP_Post $post        The current post object.
     *
     * @return mixed
     */
    public function add_display_post_states( $post_states, $post ) {
        if ( uwp_get_page_id( 'register_page' ) == $post->ID ) {
            $post_states['uwp_register_page'] = __( 'UWP Register Page', 'userswp' );
        }

        if ( uwp_get_page_id( 'login_page' ) == $post->ID ) {
            $post_states['uwp_login_page'] = __( 'UWP Login Page', 'userswp' );
        }

        if ( uwp_get_page_id( 'forgot_page' ) == $post->ID ) {
            $post_states['uwp_forgot_page'] = __( 'UWP Forgot Password Page', 'userswp' );
        }

        if ( uwp_get_page_id( 'reset_page' ) == $post->ID ) {
            $post_states['uwp_reset_page'] = __( 'UWP Reset Password Page', 'userswp' );
        }

        if ( uwp_get_page_id( 'account_page' ) == $post->ID ) {
            $post_states['uwp_account_page'] = __( 'UWP Account Page', 'userswp' );
        }

        if ( uwp_get_page_id( 'profile_page' ) == $post->ID ) {
            $post_states['uwp_profile_page'] = __( 'UWP Profile Page', 'userswp' );
        }

        if ( uwp_get_page_id( 'users_page' ) == $post->ID ) {
            $post_states['uwp_users_page'] = __( 'UWP Users Page', 'userswp' );
        }

        if ( uwp_get_page_id( 'user_list_item_page' ) == $post->ID ) {
            $post_states['uwp_user_list_item_page'] = __( 'UWP User Item Page', 'userswp' );
        }

        if ( uwp_get_page_id( 'change_page' ) == $post->ID ) {
            $post_states['uwp_change_page'] = __( 'UWP Change Password Page', 'userswp' );
        }

        return $post_states;
    }

}