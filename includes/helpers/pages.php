<?php
/**
 * Checks whether the current page is of given page type or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string|bool     $type   Page type.
 * @return      bool
 */
function is_uwp_page($type = false) {
    $page = new UsersWP_Pages();
    return $page->is_page($type);
}

/**
 * Checks whether the current page is register page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_register_page() {
    $page = new UsersWP_Pages();
    return $page->is_register_page();
}

/**
 * Checks whether the current page is login page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_login_page() {
    $page = new UsersWP_Pages();
    return $page->is_login_page();
}

/**
 * Checks whether the current page is forgot password page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_forgot_page() {
    $page = new UsersWP_Pages();
    return $page->is_forgot_page();
}

/**
 * Checks whether the current page is change password page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_change_page() {
    $page = new UsersWP_Pages();
    return $page->is_change_page();
}

/**
 * Checks whether the current page is reset password page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_reset_page() {
    $page = new UsersWP_Pages();
    return $page->is_reset_page();
}

/**
 * Checks whether the current page is account page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_account_page() {
    $page = new UsersWP_Pages();
    return $page->is_account_page();
}

/**
 * Checks whether the current page is profile page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_profile_page() {
    $page = new UsersWP_Pages();
    return $page->is_profile_page();
}

/**
 * Checks whether the current page is users page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_users_page() {
    $page = new UsersWP_Pages();
    return $page->is_users_page();
}

/**
 * Checks whether the current page is users list item page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_users_item_page() {
    $page = new UsersWP_Pages();
    return $page->is_user_item_page();
}

/**
 * Checks whether the current page is multi register page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_multi_register_page() {
    $page = new UsersWP_Pages();
    return $page->is_multi_register_page();
}

/**
 * Checks whether the current page is logged in user profile page or not.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      bool
 */
function is_uwp_current_user_profile_page() {
    $page = new UsersWP_Pages();
    return $page->is_current_user_profile_page();
}

/**
 * Returns all available pages as array to use in select dropdown.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      array                      Page array.
 */
function uwp_get_pages() {
    $page = new UsersWP_Pages();
    return $page->get_pages();
}

/**
 * Gets the page slug using the given page type.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string      $page_type      Page type.
 * @return      string                      Page slug.
 */
function uwp_get_page_slug($page_type = 'register_page') {
    $page = new UsersWP_Pages();
    return $page->get_page_slug($page_type);
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
 */
function uwp_create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0, $status = 'publish') {
    $page = new UsersWP_Pages();
    $page->create_page($slug, $option, $page_title, $page_content, $post_parent, $status);
}

/**
 * Generates default UsersWP pages. Usually called during plugin activation.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      void
 */
function uwp_generate_default_pages() {
    $page = new UsersWP_Pages();
    $page->generate_default_pages();
}

function uwp_get_page_id($type, $link = false) {
    $page = new UsersWP_Pages();
    return $page->get_page_id($type, $link);
}

