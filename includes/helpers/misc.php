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
 * Resizes the image.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $image      Reference a local file
 * @param       int         $width      Image width      
 * @param       int         $height     Image height    
 * @param       float       $scale      Image scale ratio.
 *
 * @return      mixed                   Resized image.
 */
function uwp_resizeImage($image,$width,$height,$scale) {
    /** @noinspection PhpUnusedLocalVariableInspection */
    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $imageType = image_type_to_mime_type($imageType);
    $newImageWidth = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
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
            break;
    }
    imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);

    switch($imageType) {
        case "image/gif":
            imagegif($newImage,$image);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            imagejpeg($newImage,$image,90);
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage,$image);
            break;
    }

    chmod($image, 0777);
    return $image;
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
    // ignore image createion warnings
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
            break;
    }
    imagecopyresampled($newImage,$source,0,0,$x,$y,$newImageWidth, $newImageHeight, $src_w, $src_h);
    switch($imageType) {
        case "image/gif":
            imagegif($newImage, $thumb_image_name);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            imagejpeg($newImage, $thumb_image_name, 100);
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
     * A filter to override the WP_DEBUG setting for function uwp_error_log().
     */
    $should_log = apply_filters( 'uwp_log_errors', WP_DEBUG);
    if ( true === $should_log ) {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}


/**
 * Prints the users page main content.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      void
 */
function get_uwp_users_list() {

    global $wpdb;

    $keyword = false;
    if (isset($_GET['uwps']) && $_GET['uwps'] != '') {
        $keyword = stripslashes(strip_tags($_GET['uwps']));
    }

    $sort_by = false;
    if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
        $sort_by = strip_tags(esc_sql($_GET['uwp_sort_by']));
    }

    $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

    $number = uwp_get_option('profile_no_of_items', 10);

    $where = '';
    $where = apply_filters('uwp_users_search_where', $where, $keyword);

    $arg = array(
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

    $inactive_users = new WP_User_Query($arg);
    $exclude_users = $inactive_users->get_results();

    $excluded_globally = uwp_get_option('users_excluded_from_list');
    if ( $excluded_globally ) {
        $users = str_replace(' ', '', $excluded_globally );
        $users_array = explode(',', $users );
        $exclude_users = array_merge($exclude_users, $users_array);
    }

    $exclude_users = apply_filters('uwp_excluded_users_from_list', $exclude_users, $where, $keyword);

    if($exclude_users){
        $exclude_users_list = implode(',', array_unique($exclude_users));
        $exclude_query = 'AND '. $wpdb->users.'.ID NOT IN ('.$exclude_users_list.')';
    } else {
        $exclude_query = ' ';
    }

    if ($keyword || $where) {
        if (empty($where)) {
            $users = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.*
            FROM $wpdb->users
            INNER JOIN $wpdb->usermeta
            ON ( $wpdb->users.ID = $wpdb->usermeta.user_id )
            WHERE 1=1
            $exclude_query
            AND ( 
            ( $wpdb->usermeta.meta_key = 'first_name' AND $wpdb->usermeta.meta_value LIKE %s ) 
            OR 
            ( $wpdb->usermeta.meta_key = 'last_name' AND $wpdb->usermeta.meta_value LIKE %s ) 
            OR 
            user_login LIKE %s 
            OR 
            user_nicename LIKE %s 
            OR 
            display_name LIKE %s 
            OR 
            user_email LIKE %s
            )
            ORDER BY display_name ASC
            LIMIT 0, 20",
                array(
                    '%' . $keyword . '%',
                    '%' . $keyword . '%',
                    '%' . $keyword . '%',
                    '%' . $keyword . '%',
                    '%' . $keyword . '%',
                    '%' . $keyword . '%'
                )
            ));
        } else {
            $usermeta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

            $users = $wpdb->get_results(
                "SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.*
            FROM $wpdb->users
            INNER JOIN $usermeta_table
            ON ( $wpdb->users.ID = $usermeta_table.user_id )
            WHERE 1=1
            $exclude_query
            $where
            ORDER BY display_name ASC
            LIMIT 0, 20");
        }

        $total_user = count($users);

    } else {

        $args = array(
            'number' => (int) $number,
            'paged' => $paged,
            'exclude' => $exclude_users,
        );


        if ($sort_by) {
            switch ($sort_by) {
                case "newer":
                    $args['orderby'] = 'registered';
                    $args['order'] = 'desc';
                    break;
                case "older":
                    $args['orderby'] = 'registered';
                    $args['order'] = 'asc';
                    break;
                case "alpha_asc":
                    $args['orderby'] = 'display_name';
                    $args['order'] = 'asc';
                    break;
                case "alpha_desc":
                    $args['orderby'] = 'display_name';
                    $args['order'] = 'desc';
                    break;

            }
        }

        $users_query = new WP_User_Query($args);
        $users = $users_query->get_results();
        $total_user = $users_query->get_total();

    }

    $total_pages=ceil($total_user/$number);

    $layout_class = uwp_get_layout_class();
    ?>
    <ul class="uwp-users-list-wrap <?php echo $layout_class; ?>" id="uwp_user_items_layout">
        <?php
        if ($users) {
            foreach ($users as $user) {
                $user_obj = get_user_by('id', $user->ID);

                // exclude logged in user
                $exclude_loggedin_user = apply_filters('uwp_users_list_exclude_loggedin_user', false);
                if ($exclude_loggedin_user) {
                    if ($user_obj->ID == get_current_user_id()) {
                        continue;
                    }
                }
                ?>
                <li class="uwp-users-list-user">
                    <div class="uwp-users-list-user-left">
                        <?php do_action('uwp_users_profile_header', $user); ?>
                    </div>
                    <div class="uwp-users-list-user-right">
                        <div class="uwp-users-list-user-name">
                            <h3 class="uwp-user-title" data-user="<?php echo $user_obj->ID; ?>">
                                <a href="<?php echo apply_filters('uwp_profile_link', get_author_posts_url($user_obj->ID), $user_obj->ID); ?>">
                                    <?php echo apply_filters('uwp_profile_display_name', $user_obj->display_name); ?>
                                </a>
                                <?php do_action('uwp_users_after_title', $user_obj->ID ); ?>
                            </h3>
                        </div>
                        <div class="uwp-users-list-user-btns">
                            <?php do_action('uwp_profile_buttons', $user_obj ); ?>
                        </div>
                        <div class="uwp-users-list-user-social">
                            <?php do_action('uwp_profile_social', $user_obj ); ?>
                        </div>
                        <div class="uwp-users-list-extra">
                            <?php do_action('uwp_users_extra', $user_obj ); ?>
                        </div>
                        <div class="clfx"></div>
                    </div>
                </li>
                <?php
            }
        } else {
            // no users found
            echo '<div class="uwp-alert-error text-center">';
            echo __('No Users Found', 'userswp');
            echo '</div>';
        }
        ?>
    </ul>

    <?php
    if ($total_pages > 1) {
        do_action('uwp_profile_pagination', $total_pages);
    }
    ?>
    <?php
}



