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
		'tag'       => '',
		'popover_title'=> '',
		'popover_text'=> '',
		'tooltip_text'  => '',
		'hover_content'  => '',
		'hover_icon'  => '',
		'type'=> '', // AUI only
		'color'=> '', // AUI only
		'shadow'=> '', // AUI only
	);

	$args     = shortcode_atts( $defaults, $args, 'uwp_user_badge' );

	$output = '';
	$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
	$match_field = $args['key'];
	$badge = $args['badge'];
	$field = array();
	$user_id = $user->ID;

	// Check if there is a specific filter for field.
	if ( has_filter( 'uwp_output_badge_field_key_' . $match_field ) ) {
		$output = apply_filters( 'uwp_output_badge_field_key_' . $match_field, $output, $user, $args );
	}

	if ( $match_field ) {
		$form_id = uwp_get_register_form_id( $user->ID );
		$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND htmlvar_name = %s AND form_id = %d", $match_field, $form_id));

		if(!$fields){
			return $output;
		}

		$field = isset($fields[0]) ? $fields[0] : '';

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
		$match_found = $match_field === '' ? true : false;
		$is_date = ( isset( $field->field_type ) && $field->field_type == 'datepicker' ) ? true : false;
		$is_date = apply_filters( 'uwp_user_badge_is_date', $is_date, $match_field, $field, $args );

		$excluded_fields = uwp_get_excluded_fields();
		if(isset($field->htmlvar_name) && in_array($field->htmlvar_name, $excluded_fields)){
			$match_value = '';
		} else {
			$match_value = uwp_get_usermeta($user->ID, $field->htmlvar_name, "");
		}

		if ( ! $match_found ) {
			if ( $field->field_type == 'datepicker' && empty( $args['condition'] ) || $args['condition'] == 'is_greater_than' || $args['condition'] == 'is_less_than' ) {
				if( ( empty($args['condition']) || $args['condition'] == 'is_less_than' ) && strpos( $search, '-' ) === false ) {
					$search = str_replace('+','',$search);
					$search = '-' . $search;
				} elseif ( $args['condition'] == 'is_greater_than' && strpos( $search, '+' ) === false  ) {
					$search = str_replace('-','',$search);
					$search = '+' . $search;
				}

				$the_time = strtotime(date( 'Y-m-d', $match_value ));
				$until_time   = strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) . ' ' . $search . ' days' );
				$now_time   = strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) );


				if ( ( empty( $args['condition'] ) || $args['condition'] == 'is_less_than' ) && $the_time <= $now_time && $the_time >= $until_time ) {
					$match_found = true;
				} elseif ( $args['condition'] == 'is_greater_than' && $the_time >= $now_time && $the_time <= $until_time ) {
					$match_found = true;
				}
			} else {
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
			}
		}

		$match_found = apply_filters( 'uwp_user_badge_check_match_found', $match_found, $args, $user );

		if ( $match_found ) {
			if ( $is_date && ! empty( $match_value ) && strpos( $match_value, '0000-00-00' ) === false ) {
				$args['datetime'] = mysql2date( 'c', $match_value, false );
			}

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

			$match_value = apply_filters( 'uwp_post_badge_match_value', $match_value, $match_field, $args, $user, $field );

			// File
			if ( ! empty( $badge ) &&  ! empty( $match_value ) && ! empty( $field->field_type ) && $field->field_type == 'file' ) {
				$badge = $match_value;
			}

			// badge text
			if ( empty( $badge ) && empty($args['icon_class']) ) {
				$badge = isset($field->site_title) ? $field->site_title : '';
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
				$badge = uwp_replace_variables($badge, $user_id);
			}
			if(!empty($args['popover_title'])){
				$args['popover_title'] = uwp_replace_variables($args['popover_title'], $user_id);
			}
			if(!empty($args['popover_text'])){
				$args['popover_text'] = uwp_replace_variables($args['popover_text'], $user_id);
			}
			if(!empty($args['tooltip_text'])){
				$args['tooltip_text'] = uwp_replace_variables($args['tooltip_text'], $user_id);
			}
			if(!empty($args['hover_content'])){
				$args['hover_content'] = uwp_replace_variables($args['hover_content'], $user_id);
			}

			$rel = '';
			if(!empty($args['link'])){
				$rel = strpos($args['link'], get_site_url()) !== false ? '' : 'rel="nofollow"';
			}

			$new_window = '';
			if ( ! empty( $args['new_window'] ) ) {
				$new_window = ' target="_blank" ';
			}

			$badge = ! empty( $badge ) ? __( wp_specialchars_decode( $badge, ENT_QUOTES ), 'userswp' ) : '';

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

			$badge = apply_filters( 'uwp_user_badge_output_badge', $badge, $match_value, $match_field, $args, $user, $field );

			$btn_class = 'border-0 align-middle uwp-badge';
			// color
			$color_custom = true;
			if( !empty( $args['color'] ) ) {
				$btn_class .= ' badge-' . sanitize_html_class($args['color']);
				$color_custom = false;
			}else{
				$btn_class .= ' badge-primary'; // custom colors will override this anyway.
			}

			// shadow
			if( !empty( $args['shadow'] ) ) {
				if($args['shadow']=='small'){ $btn_class .= ' shadow-sm'; }
				elseif($args['shadow']=='medium'){ $btn_class .= ' shadow'; }
				elseif($args['shadow']=='large'){ $btn_class .= ' shadow-lg'; }
			}

			// type
			if( !empty( $args['type'] ) && $args['type']=='pill' ){
				$btn_class .= ' badge badge-pill';
			}else{
				$btn_class .= ' badge';
			}

			if ( ! empty( $args['css_class'] ) ) {
				$btn_class .= ' ' .esc_attr($args['css_class']) ;
			}
			$btn_args = array(
				'class'     => $btn_class,
				'content' => $badge,
				'style' => $color_custom ? 'background-color:' . sanitize_hex_color( $args['bg_color'] ) . ';color:' . sanitize_hex_color( $args['txt_color'] ) . ';' : '',
				'data-badge'    => esc_attr($match_field),
				'data-badge-condition'  => esc_attr($args['condition']),
			);

			// onclick
			if(!empty($args['onclick'])){
				$btn_args['onclick'] = esc_attr($args['onclick']);
			}

			// popover / tooltip
			$pop_link = false;
			if(!empty($args['popover_title']) || !empty($args['popover_text'])){
				$btn_args['type'] = "button";
				$btn_args['data-toggle'] = "popover-html";
				$btn_args['data-placement'] = "top";
				$pop_link = true;
				if(!empty($args['popover_title'])){
					$btn_args['title'] = !empty($args['link']) && $args['link']!='#'  ? "<a href='".esc_url($args['link'])."' $new_window $rel>".$args['popover_title']."</a>" : $args['popover_title'];
				}
				if(!empty($args['popover_text'])){
					$btn_args['data-content'] = !empty($args['link']) && $args['link']!='#'  ? "<a href='".esc_url($args['link'])."' $new_window $rel>".$args['popover_text']."</a>" : $args['popover_text'];
				}
			}elseif(!empty($args['tooltip_text'])){
				$btn_args['data-toggle'] = "tooltip";
				$btn_args['data-placement'] = "top";
				$btn_args['title'] = esc_attr($args['tooltip_text']);
			}

			// hover content
			if(!empty($args['hover_content'])){
				$btn_args['hover_content'] = $args['hover_content'];
			}
			if(!empty($args['hover_icon'])){
				$btn_args['hover_icon'] = $args['hover_icon'];
			}

			// style
			$btn_args['style'] = '';
			if($color_custom && !empty($args['bg_color'])){
				$btn_args['style'] .= 'background-color:' . sanitize_hex_color( $args['bg_color'] ) . ';border-color:' . sanitize_hex_color( $args['bg_color'] ).';';
			}
			if($color_custom && !empty($args['txt_color'])){
				$btn_args['style'] .= 'color:' . sanitize_hex_color( $args['txt_color'] ) . ';';
			}

			if(!empty($args['link']) && $args['link']!='#' && !$pop_link){
				$btn_args['href'] = $args['link'];
			}

			if(!empty($args['link']) && $new_window){
				$btn_args['new_window'] = true;
			}

			if(!empty($args['icon_class'])) { $btn_args['icon'] = $args['icon_class'];}

			$output = '<span class="bsui uwp-badge-meta">';
			if(!empty($args['size'])){$output .= '<span class="'.esc_attr($args['size']).'">';}
			$output .= aui()->badge( $btn_args );
			if(!empty($args['size'])){$output .= '</span>';}
			$output .= '</span>';
		}
	}

	return $output;
}

