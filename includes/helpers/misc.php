<?php
/**
 * Converts string value to options array.
 * Used in select, multiselect and radio fields.
 * Wraps inside optgroup if available.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string          $option_values          String option values.
 * @param       bool            $translated             Do you want to translate the output?
 *
 * @return      array|null                              Options array.
 */
function uwp_string_values_to_options($option_values = '', $translated = false)
{
	$options = array();
	if ($option_values == '') {
		return NULL;
	}

	if (strpos($option_values, "{/optgroup}") !== false) {
		$option_values_arr = explode("{/optgroup}", $option_values);

		foreach ($option_values_arr as $optgroup) {
			if (strpos($optgroup, "{optgroup}") !== false) {
				$optgroup_arr = explode("{optgroup}", $optgroup);

				$count = 0;
				foreach ($optgroup_arr as $optgroup_str) {
					$count++;
					$optgroup_str = trim($optgroup_str);

					$optgroup_label = '';
					if (strpos($optgroup_str, "|") !== false) {
						$optgroup_str_arr = explode("|", $optgroup_str, 2);
						$optgroup_label = trim($optgroup_str_arr[0]);
						if ($translated && $optgroup_label != '') {
							$optgroup_label = __($optgroup_label, 'userswp');
						}
						$optgroup_label = ucfirst($optgroup_label);
						$optgroup_str = $optgroup_str_arr[1];
					}

					$optgroup3 = uwp_string_to_options($optgroup_str, $translated);

					if ($count > 1 && $optgroup_label != '' && !empty($optgroup3)) {
						$optgroup_start = array(array('label' => $optgroup_label, 'value' => NULL, 'optgroup' => 'start'));
						$optgroup_end = array(array('label' => $optgroup_label, 'value' => NULL, 'optgroup' => 'end'));
						$optgroup3 = array_merge($optgroup_start, $optgroup3, $optgroup_end);
					}
					$options = array_merge($options, $optgroup3);
				}
			} else {
				$optgroup1 = uwp_string_to_options($optgroup, $translated);
				$options = array_merge($options, $optgroup1);
			}
		}
	} else {
		$options = uwp_string_to_options($option_values, $translated);
	}

	return $options;
}

/**
 * Converts string value to options array.
 * Used in select, multiselect and radio fields.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $input          Input String
 * @param       bool        $translated     Do you want to translate the output?
 *
 * @return      array                       Options array.
 */
function uwp_string_to_options($input = '', $translated = false)
{
	$return = array();
	if ($input != '') {
		$input = trim($input);
		$input = rtrim($input, ",");
		$input = ltrim($input, ",");
		$input = trim($input);
	}

	$input_arr = explode(',', $input);

	if (!empty($input_arr)) {
		foreach ($input_arr as $input_str) {
			$input_str = trim($input_str);

			if (strpos($input_str, "/") !== false) {
				$input_str = explode("/", $input_str, 2);
				$label = trim($input_str[0]);
				if ($translated && $label != '') {
					$label = __($label, 'userswp');
				}
				$label = ucfirst($label);
				$value = trim($input_str[1]);
			} else {
				if ($translated && $input_str != '') {
					$input_str = __($input_str, 'userswp');
				}
				$label = ucfirst($input_str);
				$value = $input_str;
			}

			if ($label != '') {
				$return[] = array('label' => $label, 'value' => $value, 'optgroup' => NULL);
			}
		}
	}

	return $return;
}

/**
 * Resizes thumbnail image.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $thumb_image_name
 * @param       string      $image
 * @param       int         $x                      x-coordinate of source point.
 * @param       int         $y                      y-coordinate of source point.
 * @param       int         $src_w                  Source width.
 * @param       int         $src_h                  Source height.
 * @param       float       $scale                  Image scale ratio.
 *
 * @return      mixed                               Resized image.
 */
function uwp_resizeThumbnailImage($thumb_image_name, $image, $x, $y, $src_w, $src_h, $scale){
	uwp_set_php_limits();
	// ignore image creation warnings
	@ini_set('gd.jpeg_ignore_warning', 1);
	/** @noinspection PhpUnusedLocalVariableInspection */
	list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	$imageType = image_type_to_mime_type($imageType);

	$newImageWidth = ceil($src_w * $scale);
	$newImageHeight = ceil($src_h * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	$source = false;
	switch($imageType) {
		case "image/gif":
			$source=imagecreatefromgif($image);
			break;
		case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			$source=imagecreatefromjpeg($image);
			break;
		case "image/png":
		case "image/x-png":
			$source=imagecreatefrompng($image);
			if(apply_filters('uwp_keep_png_transperent', true, $thumb_image_name, $image, $x, $y, $src_w, $src_h)){
				$background = imagecolorallocate($newImage , 0, 0, 0);
				imagecolortransparent($newImage, $background);
				imagealphablending($newImage, false);
				imagesavealpha($newImage, true);
            }
			break;
	}
	imagecopyresampled($newImage,$source,0,0,$x,$y,$newImageWidth, $newImageHeight, $src_w, $src_h);
	$quality = apply_filters( 'uwp_resize_thumb_quality', 100);
	switch($imageType) {
		case "image/gif":
			imagegif($newImage, $thumb_image_name);
			break;
		case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			imagejpeg($newImage, $thumb_image_name, $quality);
			break;
		case "image/png":
		case "image/x-png":
			imagepng($newImage, $thumb_image_name);
			break;
	}

	chmod($thumb_image_name, 0777);
	return $thumb_image_name;
}

/**
 * Try to set higher limits on the fly
 */
function uwp_set_php_limits() {
	error_reporting( 0 );

	// try to set higher limits for import
	$max_input_time     = ini_get( 'max_input_time' );
	$max_execution_time = ini_get( 'max_execution_time' );
	$memory_limit       = ini_get( 'memory_limit' );

	if ( $max_input_time !== 0 && $max_input_time != -1 && ( ! $max_input_time || $max_input_time < 3000 ) ) {
		ini_set( 'max_input_time', 3000 );
	}

	if ( $max_execution_time !== 0 && ( ! $max_execution_time || $max_execution_time < 3000 ) ) {
		ini_set( 'max_execution_time', 3000 );
	}

	if ( $memory_limit && str_replace( 'M', '', $memory_limit ) ) {
		if ( str_replace( 'M', '', $memory_limit ) < 256 ) {
			ini_set( 'memory_limit', '256M' );
		}
	}

	ini_set( 'auto_detect_line_endings', true );
}

/**
 * Logs the error message.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       array|object|string     $log        Error message.
 *
 * @return      void
 */
function uwp_error_log($log){
	/*
	 * A filter to override the debugging setting for function uwp_error_log().
	 */
	$should_log = apply_filters( 'uwp_log_errors', uwp_get_option('enable_uwp_error_log', 0));
	if ( 1 == $should_log ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}

function uwp_get_excluded_users_list() {

	$args = array(
		'fields' => 'ID',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'     => 'uwp_mod',
				'value'   => 'email_unconfirmed',
				'compare' => '=='
			),
			array(
				'key'     => 'uwp_hide_from_listing',
				'value'   => 1,
				'compare' => '=='
			)
		)
	);

	$inactive_users = new WP_User_Query($args);
	$exclude_users = $inactive_users->get_results();

	$excluded_globally = uwp_get_option('users_excluded_from_list');
	if ( !empty($excluded_globally) ) {

		if(is_array($excluded_globally)) {
			$exclude_users = $excluded_globally;
		} else {
			$excluded_users = str_replace(' ', '', $excluded_globally);
			$users_array = explode(',', $excluded_users);
			$exclude_users = array_merge($exclude_users, $users_array);
		}
	}

	return $exclude_users;
}