/**
 * Loads the font-awesome css files.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      void
 */
function uwp_load_font_awesome() {
    //load font awesome
    global $wp_styles;
    if ($wp_styles) {
        $srcs = array_map('basename', (array) wp_list_pluck($wp_styles->registered, 'src') );
        if ( in_array('font-awesome.css', $srcs) || in_array('font-awesome.min.css', $srcs)  ) {
            /* echo 'font-awesome.css registered'; */
        } else {
            wp_register_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), null);
            wp_enqueue_style('font-awesome');
        }
    } else {
        wp_register_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), null);
        wp_enqueue_style('font-awesome');
    }
}

/**
 * Gets the custom field info for given key.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $htmlvar_name       Custom field key.
 *
 * @return      object                          Field info.
 */
function uwp_get_custom_field_info($htmlvar_name) {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $field = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE htmlvar_name = %s", array($htmlvar_name)));
    return $field;
}



/**
 * Returns the Users page layout class based on the setting.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      Layout class.
 */
function uwp_get_layout_class() {
    $default_layout = uwp_get_option('users_default_layout', 'list');
    switch ($default_layout) {
        case "list":
            $class = "uwp_listview";
            break;
        case "2col":
            $class = "uwp_gridview uwp_gridview_2col";
            break;
        case "3col":
            $class = "uwp_gridview uwp_gridview_3col";
            break;
        case "4col":
            $class = "uwp_gridview uwp_gridview_4col";
            break;
        case "5col":
            $class = "uwp_gridview uwp_gridview_5col";
            break;
        default:
            $class = "uwp_listview";
    }

    return $class;
}

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