function uwp_aui_colors($include_branding = false){
	$theme_colors = array(
		"primary" => __('Primary', 'userswp'),
		"secondary" => __('Secondary', 'userswp'),
		"success" => __('Success', 'userswp'),
		"danger" => __('Danger', 'userswp'),
		"warning" => __('Warning', 'userswp'),
		"info" => __('Info', 'userswp'),
		"light" => __('Light', 'userswp'),
		"dark" => __('Dark', 'userswp'),
		"white" => __('White', 'userswp'),
		"purple" => __('Purple', 'userswp'),
		"salmon" => __('Salmon', 'userswp'),
		"cyan" => __('Cyan', 'userswp'),
		"gray" => __('Gray', 'userswp'),
		"indigo" => __('Indigo', 'userswp'),
		"orange" => __('Orange', 'userswp'),
	);

	if($include_branding){
		$theme_colors = $theme_colors  + uwp_aui_branding_colors();
	}

	return $theme_colors;
}

function uwp_aui_branding_colors(){
	return array(
		"facebook" => __('Facebook', 'userswp'),
		"twitter" => __('Twitter', 'userswp'),
		"instagram" => __('Instagram', 'userswp'),
		"linkedin" => __('Linkedin', 'userswp'),
		"flickr" => __('Flickr', 'userswp'),
		"github" => __('GitHub', 'userswp'),
		"youtube" => __('YouTube', 'userswp'),
		"wordpress" => __('WordPress', 'userswp'),
		"google" => __('Google', 'userswp'),
		"yahoo" => __('Yahoo', 'userswp'),
		"vkontakte" => __('Vkontakte', 'userswp'),
	);
}

function uwp_replace_variables($text, $user_id = ''){
	// only run if we have a user ID and the start of a var
	if(!empty($user_id) && strpos( $text, '%%' ) !== false){
		$excluded_fields = uwp_get_excluded_fields();
		$user_data = uwp_get_usermeta_row($user_id);
		if(isset($user_data)){
			foreach($user_data as $key => $val) {
				if ( ! in_array( $key, $excluded_fields ) ) {
					$val  = apply_filters( 'uwp_replace_variables_' . $key, $val, $text );
					$text = str_replace( '%%' . $key . '%%', $val, $text );
				}
			}
		}
	}

	return $text;
}