/**
 * Prints the users page main content.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return   array $users array of users
 */
function get_uwp_users_list($roles = array()) {

	global $wpdb;

	$keyword = false;
	if (isset($_GET['uwps']) && $_GET['uwps'] != '') {
		$keyword = stripslashes(strip_tags($_GET['uwps']));
	}

	$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

	$number = uwp_get_option('users_no_of_items', 10);
	$number = !empty($number) ? $number : 10;

	$where = '';
	$where = apply_filters('uwp_users_search_where', $where, $keyword);

	$exclude_users = uwp_get_excluded_users_list();
	$exclude_users = apply_filters('uwp_excluded_users_from_list', $exclude_users, $where, $keyword);
	$exclude_users = !empty($exclude_users) ? array_unique($exclude_users): array();

	$exclude_query = ' ';$order_by = 'uwp_meta_value'; $order = 'ASC';

	if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
		$sort_by = strip_tags(esc_sql($_GET['uwp_sort_by']));
	} else {
		$sort_by = '';
    }

	if ($sort_by) {
		switch ( $sort_by ) {
			case "newer":
				$order_by = 'registered';
				$order    = 'DESC';
				break;
			case "older":
				$order_by = 'registered';
				$order    = 'ASC';
				break;
		}
	}

	if(!empty($exclude_users)) {
		$exclude_users_list = implode(',', $exclude_users);
		$exclude_query = 'AND '. $wpdb->users.'.ID NOT IN ('.$exclude_users_list.')';
	}

	$users = array();

	if($keyword || $where ) {

		if (empty($where)) {
			$user_query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.*
            FROM $wpdb->users
            INNER JOIN $wpdb->usermeta
            ON ( $wpdb->users.ID = $wpdb->usermeta.user_id )
            WHERE 1=1
            $exclude_query
            AND ( 
                ( $wpdb->usermeta.meta_key = 'first_name' AND $wpdb->usermeta.meta_value LIKE %s ) 
                OR 
                ( $wpdb->usermeta.meta_key = 'last_name' AND $wpdb->usermeta.meta_value LIKE %s ) 
                OR user_login LIKE %s OR user_nicename LIKE %s OR display_name LIKE %s
            )
            ORDER BY display_name ASC",
				array(
					'%' . $keyword . '%',
					'%' . $keyword . '%',
					'%' . $keyword . '%',
					'%' . $keyword . '%',
					'%' . $keyword . '%',
				)
			);
		} else{
			$usermeta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
			$keyword_query = '';

			if($keyword) {
				$keyword_query = " AND (( $wpdb->usermeta.meta_key = 'first_name' AND $wpdb->usermeta.meta_value LIKE '$keyword' ) 
                OR ( $wpdb->usermeta.meta_key = 'last_name' AND $wpdb->usermeta.meta_value LIKE '$keyword' ) 
                OR 'user_login' LIKE '$keyword' OR 'user_nicename' LIKE '$keyword' OR 'display_name' LIKE '$keyword')";
			}

			$user_query = "SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.* FROM $wpdb->users
            INNER JOIN $wpdb->usermeta ON ( $wpdb->users.ID = $wpdb->usermeta.user_id )
            INNER JOIN $usermeta_table ON ( $wpdb->users.ID = $usermeta_table.user_id )
            WHERE 1=1 $keyword_query $exclude_query  $where ORDER BY display_name ASC";
		}

		$user_results = $wpdb->get_results($user_query);
		$get_users = wp_list_pluck($user_results, 'ID');

		if(isset($roles) && is_array($roles) && count($roles) > 0){
			$users = get_users( array( 'role__in' => $roles, 'fields' => array('ID') ) );
			$users = wp_list_pluck( $users, 'ID' );
			if($get_users && count($get_users) > 0 && $users && count($users) > 0){
				foreach ($get_users as $key => $get_user){
					if(!in_array($get_user, $users)){
						unset($get_users[$key]);
					}
				}
			}
		}

		if(!empty($get_users) && is_array($get_users) && count($get_users) > 0){

			$args = array(
				'include' => $get_users,
				'number' => (int) $number,
				'paged' => (int) $paged,
			);

			if(!empty($exclude_users)) {
				$args['exclude'] = $exclude_users;
			}

			if(!empty($meta_key)) {
				$args['meta_key'] = $meta_key;
			}

			if(!empty($order_by) && !empty($order) ) {
				$args['orderby'] = $order_by;
				$args['order'] = $order;
			}

			$uwp_users_query = new WP_User_Query($args);
			$users['users'] = $uwp_users_query->get_results();
			$users['total_users'] = $uwp_users_query->get_total();

		} else {
			$users['users'] = array();
			$users['total_users'] = 0;
		}

	} else {
		$args = array(
			'number' => (int) $number,
			'paged' => (int) $paged,
		);

		if(!empty($exclude_users)) {
			$args['exclude'] = $exclude_users;
		}

		if(isset($roles) && is_array($roles) && count($roles) > 0){
			$include_users = array();
			$users = get_users( array( 'role__in' => $roles, 'fields' => array('ID') ) );
			$users = wp_list_pluck( $users, 'ID' );
			if($users && count($users) > 0){
				$include_users = array_merge($include_users, $users);
				$args['include'] = $include_users;
			}
		}

		if(!empty($meta_key)) {
			$args['meta_key'] = $meta_key;
		}

		if(!empty($order_by) && !empty($order) ) {
			$args['orderby'] = $order_by;
			$args['order'] = $order;
		}

		$uwp_users_query = new WP_User_Query($args);
		$users['users'] = $uwp_users_query->get_results();
		$users['total_users'] = $uwp_users_query->get_total();
	}

	return $users;

}