add_filter('user_profile_picture_description', 'uwp_admin_user_profile_picture_description');

/**
 * Filters the user profile picture description displayed under the Gravatar.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $description    Profile picture description.
 *
 * @return      string                      Modified description.
 */
function uwp_admin_user_profile_picture_description($description) {
    if (is_admin() && IS_PROFILE_PAGE) {
        $user_id = get_current_user_id();
        $avatar = uwp_get_usermeta($user_id, 'uwp_account_avatar_thumb', '');

        if (!empty($avatar)) {
            $description = sprintf( __( 'You can change your profile picture on your <a href="%s">Profile Page</a>.', 'userswp' ),
                uwp_build_profile_tab_url( $user_id ));
        }

    }
    return $description;
}

/**
 * Adds avatar and banner fields in admin side.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       object      $user       User object.
 *
 * @return      void
 */
function uwp_admin_edit_banner_fields($user) {
    global $wpdb;

    $file_obj = new UsersWP_Files();

    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (form_type = 'avatar' OR form_type = 'banner') ORDER BY sort_order ASC");
    if ($fields) {
        ?>
        <div class="uwp-profile-extra uwp_page">
            <?php do_action('uwp_admin_profile_edit', $user ); ?>
            <table class="uwp-profile-extra-table form-table">
                <?php
                foreach ($fields as $field) {

                    // Icon
                    $icon = uwp_get_field_icon( $field->field_icon );

                    if ($field->field_type == 'fieldset') {
                        ?>
                        <tr style="margin: 0; padding: 0">
                            <th class="uwp-profile-extra-key" style="margin: 0; padding: 0"><h3 style="margin: 10px 0;">
                                    <?php echo $icon.$field->site_title; ?></h3></th>
                            <td></td>
                        </tr>
                        <?php
                    } else { ?>
                        <tr>
                            <th class="uwp-profile-extra-key"><?php echo $icon.$field->site_title; ?></th>
                            <td class="uwp-profile-extra-value">
                                <?php
                                if ($field->htmlvar_name == "uwp_avatar_file") {
                                    $value = uwp_get_usermeta($user->ID, "uwp_account_avatar_thumb", "");
                                } elseif ($field->htmlvar_name == "uwp_banner_file") {
                                    $value = uwp_get_usermeta($user->ID, "uwp_account_banner_thumb", "");
                                } else {
                                    $value = "";
                                }
                                ?>
                                <?php echo $file_obj->uwp_file_upload_preview($field, $value); ?>
                                <?php
                                if ($field->htmlvar_name == "uwp_avatar_file") {
                                    if (!empty($value)) {
                                        ?>
                                        <a class="uwp-profile-modal-form-trigger" data-type="avatar" href="#">
                                            <?php echo __("Change Avatar", "userswp"); ?>
                                        </a>
                                        <?php
                                    } else {
                                        ?>
                                        <a class="uwp-profile-modal-form-trigger" data-type="avatar" href="#">
                                            <?php echo __("Upload Avatar", "userswp"); ?>
                                        </a>
                                        <?php
                                    }
                                } elseif ($field->htmlvar_name == "uwp_banner_file") {
                                    if (!empty($value)) {
                                        ?>
                                        <a class="uwp-profile-modal-form-trigger" data-type="banner" href="#">
                                            <?php echo __("Change Banner", "userswp"); ?>
                                        </a>
                                        <?php
                                    } else {
                                        ?>
                                        <a class="uwp-profile-modal-form-trigger" data-type="banner" href="#">
                                            <?php echo __("Upload Banner", "userswp"); ?>
                                        </a>
                                        <?php
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>
        <?php
    }
}
add_action('show_user_profile', 'uwp_admin_edit_banner_fields');
add_action('edit_user_profile', 'uwp_admin_edit_banner_fields');

add_filter('admin_body_class', 'uwp_add_admin_body_class');
function uwp_add_admin_body_class($classes) {
    $screen = get_current_screen();
    if ( 'profile' == $screen->base || 'user-edit' == $screen->base )
    $classes .= 'uwp_page';
    return $classes;
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
 * @return      string|void             Title.
 */
function uwp_account_privacy_page_title($title, $type) {
    if ($type == 'privacy') {
        $title = __( 'Privacy', 'userswp' );
    }

    return $title;
}

add_action('uwp_account_form_display', 'uwp_account_privacy_edit_form_display');

/**
 * Adds form html for privacy fields in account page.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $type       Form type.
 *
 * @return      void
 */
function uwp_account_privacy_edit_form_display($type) {
    if ($type == 'privacy') {
        $make_profile_private = uwp_can_make_profile_private();
        echo '<div class="uwp-account-form uwp_wc_form">';
        $extra_where = "AND is_public='2'";
        $fields = get_account_form_fields($extra_where);
        $fields = apply_filters('uwp_account_privacy_fields', $fields);
        $user_id = get_current_user_id();
        ?>
        <div class="uwp-profile-extra">
            <div class="uwp-profile-extra-div form-table">
                <form class="uwp-account-form uwp_form" method="post">
                    <?php if ($fields) { ?>
                        <div class="uwp-profile-extra-wrap">
                            <div class="uwp-profile-extra-key" style="font-weight: bold;">
                                <?php echo __("Field", "userswp") ?>
                            </div>
                            <div class="uwp-profile-extra-value" style="font-weight: bold;">
                                <?php echo __("Is Public?", "userswp") ?>
                            </div>
                        </div>
                        <?php foreach ($fields as $field) { ?>
                            <div class="uwp-profile-extra-wrap">
                                <div class="uwp-profile-extra-key"><?php echo $field->site_title; ?>
                                    <span class="uwp-profile-extra-sep">:</span></div>
                                <div class="uwp-profile-extra-value">
                                    <?php
                                    $field_name = $field->htmlvar_name . '_privacy';
                                    $value = uwp_get_usermeta($user_id, $field_name, false);
                                    if ($value === false) {
                                        $value = 'yes';
                                    }
                                    ?>
                                    <select name="<?php echo $field_name; ?>" class="uwp_privacy_field"
                                            style="margin: 0;">
                                        <option value="no" <?php selected($value, "no"); ?>><?php echo __("No", "userswp") ?></option>
                                        <option value="yes" <?php selected($value, "yes"); ?>><?php echo __("Yes", "userswp") ?></option>
                                    </select>
                                </div>
                            </div>
                        <?php }
                    }
                    $value = get_user_meta($user_id, 'uwp_hide_from_listing', true); ?>
                    <div class="uwp-profile-extra-wrap">
                        <div id="uwp_hide_from_listing" class="uwp_hide_from_listing">
                            <input name="uwp_hide_from_listing" class="" <?php checked($value, "1", true); ?> type="checkbox" value="1"><?php _e('Hide profile from the users listing page.', 'userswp'); ?>
                        </div>
                    </div>
                    <?php
                    do_action('uwp_after_privacy_form_fields', $fields);
                    if ($make_profile_private) {
                        $field_name = 'uwp_make_profile_private';
                        $value = get_user_meta($user_id, $field_name, true);
                        if ($value === false) {
                            $value = '0';
                        }
                        ?>
                        <div id="uwp_make_profile_private" class=" uwp_make_profile_private_row">
                            <input type="hidden" name="uwp_make_profile_private" value="0">
                            <input name="uwp_make_profile_private" class="" <?php checked( $value, "1", true ); ?> type="checkbox" value="1">
                            <?php _e( 'Make the whole profile private', 'userswp' ); ?>
                        </div>
                        <?php
                    }
                    ?>
                    <input type="hidden" name="uwp_privacy_nonce" value="<?php echo wp_create_nonce( 'uwp-privacy-nonce' ); ?>" />
                    <input name="uwp_privacy_submit" value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit">
                </form>
            </div>
        </div>
        <?php
        echo '</div>';
    }
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

    echo '<ul class="uwp_account_menu">';

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
        <li id="uwp-account-<?php echo $tab_id; ?>">
            <a class="<?php echo $active; ?>" href="<?php echo esc_url( $tab_url ); ?>">
                <i class="<?php echo $tab['icon']; ?>"></i> <?php echo $tab['title']; ?>
            </a>
        </li>
        <?php
    }

    $template = new UsersWP_Templates();
    $logout_url = $template->uwp_logout_url();
    echo '<li id="uwp-account-logout"><a class="uwp-account-logout-link" href="'.$logout_url.'"><i class="fa fa-sign-out"></i>'.__('Logout', 'userswp').'</a></li>';
    echo '</ul>';
}




/**
 * Updates extras fields sort order.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       array       $field_ids      Form extras field ids.
 * @param       string      $form_type      Form type.
 *
 * @return      array|bool                  Sorted field ids.
 */
function uwp_form_extras_field_order($field_ids = array(), $form_type = 'register')
{
    global $wpdb;
    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

    $count = 0;
    if (!empty($field_ids)):
        foreach ($field_ids as $id) {

            $cf = trim($id, '_');

            $wpdb->query(
                $wpdb->prepare(
                    "update " . $extras_table_name . " set
															sort_order=%d
															where id= %d",
                    array($count, $cf)
                )
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
    $table->column_exists($db, $column);
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
    $table->add_column_if_not_exist($db, $column, $column_attr);

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
        'uwp_account_password',
        'uwp_account_confirm_password',
    );
    return $excluded;
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
 * Returns available registration status options.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Registration status options.
 */
function uwp_registration_status_options() {
    $registration_options = array(
        'auto_approve' =>  __('Auto approve', 'userswp'),
        'auto_approve_login' =>  __('Auto approve + Auto Login', 'userswp'),
        'require_email_activation' =>  __('Require Email Activation', 'userswp'),
    );

    $registration_options = apply_filters('uwp_registration_status_options', $registration_options);

    return $registration_options;
}

/**
 * Retrieves a user row based on password reset key and login
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string         $key       Hash to validate sending user's password.
 * @param       string         $login     The user login.
 *
 * @return      WP_Error|WP_User          User object.
 */
function uwp_check_activation_key( $key, $login ) {
    $user_data = check_password_reset_key( $key, $login );

    return $user_data;
}


add_action('uwp_template_fields', 'uwp_template_fields_terms_check', 100, 1);

/**
 * Adds "Accept terms and conditions" checkbox in register form.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $form_type      Form type.
 *
 * @return      void
 */
function uwp_template_fields_terms_check($form_type) {
    if ($form_type == 'register') {
        $terms_page = false;
        $reg_terms_page_id = uwp_get_page_id('register_terms_page', false);
        $reg_terms_page_id = apply_filters('uwp_reg_terms_page_id', $reg_terms_page_id);
        if (!empty($reg_terms_page_id)) {
            $terms_page = get_permalink($reg_terms_page_id);
        }
        if ($terms_page) {
            ?>
            <div class="uwp-remember-me">
                <label style="display: inline-block;font-weight: normal" for="agree_terms">
                    <input name="agree_terms" id="agree_terms" value="yes" type="checkbox">
                    <?php echo sprintf( __( 'I Accept <a href="%s" target="_blank">Terms and Conditions</a>.', 'userswp' ), $terms_page); ?>
                </label>
            </div>
            <?php
        }
    }
}



/**
 * Redirects the user to login page when email not confirmed.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $username       Username.
 * @param       object      $user           User object.
 *
 * @return      void
 */
function uwp_unconfirmed_login_redirect( $username, $user ) {
    if (!is_wp_error($user)) {
        $mod_value = get_user_meta( $user->ID, 'uwp_mod', true );
        if ($mod_value == 'email_unconfirmed') {
            if ( !in_array( 'administrator', $user->roles ) ) {
                $login_page = uwp_get_page_id('login_page', false);
                if ($login_page) {
                    $redirect_to = add_query_arg(array('uwp_err' => 'act_pending'), get_permalink($login_page));
                    wp_destroy_current_session();
                    wp_clear_auth_cookie();
                    wp_redirect($redirect_to);
                    exit();
                }
            }
        }
    }
}
add_filter( 'wp_login', 'uwp_unconfirmed_login_redirect', 10, 2 );

/**
 * Adds notification menu in admin toolbar.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      void
 */
function uwp_notifications_toolbar_menu() {
    global $wp_admin_bar;

    if ( ! is_user_logged_in() ) {
        return;
    }

    $available_counts = apply_filters('uwp_notifications_available_counts', array());

    if (count($available_counts) == 0) {
        return;
    }

    $total_count = 0;
    foreach ($available_counts as $key => $value) {
        $total_count = $total_count + $value;
    }


    $alert_class   = (int) $total_count > 0 ? 'pending-count' : 'count';
    $menu_title    = '<span id="uwp-notification-count" class="' . $alert_class . '">'
        . number_format_i18n( $total_count ) . '</span>';
    $menu_link     = '';

    // Add the top-level Notifications button.
    $wp_admin_bar->add_menu( array(
        'parent'    => 'top-secondary',
        'id'        => 'uwp-notifications',
        'title'     => $menu_title,
        'href'      => $menu_link,
    ) );

    do_action('uwp_notifications_items', $wp_admin_bar);

    return;
}
add_action( 'admin_bar_menu', 'uwp_notifications_toolbar_menu', 90 );

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
    if (isset($field->field_type) && $field->field_type == 'multiselect') {
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
    if (isset($field->field_type) && $field->field_type == 'multiselect') {
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
    $tables->uwp_create_tables();
}

/**
 * Creates uwp_usermeta table which introduced in version 1.0.1
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      void
 */
function uwp101_create_tables() {
    $tables = new UsersWP_Tables();
    $tables->uwp101_create_tables();
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
        'text' => 'fa fa-minus',
        'datepicker' => 'fa fa-calendar',
        'textarea' => 'fa fa-bars',
        'time' =>'fa fa-clock-o',
        'checkbox' =>'fa fa-check-square-o',
        'phone' =>'fa fa-phone',
        'radio' =>'fa fa-dot-circle-o',
        'email' =>'fa fa-envelope-o',
        'select' =>'fa fa-caret-square-o-down',
        'multiselect' =>'fa fa-caret-square-o-down',
        'url' =>'fa fa-link',
        'file' =>'fa fa-file'
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
    if (class_exists('SitePress') && function_exists('icl_object_id')) {
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

function uwp_get_default_avatar_uri(){
    $default = uwp_get_option('profile_default_profile', '');
    if(empty($default)){
        $default = USERSWP_PLUGIN_URL."public/assets/images/no_profile.png";
    } else {
        $default = wp_get_attachment_url($default);
    }

    return $default;
}

function uwp_refresh_permalinks_on_bad_404() {

    global $wp;

    if( ! is_404() ) {
        return;
    }

    if( isset( $_GET['uwp-flush'] ) ) {
        return;
    }

    if( false === get_transient( 'uwp_refresh_404_permalinks' ) ) {

        flush_rewrite_rules( false );

        set_transient( 'uwp_refresh_404_permalinks', 1, HOUR_IN_SECONDS * 12 );

        wp_redirect( home_url( add_query_arg( array( 'uwp-flush' => 1 ), $wp->request ) ) ); exit;

    }
}
add_action( 'template_redirect', 'uwp_refresh_permalinks_on_bad_404' );

add_filter( 'avatar_defaults', 'uwp_avatar_defaults' , 99999 , 6 );
/*
 * Remove get_avatar filter applied by UWP for default avatars in settings
 * @param array $avatar_defaults default avatars
 * @return array $avatar_defaults default avatars
 *
 */
function uwp_avatar_defaults($avatar_defaults){
    remove_filter('get_avatar', 'uwp_modify_get_avatar', 99999, 6);
    return $avatar_defaults;
}

remove_all_filters('get_avatar');
add_filter( 'get_avatar', 'uwp_modify_get_avatar' , 99999 , 6 );
/**
 * Modifies get_avatar function to use userswp avatar.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string      $avatar         img tag value for the user's avatar.
 * @param       mixed       $id_or_email    The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
 *                                          user email, WP_User object, WP_Post object, or WP_Comment object.
 * @param       int         $size           Square avatar width and height in pixels to retrieve.
 * @param       string      $default        URL for the default image or a default type. Accepts '404', 'retro', 'monsterid',
 *                                          'wavatar', 'indenticon','mystery' (or 'mm', or 'mysteryman'), 'blank', or 'gravatar_default'.
 *                                          Default is the value of the 'avatar_default' option, with a fallback of 'mystery'.
 * @param       string      $alt            Alternative text to use in the avatar image tag. Default empty.
 * @return      string                      Modified img tag value
 */
function uwp_modify_get_avatar( $avatar, $id_or_email, $size, $default, $alt, $args )
{
    $user = false;

    if (is_numeric($id_or_email)) {

        $id = (int)$id_or_email;
        $user = get_user_by('id', $id);

    } elseif (is_object($id_or_email)) {

        if (!empty($id_or_email->user_id)) {
            $id = (int)$id_or_email->user_id;
            $user = get_user_by('id', $id);
        }

    } else {
        $user = get_user_by('email', $id_or_email);
    }

    if ($user && is_object($user)) {
        $avatar_thumb = uwp_get_usermeta($user->data->ID, 'uwp_account_avatar_thumb', '');
        if (!empty($avatar_thumb)) {
            $uploads = wp_upload_dir();
            $upload_url = $uploads['baseurl'];
            if (substr($avatar_thumb, 0, 4) !== "http") {
                $avatar_thumb = $upload_url . $avatar_thumb;
            }
            $avatar = "<img alt='{$alt}' src='{$avatar_thumb}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
        } else {
            $default = uwp_get_default_avatar_uri();
            $args = get_avatar_data($id_or_email, $args);
            $url = $args['url'];
            $url = remove_query_arg('d', $url);
            $url = add_query_arg(array('d' => $default), $url);
            if (!$url || is_wp_error($url)) {
                return $avatar;
            }
            $avatar = '<img src="' . $url . '" class="gravatar avatar avatar-' . $size . ' uwp-avatar" width="' . $size . '" height="' . $size . '" alt="' . $alt . '" />';
        }

    }

    return $avatar;
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