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

	private function __construct() {
		self::$instance = $this;
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof UsersWP_GeoDirectory_Plugin ) ) {
			self::$instance = new UsersWP_GeoDirectory_Plugin;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	private function setup_actions() {
		if ( is_admin() ) {
			add_filter( 'uwp_get_sections_uwp-addons', array( $this, 'add_gd_tab' ) );
			add_filter( 'uwp_get_settings_uwp-addons', array( $this, 'add_gd_settings' ), 10, 2 );
			add_filter( 'uwp_profile_tabs_predefined_fields', array(
				$this,
				'add_profile_tabs_predefined_fields'
			), 10, 2 );
		} else {
			add_action( 'uwp_profile_listings_tab_content', array( $this, 'add_profile_listings_tab_content' ) );
			add_action( 'uwp_profile_reviews_tab_content', array( $this, 'add_profile_reviews_tab_content' ) );
			add_action( 'uwp_profile_favorites_tab_content', array( $this, 'add_profile_favorites_tab_content' ) );
			add_action( 'uwp_profile_lists_tab_content', array( $this, 'add_profile_gd_lists_tab_content' ) );
			add_action( 'uwp_profile_gd_listings_subtab_content', array( $this, 'gd_get_listings' ), 10, 2 );
			add_action( 'uwp_profile_gd_reviews_subtab_content', array( $this, 'gd_get_reviews' ), 10, 2 );
			add_action( 'uwp_profile_gd_favorites_subtab_content', array( $this, 'gd_get_favorites' ), 10, 2 );
			add_action( 'geodir_after_edit_post_link_on_listing', array(
				$this,
				'geodir_add_post_status_author_page'
			), 11 );
			add_action( 'uwp_dashboard_links', array( $this, 'dashboard_output' ), 10, 2 );
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
		add_filter( 'geodir_dashboard_link_favorite_listing', array( $this, 'dashboard_favorite_links' ), 10, 3 );
		add_filter( 'geodir_dashboard_link_my_listing', array( $this, 'dashboard_listing_links' ), 10, 3 );
		add_filter( 'widget_post_author', array( $this, 'get_widget_post_author' ), 10, 3 );
		add_filter( 'widget_favorites_by_user', array( $this, 'get_widget_favorites_by_user' ), 10, 3 );

		add_filter( 'uwp_tp_posts_post_footer', array( $this, 'posts_footer' ) );
		add_filter( 'uwp_tp_comments_item_footer', array( $this, 'reviews_footer' ), 10, 2 );

		do_action( 'uwp_gd_setup_actions', $this );
	}

	/**
	 * Change the GD listing links to point to the new profile page.
	 *
	 * @param $link
	 * @param $post_type
	 * @param $user_id
	 *
	 * @return string
	 */
	public function dashboard_listing_links( $link, $post_type, $user_id ) {
		$gd_post_types = geodir_get_posttypes( 'array' );
		$link          = uwp_build_profile_tab_url( $user_id, 'listings', $gd_post_types[ $post_type ]['has_archive'] );

		return $link;
	}

	/**
	 * Change the GD fav links to point to the new profile page.
	 *
	 * @param $link
	 * @param $post_type
	 * @param $user_id
	 *
	 * @return string
	 */
	public function dashboard_favorite_links( $link, $post_type, $user_id ) {
		$gd_post_types = geodir_get_posttypes( 'array' );
		$link          = uwp_build_profile_tab_url( $user_id, 'favorites', $gd_post_types[ $post_type ]['has_archive'] );

		return $link;
	}

	/**
	 * Add GD quick links to the logged in Dashboard.
	 *
	 * @param $links
	 * @param $args
	 *
	 * @return array
	 */
	public function dashboard_output( $links, $args = array() ) {

		// check its not disabled
		if ( empty( $args['disable_gd'] ) && class_exists( 'GeoDir_User' ) ) {
			$user_id = get_current_user_id();
			// Add listing links
			$add_links = GeoDir_User::show_add_listings( 'array' );
			if ( ! empty( $add_links ) ) {
				$links['gd_add']   = array();
				$links['gd_add'][] = array(
					'optgroup' => 'open',
					'text'     => defined( 'ADD_LISTING_TEXT' ) ? ADD_LISTING_TEXT : __( 'Add Listing', 'userswp' )
				);
				foreach ( $add_links as $add_link ) {
					$links['gd_add'][] = array(
						'url'  => $add_link['url'],
						'text' => $add_link['text']
					);
				}
				$links['gd_add'][] = array(
					'optgroup' => 'close',
				);
			}

			// My Favourites in Dashboard
			$fav_links = GeoDir_User::show_favourites( $user_id, 'array' );
			if ( ! empty( $fav_links ) ) {
				$links['gd_favs']   = array();
				$links['gd_favs'][] = array(
					'optgroup' => 'open',
					'text'     => defined( 'MY_FAVOURITE_TEXT' ) ? MY_FAVOURITE_TEXT : __( 'My Favorites', 'userswp' )
				);
				foreach ( $fav_links as $fav_link ) {
					$links['gd_favs'][] = array(
						'url'  => $fav_link['url'],
						'text' => $fav_link['text']
					);
				}
				$links['gd_favs'][] = array(
					'optgroup' => 'close',
				);
			}

			// My Listings
			$listing_links = GeoDir_User::show_listings( $user_id, 'array' );
			if ( ! empty( $listing_links ) ) {
				$links['gd_listings']   = array();
				$links['gd_listings'][] = array(
					'optgroup' => 'open',
					'text'     => defined( 'MY_LISTINGS_TEXT' ) ? MY_LISTINGS_TEXT : __( 'My Listings', 'userswp' )
				);
				foreach ( $listing_links as $listing_link ) {
					$links['gd_listings'][] = array(
						'url'  => $listing_link['url'],
						'text' => $listing_link['text']
					);
				}
				$links['gd_listings'][] = array(
					'optgroup' => 'close',
				);
			}

			if ( class_exists( 'GeoDir_Lists_Compatibility' ) ) {
				$listing_links = GeoDir_Lists_Compatibility::geodirectory_dashboard( '', 'array' );
				if ( ! empty( $listing_links ) ) {
					$links['gd_lists']   = array();
					$links['gd_lists'][] = array(
						'optgroup' => 'open',
						'text'     => defined( 'MY_LISTS_TEXT' ) ? MY_LISTS_TEXT : __( 'My Lists', 'userswp' )
					);
					foreach ( $listing_links as $listing_link ) {
						$links['gd_lists'][] = array(
							'url'  => $listing_link['url'],
							'text' => $listing_link['text']
						);
					}
					$links['gd_lists'][] = array(
						'optgroup' => 'close',
					);
				}
			}


		}

		return $links;
	}

	/**
	 * Adds settings tabs for the current userswp addon.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       array $sections Existing sections array.
	 *
	 * @return      array     Tabs array.
	 */
	public function add_gd_tab( $sections ) {

		$sections['uwp_geodirectory'] = __( 'GeoDirectory', 'userswp' );

		return $sections;
	}

	/**
	 * Registers form fields for the current userswp addon settings page.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       array  $settings        Existing settings array.
	 * @param       string $current_section Currrent setting.
	 *
	 * @return      array     Settings array.
	 */
	public function add_gd_settings( $settings, $current_section ) {

		if ( ! empty( $current_section ) && 'uwp_geodirectory' === $current_section ) {

			$gd_posttypes = $this->get_gd_posttypes();

			$settings = apply_filters( 'uwp_addon_gd_options', array(
				array(
					'title' => __( 'GeoDirectory Settings', 'userswp' ),
					'type'  => 'title',
					'id'    => 'addons_gd_settings_options',
				),
				array(
					'id'       => 'gd_profile_listings',
					'name'     => __( 'CPT listings in profile', 'userswp' ),
					'desc'     => __( 'Choose the post types to show listings tab in UsersWP Profile', 'userswp' ),
					'multiple' => true,
					'type'     => 'multiselect',
					'options'  => $gd_posttypes,
				),
				array(
					'id'       => 'gd_profile_reviews',
					'name'     => __( 'CPT reviews in profile', 'userswp' ),
					'desc'     => __( 'Choose the post types to show reviews tab in UsersWP Profile', 'userswp' ),
					'multiple' => true,
					'type'     => 'multiselect',
					'options'  => $gd_posttypes,
				),
				array(
					'id'       => 'gd_profile_favorites',
					'name'     => __( 'CPT favorites in profile', 'userswp' ),
					'desc'     => __( 'Choose the post types to show favorites tab in UsersWP Profile', 'userswp' ),
					'multiple' => true,
					'type'     => 'multiselect',
					'options'  => $gd_posttypes,
				),
				array(
					'id'      => 'geodir_uwp_link_listing',
					'name'    => __( 'Redirect my listing link from GD login box to UsersWP profile', 'userswp' ),
					'desc'    => __( 'If this option is selected, the my listing link from GD loginbox will redirect to listings tab of UsersWP profile.', 'userswp' ),
					'type'    => 'checkbox',
					'default' => '0',
				),
				array(
					'id'      => 'geodir_uwp_link_favorite',
					'name'    => __( 'Redirect favorite link from GD login box to UsersWP profile', 'userswp' ),
					'desc'    => __( 'If this option is selected, the favorite link from GD loginbox will redirect to favorites tab of UsersWP profile.', 'userswp' ),
					'type'    => 'checkbox',
					'default' => '0',
				),
			) );

			$settings[] = array( 'type' => 'sectionend', 'id' => 'addons_gd_settings_options' );
		}

		return $settings;
	}

	/**
	 * Returns GD posttypes
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      array
	 */
	public function get_gd_posttypes() {
		$post_type_arr = array();
		$post_types    = geodir_get_posttypes( 'object' );

		foreach ( $post_types as $key => $post_types_obj ) {
			$post_type_arr[ $key ] = $post_types_obj->labels->singular_name;
		}

		return $post_type_arr;
	}

	/**
	 * Adds predefined field in for profile tabs.
	 *
	 * @package     userswp
	 *
	 * @param       array  $fields    Predefined field array.
	 * @param       string $form_type Form type.
	 *
	 * @return      array    $fields    Predefined field array.
	 */
	public function add_profile_tabs_predefined_fields( $fields, $form_type ) {
		if ( 'profile-tabs' != $form_type ) {
			return $fields;
		}

		$fields[] = array(
			'tab_type'     => 'standard',
			'tab_name'     => __( 'Listings', 'userswp' ),
			'tab_icon'     => 'fas fa-globe-americas',
			'tab_key'      => 'listings',
			'tab_content'  => '',
			'tab_privacy'  => '0',
			'user_decided' => '1',
		);

		$fields[] = array(
			'tab_type'     => 'standard',
			'tab_name'     => __( 'Reviews', 'userswp' ),
			'tab_icon'     => 'fas fa-star',
			'tab_key'      => 'reviews',
			'tab_content'  => '',
			'tab_privacy'  => '0',
			'user_decided' => '1',
		);

		$fields[] = array(
			'tab_type'     => 'standard',
			'tab_name'     => __( 'Favorites', 'userswp' ),
			'tab_icon'     => 'fas fa-heart',
			'tab_key'      => 'favorites',
			'tab_content'  => '',
			'tab_privacy'  => '0',
			'user_decided' => '1',
		);

		if ( class_exists( 'GeoDir_Lists' ) ) {
			$fields[] = array(
				'tab_type'     => 'standard',
				'tab_name'     => __( 'Lists', 'userswp' ),
				'tab_icon'     => 'fas fa-list',
				'tab_key'      => 'lists',
				'tab_content'  => '',
				'tab_privacy'  => '0',
				'user_decided' => '1',
			);
		}

		return $fields;
	}

	/**
	 * Returns listing count
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       int $user_id User ID
	 *
	 * @return      int
	 */
	public function get_total_listings_count( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			return 0;
		}

		$gd_post_types = geodir_get_posttypes( 'array' );

		if ( empty( $gd_post_types ) ) {
			return 0;
		}


		// allowed post types
		$listing_post_types = uwp_get_option( 'gd_profile_listings', array() );

		if ( ! is_array( $listing_post_types ) ) {
			$listing_post_types = array();
		}

		$count       = 0;
		$total_count = 0;
		foreach ( $listing_post_types as $post_type ) {
			if ( array_key_exists( $post_type, $gd_post_types ) ) {

				// get listing count
				$listing_count = $this->get_listings_count( $post_type, $user_id );
				$total_count   += $listing_count;

				$count ++;
			}
		}

		return $total_count;
	}

	/**
	 * Returns listings count
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string $post_type Post type
	 * @param       int    $user_id   User ID
	 *
	 * @return      mixed
	 */
	public function get_listings_count( $post_type, $user_id = 0 ) {
		global $wpdb, $sitepress, $wpml_query_filter;
		if ( empty( $user_id ) ) {
			return 0;
		}

		$post_status = is_super_admin() ? " OR p.post_status = 'private'" : '';
		if ( $user_id && $user_id == get_current_user_id() ) {
			$post_status .= " OR p.post_status = 'draft' OR p.post_status = 'private' OR p.post_status = 'pending' OR p.post_status = 'gd-closed' OR p.post_status = 'gd-expired'";
		}

		$join  = '';
		$where = '';
		if ( uwp_is_wpml() ) {
			if ( ! empty( $wpml_query_filter ) && $sitepress->is_translated_post_type( $post_type ) ) {
				$wpml_join = $wpml_query_filter->filter_single_type_join( '', $post_type );
				$wpml_join = str_replace( " {$wpdb->posts}.", " p.", $wpml_join );
				$join      .= $wpml_join;

				$wpml_where = $wpml_query_filter->filter_single_type_where( '', $post_type );
				$wpml_where = str_replace( array( " {$wpdb->posts} p", " p." ), array(
					" {$wpdb->posts} wpml_p",
					" wpml_p."
				), $wpml_where );
				$wpml_where = str_replace( " {$wpdb->posts}.", " p.", $wpml_where );

				$where .= $wpml_where;
			}
		}

		$count = (int) $wpdb->get_var( "SELECT count( p.ID ) FROM " . $wpdb->prefix . "posts AS p {$join} WHERE p.post_author=" . (int) $user_id . " AND p.post_type='" . $post_type . "' AND ( p.post_status = 'publish' " . $post_status . " ) {$where}" );

		return apply_filters( 'geodir_uwp_count_total', $count, $user_id );
	}

	/**
	 * Returns reviews count
	 *
	 * @package     userswp
	 *
	 * @param       int $user_id User ID
	 *
	 * @return      int
	 */
	public function get_total_reviews_count( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			return 0;
		}

		$gd_post_types = geodir_get_posttypes( 'array' );

		if ( empty( $gd_post_types ) ) {
			return 0;
		}

		// allowed post types
		$listing_post_types = uwp_get_option( 'gd_profile_reviews', array() );

		if ( ! is_array( $listing_post_types ) ) {
			$listing_post_types = array();
		}

		$count       = 0;
		$total_count = 0;
		foreach ( $listing_post_types as $post_type ) {
			if ( array_key_exists( $post_type, $gd_post_types ) ) {

				// get listing count
				$listing_count = $this->geodir_get_reviews_by_user_id( $post_type, $user_id, true );
				$total_count   += $listing_count;

				$count ++;
			}
		}

		return $total_count;
	}

	/**
	 * Returns reviews by user ID
	 *
	 * @package     userswp
	 *
	 * @param       string $post_type  Post type
	 * @param       int    $user_id    User ID
	 * @param       bool   $count_only Return count or object
	 * @param       int    $offset     Offset
	 * @param       int    $limit      Limit
	 *
	 * @return      mixed
	 */
	public function geodir_get_reviews_by_user_id( $post_type, $user_id, $count_only = false, $offset = 0, $limit = 20 ) {
		global $wpdb;

		if(empty($post_type)){
			$post_type = 'gd_place';
        }

		if ( $count_only ) {
			if ( uwp_is_gdv2() ) {
				$results = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(reviews.rating) FROM " . GEODIR_REVIEW_TABLE . " reviews JOIN " . $wpdb->posts . " posts ON reviews.post_id = posts.id WHERE reviews.user_id = %d AND reviews.post_type = %s AND reviews.rating > 0 AND posts.post_status = 'publish'",
						array( $user_id, $post_type )
					)
				);
			} else {
				$results = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(reviews.overall_rating) FROM " . GEODIR_REVIEW_TABLE . " reviews JOIN " . $wpdb->posts . " posts ON reviews.post_id = posts.id WHERE reviews.user_id = %d AND reviews.post_type = %s AND reviews.status=1 AND reviews.overall_rating>0 AND posts.post_status = 'publish'",
						array( $user_id, $post_type )
					)
				);
			}
		} else {
			if ( uwp_is_gdv2() ) {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT reviews.* FROM " . GEODIR_REVIEW_TABLE . " reviews JOIN " . $wpdb->posts . " posts ON reviews.post_id = posts.id WHERE reviews.user_id = %d AND reviews.post_type = %s AND reviews.rating>0 AND posts.post_status = 'publish' LIMIT %d OFFSET %d",
						array( $user_id, $post_type, $limit, $offset )
					)
				);
			} else {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT reviews.* FROM " . GEODIR_REVIEW_TABLE . " reviews JOIN " . $wpdb->posts . " posts ON reviews.post_id = posts.id WHERE reviews.user_id = %d AND reviews.post_type = %s AND reviews.status=1 AND reviews.overall_rating>0 AND posts.post_status = 'publish' LIMIT %d OFFSET %d",
						array( $user_id, $post_type, $limit, $offset )
					)
				);
			}
		}

		if ( ! empty( $results ) ) {
			return $results;
		} else {
			return false;
		}
	}

	/**
	 * Returns favourite count
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       int $user_id User ID
	 *
	 * @return      mixed
	 */
	public function get_total_favorites_count( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			return 0;
		}

		$gd_post_types = geodir_get_posttypes( 'array' );

		if ( empty( $gd_post_types ) ) {
			return 0;
		}

		// allowed post types
		$listing_post_types = uwp_get_option( 'gd_profile_favorites', array() );

		if ( ! is_array( $listing_post_types ) ) {
			$listing_post_types = array();
		}

		$count       = 0;
		$total_count = 0;
		foreach ( $listing_post_types as $post_type ) {
			if ( array_key_exists( $post_type, $gd_post_types ) ) {

				// get listing count
				$listing_count = $this->geodir_count_favorite( $post_type, $user_id );
				$total_count   += $listing_count;

				$count ++;
			}
		}

		return $total_count;
	}

	/**
	 * Returns favourite listings count
	 *
	 * @package     userswp
	 *
	 * @param       string $post_type Post type
	 * @param       int    $user_id   User ID
	 *
	 * @return      int
	 */
	public function geodir_count_favorite( $post_type, $user_id = 0 ) {
		global $wpdb;

		$post_status = is_super_admin() ? " OR " . $wpdb->posts . ".post_status = 'private'" : '';
		if ( $user_id && $user_id == get_current_user_id() ) {
			$post_status .= " OR " . $wpdb->posts . ".post_status = 'draft' OR " . $wpdb->posts . ".post_status = 'private' OR " . $wpdb->posts . ".post_status = 'pending' OR " . $wpdb->posts . ".post_status = 'gd-closed' OR " . $wpdb->posts . ".post_status = 'gd-expired'";
		}

		$user_fav_posts = geodir_get_user_favourites( (int) $user_id );
		$user_fav_posts = ! empty( $user_fav_posts ) ? implode( "','", $user_fav_posts ) : "-1";

		$count = (int) $wpdb->get_var( "SELECT count( ID ) FROM " . $wpdb->posts . " WHERE " . $wpdb->posts . ".ID IN ('" . $user_fav_posts . "') AND post_type='" . $post_type . "' AND ( post_status = 'publish' " . $post_status . " )" );

		return apply_filters( 'uwp_geodir_count_favorite', $count, $user_id );
	}

	/**
	 * Adds GD listings tab content.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user User object.
	 *
	 * @return      void
	 */
	public function add_profile_listings_tab_content( $user ) {
		$this->profile_gd_subtabs_content( $user, 'listings' );
	}

	/**
	 * Displays subtab content
	 *
	 * @package     userswp
	 *
	 * @param       object $user User object
	 * @param       string $type Subtab type
	 *
	 */
	public function profile_gd_subtabs_content( $user, $type = 'listings' ) {
		$subtab      = get_query_var( 'uwp_subtab' );
		$subtabs     = $this->profile_gd_subtabs( $user, $type );
		$default_tab = apply_filters( 'uwp_default_listing_subtab', 'places', $user, $type );
		$post_type   = apply_filters( 'uwp_default_listing_post_type', 'gd_place', $user, $type );
		if ( ! empty( $subtab ) && array_key_exists( $subtab, $subtabs ) ) {
			$active_tab = $subtab;
			$post_type  = $subtabs[ $subtab ]['ptype'];
		} elseif ( ! empty( $subtabs ) ) {
			$subtab_keys = array_keys( $subtabs );
			$active_tab  = $subtab_keys[0];
			$post_type   = $subtabs[ $subtab_keys[0] ]['ptype'];
		} else {
			$active_tab = $default_tab;
		}

		if ( uwp_get_option( "design_style", 'bootstrap' ) ) {
			if ( is_array( $subtabs ) && count( $subtabs ) > 1 ) {
				?>
                <div class="pb-3">
					<?php
					foreach ( $subtabs as $tab_id => $tab ) {

						$tab_url = uwp_build_profile_tab_url( $user->ID, $type, $tab_id );

						$active    = $active_tab == $tab_id ? 'btn-primary' : 'btn-outline-primary';
						$post_type = $active_tab == $tab_id ? $tab['ptype'] : $post_type;
						?>
                        <a id="uwp-profile-gd-<?php echo $tab_id; ?>" href="<?php echo esc_url( $tab_url ); ?>"
                           class=" btn btn-sm <?php echo $active; ?>">
							<?php echo esc_html__( $tab['title'], 'userswp' ); ?>
                            <span class="badge badge-light ml-1"><?php echo $tab['count']; ?></span>
                        </a>
						<?php
					}
					?>
                </div>
				<?php
			}
		} else {
			if ( ! empty( $subtabs ) ) { ?>
                <div class="uwp-profile-subcontent">
                    <div class="uwp-profile-subnav">
                        <ul class="item-list-subtabs-ul">
							<?php
							foreach ( $subtabs as $tab_id => $tab ) {

								$tab_url = uwp_build_profile_tab_url( $user->ID, $type, $tab_id );

								$active    = $active_tab == $tab_id ? ' active' : '';
								$post_type = $active_tab == $tab_id ? $tab['ptype'] : $post_type;
								?>
                                <li id="uwp-profile-gd-<?php echo $tab_id; ?>" class="<?php echo $active; ?>">
                                    <a href="<?php echo esc_url( $tab_url ); ?>">
                                    <span
                                            class="uwp-profile-tab-label uwp-profile-gd-<?php echo $tab_id; ?>-label "><span
                                                class="uwp-profile-tab-sub-ul-count uwp-profile-sub-ul-gd-<?php echo $tab_id; ?>-count"><?php echo $tab['count']; ?></span> <?php echo esc_html__( $tab['title'], 'userswp' ); ?></span>
                                    </a>
                                </li>
								<?php
							}
							?>
                        </ul>
                    </div>
                </div>
			<?php }
		}
		if ( has_action( 'uwp_profile_gd_' . $type . '_subtab_content' ) ) {
			do_action( 'uwp_profile_gd_' . $type . '_subtab_content', $user, $post_type );
		}
	}

	/**
	 * Returns subtab data
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user User object
	 * @param       string $type Subtab type
	 *
	 * @return      array
	 */
	public function profile_gd_subtabs( $user, $type = 'listings' ) {
		$tabs = array();

		$gd_post_types = geodir_get_posttypes( 'array' );

		if ( empty( $gd_post_types ) ) {
			return $tabs;
		}

		// allowed post types
		if ( $type == 'listings' ) {
			$listing_post_types = uwp_get_option( 'gd_profile_listings', array() );
		} elseif ( $type == 'reviews' ) {
			$listing_post_types = uwp_get_option( 'gd_profile_reviews', array() );
		} elseif ( $type == 'favorites' ) {
			$listing_post_types = uwp_get_option( 'gd_profile_favorites', array() );
		} else {
			$listing_post_types = array();
		}

		if ( ! is_array( $listing_post_types ) ) {
			$listing_post_types = array();
		}

		foreach ( $gd_post_types as $post_type_id => $post_type ) {
			if ( in_array( $post_type_id, $listing_post_types ) ) {
				$post_type_slug = $gd_post_types[ $post_type_id ]['has_archive'];

				if ( uwp_is_gdv2() ) {
					$reviews = apply_filters('uwp_profile_gd_show_all_reviews', false, $user, $post_type_id);
					if ( $type == 'favorites' && geodir_cpt_has_favourite_disabled( $post_type_id ) ) {
						continue;
					} elseif ( $type == 'reviews' && $reviews && geodir_cpt_has_rating_disabled( $post_type_id ) ) {
						continue;
					}
				}

				if ( $type == 'listings' ) {
					$count = $this->get_listings_count( $post_type_id, $user->ID );
				} elseif ( $type == 'reviews' ) {
					$count = $this->geodir_get_reviews_by_user_id( $post_type_id, $user->ID, true );
				} elseif ( $type == 'favorites' ) {
					$count = $this->geodir_count_favorite( $post_type_id, $user->ID );
				} else {
					$count = 0;
				}

				if ( ! empty( $count ) && $count > 0 ) {
					$tabs[ $post_type_slug ] = array(
						'title' => $gd_post_types[ $post_type_id ]['labels']['name'],
						'count' => $count,
						'ptype' => $post_type_id
					);
				}
			}
		}

		return apply_filters( 'uwp_profile_gd_tabs', $tabs, $user, $type );
	}

	/**
	 * Adds GD reviews tab content.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user User object.
	 *
	 * @return      void
	 */
	public function add_profile_reviews_tab_content( $user ) {
		$this->profile_gd_subtabs_content( $user, 'reviews' );
	}

	/**
	 * Adds GD Favorites tab content.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user User object.
	 *
	 * @return      void
	 */
	public function add_profile_favorites_tab_content( $user ) {
		$this->profile_gd_subtabs_content( $user, 'favorites' );
	}

	/**
	 * Adds GD Lists tab content.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user User object.
	 *
	 * @return      void
	 */
	public function add_profile_gd_lists_tab_content( $user ) {

		if ( ! class_exists( 'GeoDir_Lists' ) ) {
			return;
		}

		$subtab     = get_query_var( 'uwp_subtab' );
		$uwp_tab = get_query_var('uwp_tab');
		$type       = geodir_lists_slug();
		$user_lists = GeoDir_Lists_Data::get_user_lists( $user->ID );
		$subtabs    = array();
		if ( ! empty( $user_lists ) ) {
			foreach ( $user_lists as $list ) {
				$subtabs[ $list->post_name ] = array(
					'id'    => $list->ID,
					'title' => $list->post_title,
					'count' => count( $this->get_listings_from_list( $list->ID ) ),
				);
			}
		}

		if ( ! empty( $subtabs ) ) {
			$subtab_keys = array_keys( $subtabs );
			$default_tab = isset($subtab_keys[0]) ? $subtab_keys[0] : '';
		} else {
			$default_tab = '';
		}

		$default_tab = apply_filters( 'uwp_default_gd_lists_subtab', $default_tab, $user, $type );
		$active_tab  = ! empty( $subtab ) && array_key_exists( $subtab, $subtabs ) ? $subtab : $default_tab;
		if(! empty( $active_tab ) && isset($subtabs[ $active_tab ]['id'])){
			$active_id = $subtabs[ $active_tab ]['id'];
        } elseif(isset($subtabs[ $default_tab ]['id'])){
			$active_id = $subtabs[ $default_tab ]['id'];
        } else {
			$active_id = '';
        }

		if ( uwp_get_option( "design_style", 'bootstrap' ) ) {
			if ( is_array( $subtabs ) && count( $subtabs ) > 0 ) {
				?>
                <div class="pb-3">
					<?php
					foreach ( $subtabs as $tab_id => $tab ) {
						$tab_url = uwp_build_profile_tab_url( $user->ID, $uwp_tab, $tab_id );
						$active  = $active_tab == $tab_id ? 'btn-primary' : 'btn-outline-primary';
						?>
                        <a id="uwp-profile-gd-<?php echo esc_attr($tab_id); ?>" href="<?php echo esc_url( $tab_url ); ?>"
                           class=" btn btn-sm <?php echo esc_attr($active); ?>">
							<?php echo esc_html__( $tab['title'], 'userswp' ); ?>
                            <span class="badge badge-light ml-1"><?php echo $tab['count']; ?></span>
                        </a>
						<?php
					}
					?>
                </div>
				<?php
			}
		} else {
			if ( ! empty( $subtabs ) ) { ?>
                <div class="uwp-profile-subcontent">
                    <div class="uwp-profile-subnav">
                        <ul class="item-list-subtabs-ul">
							<?php
							foreach ( $subtabs as $tab_id => $tab ) {
								$tab_url = uwp_build_profile_tab_url( $user->ID, $type, $tab_id );
								$active  = $active_tab == $tab_id ? ' active' : '';
								?>
                                <li id="uwp-profile-gd-<?php echo esc_attr($tab_id); ?>" class="<?php echo esc_attr($active); ?>">
                                    <a href="<?php echo esc_url( $tab_url ); ?>">
                                    <span
                                            class="uwp-profile-tab-label uwp-profile-gd-<?php echo esc_attr($tab_id); ?>-label "><span
                                                class="uwp-profile-tab-sub-ul-count uwp-profile-sub-ul-gd-<?php echo esc_attr($tab_id); ?>-count"><?php echo $tab['count']; ?></span> <?php echo esc_html__( $tab['title'], 'userswp' ); ?></span>
                                    </a>
                                </li>
								<?php
							}
							?>
                        </ul>
                    </div>
                </div>
			<?php }
		}

		$post_ids = $this->get_listings_from_list( $active_id );
		$paged    = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

		if ( ! empty( $post_ids ) ) {
			ob_start();
			$query_args = array(
				'is_geodir_loop' => true,
				'gd_location'    => false,
				'post_type'      => geodir_get_posttypes(),
				'post__in'       => $post_ids,
				'post_status'    => array( 'publish' ),
				'posts_per_page' => uwp_get_option( 'profile_no_of_items', 10 ),
				'paged'          => $paged,
			);

			if ( get_current_user_id() == $user->ID ) {
				$query_args['post_status'] = array(
					'publish',
					'draft',
					'private',
					'pending',
					'gd-closed',
					'gd-expired'
				);
			}

			$the_query = new WP_Query( $query_args );

			$args                               = array();
			$args['template_args']['the_query'] = $the_query;
			$args['template_args']['title']     = $subtabs[ $active_tab ]['title'];

			uwp_get_template( "bootstrap/loop-posts.php", $args );
			echo ob_get_clean();
		}
	}

	public function get_listings_from_list( $list_id ) {
		global $wpdb;
		$post_ids_obj = $wpdb->get_results( $wpdb->prepare( "SELECT p2p_from FROM $wpdb->p2p WHERE p2p_to = %d", absint( $list_id ) ) );
		$post_ids     = wp_list_pluck( $post_ids_obj, 'p2p_from' );

		return $post_ids;
	}

	/**
	 * Adjust the post footer info for GD posts.
	 *
	 * @param $html
	 *
	 * @return string
	 */
	public function posts_footer( $html ) {
		global $post;

		if ( ! empty( $post->post_type ) && uwp_is_gdv2() && geodir_is_gd_post_type( $post->post_type ) ) {
			$post_avgratings = geodir_get_post_rating( $post->ID );
			$post_ratings    = geodir_get_rating_stars( $post_avgratings, $post->ID );
            $actions_shortcode = apply_filters('uwp_profile_gd_post_author_action', '[gd_author_actions text_color="secondary"]');
			$author_actions = do_shortcode( $actions_shortcode );

			$new_html = '<div class="row">';
			$new_html .= '<div class="col">' . $post_ratings . '</div>';
			if ( $author_actions ) {
				// add some bootstrap styles
				$author_actions = str_replace(
					array( "gd_user_action", "gd-author-actions", "gd_delete_post", "btn btn-sm text-white" ),
					array( "gd_user_action dropdown-item position-relative", "", "uwp_gd_delete_post", "" ),
					$author_actions
				);

				$new_html .= '
                        <div class="col-2 text-right">
                        <div class="btn-group dropup">
                          <a href="#"  class="dropdown h5 text-muted m-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw"></i>
                          </a>
                          <div class="dropdown-menu dropdown-menu-right dropdown-caret-0">
                          ' . $author_actions . '
                          </div>
                        </div>
                        </div>
                        ';
			}
			$new_html .= '</div>';

			return $new_html;
		} else {
			return $html;
		}
	}

	/**
	 * Displays listings
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user      User object
	 * @param       string $post_type Post type
	 *
	 */
	public function gd_get_listings( $user, $post_type ) {
		if ( uwp_get_option( "design_style", 'bootstrap' ) ) {
			self::get_bootstrap_listings( $user, $post_type );
		} else {
			$gd_post_types = geodir_get_posttypes( 'array' );

			$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

			$args = array(
				'post_type'      => $post_type,
				'post_status'    => array( 'publish' ),
				'posts_per_page' => uwp_get_option( 'profile_no_of_items', 10 ),
				'author'         => $user->ID,
				'paged'          => $paged,
			);

			if ( get_current_user_id() == $user->ID ) {
				$args['post_status'] = array( 'publish', 'draft', 'private', 'pending', 'gd-closed', 'gd-expired' );
			}
			// The Query
			$the_query = new WP_Query( $args );

			if ( isset( $the_query->found_posts ) && $the_query->found_posts == 0 ) {
				return;
			}
			?>
            <h3><?php _e( $gd_post_types[ $post_type ]['labels']['name'], 'geodirectory' ) ?></h3>

            <div class="uwp-profile-item-block">
				<?php

				do_action( 'uwp_before_profile_listing_items', $user, $post_type );
				// The Loop
				if ( $the_query->have_posts() ) {
					echo '<ul class="uwp-profile-item-ul">';
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						$post_id = get_the_ID();
						global $post;
						$post = geodir_get_post_info( $post_id );
						setup_postdata( $post );
						$post_avgratings = geodir_get_post_rating( $post->ID );
						$post_ratings    = geodir_get_rating_stars( $post_avgratings, $post->ID );
						ob_start();
						if ( uwp_is_gdv2() ) {
							geodir_comments_number();
						} else {
							geodir_comments_number( (int) $post->rating_count );
						}
						$n_comments = ob_get_clean();

						do_action( 'uwp_before_profile_listing_item', $post_id, $user, $post_type );
						?>
                        <li class="uwp-profile-item-li uwp-profile-item-clearfix <?php echo 'gd-post-' . $post_type; ?>">
                            <a class="uwp-profile-item-img" href="<?php echo get_the_permalink(); ?>">
								<?php
								if ( has_post_thumbnail() ) {
									$thumb_url = get_the_post_thumbnail_url( get_the_ID(), array( 80, 80 ) );
								} else {
									$thumb_url = uwp_get_default_thumb_uri();
								}
								?>
                                <img class="uwp-profile-item-alignleft uwp-profile-item-thumb"
                                     src="<?php echo $thumb_url; ?>">
                            </a>

							<?php do_action( 'uwp_before_profile_listing_title', $post_id, $user, $post_type ); ?>
                            <h3 class="uwp-profile-item-title">
                                <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                            </h3>
							<?php do_action( 'uwp_after_profile_listing_title', $post_id, $user, $post_type ); ?>

                            <div class="uwp-time-ratings-wrap">
								<?php do_action( 'uwp_before_profile_listing_ratings', $post_id, $user, $post_type ); ?>
                                <time class="uwp-profile-item-time published"
                                      datetime="<?php echo get_the_time( 'c' ); ?>">
									<?php echo get_the_date(); ?>
                                </time>
								<?php echo '<div class="uwp-ratings">' . $post_ratings . ' <a href="' . get_comments_link() . '" class="uwp-num-comments">' . $n_comments . '</a></div>'; ?>
								<?php
								if ( ! is_user_logged_in() ) {
									do_action( 'geodir_after_favorite_html', $post->ID, 'listing' );
								}
								?>
								<?php do_action( 'uwp_after_profile_listing_ratings', $post_id, $user, $post_type ); ?>
                            </div>

                            <div class="uwp-profile-item-summary">
								<?php
								do_action( 'uwp_before_profile_listing_summary', $post_id, $user, $post_type );
								$excerpt = strip_shortcodes( wp_trim_words( get_the_excerpt(), 15, '...' ) );
								echo $excerpt;
								do_action( 'uwp_after_profile_listing_summary', $post_id, $user, $post_type );
								?>
                            </div>

                            <div class="uwp-item-actions">
								<?php
								if ( is_user_logged_in() ) {
									geodir_favourite_html( '', $post->ID );
								}
								if ( $post->post_author == get_current_user_id() ) {
									if ( uwp_is_gdv2() ) {
										$href     = 'javascript:void(0);';
										$class    = '';
										$editlink = geodir_edit_post_link( $post_id );
										$extra    = 'onclick="uwp_gd_delete_post(' . $post_id . ');"';
									} else {
										$ajaxlink     = geodir_get_ajax_url();
										$href         = geodir_getlink( $ajaxlink, array(
											'geodir_ajax' => 'add_listing',
											'ajax_action' => 'delete',
											'pid'         => $post->ID
										), false );
										$class        = 'geodir-delete';
										$addplacelink = get_permalink( geodir_add_listing_page_id() );
										$editlink     = geodir_getlink( $addplacelink, array( 'pid' => $post->ID ), false );
										$extra        = '';
									}
									?>

                                    <span class="geodir-authorlink clearfix">

                                    <?php do_action( 'geodir_before_edit_post_link_on_listing', $post_id, $user, $post_type ); ?>

                                        <a href="<?php echo esc_url( $editlink ); ?>" class="geodir-edit"
                                           title="<?php _e( 'Edit Listing', 'userswp' ); ?>">
                                            <?php
                                            $geodir_listing_edit_icon = apply_filters( 'geodir_listing_edit_icon', 'fas fa-edit' );
                                            echo '<i class="' . $geodir_listing_edit_icon . '"></i>';
                                            ?>
                                            <?php _e( 'Edit', 'userswp' ); ?>
                                        </a>
                                        <a href="<?php echo $href; ?>"
                                           <?php echo $extra; ?>class="<?php echo $class; ?>"
                                           title="<?php _e( 'Delete Listing', 'userswp' ); ?>">
                                            <?php
                                            $geodir_listing_delete_icon = apply_filters( 'geodir_listing_delete_icon', 'fas fa-times', $post_id, $user, $post_type );
                                            echo '<i class="' . $geodir_listing_delete_icon . '"></i>';
                                            ?>
                                            <?php _e( 'Delete', 'userswp' ); ?>
                                        </a>
										<?php do_action( 'geodir_after_edit_post_link_on_listing', $post_id, $user, $post_type ); ?>

                                </span>

								<?php } ?>
                            </div>
                        </li>
						<?php
						do_action( 'uwp_after_profile_listing_item', $post_id, $user, $post_type );
					}
					echo '</ul>';
					/* Restore original Post Data */
					wp_reset_postdata();
				} else {
					echo aui()->alert( array(
						'type'    => 'info',
						'content' => sprintf( __( "No %s found.", 'userswp' ), $gd_post_types[ $post_type ]['labels']['name'] )
					) );
				}
				do_action( 'uwp_after_profile_listing_items', $user, $post_type );

				do_action( 'uwp_profile_pagination', $the_query->max_num_pages );
				?>
            </div>
			<?php
		}
	}

	/**
	 * Displays subtab content
	 *
	 * @package     userswp
	 *
	 * @param       object $user      User object
	 * @param       string $post_type Post type
	 *
	 */
	public function get_bootstrap_listings( $user, $post_type ) {

		$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

		$query_args = array(
			'post_type'      => $post_type,
			'post_status'    => array( 'publish' ),
			'posts_per_page' => uwp_get_option( 'profile_no_of_items', 10 ),
			'author'         => $user->ID,
			'paged'          => $paged,
		);

		if ( get_current_user_id() == $user->ID ) {
			$query_args['post_status'] = array( 'publish', 'draft', 'private', 'pending', 'gd-closed', 'gd-expired' );
		}
		// The Query
		$the_query     = new WP_Query( $query_args );
		$gd_post_types = geodir_get_posttypes( 'array' );

		$args                               = array();
		$args['template_args']['the_query'] = $the_query;
		$args['template_args']['title']     = __( $gd_post_types[ $post_type ]['labels']['name'], 'geodirectory' );

		uwp_get_template( "bootstrap/loop-posts.php", $args );

	}

	/**
	 * Adjust the post footer info for GD posts.
	 *
	 * @param $html
	 *
	 * @return string
	 */
	public function reviews_footer( $html, $comment ) {
		if ( ! empty( $comment->post_type ) && geodir_is_gd_post_type( $comment->post_type ) ) {
			$comment_rating      = geodir_get_comment_rating( $comment->ID );
			$comment_rating_html = geodir_get_rating_stars( $comment_rating, $comment->comment_post_ID );

			$new_html     = '<div class="row">';
			$new_html     .= '<div class="col">' . $comment_rating_html . '</div>';
			$comment_link = '<a href="' . get_comment_link( $comment->comment_ID ) . '" class="btn btn-sm btn-outline-primary float-right"><i class="fas fa-comments"></i> ' . esc_attr__( "View", "userswp" ) . '</a>';

			$new_html .= '<div class="col">' . $comment_link . '</div>';

			$new_html .= '</div>';

			return $new_html;
		} else {
			return $html;
		}
	}

	/**
	 * Displays reviews
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user      User object
	 * @param       string $post_type Post type
	 *
	 */
	public function gd_get_reviews( $user, $post_type ) {
		if ( uwp_get_option( "design_style", 'bootstrap' ) ) {
			self::get_bootstrap_reviews( $user, $post_type );
		} else {
			$gd_post_types = geodir_get_posttypes( 'array' );

			$paged  = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
			$limit  = uwp_get_option( 'profile_no_of_items', 10 );
			$offset = ( $paged - 1 ) * $limit;

			$total_reviews = $this->geodir_get_reviews_by_user_id( $post_type, $user->ID, true, $offset, $limit );
			$maximum_pages = ceil( $total_reviews / $limit );

			$reviews = $this->geodir_get_reviews_by_user_id( $post_type, $user->ID, false, $offset, $limit );

			// The Loop
			if ( !$reviews ) {
			    return;
			}
				do_action( 'uwp_before_profile_reviews_items', $user, $post_type );
				?>
                <h3><?php _e( $gd_post_types[ $post_type ]['labels']['name'], 'geodirectory' ); ?></h3>

                <div class="uwp-profile-item-block">
                    <ul class="uwp-profile-item-ul">
						<?php
						foreach ( $reviews as $review ) {
							$rating = 0;
							if ( ! empty( $review ) ) {
								$rating = geodir_get_post_rating( $review->post_id );
							}
							do_action( 'uwp_before_profile_reviews_item', $review->comment_id, $user, $post_type );
							?>
                            <li class="uwp-profile-item-li uwp-profile-item-clearfix <?php echo 'gd-post-' . $post_type; ?>">
                                <a class="uwp-profile-item-img"
                                   href="<?php echo get_comment_link( $review->comment_id ); ?>">
									<?php
									if ( has_post_thumbnail( $review->post_id ) ) {
										$thumb_url = get_the_post_thumbnail_url( $review->post_id, array( 80, 80 ) );
									} else {
										$thumb_url = uwp_get_default_thumb_uri();
									}
									?>
                                    <img class="uwp-profile-item-alignleft uwp-profile-item-thumb"
                                         src="<?php echo $thumb_url; ?>">
                                </a>

								<?php do_action( 'uwp_before_profile_reviews_title', $review->comment_id, $user, $post_type ); ?>
                                <h3 class="uwp-profile-item-title">
                                    <a href="<?php echo get_comment_link( $review->comment_id ); ?>"><?php echo get_the_title( $review->post_id ); ?></a>
                                </h3>
								<?php do_action( 'uwp_after_profile_reviews_title', $review->comment_id, $user, $post_type ); ?>

                                <div class="uwp-time-ratings-wrap">
									<?php do_action( 'uwp_before_profile_reviews_ratings', $review->comment_id, $user, $post_type ); ?>
                                    <time class="uwp-profile-item-time published"
                                          datetime="<?php echo get_the_time( 'c' ); ?>">
										<?php echo date_i18n( get_option( 'date_format' ), strtotime( get_comment_date( "", $review->comment_id ) ) ); ?>
                                    </time>
									<?php echo '<div class="uwp-ratings">' . geodir_get_rating_stars( $rating, $review->comment_id ) . '</div>'; ?>
									<?php do_action( 'uwp_after_profile_reviews_ratings', $review->comment_id, $user, $post_type ); ?>
                                </div>
                                <div class="uwp-profile-item-summary">
									<?php
									do_action( 'uwp_before_profile_reviews_summary', $review->comment_id, $user, $post_type );
									$excerpt = strip_shortcodes( wp_trim_words( get_comment_excerpt( $review->comment_id ), 15, '...' ) );
									echo $excerpt;
									do_action( 'uwp_after_profile_reviews_summary', $review->comment_id, $user, $post_type );
									?>
                                </div>
                                <div class="uwp-item-actions">
									<?php
									if ( is_user_logged_in() ) {
										geodir_favourite_html( '', $review->post_id );
									}
									if ( get_post_field( 'post_author', $review->post_id ) == get_current_user_id() ) {
										if ( uwp_is_gdv2() ) {
											$href     = 'javascript:void(0);';
											$class    = '';
											$editlink = geodir_edit_post_link( $review->post_id );
											$extra    = 'onclick="uwp_gd_delete_post(' . $review->post_id . ');"';
										} else {
											$ajaxlink     = geodir_get_ajax_url();
											$href         = geodir_getlink( $ajaxlink, array(
												'geodir_ajax' => 'add_listing',
												'ajax_action' => 'delete',
												'pid'         => $review->post_id
											), false );
											$class        = 'geodir-delete';
											$addplacelink = get_permalink( geodir_add_listing_page_id() );
											$editlink     = geodir_getlink( $addplacelink, array( 'pid' => $review->post_id ), false );
											$extra        = '';
										}
										?>

                                        <span class="geodir-authorlink clearfix">

                                        <?php do_action( 'geodir_before_edit_post_link_on_listing', $review->post_id, $user, $post_type ); ?>

                                            <a href="<?php echo esc_url( $editlink ); ?>" class="geodir-edit"
                                               title="<?php _e( 'Edit Listing', 'userswp' ); ?>">
                                            <?php
                                            $geodir_listing_edit_icon = apply_filters( 'geodir_listing_edit_icon', 'fas fa-edit' );
                                            echo '<i class="' . $geodir_listing_edit_icon . '"></i>';
                                            ?>
                                            <?php _e( 'Edit', 'userswp' ); ?>
                                        </a>
                                        <a href="<?php echo $href; ?>" <?php echo $extra; ?>
                                           class="<?php echo $class; ?>"
                                           title="<?php _e( 'Delete Listing', 'userswp' ); ?>">
                                            <?php
                                            $geodir_listing_delete_icon = apply_filters( 'geodir_listing_delete_icon', 'fas fa-times' );
                                            echo '<i class="' . $geodir_listing_delete_icon . '"></i>';
                                            ?>
                                            <?php _e( 'Delete', 'userswp' ); ?>
                                        </a>

											<?php do_action( 'geodir_after_edit_post_link_on_listing', $review->post_id, $user, $post_type ); ?>

                                </span>

									<?php } ?>
                                </div>
                            </li>
							<?php
							do_action( 'uwp_after_profile_reviews_item', $review->comment_id, $user, $post_type );
						}
						?>
                    </ul>

				<?php
				/* Restore original Post Data */
				wp_reset_postdata();

				do_action( 'uwp_after_profile_reviews_items', $user, $post_type );

				do_action( 'uwp_profile_pagination', $maximum_pages );
			?>
            </div>
			<?php
		}
	}

	/**
	 * Displays reviews in bootstrap layout
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user      User object
	 * @param       string $post_type Post type
	 *
	 */
	public function get_bootstrap_reviews( $user, $post_type ) {
		global $userswp, $geodir_post_type;
		$geodir_post_type = $post_type;
		$args = array();

		$args['template_args']['title'] = __( "Reviews", 'userswp' );

		$userswp->profile->get_profile_comments( $user, $post_type, $args );

		$geodir_post_type = '';

	}

	/**
	 * Displays favourite listings
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user      User object
	 * @param       string $post_type Post type
	 *
	 */
	public function gd_get_favorites( $user, $post_type ) {
		if ( uwp_get_option( "design_style", 'bootstrap' ) ) {
			self::get_bootstrap_favorites( $user, $post_type );
		} else {
			$gd_post_types = geodir_get_posttypes( 'array' );

			$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

			$user_fav_posts = geodir_get_user_favourites( $user->ID );

			if ( ! $user_fav_posts ) {
				return;
			}

			$args = array(
				'post_type'      => $post_type,
				'post_status'    => array( 'publish' ),
				'posts_per_page' => uwp_get_option( 'profile_no_of_items', 10 ),
				'post__in'       => $user_fav_posts,
				'paged'          => $paged,
			);

			if ( get_current_user_id() == $user->ID ) {
				$args['post_status'] = array(
					'publish',
					'draft',
					'private',
					'pending',
					'gd-closed',
					'gd-expired'
				);
			}

			// The Query
			$the_query = new WP_Query( $args );

			if ( isset( $the_query->found_posts ) && $the_query->found_posts == 0 ) {
				return;
			}
			?>
            <h3><?php _e( $gd_post_types[ $post_type ]['labels']['name'], 'geodirectory' ) ?></h3>

            <div class="uwp-profile-item-block">
				<?php
				do_action( 'uwp_before_profile_favourite_items', $user, $post_type );
				// The Loop
				if ( $the_query->have_posts() ) {
					echo '<ul class="uwp-profile-item-ul">';
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						$post_id = get_the_ID();
						global $post;
						$post = geodir_get_post_info( $post_id );
						setup_postdata( $post );
						$post_avgratings = geodir_get_post_rating( $post->ID );
						$post_ratings    = geodir_get_rating_stars( $post_avgratings, $post->ID );
						ob_start();
						if ( uwp_is_gdv2() ) {
							geodir_comments_number();
						} else {
							geodir_comments_number( (int) $post->rating_count );
						}
						$n_comments = ob_get_clean();
						do_action( 'uwp_before_profile_favourite_item', $post_id, $user, $post_type );
						?>
                        <li class="uwp-profile-item-li uwp-profile-item-clearfix <?php echo 'gd-post-' . $post_type; ?>">
                            <a class="uwp-profile-item-img" href="<?php echo get_the_permalink(); ?>">
								<?php
								if ( has_post_thumbnail() ) {
									$thumb_url = get_the_post_thumbnail_url( get_the_ID(), array( 80, 80 ) );
								} else {
									$thumb_url = uwp_get_default_thumb_uri();
								}
								?>
                                <img class="uwp-profile-item-alignleft uwp-profile-item-thumb"
                                     src="<?php echo $thumb_url; ?>">
                            </a>

							<?php do_action( 'uwp_before_profile_favourite_title', $post_id, $user, $post_type ); ?>
                            <h3 class="uwp-profile-item-title">
                                <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                            </h3>
							<?php do_action( 'uwp_after_profile_favourite_title', $post_id, $user, $post_type ); ?>

                            <div class="uwp-time-ratings-wrap">
								<?php do_action( 'uwp_before_profile_favourite_ratings', $post_id, $user, $post_type ); ?>
                                <time class="uwp-profile-item-time published"
                                      datetime="<?php echo get_the_time( 'c' ); ?>">
									<?php echo get_the_date(); ?>
                                </time>
								<?php echo '<div class="uwp-ratings">' . $post_ratings . ' <a href="' . get_comments_link() . '" class="uwp-num-comments">' . $n_comments . '</a></div>'; ?>
								<?php
								if ( ! is_user_logged_in() ) {
									do_action( 'geodir_after_favorite_html', $post->ID, 'listing' );
								}
								?>
								<?php do_action( 'uwp_after_profile_favourite_ratings', $post_id, $user, $post_type ); ?>
                            </div>

                            <div class="uwp-profile-item-summary">
								<?php
								do_action( 'uwp_before_profile_favourite_summary', $post_id, $user, $post_type );
								$excerpt = strip_shortcodes( wp_trim_words( get_the_excerpt(), 15, '...' ) );
								echo $excerpt;
								do_action( 'uwp_after_profile_favourite_summary', $post_id, $user, $post_type );
								?>
                            </div>

                            <div class="uwp-item-actions">
								<?php
								if ( is_user_logged_in() ) {
									geodir_favourite_html( '', $post->ID );
								}
								if ( $post->post_author == get_current_user_id() ) {
									if ( uwp_is_gdv2() ) {
										$href     = 'javascript:void(0);';
										$class    = '';
										$editlink = geodir_edit_post_link( $post_id );
										$extra    = 'onclick="uwp_gd_delete_post(' . $post_id . ');"';
									} else {
										$ajaxlink     = geodir_get_ajax_url();
										$href         = geodir_getlink( $ajaxlink, array(
											'geodir_ajax' => 'add_listing',
											'ajax_action' => 'delete',
											'pid'         => $post->ID
										), false );
										$class        = 'geodir-delete';
										$addplacelink = get_permalink( geodir_add_listing_page_id() );
										$editlink     = geodir_getlink( $addplacelink, array( 'pid' => $post->ID ), false );
										$extra        = '';
									}
									?>

                                    <span class="geodir-authorlink clearfix">

                                        <?php do_action( 'geodir_before_edit_post_link_on_listing', $post_id, $user, $post_type ); ?>

                                        <a href="<?php echo esc_url( $editlink ); ?>" class="geodir-edit"
                                           title="<?php _e( 'Edit Listing', 'userswp' ); ?>">
                                            <?php
                                            $geodir_listing_edit_icon = apply_filters( 'geodir_listing_edit_icon', 'fas fa-edit' );
                                            echo '<i class="' . $geodir_listing_edit_icon . '"></i>';
                                            ?>
                                            <?php _e( 'Edit', 'userswp' ); ?>
                                        </a>
                                        <a href="<?php echo $href; ?>" <?php echo $extra; ?>
                                           class="<?php echo $class; ?>"
                                           title="<?php _e( 'Delete Listing', 'userswp' ); ?>">
                                            <?php
                                            $geodir_listing_delete_icon = apply_filters( 'geodir_listing_delete_icon', 'fas fa-times' );
                                            echo '<i class="' . $geodir_listing_delete_icon . '"></i>';
                                            ?>
                                            <?php _e( 'Delete', 'userswp' ); ?>
                                        </a>

										<?php do_action( 'geodir_after_edit_post_link_on_listing', $post_id, $user, $post_type ); ?>

                                </span>

								<?php } ?>
                            </div>
                        </li>
						<?php
						do_action( 'uwp_after_profile_favourite_item', $post_id, $user, $post_type );
					}
					echo '</ul>';
					/* Restore original Post Data */
					wp_reset_postdata();
				}

				do_action( 'uwp_after_profile_favourite_items', $user, $post_type );

				do_action( 'uwp_profile_pagination', $the_query->max_num_pages );
				?>
            </div>
			<?php
		}
	}

	/**
	 * Displays favourite listings in bootstrap layout
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object $user      User object
	 * @param       string $post_type Post type
	 *
	 */
	public function get_bootstrap_favorites( $user, $post_type ) {

		$favorite_ids = geodir_get_user_favourites( $user->ID );
		if ( $favorite_ids ) {

			$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

			$args = array(
				'post_type'      => $post_type,
				'post_status'    => array( 'publish' ),
				'posts_per_page' => uwp_get_option( 'profile_no_of_items', 10 ),
				'paged'          => $paged,
				'post__in'       => $favorite_ids
			);

			if ( get_current_user_id() == $user->ID ) {
				$args['post_status'] = array( 'publish', 'draft', 'private', 'pending', 'gd-closed', 'gd-expired' );
			}
			// The Query
			$the_query     = new WP_Query( $args );
			$gd_post_types = geodir_get_posttypes( 'array' );

			$args['template_args']['the_query'] = $the_query;
			$args['template_args']['title']     = __( $gd_post_types[ $post_type ]['labels']['name'], 'geodirectory' );

			uwp_get_template( "bootstrap/loop-posts.php", $args );
		}

	}

	/**
	 * Returns login URL for GD V1 login form
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string $url
	 * @param       array  $args
	 *
	 * @return      mixed
	 */
	public function get_gd_login_url( $url, $args ) {
		if ( ! uwp_is_gdv2() ) {
			return $url;
		}

		$register_page = uwp_get_page_id( 'register_page', false );
		$login_page    = uwp_get_page_id( 'login_page', false );
		$forgot_page   = uwp_get_page_id( 'forgot_page', false );
		$reset_page    = uwp_get_page_id( 'reset_page', false );

		if ( ! empty( $args ) ) {
			if ( isset( $args['signup'] ) && $args['signup'] ) {
				$page_id = $register_page;
			} elseif ( isset( $args['forgot'] ) && $args['forgot'] ) {
				$page_id = $forgot_page;
			} elseif ( isset( $args['reset'] ) && $args['reset'] ) {
				$page_id = $reset_page;
			} else {
				$page_id = $login_page;
			}
		} else {
			$page_id = $login_page;
		}
		if ( $page_id ) {
			$uwp_url = get_permalink( $page_id );

			if ( strpos( $url, 'redirect_add_listing' ) !== false ) {
				$parsed = wp_parse_url( $url );
				parse_str( $parsed['query'], $query );
				$uwp_url = add_query_arg( array(
					'redirect_to' => $query['redirect_add_listing'],
				), $uwp_url );
			}

			if ( isset( $args['redirect_to'] ) && ! empty( $args['redirect_to'] ) ) {
				$uwp_url = add_query_arg( array(
					'redirect_to' => $args['redirect_to'],
				), $uwp_url );
			}

			$url = $uwp_url;
		}

		return $url;
	}

	/**
	 * GD author redirect
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      mixed
	 */
	public function geodir_uwp_author_redirect() {
		if ( ! empty( $_REQUEST['geodir_dashbord'] ) ) {
			$author = get_query_var( 'author_name' ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

			if ( ! empty( $author ) && ! empty( $author->ID ) ) {
				$favourite = isset( $_REQUEST['list'] ) && $_REQUEST['list'] == 'favourite' ? true : false;
				$post_type = isset( $_REQUEST['stype'] ) ? esc_attr( $_REQUEST['stype'] ) : null;

				$author_id     = $author->ID;
				$author_link   = uwp_build_profile_tab_url( $author_id );
				$gd_post_types = geodir_get_posttypes( 'array' );

				if ( ! empty( $gd_post_types ) && array_key_exists( $post_type, $gd_post_types ) ) {
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

				wp_redirect( $author_link );
				exit;
			}
		}

		return;
	}

	/**
	 * Checks if listing author page
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string $value
	 *
	 * @return      mixed
	 */
	public function geodir_post_status_is_author_page( $value ) {
		return $value || $this->gd_is_listings_tab();
	}

	/**
	 * Check if listing tab
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      bool
	 */
	public function gd_is_listings_tab() {
		global $wp_query, $uwp_profile_tabs_array;

		if ( is_page() && class_exists( 'UsersWP' ) && isset( $wp_query->query_vars['uwp_profile'] ) && ( $profile_page = uwp_get_page_id( 'profile_page', false ) ) ) {
			$active_tab = ! empty( $wp_query->query_vars['uwp_tab'] ) ? $wp_query->query_vars['uwp_tab'] : '';

			if ( empty( $active_tab ) && ! empty( $uwp_profile_tabs_array ) && ! empty( $uwp_profile_tabs_array[0]->tab_key ) ) {
				$active_tab = $uwp_profile_tabs_array[0]->tab_key;
			}

			if ( $active_tab == 'listings' || $active_tab == 'favorites' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Displays post status
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      string|void
	 */
	public function geodir_add_post_status_author_page() {
		global $wpdb, $post;

		$html = '';
		if ( get_current_user_id() ) {
			if ( $this->gd_is_listings_tab() && ! empty( $post ) && isset( $post->post_author ) && $post->post_author == get_current_user_id() ) {

				// we need to query real status direct as we dynamically change the status for author on author page so even non author status can view them.
				$real_status = $wpdb->get_var( "SELECT post_status from $wpdb->posts WHERE ID=$post->ID" );
				$status      = "<strong>(";
				$status_icon = '<i class="fas fa-play"></i>';
				switch ( $real_status ) {
					case 'publish' :
						$status .= __( 'Published', 'userswp' );
						break;
					case 'pending' :
						$status .= __( 'Pending', 'userswp' );
						break;
					case 'gd-closed' :
						$status .= __( 'Closed', 'userswp' );
						break;
					case 'gd-expired' :
						$status .= __( 'Expired', 'userswp' );
						break;
					default :
						$status .= __( 'Not published', 'userswp' );
						break;
				}
				$status .= ")</strong>";

				$html = '<span class="geodir-post-status">' . $status_icon . ' <span class="geodir-status-label">' . __( 'Status: ', 'userswp' ) . '</span>' . $status . '</span>';
			}
		}

		if ( $html != '' ) {
			echo apply_filters( 'geodir_filter_status_text_on_author_page', $html );
		}
	}

	/**
	 * Returns GD login form placeholder
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      string
	 */
	public function gd_login_wid_login_placeholder() {
		if ( ! uwp_is_gdv2() ) {
			return __( 'Username', 'userswp' );
		}
	}

	/**
	 * Returns GD login form name
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      string
	 */
	public function gd_login_wid_login_name() {
		if ( ! uwp_is_gdv2() ) {
			return "username";
		}
	}

	/**
	 * Returns GD login form password name
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      string
	 */
	public function gd_login_wid_login_pwd() {
		if ( ! uwp_is_gdv2() ) {
			return "uwp_login_password";
		}
	}

	/**
	 * Displays nonce field in GD login form
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function gd_login_inject_nonce() {
		if ( ! uwp_is_gdv2() ) {
			?>
            <input type="hidden" name="uwp_login_nonce" value="<?php echo wp_create_nonce( 'uwp-login-nonce' ); ?>"/>
			<?php
		}
	}

	/**
	 * Check author page redirect
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       bool $redirect
	 *
	 * @return      bool
	 *
	 */
	public function check_redirect_author_page( $redirect = false ) {
		if ( $redirect && ! empty( $_REQUEST['geodir_dashbord'] ) ) {
			$author = get_query_var( 'author_name' ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

			if ( ! empty( $author ) && ! empty( $author->ID ) ) {
				$favourite = isset( $_REQUEST['list'] ) && $_REQUEST['list'] == 'favourite' ? true : false;
				$post_type = isset( $_REQUEST['stype'] ) ? esc_attr( $_REQUEST['stype'] ) : null;

				$author_id     = $author->ID;
				$author_link   = uwp_build_profile_tab_url( $author_id );
				$gd_post_types = geodir_get_posttypes( 'array' );

				if ( ! empty( $gd_post_types ) && array_key_exists( $post_type, $gd_post_types ) ) {
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
							$redirect = false; // Do not redirect to dashboard CPT listings.
						}
					}
				}
			}
		}

		return $redirect;
	}

	/**
	 * Check if skip author page
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       bool $uwp_author
	 *
	 * @return      bool
	 *
	 */
	public function skip_uwp_author_page( $uwp_author = true ) {
		if ( geodir_is_page( 'author' ) ) {
			$uwp_author = false;
		}

		return $uwp_author;
	}

	public function get_widget_post_author( $post_author, $instance, $id_base = '' ) {
		if ( isset( $id_base ) && 'gd_listings' == $id_base ) {
			if ( is_uwp_profile_page() && isset( $instance['post_author'] ) && 'current_author' == $instance['post_author'] ) {
				$user = uwp_get_user_by_author_slug();
				if ( $user && isset( $user->ID ) ) {
					$post_author = $user->ID;
				}
			}
		}

		return $post_author;
	}

	public function get_widget_favorites_by_user( $favorites_by_user, $instance, $id_base = '' ) {
		if ( isset( $id_base ) && 'gd_listings' == $id_base ) {
			if ( is_uwp_profile_page() && isset( $instance['favorites_by_user'] ) && 'current_author' == $instance['favorites_by_user'] ) {
				$user = uwp_get_user_by_author_slug();
				if ( $user && isset( $user->ID ) ) {
					$favorites_by_user = $user->ID;
				}
			}
		}

		return $favorites_by_user;
	}
}

$userswp_geodirectory = UsersWP_GeoDirectory_Plugin::get_instance();