/**
 * Returns the Users page layout class based on the setting.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      Layout class.
 */
function uwp_get_layout_class($layout, $count_only = false) {
	if(!$layout){
		if(uwp_get_option("design_style",'bootstrap')){
            $value = '3col';
        } else {
			$value = 'list';
        }
	    $layout = uwp_get_option('users_default_layout', $value);
	}

	switch ($layout) {
		case "list":
			$class = "uwp_listview";
			$bs_class = "row-cols-md-1";
			$col_count = 1;
			break;
		case "2col":
			$class = "uwp_gridview uwp_gridview_2col";
			$bs_class = "row-cols-md-2";
			$col_count = 2;
			break;
		case "3col":
			$class = "uwp_gridview uwp_gridview_3col";
			$bs_class = "row-cols-md-3";
			$col_count = 3;
			break;
		case "4col":
			$class = "uwp_gridview uwp_gridview_4col";
			$bs_class = "row-cols-md-4";
			$col_count = 4;
			break;
		case "5col":
			$class = "uwp_gridview uwp_gridview_5col";
			$bs_class = "row-cols-md-5";
			$col_count = 5;
			break;
		default:
			$class = "uwp_listview";
			$bs_class = "row-cols-md-3";
			$col_count = 1;
	}

	if($count_only){
		return $col_count;
	}

	if(uwp_get_option("design_style",'bootstrap')){
	    return $bs_class;
    }

	return $class;
}

add_filter( 'uwp_users_list_ul_extra_class', 'uwp_get_layout_class', 10, 1 );

add_filter( 'get_user_option_metaboxhidden_nav-menus', 'uwp_always_nav_menu_visibility', 10, 3 );

/**
 * Filters nav menu visibility option value.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       mixed       $result     Value for the user's option.
 * @param       string      $option     Name of the option being retrieved.
 * @param       WP_User     $user       WP_User object of the user whose option is being retrieved.
 *
 * @return      array                   Filtered value.
 */
function uwp_always_nav_menu_visibility( $result, $option, $user )
{
	if( is_array($result) && in_array( 'add-users-wp-nav-menu', $result ) ) {
		$result = array_diff( $result, array( 'add-users-wp-nav-menu' ) );
	}

	return $result;
}

// Privacy
add_filter('uwp_account_page_title', 'uwp_account_privacy_page_title', 10, 2);

/**
 * Adds Privacy tab title in Account page.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $title      Privacy title.
 * @param       string      $type       Tab type.
 *
 * @return      string             Title.
 */
function uwp_account_privacy_page_title($title, $type) {

	if ($type == 'privacy') {
		$title = __( 'Privacy', 'userswp' );
	} elseif ($type == 'notifications') {
		$title = __( 'E-Mail Notifications', 'userswp' );
	} elseif ($type == 'delete-account') {
		$title = __( 'Delete Account', 'userswp' );
	} elseif ($type == 'change-password') {
		$title = __( 'Change Password', 'userswp' );
    } elseif ($type == 'wp2fa') {
		$title = __( 'Two-factor Authentication Settings', 'userswp' );
	}

	return $title;
}

add_action('uwp_account_menu_display', 'uwp_add_account_menu_links');

/**
 * Prints "Edit account" page subtab / submenu links. Ex: Privacy
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      void
 */
function uwp_add_account_menu_links() {

	if (isset($_GET['type'])) {
		$type = strip_tags(esc_sql($_GET['type']));
	} else {
		$type = 'account';
	}

	$account_page = uwp_get_page_id('account_page', false);
	$account_page_link = get_permalink($account_page);

	$account_available_tabs = uwp_account_get_available_tabs();

	if (!is_array($account_available_tabs) && count($account_available_tabs) > 0) {
		return;
	}

	$legacy = '<ul class="uwp_account_menu">';
	ob_start();
	?>
    <ul class="navbar-nav m-0 p-0 mt-3 list-unstyled flex-lg-column flex-row flex-wrap" aria-labelledby="account_settings">
		<?php
		foreach( $account_available_tabs as $tab_id => $tab ) {

			if ($tab_id == 'account') {
				$tab_url = $account_page_link;
			} else {
				$tab_url = add_query_arg(array(
					'type' => $tab_id,
				), $account_page_link);
			}

			if (isset($tab['link'])) {
				$tab_url = $tab['link'];
			}

			$active = $type == $tab_id ? ' active' : '';

			?>
            <li class="nav-item m-0 p-0 list-unstyled mx-md-2 mx-2">
                <a class="nav-link text-decoration-none uwp-account-<?php echo $tab_id.' '.$active; ?>" href="<?php echo esc_url( $tab_url ); ?>">
					<?php echo '<i class="'.esc_attr($tab["icon"]).' mr-1 fa-fw"></i>'.sanitize_text_field($tab['title']); ?>
                </a>
            </li>
			<?php

			$legacy .= '<li id="uwp-account-'.$tab_id.'">';
			$legacy .= '<a class="'.$active.'" href="'.esc_url( $tab_url ).'">';
			$legacy .= '<i class="'.esc_attr($tab["icon"]).'"></i>'.sanitize_text_field($tab["title"]);
			$legacy .= '</a></li>';
		}
		?>
    </ul>
	<?php
	$legacy .=  '</ul>';
	$bs_output = ob_get_clean();
	$style = uwp_get_option('design_style', 'bootstrap');
	if(!empty($style)){
		echo $bs_output;
	} else {
		echo $legacy;
	}
}

