<?php

defined( 'ABSPATH' ) || exit;

class UsersWP_Seo {

    public function __construct(){
        add_action('pre_get_document_title', array($this,'output_title'));
        add_action('uwp_profile_options', array($this,'profile_options'));
        add_filter( 'plugins_loaded', array( $this, 'run_once_all_plugins_loaded' ) );
        add_filter( 'get_canonical_url', array( $this, 'filter_profile_page_canonical' ) );

        // Compatibility with SEOPress plugin
        add_action( 'seopress_titles_title', array( $this, 'get_title' ) );
        add_action( 'seopress_titles_desc', array( $this, 'get_description' ) );
    }

    public function run_once_all_plugins_loaded(){
        if(UsersWP_Seo::has_yoast()) {
            if ( UsersWP_Seo::has_yoast_14() ) {
                add_filter( 'wpseo_opengraph_title', array( $this, 'get_title' ), 10);
                add_filter( 'wpseo_opengraph_desc', array( $this, 'get_description' ), 10 );
                add_filter( 'wpseo_opengraph_url', array( $this, 'get_opengraph_url' ), 20 );
            }

            add_filter( 'wpseo_title', array( $this, 'get_title' ), 10);
            add_filter( 'wpseo_metadesc', array( $this, 'get_description' ), 10);
            add_filter( 'wpseo_canonical', array( $this, 'filter_profile_page_canonical' ) );
        }elseif ( defined( 'RANK_MATH_VERSION' ) ) {
            add_filter( 'rank_math/frontend/description', array( $this,'get_description' ), 10, 1 );
            add_filter( 'rank_math/frontend/title', array( $this, 'get_title' ), 10, 1 );
        } else{
            add_action( 'wp_head', array( $this, 'output_description' ) );
        }

        if ( uwp_is_wpml() ) {
            add_filter( 'rewrite_rules_array', array( $this, 'rewrite_rules_array' ), 999, 1 );
        }
    }

    public function profile_options($settings) {

        $settings[] = array(
            'title' => __( 'Profile SEO', 'userswp' ),
            'type'  => 'title',
            'id'    => 'profile_seo_options',
            'advanced'  => true,
        );

        $settings[] = array(
            'id'   => 'profile_seo_disable',
            'name' => __( 'Disable meta tags', 'userswp' ),
            'desc' => __( 'This will disable adding SEO meta tags on profile page.','userswp'),
            'type' => 'checkbox',
            'default'  => 0,
            'advanced'  => false,
        );

        $settings[] = array(
            'id' => 'profile_seo_meta_separator',
            'name' => __( 'Title separator', 'userswp' ),
            'type' => 'radio',
            'default' => self::get_default_sep(),
            'class' => 'uwp-seo-meta-separator',
            'desc' 	=> __('Choose the symbol to use as your title separator. This will display, for instance, between your user profile title and site name. Symbols are shown in the size they will appear in the search results.', 'uwp-groups'),
            'desc_tip' => true,
            'advanced'  => true,
            'placeholder' => '',
            'options' => array(
                '-' => '-',
                '|' => '|',
                '>' => '>',
                '<' => '<',
                '~' => '~',
                ':' => ':',
                '*' => '*',
            ),
        );

        $settings[] = array(
            'id' => 'profile_seo_meta_title',
            'name' => __( 'Meta Title', 'userswp' ),
            'type' => 'text',
            'default' => '',
            'class' => 'large-text',
            'desc' 	=> __('Available SEO tags:', 'uwp-groups') . ' '.self::get_seo_tags(true),
            'desc_tip' => false,
            'advanced'  => true,
            'placeholder' => $this->get_default_meta_title(),
        );

        $settings[] = array(
            'id' => 'profile_seo_meta_description',
            'name' => __( 'Meta Description', 'userswp' ),
            'type' => 'textarea',
            'default' => '',
            'desc' 	=> __( 'Enter the meta description to use for the page.', 'userswp' ),
            'desc_tip' => true,
            'advanced'  => true,
            'placeholder' => $this->get_default_meta_description(),
            'custom_desc' => __('Available SEO tags:', 'uwp-groups') . ' '.self::get_seo_tags(true),
        );

        $settings[] = array(
            'id'   => 'profile_seo_meta_description_length',
            'name' => __( 'Meta Description Length', 'userswp' ),
            'type' => 'number',
            'default'  => 150,
            'advanced'  => true,
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'profile_seo_options' );

        return $settings;
    }

    public static function get_default_sep() {
        return apply_filters('uwp_profile_seo_default_separator', '|');
    }

    public static function get_seo_tags( $inline = true ) {

        $tags = array(
            '[#site_name#]',
            '[#user_name#]',
            '[#display_name#]',
            '[#first_name#]',
            '[#last_name#]',
            '[#email#]',
            '[#user_bio#]',
            '[#sep#]',
        );

        $tags = apply_filters('uwp_seo_tags',$tags);

        if(!$inline) {
            return  $tags;
        }

        return '<code>' . implode( '</code> <code>', $tags ) . '</code>';
    }

    public function get_default_meta_title() {
        return '[#site_name#] [#sep#] [#user_name#]';
    }

    public function get_default_meta_description() {
        return '[#user_bio#]';
    }

