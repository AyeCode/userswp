<?php
/**
 * Upgrade related functions.
 *
 * @since 1.0.0
 * @package UsersWP
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds default sorting options in user sorting table
 */
function uwp_upgrade_1230(){
	$updated  = uwp_get_option( "user_sorting_updated" );
	if ( isset($updated) && $updated > 0 ) {
		return;
	}

	global $wpdb;
	$user_sorting_table_name = uwp_get_table_prefix() . 'uwp_user_sorting';
	$fields = array();

	$fields['display_name_asc'] = array(
		'data_type'      => '',
		'field_type'     => 'text',
		'site_title' => __('Display Name (A-Z)', 'userswp'),
		'htmlvar_name'   => 'display_name',
		'field_icon'     => 'fas fa-sort-alpha-up',
		'sort'           => 'asc',
		'old_value'      => 'alpha_asc',
	);

	$fields['display_name_desc'] = array(
		'data_type'      => '',
		'field_type'     => 'text',
		'site_title' => __('Display Name (Z-A)', 'userswp'),
		'htmlvar_name'   => 'display_name',
		'field_icon'     => 'fas fa-sort-alpha-up',
		'sort'           => 'desc',
		'old_value'      => 'alpha_desc',
	);

	$fields['newer'] = array(
		'data_type'      => '',
		'field_type'     => 'newer',
		'site_title' => __('Newer', 'userswp'),
		'htmlvar_name'   => 'newer',
		'field_icon'     => 'fas fa-calendar',
		'sort'           => 'asc',
		'old_value'      => 'newer',
	);

	$fields['older'] = array(
		'data_type'      => '',
		'field_type'     => 'older',
		'site_title' => __('Older', 'userswp'),
		'htmlvar_name'   => 'older',
		'field_icon'     => 'fas fa-calendar',
		'sort'           => 'desc',
		'old_value'      => 'older',
	);

	$fields['first_name_asc'] = array(
		'data_type'      => '',
		'field_type'     => 'text',
		'site_title' => __('First Name (A-Z)', 'userswp'),
		'htmlvar_name'   => 'first_name',
		'field_icon'     => 'fas fa-fa-sort-alpha-up',
		'sort'           => 'asc',
		'old_value'      => 'fname_asc',
	);

	$fields['first_name_desc'] = array(
		'data_type'      => '',
		'field_type'     => 'text',
		'site_title' => __('First Name (Z-A)', 'userswp'),
		'htmlvar_name'   => 'first_name',
		'field_icon'     => 'fas fa-fa-sort-alpha-up',
		'sort'           => 'desc',
		'old_value'      => 'fname_desc',
	);

	$fields['last_name_asc'] = array(
		'data_type'      => '',
		'field_type'     => 'text',
		'site_title' => __('Last Name (A-Z)', 'userswp'),
		'htmlvar_name'   => 'last_name',
		'field_icon'     => 'fas fa-fa-sort-alpha-up',
		'sort'           => 'asc',
		'old_value'      => 'lname_asc',
	);

	$fields['last_name_desc'] = array(
		'data_type'      => '',
		'field_type'     => 'text',
		'site_title' => __('Last Name (Z-A)', 'userswp'),
		'htmlvar_name'   => 'last_name',
		'field_icon'     => 'fas fa-fa-sort-alpha-up',
		'sort'           => 'desc',
		'old_value'      => 'lname_desc',
	);

	$sort_order = 1;
	$default_option = uwp_get_option('users_default_order_by', 'alpha_asc');

	foreach ($fields as $field) {
		if(isset($default_option) && $default_option == $field['old_value']){
			$is_default = 1;
		} else {
			$is_default = 0;
		}
		$wpdb->query(
			$wpdb->prepare(
				"insert into " . $user_sorting_table_name . " set
					field_type = %s,
					site_title = %s,
					htmlvar_name = %s,
					field_icon = %s,
					sort_order = %s,
					is_default = %d,
					sort = %s,
					is_active = %d",
				array(
					$field['field_type'],
					$field['site_title'],
					$field['htmlvar_name'],
					$field['field_icon'],
					$sort_order,
					$is_default,
					$field['sort'],
					1,
				)
			)
		);
		$sort_order++;
	}

	// set as updated
	uwp_update_option( "user_sorting_updated", "1230" );
}

/**
 * Change account fields to not have uwp_account_ prefix.
 */