/**
 * Updates extras fields sort order.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       array       $field_ids      Form extras field ids.
 * @param       string      $form_type      Form type.
 * @param       int      $form_id      Form ID.
 *
 * @return      array|bool                  Sorted field ids.
 */
function uwp_form_extras_field_order($field_ids = array(), $form_type = 'register', $form_id = 1)
{
	global $wpdb;
	$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

	$count = 0;
	if (!empty($field_ids)):
		foreach ($field_ids as $id) {

			$cf = trim($id, '_');

			$wpdb->update(
				$extras_table_name,
				array(
					'sort_order' => $count,
				),
				array( 'id' => $cf, 'form_id' => $form_id )
			);

			$count++;
		}

		return $field_ids;
	else:
		return false;
	endif;
}

/**
 * Uppercase the first character of each word in a string.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $string     String to convert.
 * @param       string      $charset    Charset.
 *
 * @return      string                  Converted string.
 */
function uwp_ucwords($string, $charset='UTF-8') {
	if (function_exists('mb_convert_case')) {
		return mb_convert_case($string, MB_CASE_TITLE, $charset);
	} else {
		return ucwords($string);
	}
}

/**
 * Checks whether the column exists in the table.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $db             Table name.
 * @param       string      $column         Column name.
 *
 * @return      bool
 */
function uwp_column_exist($db, $column)
{
	$table = new UsersWP_Tables();
	return $table->column_exists($db, $column);
}

/**
 * Adds column if not exist in the table.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $db             Table name.
 * @param       string      $column         Column name.
 * @param       string      $column_attr    Column attributes.
 *
 * @return      bool|int                    True when success.
 */
function uwp_add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NOT NULL")
{
	$table = new UsersWP_Tables();
	return $table->add_column_if_not_exist($db, $column, $column_attr);

}

/**
 * Returns excluded custom fields.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array   Excluded custom fields.
 */
function uwp_get_excluded_fields() {
	$excluded = array(
		'password',
		'confirm_password',
		'user_privacy',
	);
	return apply_filters('uwp_excluded_fields',$excluded);
}

/**
 * Formats the currency using currency separator.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string              $number     Currency number.
 * @param       array|string        $cf         Custom field info.
 *
 * @return      string                          Formatted currency.
 */
function uwp_currency_format_number($number='',$cf=''){

	$cs = isset($cf['extra_fields']) ? maybe_unserialize($cf['extra_fields']) : '';

	$symbol = isset($cs['currency_symbol']) ? $cs['currency_symbol'] : '$';
	$decimals = isset($cf['decimal_point']) && $cf['decimal_point'] ? $cf['decimal_point'] : 2;
	$decimal_display = isset($cf['decimal_display']) && $cf['decimal_display'] ? $cf['decimal_display'] : 'if';
	$decimalpoint = '.';

	if(isset($cs['decimal_separator']) && $cs['decimal_separator']=='comma'){
		$decimalpoint = ',';
	}

	$separator = ',';

	if(isset($cs['thousand_separator'])){
		if($cs['thousand_separator']=='comma'){$separator = ',';}
		if($cs['thousand_separator']=='slash'){$separator = '\\';}
		if($cs['thousand_separator']=='period'){$separator = '.';}
		if($cs['thousand_separator']=='space'){$separator = ' ';}
		if($cs['thousand_separator']=='none'){$separator = '';}
	}

	$currency_symbol_placement = isset($cs['currency_symbol_placement']) ? $cs['currency_symbol_placement'] : 'left';

	if($decimals>0 && $decimal_display=='if'){
		if(is_int($number) || floor( $number ) == $number)
			$decimals = 0;
	}

	$number = number_format($number,$decimals,$decimalpoint,$separator);



	if($currency_symbol_placement=='left'){
		$number = $symbol . $number;
	}else{
		$number = $number . $symbol;
	}


	return $number;
}


/**
 * Checks whether the user can make his/her own profile private or not.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      bool
 */
function uwp_can_make_profile_private() {
	$make_profile_private = apply_filters('uwp_user_can_make_profile_private', false);
	return $make_profile_private;
}

/**
 * Returns the installation type.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      Installation type.
 */
function uwp_get_installation_type() {
	// *. Single Site
	if (!is_multisite()) {
		return "single";
	} else {
		// Multisite
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		// Network active.
		if ( is_plugin_active_for_network( 'userswp/userswp.php' ) ) {
			if (defined('UWP_ROOT_PAGES')) {
				if (UWP_ROOT_PAGES == 'all') {
					// *. Multisite - Network Active - Pages on all sites
					return "multi_na_all";
				} else {
					// *. Multisite - Network Active - Pages on specific site
					return "multi_na_site_id";
				}
			} else {
				// Multi - network active - default
				// *. Multisite - Network Active - Pages on main site
				return "multi_na_default";
			}
		} else {
			// * Multisite - Not network active
			return "multi_not_na";
		}
	}
}

/**
 * Returns the table prefix based on the installation type.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      Table prefix
 */
function uwp_get_table_prefix() {
	$tables = new UsersWP_Tables();
	return $tables->get_table_prefix();
}

/**
 * Returns the table prefix based on the installation type.
 *
 * @since       1.0.16
 * @package     userswp
 *
 * @return      string      Table prefix
 */
function get_usermeta_table_prefix() {
	$tables = new UsersWP_Tables();
	return $tables->get_usermeta_table_prefix();
}

/**
 * Converts array to comma separated string.
 *
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $key        Custom field key.
 * @param       string      $value      Custom field value.
 *
 * @return      string                  Converted custom field value string.
 */
function uwp_maybe_serialize($key, $value) {
	$field = uwp_get_custom_field_info($key);
	if (isset($field->field_type) && $field->field_type == 'multiselect' && is_array($value)) {
		$value = implode(",", $value);
	}
	return $value;
}

