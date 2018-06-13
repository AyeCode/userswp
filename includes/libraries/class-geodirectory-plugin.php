<?php
/**
 * GeoDirectory plugin related functions
 *
 * This class defines all code necessary for GeoDirectory plugin.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_GeoDirectory_Plugin {
    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof UsersWP_GeoDirectory_Plugin ) ) {
            self::$instance = new UsersWP_GeoDirectory_Plugin;
            self::$instance->setup_globals();
            self::$instance->includes();
            self::$instance->setup_actions();
        }

        return self::$instance;
    }

    private function __construct() {
        self::$instance = $this;
    }

    private function setup_globals() {
        
    }
    
    private function setup_actions() {
        if ( is_admin() ) {
            add_filter( 'uwp_display_form_title', array( $this, 'display_form_title' ), 10, 3 );
            add_action( 'uwp_geodirectory_settings_main_tab_content', array( $this, 'main_tab_content' ), 10, 1 );
            add_action( 'uwp_admin_sub_menus', array( $this, 'add_admin_gd_sub_menu' ), 10, 1 );
            add_filter( 'uwp_settings_tabs', array( $this, 'add_gd_tab' ) );
            add_filter( 'uwp_registered_settings', array( $this, 'add_gd_settings' ) );
            add_filter( 'uwp_available_tab_items', array( $this, 'available_tab_items' ) );
        } else {
            add_filter( 'uwp_profile_tabs', array( $this, 'add_profile_gd_tabs' ), 10, 3 );
            add_action( 'uwp_profile_listings_tab_content', array( $this, 'add_profile_listings_tab_content' ) );
            add_action( 'uwp_profile_reviews_tab_content', array( $this, 'add_profile_reviews_tab_content' ) );
            add_action( 'uwp_profile_favorites_tab_content', array( $this, 'add_profile_favorites_tab_content' ) );
            add_action( 'uwp_profile_invoices_tab_content', array( $this, 'add_profile_invoices_tab_content' ) );
            add_action( 'uwp_profile_gd_listings_subtab_content', array( $this, 'gd_get_listings' ), 10, 2 );
            add_action( 'uwp_profile_gd_reviews_subtab_content', array( $this, 'gd_get_reviews' ), 10, 2 );
            add_action( 'uwp_profile_gd_favorites_subtab_content', array( $this, 'gd_get_favorites' ), 10, 2 );
            //add_action( 'geodir_after_edit_post_link_on_listing', array( $this, 'geodir_add_post_status_author_page' ), 11 );
        }
        add_filter( 'geodir_login_url', array( $this, 'get_gd_login_url' ), 10, 2 );
        add_action( 'wp', array( $this, 'geodir_uwp_author_redirect' ) );
        add_filter( 'geodir_post_status_is_author_page', array( $this, 'geodir_post_status_is_author_page' ) );
        add_filter( 'gd_login_wid_login_placeholder', array( $this, 'gd_login_wid_login_placeholder' ) );
        add_filter( 'gd_login_wid_login_name', array( $this, 'gd_login_wid_login_name' ) );
        add_filter( 'gd_login_wid_login_pwd', array( $this, 'gd_login_wid_login_pwd' ) );
        add_action( 'login_form', array( $this, 'gd_login_inject_nonce' ) );
        add_filter( 'uwp_check_redirect_author_page', array( $this, 'check_redirect_author_page' ), 10, 1 );
        add_filter( 'uwp_use_author_page_content', array( $this, 'skip_uwp_author_page' ), 10, 1 );

        do_action( 'uwp_gd_setup_actions', $this );
    }

    private function includes() {
        do_action( 'uwp_gd_include_files' );

        if ( is_admin() ) {
            do_action( 'uwp_gd_include_admin_files' );
        }
    }

    /**
     * Modifies the settings form title.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       string      $title         Original title.
     * @param       string      $page          admin.php?page=uwp_xxx.
     * @param       string      $active_tab    active tab in that settings page.
     *
     * @return      string      Form title.
     */
    public function display_form_title( $title, $page, $active_tab ) {
        if ( $page == 'uwp_geodirectory' && $active_tab == 'main' ) {
            $title = __( 'GeoDirectory Settings', 'userswp' );
        }
        return $title;
    }
    
    /**
     * Prints the settings form.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       string      $form         Setting form html.
     *
     * @return      void
     */
    public function main_tab_content( $form ) {
        echo $form;
    }
    
    /**
     * Adds the current userswp addon settings page menu as submenu.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       callable   $settings_page    The function to be called to output the content for this page.
     *
     * @return      void
     */
    public function add_admin_gd_sub_menu( $settings_page ) {
        add_submenu_page(
            "userswp",
            __( 'GeoDirectory', 'userswp' ),
            __( 'GeoDirectory', 'userswp' ),
            'manage_options',
            'uwp_geodirectory',
            $settings_page
        );
    }

    /**
     * Adds settings tabs for the current userswp addon.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       array     $tabs    Existing tabs array.
     *
     * @return      array     Tabs array.
     */
    public function add_gd_tab( $tabs ) {
        $tabs['uwp_geodirectory']   = array(
            'main' => __( 'GeoDirectory', 'userswp' ),
        );
        return $tabs;
    }

    /**
     * Registers form fields for the current userswp addon settings page.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       array     $uwp_settings    Existing settings array.
     *
     * @return      array     Settings array.
     */
    public function add_gd_settings( $uwp_settings ) {
        $gd_posttypes = $this->get_gd_posttypes();

        $options = array(
            'gd_settings_info' => array(
                'id'   => 'woo_settings_info',
                'name' => __( 'Info', 'userswp' ),
                'desc' => __( 'You can now add Listings, Reviews and Favorites tabs under UsersWP > Profile > Choose the tabs to display in Profile', 'userswp' ),
                'type' => 'info',
                'std'  => '1',
                'class' => 'uwp_label_inline',
            ),
            'gd_profile_listings' => array(
                'id' => 'gd_profile_listings',
                'name' => __( 'CPT listings in Profile', 'userswp' ),
                'desc' => __( 'Choose the post types to show listings tab in UsersWP Profile', 'userswp' ),
                'multiple'    => true,
                'chosen'      => true,
                'type'        => 'select',
                'options' => $gd_posttypes,
                'placeholder' => __( 'Select Post Types', 'userswp' )
            ),
            'gd_profile_reviews' => array(
                'id' => 'gd_profile_reviews',
                'name' => __( 'CPT reviews in Profile', 'userswp' ),
                'desc' => __( 'Choose the post types to show reviews tab in UsersWP Profile', 'userswp' ),
                'multiple'    => true,
                'chosen'      => true,
                'type'        => 'select',
                'options' => $gd_posttypes,
                'placeholder' => __( 'Select Post Types', 'userswp' )
            ),
            'gd_profile_favorites' => array(
                'id' => 'gd_profile_favorites',
                'name' => __( 'CPT favorites in Profile', 'userswp' ),
                'desc' => __( 'Choose the post types to show favorites tab in UsersWP Profile', 'userswp' ),
                'multiple'    => true,
                'chosen'      => true,
                'type'        => 'select',
                'options' => $gd_posttypes,
                'placeholder' => __( 'Select Post Types', 'userswp' )
            ),
            'geodir_uwp_link_listing' => array(
                'id'   => 'geodir_uwp_link_listing',
                'name' => __( 'Redirect my listing link from GD loginbox to UsersWP profile', 'userswp' ),
                'desc' => __( 'If this option is selected, the my listing link from GD loginbox will redirect to listings tab of UsersWP profile.', 'userswp' ),
                'type' => 'checkbox',
                'std'  => '0',
                'class' => 'uwp_label_inline',
            ),
            'geodir_uwp_link_favorite' => array(
                'id'   => 'geodir_uwp_link_favorite',
                'name' => __( 'Redirect favorite link from GD loginbox to UsersWP profile', 'userswp' ),
                'desc' => __( 'If this option is selected, the favorite link from GD loginbox will redirect to favorites tab of UsersWP profile.', 'userswp' ),
                'type' => 'checkbox',
                'std'  => '0',
                'class' => 'uwp_label_inline',
            ),
        );

        $uwp_settings['uwp_geodirectory'] = array(
            'main' => apply_filters( 'uwp_settings_geodirectory', $options ),
        );

        return $uwp_settings;
    }

    public function get_gd_posttypes() {
        $post_type_arr = array();
        $post_types = geodir_get_posttypes( 'object' );

        foreach ( $post_types as $key => $post_types_obj ) {
            $post_type_arr[$key] = $post_types_obj->labels->singular_name;
        }
        return $post_type_arr;
    }

    /**
     * Registers the current addon tab items in "Choose the tabs to display in UsersWP Profile" setting.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       array     $tabs_arr    Existing tabs array.
     *
     * @return      array     Tabs array.
     */
    public function available_tab_items( $tabs_arr ) {
        $tabs_arr['listings'] = __( 'Listings', 'userswp' );
        $tabs_arr['reviews'] = __( 'Reviews', 'userswp' );
        $tabs_arr['favorites'] = __( 'Favorites', 'userswp' );

        if ( class_exists( 'WPInv_Invoice' ) ) {
            $tabs_arr['invoices'] = __( 'Invoices', 'userswp' );
        }

        return $tabs_arr;
    }
    
    public function geodir_get_reviews_by_user_id($post_type = 'gd_place', $user_id, $count_only = false, $offset = 0, $limit = 20) {
        global $wpdb;

        if ($count_only) {
            $results = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(reviews.overall_rating) FROM " . GEODIR_REVIEW_TABLE . " reviews JOIN " . $wpdb->posts . " posts ON reviews.post_id = posts.id WHERE reviews.user_id = %d AND reviews.post_type = %s AND reviews.status=1 AND reviews.overall_rating>0 AND posts.post_status = 'publish'",
                    array($user_id, $post_type)
                )
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT reviews.* FROM " . GEODIR_REVIEW_TABLE . " reviews JOIN " . $wpdb->posts . " posts ON reviews.post_id = posts.id WHERE reviews.user_id = %d AND reviews.post_type = %s AND reviews.status=1 AND reviews.overall_rating>0 AND posts.post_status = 'publish' LIMIT %d OFFSET %d",
                    array($user_id, $post_type, $limit, $offset )
                )
            );
        }

        if (!empty($results))
            return $results;
        else
            return false;
    }

    public function geodir_count_favorite( $post_type, $user_id = 0 ) {
        global $wpdb;

        $post_status = is_super_admin() ? " OR " . $wpdb->posts . ".post_status = 'private'" : '';
        if ( $user_id && $user_id == get_current_user_id() ) {
            $post_status .= " OR " . $wpdb->posts . ".post_status = 'draft' OR " . $wpdb->posts . ".post_status = 'private'";
        }

        $user_fav_posts = geodir_get_user_favourites( (int)$user_id );
        $user_fav_posts = !empty( $user_fav_posts ) ? implode( "','", $user_fav_posts ) : "-1";

        $count = (int)$wpdb->get_var( "SELECT count( ID ) FROM ".$wpdb->posts." WHERE " . $wpdb->posts . ".ID IN ('" . $user_fav_posts . "') AND post_type='" . $post_type . "' AND ( post_status = 'publish' " . $post_status . " )" );

        return apply_filters( 'uwp_geodir_count_favorite', $count, $user_id );
    }
    
    public function get_listings_count($post_type, $user_id = 0) {
        global $wpdb;
        if (empty($user_id)) {
            return 0;
        }

        $post_status = is_super_admin() ? " OR " . $wpdb->posts . ".post_status = 'private'" : '';
        if ($user_id && $user_id == get_current_user_id()) {
            $post_status .= " OR " . $wpdb->posts . ".post_status = 'draft' OR " . $wpdb->posts . ".post_status = 'private'";
        }

        $count = (int)$wpdb->get_var("SELECT count( ID ) FROM " . $wpdb->prefix . "posts WHERE post_author=" . (int)$user_id . " AND post_type='" . $post_type . "' AND ( post_status = 'publish' " . $post_status . " )");

        return apply_filters('geodir_uwp_count_total', $count, $user_id);
    }

    public function get_total_listings_count($user_id = 0) {
        if (empty($user_id)) {
            return 0;
        }

        $gd_post_types = geodir_get_posttypes('array');

        if (empty($gd_post_types)) {
            return 0;
        }


        // allowed post types
        $listing_post_types = uwp_get_option('gd_profile_listings', array());

        if (!is_array($listing_post_types)) {
            $listing_post_types = array();
        }

        $count = 0;
        $total_count = 0;
        foreach ($listing_post_types as $post_type) {
            if (array_key_exists($post_type, $gd_post_types)) {

                // get listing count
                $listing_count = $this->get_listings_count($post_type, $user_id);
                $total_count += $listing_count;

                $count++;
            }
        }

        return $total_count;
    }

    public function get_total_reviews_count($user_id = 0) {
        if (empty($user_id)) {
            return 0;
        }

        $gd_post_types = geodir_get_posttypes('array');

        if (empty($gd_post_types)) {
            return 0;
        }

        // allowed post types
        $listing_post_types = uwp_get_option('gd_profile_reviews', array());

        if (!is_array($listing_post_types)) {
            $listing_post_types = array();
        }

        $count = 0;
        $total_count = 0;
        foreach ($listing_post_types as $post_type) {
            if (array_key_exists($post_type, $gd_post_types)) {

                // get listing count
                $listing_count = $this->geodir_get_reviews_by_user_id($post_type, $user_id, true);
                $total_count += $listing_count;

                $count++;
            }
        }

        return $total_count;
    }

    public function get_total_favorites_count($user_id = 0) {
        if (empty($user_id)) {
            return 0;
        }

        $gd_post_types = geodir_get_posttypes('array');

        if (empty($gd_post_types)) {
            return 0;
        }

        // allowed post types
        $listing_post_types = uwp_get_option('gd_profile_favorites', array());

        if (!is_array($listing_post_types)) {
            $listing_post_types = array();
        }

        $count = 0;
        $total_count = 0;
        foreach ($listing_post_types as $post_type) {
            if (array_key_exists($post_type, $gd_post_types)) {

                // get listing count
                $listing_count = $this->geodir_count_favorite($post_type, $user_id);
                $total_count += $listing_count;

                $count++;
            }
        }

        return $total_count;
    }

    /**
     * Adds tab in user profile page.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       array     $tabs             Existing tabs array.
     * @param       object    $user             User object.
     * @param       array     $allowed_tabs     Allowed tabs array.
     *
     * @return      array     Tabs array.
     */
    function add_profile_gd_tabs($tabs, $user,$allowed_tabs) {
        // allowed post types
        $listing_post_types = uwp_get_option('gd_profile_listings', array());
        if (!empty($listing_post_types) && in_array('listings', $allowed_tabs)) {
            $tabs['listings'] = array(
                'title' => __('Listings', 'userswp'),
                'count' => $this->get_total_listings_count($user->ID)
            );
        }

        $listing_post_types = uwp_get_option('gd_profile_reviews', array());
        if (!empty($listing_post_types) && in_array('reviews', $allowed_tabs)) {
            $tabs['reviews'] = array(
                'title' => __('Reviews', 'userswp'),
                'count' => $this->get_total_reviews_count($user->ID)
            );
        }

        $listing_post_types = uwp_get_option('gd_profile_favorites', array());
        if (!empty($listing_post_types) && in_array('favorites', $allowed_tabs) && (get_current_user_id() == $user->ID)) {
            $tabs['favorites'] = array(
                'title' => __('Favorites', 'userswp'),
                'count' => $this->get_total_favorites_count($user->ID)
            );
        }

        if (class_exists('WPInv_Invoice') && in_array('invoices', $allowed_tabs) && (get_current_user_id() == $user->ID)) {
            $tabs['invoices'] = array(
                'title' => __('Invoices', 'userswp'),
                'count' => $this->invoice_count($user->ID)
            );
        }

        return $tabs;
    }

    /**
     * Adds GD listings tab content.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       object    $user             User object.
     *
     * @return      void
     */
    public function add_profile_listings_tab_content($user) {
        $this->profile_gd_subtabs_content($user, 'listings');
    }

    /**
     * Adds GD reviews tab content.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       object    $user             User object.
     *
     * @return      void
     */
    public function add_profile_reviews_tab_content($user) {
        $this->profile_gd_subtabs_content($user, 'reviews');
    }

    /**
     * Adds GD Favorites tab content.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       object    $user             User object.
     *
     * @return      void
     */
    public function add_profile_favorites_tab_content($user) {
        $this->profile_gd_subtabs_content($user, 'favorites');
    }

    /**
     * Adds GD Invoices tab content.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       object    $user             User object.
     *
     * @return      void
     */
    public function add_profile_invoices_tab_content($user) {
        $this->profile_gd_invoices_content($user);
    }

    public function profile_gd_subtabs($user, $type = 'listings') {
        $tabs = array();

        $gd_post_types = geodir_get_posttypes('array');

        if (empty($gd_post_types)) {
            return $tabs;
        }

        // allowed post types
        if ($type == 'listings') {
            $listing_post_types = uwp_get_option('gd_profile_listings', array());
        } elseif ($type == 'reviews') {
            $listing_post_types = uwp_get_option('gd_profile_reviews', array());
        } elseif ($type == 'favorites') {
            $listing_post_types = uwp_get_option('gd_profile_favorites', array());
        } else {
            $listing_post_types = array();
        }

        if (!is_array($listing_post_types)) {
            $listing_post_types = array();
        }

        foreach ($gd_post_types as $post_type_id => $post_type) {
            if (in_array($post_type_id, $listing_post_types)) {
                $post_type_slug = $gd_post_types[$post_type_id]['has_archive'];

                if ($type == 'listings') {
                    $count = uwp_post_count($user->ID, $post_type_id);
                } elseif ($type == 'reviews') {
                    $count = $this->geodir_get_reviews_by_user_id($post_type_id, $user->ID, true);
                } elseif ($type == 'favorites') {
                    $count = $this->geodir_count_favorite($post_type_id, $user->ID);
                } else {
                    $count = 0;
                }

                if (empty($count)) {
                    $count = 0;
                }
                $tabs[$post_type_slug] = array(
                    'title' => $gd_post_types[$post_type_id]['labels']['name'],
                    'count' => $count,
                    'ptype' => $post_type_id
                );
            }
        }

        return apply_filters('uwp_profile_gd_tabs', $tabs, $user, $type);
    }

    public function profile_gd_subtabs_content($user, $type = 'listings') {
        $subtab = get_query_var('uwp_subtab');
        $subtabs = $this->profile_gd_subtabs($user, $type);
        $default_tab = apply_filters('uwp_default_listing_subtab', 'places', $user, $type);
        $active_tab = !empty($subtab) && array_key_exists($subtab, $subtabs) ? $subtab : $default_tab;
        if (!empty($subtabs)) {
            $subtab_keys = array_keys($subtabs);
            $post_type = $subtabs[$subtab_keys[0]]['ptype'];
        } else {
            $post_type = false;
        }
        ?>
        <div class="uwp-profile-subcontent">
            <div class="uwp-profile-subnav">
                <?php if ($subtabs) { ?>
                    <ul class="item-list-subtabs-ul">
                        <?php
                        foreach ($this->profile_gd_subtabs($user, $type) as $tab_id => $tab) {

                            $tab_url = uwp_build_profile_tab_url($user->ID, $type, $tab_id);

                            $active = $active_tab == $tab_id ? ' active' : '';
                            $post_type = $active_tab == $tab_id ? $tab['ptype'] : $post_type;
                            ?>
                            <li id="uwp-profile-gd-<?php echo $tab_id; ?>" class="<?php echo $active; ?>">
                                <a href="<?php echo esc_url($tab_url); ?>">
                                    <span class="uwp-profile-tab-label uwp-profile-gd-<?php echo $tab_id; ?>-label "><span
                                            class="uwp-profile-tab-sub-ul-count uwp-profile-sub-ul-gd-<?php echo $tab_id; ?>-count"><?php echo $tab['count']; ?></span> <?php echo esc_html($tab['title']); ?></span>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                <?php } ?>
            </div>

            <div class="uwp-profile-subtab-entries">
                <?php
                do_action('uwp_profile_gd_' . $type . '_subtab_content', $user, $post_type);
                ?>
            </div>
        </div>
    <?php 
    }

    function gd_get_listings($user, $post_type) {
        $gd_post_types = geodir_get_posttypes('array');
        ?>
        <h3><?php echo __($gd_post_types[$post_type]['labels']['name'], 'userswp') ?></h3>

        <div class="uwp-profile-item-block">
            <?php
            
            $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

            $args = array(
                'post_type' => $post_type,
                'post_status' => array('publish'),
                'posts_per_page' => uwp_get_option('profile_no_of_items', 10),
                'author' => $user->ID,
                'paged' => $paged,
            );

            if (get_current_user_id() == $user->ID) {
                $args['post_status'] = array('publish', 'draft', 'private');
            }
            // The Query
            $the_query = new WP_Query($args);

            do_action('uwp_before_profile_listing_items', $user, $post_type);
            // The Loop
            if ($the_query->have_posts()) {
                echo '<ul class="uwp-profile-item-ul">';
                while ($the_query->have_posts()) {
                    $the_query->the_post();
                    $post_id = get_the_ID();
                    global $post;
                    $post = geodir_get_post_info($post_id);
                    setup_postdata($post);
                    $post_avgratings = geodir_get_post_rating($post->ID);
                    $post_ratings = geodir_get_rating_stars($post_avgratings, $post->ID);
                    ob_start();
                    geodir_comments_number($post->rating_count);
                    $n_comments = ob_get_contents();
                    ob_end_clean();

                    do_action('uwp_before_profile_listing_item', $post_id, $user, $post_type);
                    ?>
                    <li class="uwp-profile-item-li uwp-profile-item-clearfix <?php echo 'gd-post-'.$post_type; ?>">
                        <a class="uwp-profile-item-img" href="<?php echo get_the_permalink(); ?>">
                            <?php
                            if (has_post_thumbnail()) {
                                $thumb_url = get_the_post_thumbnail_url(get_the_ID(), array(80, 80));
                            } else {
                                $thumb_url = USERSWP_PLUGIN_URL."/public/assets/images/no_thumb.png";
                            }
                            ?>
                            <img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo $thumb_url; ?>">
                        </a>

                        <?php do_action('uwp_before_profile_listing_title', $post_id, $user, $post_type); ?>
                        <h3 class="uwp-profile-item-title">
                            <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                        </h3>
                        <?php do_action('uwp_after_profile_listing_title', $post_id, $user, $post_type); ?>

                        <div class="uwp-time-ratings-wrap">
                            <?php do_action('uwp_before_profile_listing_ratings', $post_id, $user, $post_type); ?>
                            <time class="uwp-profile-item-time published" datetime="<?php echo get_the_time('c'); ?>">
                                <?php echo get_the_date(); ?>
                            </time>
                            <?php echo '<div class="uwp-ratings">' . $post_ratings . ' <a href="' . get_comments_link() . '" class="uwp-num-comments">' . $n_comments . '</a></div>'; ?>
                            <?php
                            if (!is_user_logged_in()) {
                                do_action( 'geodir_after_favorite_html', $post->ID, 'listing' );
                            }
                            ?>
                            <?php do_action('uwp_after_profile_listing_ratings', $post_id, $user, $post_type); ?>
                        </div>
                        <div class="uwp-item-actions">
                            <?php
                            if (is_user_logged_in()) {
                                do_action( 'geodir_after_favorite_html', $post->ID, 'listing' );
                            }
                            if ($post->post_author == get_current_user_id()) {
                                $addplacelink = get_permalink(geodir_add_listing_page_id());
                                $editlink = geodir_getlink($addplacelink, array('pid' => $post->ID), false);

                                $ajaxlink = geodir_get_ajax_url();
                                $deletelink = geodir_getlink($ajaxlink, array('geodir_ajax' => 'add_listing', 'ajax_action' => 'delete', 'pid' => $post->ID), false);
                                ?>

                                <span class="geodir-authorlink clearfix">

                                    <?php do_action('geodir_before_edit_post_link_on_listing', $post_id, $user, $post_type); ?>

                                    <a href="<?php echo esc_url($editlink); ?>" class="geodir-edit"
                                       title="<?php _e('Edit Listing', 'userswp'); ?>">
                                            <?php
                                            $geodir_listing_edit_icon = apply_filters('geodir_listing_edit_icon', 'fa fa-edit');
                                            echo '<i class="' . $geodir_listing_edit_icon . '"></i>';
                                            ?>
                                            <?php _e('Edit', 'userswp'); ?>
                                        </a>
                                        <a href="<?php echo esc_url($deletelink); ?>" class="geodir-delete"
                                           title="<?php _e('Delete Listing', 'userswp'); ?>">
                                            <?php
                                            $geodir_listing_delete_icon = apply_filters('geodir_listing_delete_icon', 'fa fa-close', $post_id, $user, $post_type);
                                            echo '<i class="' . $geodir_listing_delete_icon . '"></i>';
                                            ?>
                                            <?php _e('Delete', 'userswp'); ?>
                                        </a>

                                    <?php do_action('geodir_after_edit_post_link_on_listing', $post_id, $user, $post_type); ?>

                                </span>

                            <?php } ?>
                        </div>

                        <div class="uwp-profile-item-summary">
                            <?php
                            do_action('uwp_before_profile_listing_summary', $post_id, $user, $post_type);
                            $excerpt = strip_shortcodes(wp_trim_words(get_the_excerpt(), 15, '...'));
                            echo $excerpt;
                            if ($excerpt) {
                                ?>
                                <a href="<?php echo get_the_permalink(); ?>" class="more-link"><?php echo __( 'Read More »', 'userswp' ); ?></a>
                                <?php
                            }
                            do_action('uwp_after_profile_listing_summary', $post_id, $user, $post_type);
                            ?>
                        </div>
                    </li>
                    <?php
                    do_action('uwp_after_profile_listing_item', $post_id, $user, $post_type);
                }
                echo '</ul>';
                /* Restore original Post Data */
                wp_reset_postdata();
            } else {
                echo sprintf( __( "No %s found", 'userswp' ), $gd_post_types[$post_type]['labels']['name']);
            }
            do_action('uwp_after_profile_listing_items', $user, $post_type);

            do_action('uwp_profile_pagination', $the_query->max_num_pages);
            ?>
        </div>
        <?php
    }

    public function gd_get_reviews($user, $post_type) {
        $gd_post_types = geodir_get_posttypes('array');
        ?>
        <h3><?php echo __($gd_post_types[$post_type]['labels']['name'], 'userswp') ?></h3>

        <div class="uwp-profile-item-block">
            <?php
            $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;
            $limit = uwp_get_option('profile_no_of_items', 10);
            $offset = ($paged - 1) * $limit;

            $total_reviews = $this->geodir_get_reviews_by_user_id($post_type, $user->ID, true, $offset, $limit);
            $maximum_pages = ceil($total_reviews / $limit);

            $reviews = $this->geodir_get_reviews_by_user_id($post_type, $user->ID, false, $offset, $limit);

            do_action('uwp_before_profile_reviews_items', $user, $post_type);
            // The Loop
            if ($reviews) {
                echo '<ul class="uwp-profile-item-ul">';
                foreach ($reviews as $review) {
                    $rating = 0;
                    if (!empty($review))
                        $rating = geodir_get_commentoverall($review->comment_id);

                        do_action('uwp_before_profile_reviews_item', $review->comment_id, $user, $post_type);
                    ?>
                    <li class="uwp-profile-item-li uwp-profile-item-clearfix <?php echo 'gd-post-'.$post_type; ?>">
                        <a class="uwp-profile-item-img" href="<?php echo get_comment_link($review->comment_id); ?>">
                            <?php
                            if ( has_post_thumbnail($review->post_id) ) {
                                $thumb_url = get_the_post_thumbnail_url($review->post_id, array(80, 80));
                            } else {
                                $thumb_url = USERSWP_PLUGIN_URL."/public/assets/images/no_thumb.png";
                            }
                            ?>
                            <img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo $thumb_url; ?>">
                        </a>

                        <?php do_action('uwp_before_profile_reviews_title', $review->comment_id, $user, $post_type); ?>
                        <h3 class="uwp-profile-item-title">
                            <a href="<?php echo get_comment_link($review->comment_id); ?>"><?php echo get_the_title($review->post_id); ?></a>
                        </h3>
                        <?php do_action('uwp_after_profile_reviews_title', $review->comment_id, $user, $post_type); ?>

                        <div class="uwp-time-ratings-wrap">
                            <?php do_action('uwp_before_profile_reviews_ratings', $review->comment_id, $user, $post_type); ?>
                            <time class="uwp-profile-item-time published" datetime="<?php echo get_the_time('c'); ?>">
                                <?php echo date_i18n(get_option('date_format'), strtotime(get_comment_date("", $review->comment_id))); ?>
                            </time>
                            <?php echo '<div class="uwp-ratings">' . geodir_get_rating_stars($rating, $review->comment_id) . '</div>'; ?>
                            <?php do_action('uwp_after_profile_reviews_ratings', $review->comment_id, $user, $post_type); ?>
                        </div>
                        <div class="uwp-profile-item-summary">
                            <?php
                            do_action('uwp_before_profile_reviews_summary', $review->comment_id, $user, $post_type);
                            $excerpt = strip_shortcodes(wp_trim_words($review->comment_content, 15, '...'));
                            echo $excerpt;
                            if ($excerpt) {
                                ?>
                                <a href="<?php echo get_comment_link($review->comment_id); ?>" class="more-link">Read More
                                    »</a>
                                <?php
                            }
                            do_action('uwp_after_profile_reviews_summary', $review->comment_id, $user, $post_type);
                            ?>
                        </div>
                    </li>
                    <?php
                    do_action('uwp_after_profile_reviews_item', $review->comment_id, $user, $post_type);
                }
                echo '</ul>';
                /* Restore original Post Data */
                wp_reset_postdata();
            } else {
                echo sprintf( __( "No %s found", 'userswp' ), $gd_post_types[$post_type]['labels']['name']);
            }

            do_action('uwp_after_profile_reviews_items', $user, $post_type);

            do_action('uwp_profile_pagination', $maximum_pages);
            ?>
        </div>
        <?php
    }

    public function gd_get_favorites($user, $post_type) {
        $gd_post_types = geodir_get_posttypes('array');
        ?>
        <h3><?php echo __($gd_post_types[$post_type]['labels']['name'], 'userswp') ?></h3>

        <div class="uwp-profile-item-block">
            <?php
            $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

            $user_fav_posts = geodir_get_user_favourites($user->ID);

            if ($user_fav_posts) {
                $args = array(
                    'post_type' => $post_type,
                    'post_status' => array('publish'),
                    'posts_per_page' => uwp_get_option('profile_no_of_items', 10),
                    'post__in' => $user_fav_posts,
                    'paged' => $paged,
                );

                if (get_current_user_id() == $user->ID) {
                    $args['post_status'] = array('publish', 'draft', 'private');
                }

                // The Query
                $the_query = new WP_Query($args);

                do_action('uwp_before_profile_favourite_items', $user, $post_type);
                // The Loop
                if ($the_query->have_posts()) {
                    echo '<ul class="uwp-profile-item-ul">';
                    while ($the_query->have_posts()) {
                        $the_query->the_post();
                        $post_id = get_the_ID();
                        global $post;
                        $post = geodir_get_post_info($post_id);
                        setup_postdata($post);
                        $post_avgratings = geodir_get_post_rating($post->ID);
                        $post_ratings = geodir_get_rating_stars($post_avgratings, $post->ID);
                        ob_start();
                        geodir_comments_number($post->rating_count);
                        $n_comments = ob_get_contents();
                        ob_end_clean();
                        do_action('uwp_before_profile_favourite_item', $post_id, $user, $post_type);
                        ?>
                        <li class="uwp-profile-item-li uwp-profile-item-clearfix <?php echo 'gd-post-'.$post_type; ?>">
                            <a class="uwp-profile-item-img" href="<?php echo get_the_permalink(); ?>">
                                <?php
                                if (has_post_thumbnail()) {
                                    $thumb_url = get_the_post_thumbnail_url(get_the_ID(), array(80, 80));
                                } else {
                                    $thumb_url = USERSWP_PLUGIN_URL."/public/assets/images/no_thumb.png";
                                }
                                ?>
                                <img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo $thumb_url; ?>">
                            </a>

                            <?php do_action('uwp_before_profile_favourite_title', $post_id, $user, $post_type); ?>
                            <h3 class="uwp-profile-item-title">
                                <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                            </h3>
                            <?php do_action('uwp_after_profile_favourite_title', $post_id, $user, $post_type); ?>

                            <div class="uwp-time-ratings-wrap">
                                <?php do_action('uwp_before_profile_favourite_ratings', $post_id, $user, $post_type); ?>
                                <time class="uwp-profile-item-time published" datetime="<?php echo get_the_time('c'); ?>">
                                    <?php echo get_the_date(); ?>
                                </time>
                                <?php echo '<div class="uwp-ratings">' . $post_ratings . ' <a href="' . get_comments_link() . '" class="uwp-num-comments">' . $n_comments . '</a></div>'; ?>
                                <?php
                                if (!is_user_logged_in()) {
                                    do_action( 'geodir_after_favorite_html', $post->ID, 'listing' );
                                }
                                ?>
                                <?php do_action('uwp_after_profile_favourite_ratings', $post_id, $user, $post_type); ?>
                            </div>
                            <div class="uwp-item-actions">
                                <?php
                                if (is_user_logged_in()) {
                                    do_action( 'geodir_after_favorite_html', $post->ID, 'listing' );
                                }
                                if ($post->post_author == get_current_user_id()) {
                                    $addplacelink = get_permalink(geodir_add_listing_page_id());
                                    $editlink = geodir_getlink($addplacelink, array('pid' => $post->ID), false);

                                    $ajaxlink = geodir_get_ajax_url();
                                    $deletelink = geodir_getlink($ajaxlink, array('geodir_ajax' => 'add_listing', 'ajax_action' => 'delete', 'pid' => $post->ID), false);
                                    ?>

                                    <span class="geodir-authorlink clearfix">

                                        <?php do_action('geodir_before_edit_post_link_on_listing', $post_id, $user, $post_type); ?>

                                        <a href="<?php echo esc_url($editlink); ?>" class="geodir-edit"
                                           title="<?php _e('Edit Listing', 'userswp'); ?>">
                                            <?php
                                            $geodir_listing_edit_icon = apply_filters('geodir_listing_edit_icon', 'fa fa-edit');
                                            echo '<i class="' . $geodir_listing_edit_icon . '"></i>';
                                            ?>
                                            <?php _e('Edit', 'userswp'); ?>
                                        </a>
                                        <a href="<?php echo esc_url($deletelink); ?>" class="geodir-delete"
                                           title="<?php _e('Delete Listing', 'userswp'); ?>">
                                            <?php
                                            $geodir_listing_delete_icon = apply_filters('geodir_listing_delete_icon', 'fa fa-close');
                                            echo '<i class="' . $geodir_listing_delete_icon . '"></i>';
                                            ?>
                                            <?php _e('Delete', 'userswp'); ?>
                                        </a>

                                        <?php do_action('geodir_after_edit_post_link_on_listing', $post_id, $user, $post_type); ?>

                                </span>

                                <?php } ?>
                            </div>
                            <div class="uwp-profile-item-summary">
                                <?php
                                do_action('uwp_before_profile_favourite_summary', $post_id, $user, $post_type);
                                $excerpt = strip_shortcodes(wp_trim_words(get_the_excerpt(), 15, '...'));
                                echo $excerpt;
                                if ($excerpt) {
                                    ?>
                                    <a href="<?php echo get_the_permalink(); ?>" class="more-link"><?php echo __( 'Read More »', 'userswp' ); ?></a>
                                    <?php
                                }
                                do_action('uwp_after_profile_favourite_summary', $post_id, $user, $post_type);
                                ?>
                            </div>
                        </li>
                        <?php
                        do_action('uwp_after_profile_favourite_item', $post_id, $user, $post_type);
                    }
                    echo '</ul>';
                    /* Restore original Post Data */
                    wp_reset_postdata();
                } else {
                    echo sprintf( __( "No %s found", 'userswp' ), $gd_post_types[$post_type]['labels']['name']);
                }

                do_action('uwp_after_profile_favourite_items', $user, $post_type);

                do_action('uwp_profile_pagination', $the_query->max_num_pages);
            } else {
                echo sprintf( __( "No %s found", 'userswp' ), $gd_post_types[$post_type]['labels']['name']);
            }
            ?>
        </div>
        <?php
    }

    public function get_gd_login_url($url, $args) {
        $register_page = uwp_get_page_id('register_page', false);
        $login_page = uwp_get_page_id('login_page', false);
        $forgot_page = uwp_get_page_id('forgot_page', false);
        $reset_page = uwp_get_page_id('reset_page', false);

        if (!empty($args)) {
            if (isset($args['signup']) && $args['signup']) {
                $page_id = $register_page;
            } elseif (isset($args['forgot']) && $args['forgot']) {
                $page_id = $forgot_page;
            } elseif (isset($args['reset']) && $args['reset']) {
                $page_id = $reset_page;
            } else {
                $page_id = $login_page;
            }
        } else {
            $page_id = $login_page;
        }
        if ($page_id) {
            $uwp_url = get_permalink($page_id);

            if (strpos($url, 'redirect_add_listing') !== false) {
                $parsed = wp_parse_url($url);
                parse_str($parsed['query'], $query);
                $uwp_url = add_query_arg( array(
                            'redirect_to' => $query['redirect_add_listing'],
                        ), $uwp_url );
            }

            if(isset($args['redirect_to']) && !empty($args['redirect_to'])){
                $uwp_url = add_query_arg( array(
                    'redirect_to' => $args['redirect_to'],
                ), $uwp_url );
            }

            $url = $uwp_url;
        }
        return $url;
    }

    public function geodir_uwp_author_redirect() {
        if ( ! empty( $_REQUEST['geodir_dashbord'] ) ) {
            $author = get_query_var( 'author_name' ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

            if ( !empty( $author ) && ! empty( $author->ID ) ) {
                $favourite = isset($_REQUEST['list']) && $_REQUEST['list'] == 'favourite' ? true : false;
                $post_type = isset( $_REQUEST['stype'] ) ? sanitize_text_field( $_REQUEST['stype'] ) : NULL;

                $author_id = $author->ID;
                $author_link = uwp_build_profile_tab_url( $author_id );
                $gd_post_types = geodir_get_posttypes( 'array' );

                if ( !empty( $gd_post_types ) && array_key_exists( $post_type, $gd_post_types ) ) {
                    if ( $favourite ) {
                        $profile_favorites = uwp_get_option( 'gd_profile_favorites', array() );

                        if ( uwp_get_option( 'geodir_uwp_link_favorite', '0' ) && ! empty( $profile_favorites ) && in_array( $post_type, $profile_favorites ) ) {
                            $author_link = uwp_build_profile_tab_url( $author_id, 'favorites', $gd_post_types[ $post_type ]['has_archive'] );
                        } else {
                            return; // Do not redirect to dashboard CPT favorites.
                        }
                    } else {
                        $profile_listings = uwp_get_option( 'gd_profile_listings', array() );
                        
                        if ( uwp_get_option( 'geodir_uwp_link_listing', '0' ) && ! empty( $profile_listings ) && in_array( $post_type, $profile_listings ) ) {
                            $author_link = uwp_build_profile_tab_url( $author_id, 'listings', $gd_post_types[ $post_type ]['has_archive'] );
                        } else {
                            return; // Do not redirect to dashboard CPT listings.
                        }
                    }
                }

                wp_redirect($author_link);
                exit;
            }
        }
        return;
    }

    public function gd_is_listings_tab() {
        global $wp_query;
        if (is_page() && class_exists('UsersWP')) {
            $profile_page = uwp_get_page_id('profile_page', false);
            if ($profile_page) {
                if (isset($wp_query->query_vars['uwp_profile'])
                    && isset($wp_query->query_vars['uwp_tab'])
                    && ($wp_query->query_vars['uwp_tab'] == 'listings' || $wp_query->query_vars['uwp_tab'] == 'favorites')
                ) {

                    return true;

                }
            }
        }
        return false;
    }

    public function geodir_post_status_is_author_page($value) {
        return $value || $this->gd_is_listings_tab();
    }

    public function geodir_add_post_status_author_page() {
        global $wpdb, $post;

        $html = '';
        if (get_current_user_id()) {
            if ($this->gd_is_listings_tab() && !empty($post) && isset($post->post_author) && $post->post_author == get_current_user_id()) {

                // we need to query real status direct as we dynamically change the status for author on author page so even non author status can view them.
                $real_status = $wpdb->get_var("SELECT post_status from $wpdb->posts WHERE ID=$post->ID");
                $status = "<strong>(";
                $status_icon = '<i class="fa fa-play"></i>';
                if ($real_status == 'publish') {
                    $status .= __('Published', 'userswp');
                } else {
                    $status .= __('Not published', 'userswp');
                    $status_icon = '<i class="fa fa-pause"></i>';
                }
                $status .= ")</strong>";

                $html = '<span class="geodir-post-status">' . $status_icon . ' <font class="geodir-status-label">' . __('Status: ', 'userswp') . '</font>' . $status . '</span>';
            }
        }

        if ($html != '') {
            echo apply_filters('geodir_filter_status_text_on_author_page', $html);
        }
    }

    public function profile_gd_invoices_content($user) {
        if (!is_user_logged_in()) {
            return;
        }

        if (get_current_user_id() != $user->ID) {
            return;
        }
        ?>
        <h3><?php echo __('Invoices', 'userswp'); ?></h3>

        <div class="uwp-profile-item-block">
            <?php
            $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

            $args = array(
                'post_type' => 'wpi_invoice',
                'post_status' => array_keys(wpinv_get_invoice_statuses()),
                'posts_per_page' => uwp_get_option('profile_no_of_items', 10),
                'author' => $user->ID,
                'paged' => $paged,
            );
            // The Query
            $the_query = new WP_Query($args);

            // The Loop
            if ($the_query->have_posts()) {
                echo '<ul class="uwp-profile-item-ul">';
                while ($the_query->have_posts()) {
                    $the_query->the_post();
                    $wpi_invoice = new WPInv_Invoice( get_the_ID() );
                    ?>
                    <li class="uwp-profile-item-li uwp-profile-item-clearfix">
                        <h3 class="uwp-profile-item-title">
                            <a href="<?php echo get_the_permalink(); ?>"><?php _e('Invoice','userswp');?> <?php echo get_the_title(); ?></a>
                        </h3>
                        <time class="uwp-profile-item-time published" datetime="<?php echo get_the_time('c'); ?>">
                            <?php echo get_the_date(); ?>
                        </time>
                        <div class="uwp-profile-item-summary">
                            <div class="uwp-order-status">
                                <?php
                                echo __('Invoice Status: ', 'userswp').$wpi_invoice->get_status( true ) . ( $wpi_invoice->is_recurring() && $wpi_invoice->is_parent() ? ' <span class="wpi-suffix">' . __( '(r)', 'invoicing' ) . '</span>' : '' );
                                ?>
                            </div>
                            <div class="uwp-order-total">
                                <?php
                                echo __('Invoice Total: ', 'userswp'). $wpi_invoice->get_total( true );
                                ?>
                            </div
                        </div>
                    </li>
                    <?php
                }
                echo '</ul>';
                /* Restore original Post Data */
                wp_reset_postdata();
            } else {
                // no posts found
                echo "<p>".__('No Invoices Found', 'userswp')."</p>";
            }
            do_action('uwp_profile_pagination', $the_query->max_num_pages);
            ?>
        </div>
        <?php
    }

    public function invoice_count($user_id) {
        global $wpdb;

        $post_status_array = array_keys(wpinv_get_invoice_statuses());
        $post_status = "'" . implode("','", $post_status_array) . "'";

        $count = $wpdb->get_var('
                 SELECT COUNT(ID)
                 FROM ' . $wpdb->posts. '
                 WHERE post_author = "' . $user_id . '"
                 AND post_status IN ('.$post_status.')
                 AND post_type = "wpi_invoice"'
        );
        return $count;
    }

    public function gd_login_wid_login_placeholder() {
        return __( 'Username', 'userswp' );
    }

    public function gd_login_wid_login_name() {
        return "uwp_login_username";
    }

    public function gd_login_wid_login_pwd() {
        return "uwp_login_password";
    }

    public function gd_login_inject_nonce() {
        ?>
        <input type="hidden" name="uwp_login_nonce" value="<?php echo wp_create_nonce( 'uwp-login-nonce' ); ?>" />
        <?php
    }
    
    public function check_redirect_author_page( $redirect = false ) {
        if ( $redirect && ! empty( $_REQUEST['geodir_dashbord'] ) ) {
            $author = get_query_var( 'author_name' ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

            if ( !empty( $author ) && ! empty( $author->ID ) ) {
                $favourite = isset($_REQUEST['list']) && $_REQUEST['list'] == 'favourite' ? true : false;
                $post_type = isset( $_REQUEST['stype'] ) ? sanitize_text_field( $_REQUEST['stype'] ) : NULL;

                $author_id = $author->ID;
                $author_link = uwp_build_profile_tab_url( $author_id );
                $gd_post_types = geodir_get_posttypes( 'array' );

                if ( !empty( $gd_post_types ) && array_key_exists( $post_type, $gd_post_types ) ) {
                    if ( $favourite ) {
                        $profile_favorites = uwp_get_option( 'gd_profile_favorites', array() );

                        if ( uwp_get_option( 'geodir_uwp_link_favorite', '0' ) && ! empty( $profile_favorites ) && in_array( $post_type, $profile_favorites ) ) {
                            // Redirect to dashboard CPT favorites.
                        } else {
                            $redirect = false; // Do not redirect to dashboard CPT favorites.
                        }
                    } else {
                        $profile_listings = uwp_get_option( 'gd_profile_listings', array() );

                        if ( uwp_get_option( 'geodir_uwp_link_listing', '0' ) && ! empty( $profile_listings ) && in_array( $post_type, $profile_listings ) ) {
                            // Redirect to dashboard CPT listings.
                        } else {
                            $redirect =false; // Do not redirect to dashboard CPT listings.
                        }
                    }
                }
            }
        }

        return $redirect;
    }
    
    public function skip_uwp_author_page( $uwp_author = true ) {
        if ( geodir_is_page( 'author' ) ) {
            $uwp_author = false;
        }
        
        return $uwp_author;
    }
}
$userswp_geodirectory = UsersWP_GeoDirectory_Plugin::get_instance();