function uwp_upgrade_1200() {

	// Change the users item page template content and backup the old content
	$page_id = uwp_get_page_id( 'user_list_item_page' );
	$updated = uwp_get_option( "user_list_page_updated" );
	if ( $page_id && ! $updated ) {
		$backup_content = get_post_meta( $page_id, 'uwp_1100_content' );
		if ( ! $backup_content ) {
			$content = get_post_field( 'post_content', $page_id );
			if ( $content ) {
				update_post_meta( $page_id, 'uwp_1100_content', $content );
				wp_update_post( array( 'ID' => $page_id, 'post_content' => '[uwp_users_item]' ) );
				uwp_update_option( "user_list_page_updated", "1100" );
			}
		}
	}

	// Convert tabs
	uwp_upgrade_convert_tabs();

}

/**
 * Convert v1.0 tabs to v1.2.
 */
function uwp_upgrade_convert_tabs() {
	$old_tabs = uwp_get_option( 'enable_profile_tabs' );

	$updated  = uwp_get_option( "user_profile_tabs_updated" );
	$new_tabs = array();
	if ( $old_tabs && ! $updated ) {

		foreach ( $old_tabs as $tab ) {
			$tab_data = array();
			if ( $tab == 'posts' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Posts', 'userswp' ),
					'tab_icon'    => 'fas fa-info-circle',
					'tab_key'     => 'posts',
					'tab_content' => ''
				);
			} elseif ( $tab == 'more_info' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'More Info', 'userswp' ),
					'tab_icon'    => 'fas fa-info-circle',
					'tab_key'     => 'more_info',
					'tab_content' => ''
				);
			} elseif ( $tab == 'comments' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Comments', 'userswp' ),
					'tab_icon'    => 'fas fa-comments',
					'tab_key'     => 'comments',
					'tab_content' => ''
				);
			} elseif ( $tab == 'listings' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Listings', 'userswp' ),
					'tab_icon'    => 'fas fa-globe-americas',
					'tab_key'     => 'listings',
					'tab_content' => ''
				);
			} elseif ( $tab == 'reviews' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Reviews', 'userswp' ),
					'tab_icon'    => 'fas fa-star',
					'tab_key'     => 'reviews',
					'tab_content' => ''
				);
			} elseif ( $tab == 'favorites' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Favorites', 'userswp' ),
					'tab_icon'    => 'fas fa-heart',
					'tab_key'     => 'favorites',
					'tab_content' => ''
				);
			} elseif ( $tab == 'activity' ) {
				$tab_data = array(
					'tab_type'    => 'shortcode',
					'tab_name'    => __( 'Activity', 'userswp' ),
					'tab_icon'    => 'fas fa-cubes',
					'tab_key'     => 'activity',
					'tab_content' => '[uwp_activity]'
				);
			} elseif ( $tab == 'downloads' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Downloads', 'userswp' ),
					'tab_icon'    => 'fas fa-download',
					'tab_key'     => 'downloads',
					'tab_content' => '[uwp_edd_downloads]'
				);
			} elseif ( $tab == 'purchases' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Purchases', 'userswp' ),
					'tab_icon'    => 'fas fa-receipt',
					'tab_key'     => 'purchases',
					'tab_content' => '[uwp_edd_purchases]'
				);
			} elseif ( $tab == 'followers' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Followers', 'userswp' ),
					'tab_icon'    => 'fas fa-chevron-circle-left',
					'tab_key'     => 'followers',
					'tab_content' => '[uwp_followers]'
				);
			} elseif ( $tab == 'followers' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Following', 'userswp' ),
					'tab_icon'    => 'fas fa-chevron-circle-right',
					'tab_key'     => 'following',
					'tab_content' => '[uwp_following]'
				);
			} elseif ( $tab == 'friends' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Friends', 'userswp' ),
					'tab_icon'    => 'fas fa-chevron-circle-right',
					'tab_key'     => 'friends',
					'tab_content' => '[uwp_friends]'
				);
			} elseif ( $tab == 'mycred' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'MyCred', 'userswp' ),
					'tab_icon'    => 'fas fa-star',
					'tab_key'     => 'mycred',
					'tab_content' => '[uwp_mycred]'
				);
			} elseif ( $tab == 'products' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Products', 'userswp' ),
					'tab_icon'    => 'fas fa-info-circle',
					'tab_key'     => 'products',
					'tab_content' => ''
				);
			} elseif ( $tab == 'orders' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Orders', 'userswp' ),
					'tab_icon'    => 'fas fa-info-circle',
					'tab_key'     => 'orders',
					'tab_content' => ''
				);
			} elseif ( $tab == 'jobs' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Jobs', 'userswp' ),
					'tab_icon'    => 'fas fa-briefcase',
					'tab_key'     => 'jobs',
					'tab_content' => ''
				);
			} elseif ( $tab == 'forums' ) {
				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Topics', 'userswp' ),
					'tab_icon'    => 'fas fa-info-circle',
					'tab_key'     => 'topics',
					'tab_content' => ''
				);
				uwp_profile_add_tabs( $tab_data ); // we are adding two here

				$tab_data = array(
					'tab_type'    => 'standard',
					'tab_name'    => __( 'Replies', 'userswp' ),
					'tab_icon'    => 'fas fa-reply-all',
					'tab_key'     => 'replies',
					'tab_content' => ''
				);
			}


			if ( ! empty( $tab_data ) ) {
				uwp_profile_add_tabs( $tab_data );
			}
		}

		// set as updated
		uwp_update_option( "user_profile_tabs_updated", "1200" );
	}

}