/**
 * Converts comma separated string to array.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $key        Custom field key.
 * @param       string      $value      Custom field value.
 *
 * @return      array                   Converted custom field value array.
 */
function uwp_maybe_unserialize($key, $value) {
	$field = uwp_get_custom_field_info($key);
	if (isset($field->field_type) && $field->field_type == 'multiselect' && $value) {
		$value = explode(",", $value);
	}
	return $value;
}

/**
 * Creates UsersWP related tables.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      void
 */
function uwp_create_tables()
{
	$tables = new UsersWP_Tables();
	$tables->create_tables();
}

/**
 * Returns tye client IP.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      IP address.
 */
function uwp_get_ip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return apply_filters('uwp_get_ip', $ip);
}

/**
 * Checks whether the string starts with the given string.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $haystack       String to compare with.
 * @param       string      $needle         String to search for.
 *
 * @return      bool                        True when success. False when failure.
 */
function uwp_str_starts_with($haystack, $needle)
{
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

/**
 * Checks whether the string ends with the given string.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $haystack       String to compare with.
 * @param       string      $needle         String to search for.
 *
 * @return      bool                        True when success. False when failure.
 */
function uwp_str_ends_with($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}

/**
 * Returns the font awesome icon value for field type.
 * Displayed in profile tabs.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $type       Field type.
 *
 * @return      string                  Font awesome icon value.
 */
function uwp_field_type_to_fa_icon($type) {
	$field_types = array(
		'text' => 'fas fa-minus',
		'datepicker' => 'fas fa-calendar-alt',
		'textarea' => 'fas fa-bars',
		'time' =>'far fa-clock',
		'checkbox' =>'far fa-check-square',
		'phone' =>'far fa-phone',
		'radio' =>'far fa-dot-circle',
		'email' =>'far fa-envelope',
		'select' =>'far fa-caret-square-down',
		'multiselect' =>'far fa-caret-square-down',
		'url' =>'fas fa-link',
		'file' =>'fas fa-file'
	);

	if (isset($field_types[$type])) {
		return $field_types[$type];
	} else {
		return "";
	}

}

/**
 * Check wpml active or not.
 *
 * @since 1.0.7
 *
 * @return True if WPML is active else False.
 */
function uwp_is_wpml() {
	if (function_exists('icl_object_id')) {
		return true;
	}

	return false;
}

/**
 * Get the element in the WPML current language.
 *
 * @since 1.0.7
 *
 * @param int         $element_id                 Use term_id for taxonomies, post_id for posts
 * @param string      $element_type               Use post, page, {custom post type name}, nav_menu, nav_menu_item, category, tag, etc.
 *                                                You can also pass 'any', to let WPML guess the type, but this will only work for posts.
 * @param bool        $return_original_if_missing Optional, default is FALSE. If set to true it will always return a value (the original value, if translation is missing).
 * @param string|NULL $ulanguage_code              Optional, default is NULL. If missing, it will use the current language.
 *                                                If set to a language code, it will return a translation for that language code or
 *                                                the original if the translation is missing and $return_original_if_missing is set to TRUE.
 *
 * @return int|NULL
 */
function uwp_wpml_object_id( $element_id, $element_type = 'post', $return_original_if_missing = false, $ulanguage_code = null ) {
	if ( uwp_is_wpml() ) {
		if ( function_exists( 'wpml_object_id_filter' ) ) {
			return apply_filters( 'wpml_object_id', $element_id, $element_type, $return_original_if_missing, $ulanguage_code );
		} else {
			return icl_object_id( $element_id, $element_type, $return_original_if_missing, $ulanguage_code );
		}
	}

	return $element_id;
}

/**
 * Check if we might be on localhost.
 *
 * @return bool
 */
function uwp_is_localhost(){
	$localhost = false;

	if( isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME']=='localhost' ){
		$localhost = true;
	}elseif(isset($_SERVER['SERVER_ADDR']) && ( $_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1' )  ){
		$localhost = true;
	}

	return $localhost;
}

function uwp_get_default_avatar_uri(){
	$default = uwp_get_option('profile_default_profile', '');
	if(empty($default)){
		$default = USERSWP_PLUGIN_URL."assets/images/no_profile.png";
	} else {
		$default = wp_get_attachment_url($default);
	}

	return apply_filters('uwp_default_avatar_uri', $default);
}

function uwp_get_default_thumb_uri(){
	$thumb_url = USERSWP_PLUGIN_URL."assets/images/no_thumb.png";
	return apply_filters('uwp_default_thumb_uri', $thumb_url);
}

function uwp_get_default_banner_uri(){
	$banner = uwp_get_option('profile_default_banner', '');
	if(empty($banner)) {
		$banner_url = USERSWP_PLUGIN_URL."assets/images/banner.png";
	} else {
		$banner_url = wp_get_attachment_url($banner);
	}
	return apply_filters('uwp_default_banner_uri', $banner_url);
}

/**
 * Handles multisite upload dir path
 *
 * @param $uploads array upload variable array
 *
 * @return array updated upload variable array.
 */
function uwp_handle_multisite_profile_image($uploads){
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}

	// Network active.
	if ( is_plugin_active_for_network( 'userswp/userswp.php' ) ) {
		$main_site = get_network()->site_id;
		switch_to_blog( $main_site );
		remove_filter( 'upload_dir', 'uwp_handle_multisite_profile_image');
		$uploads = wp_upload_dir();
		restore_current_blog();
	}

	return $uploads;
}

/**
 * let_to_num function.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @since 2.0.0
 * @param $size
 * @return int
 */
function uwp_let_to_num( $size ) {
	$l   = substr( $size, -1 );
	$ret = substr( $size, 0, -1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
	}
	return $ret;
}

function uwp_format_decimal($number, $dp = false, $trim_zeros = false){
	$locale   = localeconv();
	$decimals = array( uwp_get_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

	// Remove locale from string.
	if ( ! is_float( $number ) ) {
		$number = str_replace( $decimals, '.', $number );
		$number = preg_replace( '/[^0-9\.,-]/', '', uwp_clean( $number ) );
	}

	if ( false !== $dp ) {
		$dp     = intval( '' == $dp ? uwp_get_decimal_separator() : $dp );
		$number = number_format( floatval( $number ), $dp, '.', '' );
		// DP is false - don't use number format, just return a string in our format
	} elseif ( is_float( $number ) ) {
		// DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
		$number     = str_replace( $decimals, '.', sprintf( '%.' . uwp_get_rounding_precision() . 'f', $number ) );
		// We already had a float, so trailing zeros are not needed.
		$trim_zeros = true;
	}

	if ( $trim_zeros && strstr( $number, '.' ) ) {
		$number = rtrim( rtrim( $number, '0' ), '.' );
	}

	return $number;
}

/**
 * Return the decimal separator.
 * @since  1.0.20
 * @return string
 */
function uwp_get_decimal_separator() {
	$separator = apply_filters( 'uwp_decimal_separator', '.' );
	return $separator ? stripslashes( $separator ) : '.';
}

/**
 * Get rounding precision for internal UWP calculations.
 * Will increase the precision of uwp_get_decimal_separator by 2 decimals, unless UWP_ROUNDING_PRECISION is set to a higher number.
 *
 * @since 1.0.20
 * @return int
 */
function uwp_get_rounding_precision() {
	$precision = uwp_get_decimal_separator() + 2;
	if ( defined(UWP_ROUNDING_PRECISION) && absint( UWP_ROUNDING_PRECISION ) > $precision ) {
		$precision = absint( UWP_ROUNDING_PRECISION );
	}
	return $precision;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 *
 * @return string|array
 */
function uwp_clean( $var ) {

	if ( is_array( $var ) ) {
		return array_map( 'uwp_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}

}

/**
 * Define a constant if it is not already defined.
 *
 * @since 1.0.21
 *
 * @param string $name Constant name.
 * @param string $value Value.
 */
function uwp_maybe_define( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

function uwp_insert_usermeta(){
	global $wpdb;
	$sort= "user_registered";

	$all_users_id = $wpdb->get_col( $wpdb->prepare(
		"SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC"
		, $sort ));

	//we got all the IDs, now loop through them to get individual IDs
	foreach ( $all_users_id as $user_id ) {
		$user_data = get_userdata($user_id);

		$meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
		$user_meta = array(
			'username' => $user_data->user_login,
			'email' => sanitize_email( $user_data->user_email ),
			'first_name' => $user_data->first_name,
			'last_name' => $user_data->last_name,
			'display_name' => $user_data->display_name,
		);

		$users = $wpdb->get_var($wpdb->prepare("SELECT COUNT(user_id) FROM {$meta_table} WHERE user_id = %d", $user_id));

		if(!empty($users)) {
			$wpdb->update(
				$meta_table,
				$user_meta,
				array('user_id' => $user_id)
			);
		}  else {
			$user_meta['user_id'] = $user_id;
			$wpdb->insert(
				$meta_table,
				$user_meta
			);
		}
	}
}

function uwp_get_localize_data(){
	$uwp_localize_data = array(
		'uwp_more_char_limit' => 100,
		'uwp_more_text' => __('more','userswp'),
		'uwp_less_text' => __('less','userswp'),
		'error' => __('Something went wrong.','userswp'),
		'error_retry' => __('Something went wrong, please retry.','userswp'),
		'uwp_more_ellipses_text' => '...',
		'ajaxurl' => admin_url('admin-ajax.php'),
		'login_modal' => uwp_get_option("design_style",'bootstrap')=='bootstrap' && uwp_get_option("login_modal",1) ? 1 : '',
		'register_modal' => uwp_get_option("design_style",'bootstrap')=='bootstrap' && uwp_get_option("register_modal",1) ? 1 : '',
		'forgot_modal' => uwp_get_option("design_style",'bootstrap')=='bootstrap' && uwp_get_option("forgot_modal",1) ? 1 : '',
		'default_banner' => uwp_get_default_banner_uri(),
	);

	return apply_filters('uwp_localize_data', $uwp_localize_data);
}

function uwp_is_page_builder(){
	if(
		(isset($_GET['elementor-preview']) && $_GET['elementor-preview'] > 0) // elementor
		|| isset( $_REQUEST['et_fb'] ) || isset( $_REQUEST['et_pb_preview'] ) // divi
		|| isset( $_REQUEST['fl_builder'] ) // beaver
		|| ! empty( $_REQUEST['siteorigin_panels_live_editor'] ) // siteorigin
		|| ! empty( $_REQUEST['cornerstone_preview'] ) // cornerstone
		|| ! empty( $_REQUEST['fb-edit'] ) || ! empty( $_REQUEST['fusion_load_nonce'] ) // fusion builder
		|| ! empty( $_REQUEST['ct_builder'] ) || ( ! empty( $_REQUEST['action'] ) && ( substr( $_REQUEST['action'], 0, 11 ) === "oxy_render_"  || substr( $_REQUEST['action'], 0, 10 ) === "ct_render_" )  ) // oxygen
	){
		return true; // builder.
	}

	return false;
}

/**
 * Display a help tip for settings.
 *
 * @param  string $tip Help tip text
 * @param  bool $allow_html Allow sanitized HTML if true or escape
 *
 * @return string
 */
function uwp_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = uwp_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="uwp-help-tip dashicons dashicons-editor-help" title="' . $tip . '"></span>';
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
 *
 * @param string $var
 * @return string
 */
function uwp_sanitize_tooltip( $var ) {
	return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'small'  => array(),
		'span'   => array(),
		'ul'     => array(),
		'li'     => array(),
		'ol'     => array(),
		'p'      => array(),
	) ) );
}

function uwp_all_email_tags( $inline = true, $extra_tags = array() ){
	$tags = array( '[#site_name#]', '[#site_name_url#]', '[#to_name#]', '[#from_name#]', '[#from_email#]', '[#user_name#]', '[#username#]', '[#user_email#]', '[#login_details#]', '[#date_time#]', '[#current_date#]', '[#login_url#]', '[#user_login#]', '[#profile_link#]' );

	if(is_array($extra_tags) && count($extra_tags) > 0){
		$tags = array_merge($extra_tags, $tags);
	}

	$tags = apply_filters( 'uwp_all_email_tags', $tags );

	if ( $inline ) {
		$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
	}

	return $tags;
}

function uwp_wp_new_user_notification_tags( $inline = true, $extra_tags = array() ){
	$tags = array( '[#site_name#]', '[#site_name_url#]', '[#to_name#]', '[#from_name#]', '[#from_email#]', '[#user_name#]', '[#username#]', '[#user_email#]', '[#date_time#]', '[#current_date#]', '[#login_url#]', '[#user_login#]', );

	if(is_array($extra_tags) && count($extra_tags) > 0){
		$tags = array_merge($tags, $extra_tags);
	}

	$tags = apply_filters( 'uwp_wp_new_user_notification_email_tags', $tags );

	if ( $inline ) {
		$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
	}

	return $tags;
}


function uwp_delete_account_email_tags( $inline = true ){
	$tags = array( '[#site_name#]', '[#site_name_url#]', '[#from_name#]', '[#from_email#]', '[#date_time#]', '[#current_date#]', '[#login_url#]', '[#user_login#]' );

	$tags = apply_filters( 'uwp_delete_account_email_tags', $tags );

	if ( $inline ) {
		$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
	}

	return $tags;
}

function uwp_authbox_tags( $inline = true ){
	global $wpdb;

	$tags = array( '[#post_id#]', '[#author_id#]', '[#author_name#]', '[#author_link#]', '[#author_bio#]', '[#author_image#]', '[#author_image_url#]', '[#post_modified#]', '[#post_date#]', '[#author_nicename#]', '[#author_registered#]', '[#author_website#]' );

	$tags = apply_filters('uwp_author_box_default_tags', $tags, $inline);

	$table_name = uwp_get_table_prefix() . 'uwp_usermeta';

	$excluded = uwp_get_excluded_fields();

	$columns = $wpdb->get_col("show columns from $table_name");

	$extra_tags = array_diff($columns,$excluded);

	if( !empty( $extra_tags ) && '' != $extra_tags ) {

		foreach ( $extra_tags as $tag_val ) {
			$tags[] = '[#'.$tag_val.'#]';
		}

	}

	$tags = apply_filters( 'uwp_all_author_box_tags', $tags );

	if ( $inline ) {
		$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
	}

	return $tags;
}

function uwp_get_posttypes() {

	$exclude_posts = array('attachment','revision','nav_menu_item','custom_css','uwp-post');
	$exclude_posttype = apply_filters('uwp_exclude_register_posttype', $exclude_posts);

	$all_posttyps = get_post_types(array('public'   => true,),'objects');

	$display_posttypes = array();

	if( !empty( $all_posttyps ) && '' != $all_posttyps ) {
		foreach ( $all_posttyps as $pt_keys => $pt_values ) {

			if( !in_array($pt_values->name,$exclude_posttype) ) {
				$display_posttypes[$pt_values->name] = $pt_values->label;
			}

		}
	}

	return $display_posttypes;
}

function uwp_get_user_by_author_slug(){
	$url_type = apply_filters('uwp_profile_url_type', 'slug');
	$author_slug = get_query_var('uwp_profile');
	if ($url_type == 'id') {
		$user = get_user_by('id', $author_slug);
	} else {
		$user = get_user_by('slug', $author_slug);
	}

	return $user;
}

function uwp_get_show_in_locations(){
	$show_in_locations = array(
		"[users]" => __("Users Page", 'userswp'),
		"[more_info]" => __("More info tab", 'userswp'),
		"[profile_side]" => __("Profile side (non bootstrap)", 'userswp'),
		"[fieldset]" => __("Fieldset", 'userswp'),
	);

	$show_in_locations = apply_filters('uwp_show_in_locations', $show_in_locations);

	return $show_in_locations;
}

function uwp_get_displayed_user(){
	global $uwp_user;
	$user = uwp_get_user_by_author_slug(); // for user displayed in profile

	if(!$user && is_user_logged_in()){
		$user = get_userdata(get_current_user_id()); // for user currently logged in
	}

	if(isset($uwp_user) && !empty($uwp_user) && $uwp_user instanceof WP_User){ // for user displaying in loop
		$user = $uwp_user;
	}

	return apply_filters('uwp_get_displayed_user', $user);
}

function uwp_is_gdv2(){

	if(defined('GEODIRECTORY_VERSION') && version_compare(GEODIRECTORY_VERSION,'2.0.0.0', '>=') ) {
		return true;
	}

	return false;
}

function uwp_get_blogname() {
	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	return apply_filters( 'uwp_get_blogname', $blogname );
}

/**
 * RGB from hex.
 *
 * @since 1.2.1.3
 *
 * @param string $color Color.
 * @return array $rgb.
 */
function uwp_rgb_from_hex( $color ) {
	$color = str_replace( '#', '', $color );

	// Convert shorthand colors to full format, e.g. "FFF" -> "FFFFFF"
	$color = preg_replace( '~^(.)(.)(.)$~', '$1$1$2$2$3$3', $color );
	if ( empty( $color ) ) {
		return NULL;
	}

	$color = str_split( $color );

	$rgb      = array();
	$rgb['R'] = hexdec( $color[0].$color[1] );
	$rgb['G'] = hexdec( $color[2].$color[3] );
	$rgb['B'] = hexdec( $color[4].$color[5] );

	return $rgb;
}

/**
 * HEX darker.
 *
 * @since 1.2.1.3
 *
 * @param string $color Color.
 * @param int $factor Optional. Factor. Default 30.
 * @return string $color.
 */
function uwp_hex_darker( $color, $factor = 30 ) {
	$base  = uwp_rgb_from_hex( $color );
	if ( empty( $base ) ) {
		return $color;
	}

	$color = '#';
	foreach ( $base as $k => $v ) {
		$amount      = $v / 100;
		$amount      = round( $amount * $factor );
		$new_decimal = $v - $amount;

		$new_hex_component = dechex( $new_decimal );
		if ( strlen( $new_hex_component ) < 2 ) {
			$new_hex_component = "0" . $new_hex_component;
		}
		$color .= $new_hex_component;
	}

	return $color;
}

/**
 * Hex lighter.
 *
 * @since 1.2.1.3
 *
 * @param string $color Color.
 * @param int $factor Optional. factor. Default 30.
 * @return string $color.
 */
function uwp_hex_lighter( $color, $factor = 30 ) {
	$base  = uwp_rgb_from_hex( $color );
	if ( empty( $base ) ) {
		return $color;
	}

	$color = '#';

	foreach ( $base as $k => $v ) {
		$amount      = 255 - $v;
		$amount      = $amount / 100;
		$amount      = round( $amount * $factor );
		$new_decimal = $v + $amount;

		$new_hex_component = dechex( $new_decimal );
		if ( strlen( $new_hex_component ) < 2 ) {
			$new_hex_component = "0" . $new_hex_component;
		}
		$color .= $new_hex_component;
	}

	return $color;
}

/**
 * Get Light or dark.
 *
 * @since 1.2.1.3
 *
 * @param string $color color.
 * @param string $dark Optional. Dark. Default #000000.
 * @param string $light Optional. Light. Default #FFFFFF.
 * @return string
 */
function uwp_light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {
	$hex = str_replace( '#', '', $color );
	if ( empty( $hex ) ) {
		return $color;
	}

	$c_r = hexdec( substr( $hex, 0, 2 ) );
	$c_g = hexdec( substr( $hex, 2, 2 ) );
	$c_b = hexdec( substr( $hex, 4, 2 ) );

	$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

	return $brightness > 155 ? $dark : $light;
}

/**
 * Format hex.
 *
 * @since 1.2.1.3
 *
 * @param string $hex hex.
 * @return string
 */
function uwp_format_hex( $hex ) {
	$hex = trim( str_replace( '#', '', $hex ) );
	if ( empty( $hex ) ) {
		return NULL;
	}

	if ( strlen( $hex ) == 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	return $hex ? '#' . $hex : null;
}

/**
 * Returns activation link for user.
 *
 * @since 1.2.1.3
 *
 * @param int $user_id User ID.
 *
 * @return string $activation_link
 */
function uwp_get_activation_link($user_id){

	global $wpdb, $wp_hasher;

	if(!$user_id){
		return false;
	}

	$user_data = get_userdata($user_id);

	$key = wp_generate_password( 20, false );

	do_action( 'uwp_activation_key', $user_data->user_login, $key );

	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . 'wp-includes/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );
	}
	$hashed = $wp_hasher->HashPassword( $key );
	$wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_data->user_login ) );
	update_user_meta( $user_id, 'uwp_mod', 'email_unconfirmed' );

	$activation_link = add_query_arg(
		array(
			'uwp_activate' => 'yes',
			'key' => $key,
			'login' => $user_data->user_login
		),
		home_url('/login/')
	);

	return $activation_link;
}

/**
 * Checks a version number against the core version and adds a admin notice if requirements are not met.
 *
 * @param $name
 * @param $version
 *
 * @return bool
 */
function uwp_min_version_check( $name, $version ) {
	if ( version_compare( USERSWP_VERSION, $version, '<' ) ) {
		add_action( 'admin_notices', function () use ( &$name ) {
			?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo sprintf( __( "%s requires a newer version of UsersWP and will not run until the UsersWP plugin is updated.", "userswp" ), $name ); ?></p>
            </div>
			<?php
		} );

		return false;
	}

	return true;
}

function uwp_get_user_roles($exclude = array()) {
	$user_roles = array();
	if ( !function_exists('get_editable_roles') ) {
		require_once( ABSPATH . '/wp-admin/includes/user.php' );
	}

	$wp_roles = get_editable_roles();
	if(!empty($wp_roles) && is_array($wp_roles)) {
		foreach ( $wp_roles as $role => $details ) {
			if ( in_array( $role, $exclude ) ) {
			} else {
				$user_roles[ esc_attr( $role ) ] = !empty($details['name']) ? translate_user_role( $details['name'] ): $role;
			}

		}
	}

	return $user_roles;
}

function uwp_get_sort_by_order_list(){

	$cache = wp_cache_get("uwp_get_sort_options");
	if($cache !== false){
		return $cache;
	}

	global $wpdb;
	$table_name = uwp_get_table_prefix() . 'uwp_user_sorting';

	$sort_options_raw = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE is_active = %d AND field_type != 'address' AND tab_parent = '0' ORDER BY sort_order ASC", array(
		1
	) ) );

	$sort_options = array();

	if ( ! empty( $sort_options_raw ) && count( $sort_options_raw ) > 1 ) {
		foreach ( $sort_options_raw as $sort ) {
			$sort = stripslashes_deep( $sort );

			$sort->site_title = __( $sort->site_title, 'userswp' );

			if ( $sort->htmlvar_name == 'comment_count' ) {
				$sort->htmlvar_name = 'rating_count';
			}

			$key = $sort->htmlvar_name;
			if ( !in_array($key, array('newer', 'older')) ) {
				if($sort->sort == 'asc'){$key = esc_attr($sort->htmlvar_name."_asc");}
                elseif($sort->sort == 'desc'){$key = esc_attr($sort->htmlvar_name."_desc");}
			}

			$sort_options[$key] = $sort->site_title;
		}
	}

	/**
	 * Filter post sort options.
	 *
	 * @param array $sort_options Unfiltered sort field array.
	 */
	$sort_options = apply_filters( 'uwp_available_users_layout', $sort_options );

	wp_cache_set("uwp_get_sort_options", $sort_options );

	return $sort_options;
}

function uwp_get_default_sort(){

	$cache = wp_cache_get("uwp_get_default_sort");

	if($cache !== false){
		return $cache;
	}

	$default_sort = 'newer_asc';

    global $wpdb;
	$table_name = uwp_get_table_prefix() . 'uwp_user_sorting';

	$field = $wpdb->get_row("SELECT htmlvar_name, sort, field_type FROM " . $table_name . " WHERE is_active = 1 AND is_default = 1 ORDER BY sort_order ASC" );
	if ( ! empty( $field ) ) {
		if ( $field->field_type == 'random' ) {
			$default_sort = 'random';
		} else {
			$default_sort = $field->htmlvar_name . '_' . $field->sort;
		}
	}

	wp_cache_set("uwp_get_default_sort", $default_sort );

	return $default_sort;

}