function uwp_get_user_badge($args){
	global $wpdb;

	if ( isset($args) && empty( $args['user_id'] ) ) {
		return;
	}

	$user = get_userdata($args['user_id']);
	if(!$user){
		return;
	}

	$defaults = array(
		'user_id'   => 0,
		'key'       => '',
		'condition' => '',
		'search'    => 'is_equal',
		'badge'     => '',
		'link'     => '',
		'new_window'     => '',
		'bg_color'  => '#0073aa',
		'txt_color' => '#ffffff',
		'size'      => '',
		'alignment' => '',
		'css_class' => '',
		'onclick'   => '',
		'icon_class'=> '',
		'extra_attributes'=> '',
		'tag'       => ''
	);
	$args     = shortcode_atts( $defaults, $args, 'uwp_user_badge' );

	$output = '';
	$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
	$key = $args['key'];
	$badge = $args['badge'];
	$field = array();
	$user_id = $user->ID;

	// Check if there is a specific filter for field.
	if ( has_filter( 'uwp_output_badge_field_key_' . $key ) ) {
		$output = apply_filters( 'uwp_output_badge_field_key_' . $key, $output, $user, $args );
	}

	if ( $key ) {
		$fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND htmlvar_name = '".$key."'");

		if(!$fields){
			return '';
		}

		$field = $fields[0];

		if ( ! empty( $field ) ) {
			// Check if there is a specific filter for key type.
			if ( has_filter( 'uwp_output_badge_key_' . $field->field_type_key ) ) {
				$output = apply_filters( 'uwp_output_badge_key_' . $field->field_type_key, $output, $user, $args, $field );
			}

			// Check if there is a specific filter for condition.
			if ( has_filter( 'uwp_output_badge_condition_' . $args['condition'] ) ) {
				$output = apply_filters( 'uwp_output_badge_condition_' . $args['condition'], $output, $user, $args, $field );
			}
		} else {
			return $output;
		}
	}

	// If not then we run the standard output.
	if ( empty( $output ) ) {
		$search = $args['search'];
		$match_found = $key === '' ? true : false;

		$excluded_fields = uwp_get_excluded_fields();
		if(in_array($field->htmlvar_name, $excluded_fields)){
			$match_value = '';
		} else {
			$match_value = uwp_get_usermeta($user->ID, $field->htmlvar_name, "");
		}

		switch ( $args['condition'] ) {
			case 'is_equal':
				$match_found = (bool) ( $search != '' && $match_value == $search );
				break;
			case 'is_not_equal':
				$match_found = (bool) ( $search != '' && $match_value != $search );
				break;
			case 'is_greater_than':
				$match_found = (bool) ( $search != '' && is_float( $search ) && is_float( $match_value ) && $match_value > $search );
				break;
			case 'is_less_than':
				$match_found = (bool) ( $search != '' && is_float( $search ) && is_float( $match_value ) && $match_value < $search );
				break;
			case 'is_empty':
				$match_found = (bool) ( $match_value === '' || $match_value === false || $match_value === '0' || is_null( $match_value ) );
				break;
			case 'is_not_empty':
				$match_found = (bool) ( $match_value !== '' && $match_value !== false && $match_value !== '0' && ! is_null( $match_value ) );
				break;
			case 'is_contains':
				$match_found = (bool) ( $search != '' && stripos( $match_value, $search ) !== false );
				break;
			case 'is_not_contains':
				$match_found = (bool) ( $search != '' && stripos( $match_value, $search ) === false );
				break;
		}

		$match_found = apply_filters( 'uwp_user_badge_check_match_found', $match_found, $args, $user );

		if ( $match_found ) {
			// Option value
			if ( ! empty( $field->option_values ) ) {
				$option_values = uwp_string_values_to_options( stripslashes_deep( $field->option_values ), true );

				if ( ! empty( $option_values ) ) {
					if ( ! empty( $field->option_values ) && $field->option_values == 'multiselect' ) {
						$values = explode( ',', trim( $match_value, ', ' ) );

						if ( is_array( $values ) ) {
							$values = array_map( 'trim', $values );
						}

						$_match_value = array();
						foreach ( $option_values as $option_value ) {
							if ( isset( $option_value['value'] ) && in_array( $option_value['value'], $values ) ) {
								$_match_value[] = $option_value['label'];
							}
						}

						$match_value = ! empty( $_match_value ) ? implode( ', ', $_match_value ) : '';
					} else {
						foreach ( $option_values as $option_value ) {
							if ( isset( $option_value['value'] ) && $option_value['value'] == $match_value ) {
								$match_value = $option_value['label'];
							}
						}
					}
				}
			}

			$match_value = apply_filters( 'uwp_post_badge_match_value', $match_value, $key, $args, $user, $field );

			// File
			if ( ! empty( $badge ) &&  ! empty( $match_value ) && ! empty( $field->field_type ) && $field->field_type == 'file' ) {
				$badge = $match_value;
			}

			// badge text
			if ( empty( $badge ) && empty($args['icon_class']) ) {
				$badge = $field->site_title;
			}
			if( !empty( $badge ) && $badge = str_replace("%%input%%", $match_value,$badge) ){
				// will be replace in condition check
			}
			if( !empty( $badge ) && $user_id && $badge = str_replace("%%profile_url%%", uwp_build_profile_tab_url($user_id),$badge) ){
				// will be replace in condition check
			}

			//link url, replace vars
			if( !empty( $args['link'] ) && $args['link'] = str_replace("%%input%%", $match_value,$args['link']) ){
				// will be replace in condition check
			}
			if( !empty( $args['link'] ) && $user_id && $args['link'] = str_replace("%%profile_url%%", uwp_build_profile_tab_url($user_id),$args['link']) ){
				// will be replace in condition check
			}

			// replace other post variables
			if(!empty($badge)){
				//$badge = uwp_replace_variables($badge);
			}

			$class = 'badge badge-primary';
			if ( ! empty( $args['size'] ) ) {
				$class .= ' uwp-badge-size badge-' . sanitize_title( $args['size'] );
			}
			if ( ! empty( $args['alignment'] ) ) {
				$class .= ' uwp-badge-align align-' . sanitize_title($args['alignment']);
			}
			if ( ! empty( $args['css_class'] ) ) {
				$class .= ' ' . esc_attr($args['css_class']);
			}

			// data-attributes
			$extra_attributes = '';
			if(!empty($args['extra_attributes'])){
				$extra_attributes = esc_attr( $args['extra_attributes'] );
				$extra_attributes = str_replace("&quot;",'"',$extra_attributes);
			}

			// title
			$title = ! empty( $field->site_title ) ? __( $field->site_title, 'userswp' ) : '';
			if ( ! empty( $title ) ) {
				$title = sanitize_text_field( stripslashes( $title ) );
			}

			$rel = '';
			if(!empty($args['link'])){
				$rel = strpos($args['link'], get_site_url()) !== false ? '' : 'rel="nofollow"';
			}

			$new_window = '';
			if ( ! empty( $args['new_window'] ) ) {
				$new_window = ' target="_blank" ';
			}

			// phone & email link
			if ( ! empty( $field ) && ! empty( $field->field_type ) && ! empty( $args['link'] ) && strpos( $args['link'], 'http' ) !== 0 ) {
				if ( $field->field_type == 'phone' ) {
					$rel = 'rel="nofollow"';
					if ( strpos( $args['link'], 'tel:' ) !== 0 ) {
						$args['link'] = 'tel:' . preg_replace( '/[^0-9+]/', '', $args['link'] );
					}
				} elseif ( $field->field_type == 'email' ) {
					$rel = 'rel="nofollow"';
					if ( strpos( $args['link'], 'mailto:' ) !== 0 ) {
						$args['link'] = 'mailto:' . $args['link'];
					}
				}
			}

			$link = ! empty( $args['link'] ) ? ( $args['link'] == 'javascript:void(0);' ? $args['link'] : esc_url( $args['link'] ) ) : '';

			$style = '';
			if(!empty($args['bg_color'])){
				$style .= "background-color:'" . esc_attr( $args['bg_color'] ) . "';";
			}

			if(!empty($args['bg_color'])){
				$style .= "color:'" . esc_attr( $args['txt_color'] ) . "';";
			}

			$badge = aui()->badge(array(
				'type'  =>  'badge',
				'href'  =>  $link,
				'class'      => $class,
				'id'         => $title.'-'.$user_id,
				'title'      => $title,
				'value'      => $match_value,
				'content'    => '',
				'icon'       => esc_attr($args['icon_class']),
				'onclick'    => esc_attr($args['onclick']),
				'style'      => $style,
			));

			$badge = apply_filters( 'uwp_user_badge_output_badge', $badge, $match_value, $key, $args, $user, $field );

			$output = '<div class="uwp-badge-meta uwp-badge-meta-' . sanitize_title_with_dashes( esc_attr( $title ) ).'"'.$extra_attributes.' title="'.esc_attr( $title ).'">';

			if ( ! empty( $link ) ) {
				$output .= "<a href='" . $link . "' $new_window $rel>";
			}

			$output .= $badge;

			if ( ! empty( $link ) ) {
				$output .= "</a>";
			}

			// we escape the user input from $match_value but we don't escape the user badge input so they can use html like font awesome.
//			$output .= '<' . $tag . ' data-id="' . $user_id . '" class="uwp-badge" data-badge="' . esc_attr($key) . '" data-badge-condition="' . esc_attr($args['condition']) . '" style="background-color:' . esc_attr( $args['bg_color'] ) . ';color:' . esc_attr( $args['txt_color'] ) . ';" ' . $inner_attributes . '>' . $icon . $badge . '</' . $tag . '>';
			$output .= '</div>';
		}
	}

	return $output;
}