/**
 * Change country htmlvar name to uwp_country to prevent conflicts with location manager plugin.
 */
function uwp_upgrade_12013() {
	global $wpdb;

	$default_field = 'country';
	$replace_field = 'uwp_country';
	$fields_table = $wpdb->prefix . 'uwp_form_fields';

	$fields_results = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$fields_table` WHERE `htmlvar_name` = '%s'",$default_field));
	if( !empty($fields_results) && count($fields_results) > 0 ) {
		$wpdb->update( $fields_table,
			array( 'htmlvar_name' => $replace_field ),
			array( 'htmlvar_name' => $default_field )
		);
	}

	$uwp_usermeta_table = $wpdb->prefix . 'uwp_usermeta';

	$usermeta_columns = $wpdb->get_col("SHOW COLUMNS FROM `$uwp_usermeta_table` LIKE '$default_field'");

	if( !empty( $usermeta_columns ) && count($usermeta_columns) > 0 ) {
		$wpdb->query("ALTER TABLE $uwp_usermeta_table CHANGE COLUMN $default_field $replace_field varchar(500) NOT NULL");
	}
}

/**
 * Add TOS and GDPR field in form builder if set in registration form setting.
 */
function uwp_upgrade_1225() {
	global $wpdb;
	$fields = array();

	$reg_tos = uwp_get_option( 'register_gdpr_page', false );
	if ( isset( $reg_tos ) && $reg_tos > 0 ) {
		$fields[] = array(
			'form_type'              => 'account',
			'field_type'             => 'checkbox',
			'data_type'              => 'TINYINT',
			'site_title'             => __( 'GDPR Policy', 'userswp' ),
			'htmlvar_name'           => 'register_gdpr',
			'is_public'              => '1',
			'is_active'              => '1',
			'is_required'            => '1',
			'required_msg'           => __( 'You must read and accept our GDPR policy.', 'userswp' ),
			'is_register_field'      => '1',
			'is_register_only_field' => '1',
		);
	}

	$reg_gdpr = uwp_get_option( 'register_terms_page', false );
	if ( isset( $reg_gdpr ) && $reg_gdpr > 0 ) {
		$fields[] = array(
			'form_type'              => 'account',
			'field_type'             => 'checkbox',
			'data_type'              => 'TINYINT',
			'site_title'             => __( 'Terms & Conditions', 'userswp' ),
			'htmlvar_name'           => 'register_tos',
			'is_public'              => '1',
			'is_active'              => '1',
			'is_required'            => '1',
			'required_msg'           => __( 'You must read and accept our terms and conditions.', 'userswp' ),
			'is_register_field'      => '1',
			'is_register_only_field' => '1',
		);
	}

	if($fields && count($fields) > 0){
		$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
		$form_type         = 'register';
		$form_builder      = new UsersWP_Form_Builder();

		foreach ($fields as $field){
			$site_htmlvar_name = $field['htmlvar_name'];
			$field_type = $field['field_type'];

			$last_order = $wpdb->get_var( "SELECT MAX(sort_order) as last_order FROM " . $extras_table_name );
			$sort_order = (int) $last_order + 1;

			$form_builder->admin_form_field_save( $field );

			$check_html_variable = $wpdb->get_var($wpdb->prepare("select site_htmlvar_name from " . $extras_table_name . " where site_htmlvar_name = %s and form_type = %s ",
				array($site_htmlvar_name, $form_type)));

			if (!$check_html_variable) {
				$wpdb->query(
					$wpdb->prepare(
						"insert into " . $extras_table_name . " set
					form_type = %s,
					field_type = %s,
					site_htmlvar_name = %s,
					sort_order = %s",
						array(
							$form_type,
							$field_type,
							$site_htmlvar_name,
							$sort_order
						)
					)
				);
			}
		}
	}
}