    public function replace_tags($string) {

        if(is_uwp_profile_page()) {
            $displayed_user = uwp_get_displayed_user();
            if(!$displayed_user){
                return $string;
            }
            $site_name = esc_attr( get_bloginfo('name') );
            $first_name = !empty($displayed_user->first_name) ? esc_attr( $displayed_user->first_name ) :'';
            $last_name = !empty($displayed_user->last_name) ? esc_attr( $displayed_user->last_name ) :'';
            $user_name = !empty($displayed_user->user_login) ? esc_attr( $displayed_user->user_login ) :'';
            $display_name = !empty($displayed_user->display_name) ? esc_attr( $displayed_user->display_name ) :'';
            $user_email = !empty($displayed_user->user_email) ? esc_url( $displayed_user->user_email ) :'';
            $user_bio = !empty($displayed_user->description) ? strip_tags($displayed_user->description) :'';

            $meta_separator = uwp_get_option('profile_seo_meta_separator');
            $sep = !empty($meta_separator) ? $meta_separator : self::get_default_sep();

            $string = str_replace('[#site_name#]', $site_name, $string);
            $string = str_replace('[#user_name#]', $user_name, $string);
            $string = str_replace('[#display_name#]', $display_name, $string);
            $string = str_replace('[#first_name#]', $first_name, $string);
            $string = str_replace('[#last_name#]', $last_name, $string);
            $string = str_replace('[#email#]', $user_email, $string);
            $string = str_replace('[#user_bio#]', $user_bio, $string);
            $string = str_replace('[#sep#]', $sep, $string);
        }

        return $string;
    }

    public function get_meta_title() {

        $meta_title = uwp_get_option('profile_seo_meta_title');
        $meta_title = !empty($meta_title) ? $meta_title : $this->get_default_meta_title();
        $meta_title = $this->replace_tags($meta_title);

        return esc_html($meta_title);
    }

    public function get_meta_description() {

        $meta_description = uwp_get_option('profile_seo_meta_description');
        $meta_description = !empty($meta_description) ? $meta_description : $this->get_default_meta_description();
        $meta_description = $this->replace_tags($meta_description);

        return esc_html($meta_description);
    }

    public function output_title($title) {
        if(1 == uwp_get_option('profile_seo_disable')){
            return $title;
        }

        if(is_uwp_profile_page()) {
            $title = $this->get_meta_title();
        }

        return $title;
    }

    public function output_description() {

        if(is_uwp_profile_page()) {
            $description = $this->get_description();
            echo '<meta name="description" content="' . esc_attr( $description ) . '" />';
        }
    }

    public static function has_yoast() {
        return defined( 'WPSEO_VERSION' );
    }

    public static function has_yoast_14() {
        return ( self::has_yoast() && version_compare( WPSEO_VERSION, '14.0', '>=' ) );
    }

    public function get_title($title) {
        if(1 == uwp_get_option('profile_seo_disable')){
            return $title;
        }

        if(is_uwp_profile_page()) {
            $title = $this->get_meta_title();
        }

        return apply_filters('uwp_seo_profile_meta_title', $title);
    }

    public function get_description($description = '') {
        if(1 == uwp_get_option('profile_seo_disable')){
            return $description;
        }

        if(is_uwp_profile_page()) {
            $description = $this->get_meta_description();
        }

        $length = uwp_get_option('profile_seo_meta_description_length', 150);

        return apply_filters('uwp_seo_profile_meta_description', substr($description, 0, $length), $description);
    }

    public function get_opengraph_url($url) {
        if(1 == uwp_get_option('profile_seo_disable')){
            return $url;
        }

        if(is_uwp_profile_page()) {
            $displayed_user = uwp_get_displayed_user();
            $displayed_user_id = !empty($displayed_user->ID) ? $displayed_user->ID : 0;
            $url = uwp_build_profile_tab_url($displayed_user_id);
        }

        return $url;
    }

    /**
     * Filter the rewrite rules to work with translated profile slugs.
     *
     * @since 1.2.3.7
     *
     * @param array $rules Rewrite rules.
     * @return array Filtered rewrite rules.
     */
    public function rewrite_rules_array( $rules ) {
        global $sitepress;

        $page_id = uwp_get_page_id( 'profile_page', false );
        if ( empty( $page_id ) ) {
            return $rules;
        }

        $trid = $sitepress->get_element_trid( $page_id, 'post_page' );
        if ( empty( $trid ) ) {
            return $rules;
        }

        $page_slug = get_post_field( 'post_name', $page_id );
        $tr_page_slugs = array();

        if ( $translations = $sitepress->get_element_translations( $trid, 'post_page' ) ) {
            foreach ( $translations as $lang => $translation ) {
                if ( ! empty( $translation->element_id ) ) {
                    $tr_page_slug = get_post_field( 'post_name', $translation->element_id );

                    if ( $tr_page_slug && $tr_page_slug != $page_slug && ! in_array( $tr_page_slug, $tr_page_slugs ) ) {
                        $tr_page_slugs[ $translation->element_id ] = $tr_page_slug;
                    }
                }
            }
        }

        if ( ! empty( $tr_page_slugs ) ) {
            $_rules = $rules;
            $rules = array();

            foreach ( $_rules as $regex => $query ) {
                $rules[ $regex ] = $query;

                if ( strpos( $regex, '^' . $page_slug . '/' ) !== false && strpos( $query, 'page_id=' . $page_id . '&' ) !== false ) {
                    foreach ( $tr_page_slugs as $tr_page_id => $tr_page_slug ) {
                        $_regex = str_replace( '^' . $page_slug . '/', '^' . $tr_page_slug . '/', $regex );
                        $_query = str_replace( 'page_id=' . $page_id . '&', 'page_id=' . $tr_page_id . '&', $query );

                        $rules[ $_regex ] = $_query;
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * Filters the canonical URL.
     *
     * @param string $canonical The current page's generated canonical URL.
     *
     * @return string The filtered canonical URL.
     */
    function filter_profile_page_canonical( $canonical ) {
        $page = new UsersWP_Pages();
        if ( $page->is_profile_page() ) {
            $canonical = get_author_posts_url( get_current_user_id() );
        }

        return $canonical;
    }

}

new UsersWP_Seo();