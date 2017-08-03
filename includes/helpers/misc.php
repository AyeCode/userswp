<?php
/**
 * Converts string value to options array.
 * Used in select, multiselect and radio fields.
 * Wraps inside optgroup if available.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
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
 * Returns allowed mime types in uploads
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Allowed mime types.
 */
function uwp_allowed_mime_types() {
    return apply_filters( 'uwp_allowed_mime_types', array(
            'Image'       => array( // Image formats.
                'jpg'  => 'image/jpeg',
                'jpe'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'png'  => 'image/png',
                'bmp'  => 'image/bmp',
                'ico'  => 'image/x-icon',
            ),
            'Video'       => array( // Video formats.
                'asf'  => 'video/x-ms-asf',
                'avi'  => 'video/avi',
                'flv'  => 'video/x-flv',
                'mkv'  => 'video/x-matroska',
                'mp4'  => 'video/mp4',
                'mpeg' => 'video/mpeg',
                'mpg'  => 'video/mpeg',
                'wmv'  => 'video/x-ms-wmv',
                '3gp'  => 'video/3gpp',
            ),
            'Audio'       => array( // Audio formats.
                'ogg' => 'audio/ogg',
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'wma' => 'audio/x-ms-wma',
            ),
            'Text'        => array( // Text formats.
                'css'  => 'text/css',
                'csv'  => 'text/csv',
                'htm'  => 'text/html',
                'html' => 'text/html',
                'txt'  => 'text/plain',
                'rtx'  => 'text/richtext',
                'vtt'  => 'text/vtt',
            ),
            'Application' => array( // Application formats.
                'doc'  => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'exe'  => 'application/x-msdownload',
                'js'   => 'application/javascript',
                'odt'  => 'application/vnd.oasis.opendocument.text',
                'pdf'  => 'application/pdf',
                'pot'  => 'application/vnd.ms-powerpoint',
                'ppt'  => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.ms-powerpoint',
                'psd'  => 'application/octet-stream',
                'rar'  => 'application/rar',
                'rtf'  => 'application/rtf',
                'swf'  => 'application/x-shockwave-flash',
                'tar'  => 'application/x-tar',
                'xls'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'zip'  => 'application/zip',
            )
        )
    );
}

/**
 * Gets the file type using the extension.
 * Ex: 'jpg'  => 'image/jpeg'
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string      $ext        Extension string. Ex: png, jpg
 *
 * @return      string                  File type.
 */
function uwp_get_file_type($ext) {
    $allowed_file_types = uwp_allowed_mime_types();
    $file_types = array();
    foreach ( $allowed_file_types as $format => $types ) {
        $file_types = array_merge($file_types, $types);
    }
    $file_types = array_flip($file_types);
    return $file_types[$ext];
}

/**
 * Resizes the image.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
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
 * Prints the tab content based on post type.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       object          $user       User object.
 * @param       bool            $post_type  Post type.
 * @param       string          $title      Tab title          
 * @param       array|bool      $post_ids   Post ids for post__in. Optional
 *
 * @return      void
 */
function uwp_generic_tab_content($user, $post_type = false, $title, $post_ids = false) {
    ?>
    <h3><?php echo $title; ?></h3>
    <div class="uwp-profile-item-block">
        <?php
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
                echo "<p>".__('No '.$title.' Found', 'userswp')."</p>";
                return;
            }
        }
        // The Query
        $the_query = new WP_Query($args);

        // The Loop
        if ($the_query->have_posts()) {
            echo '<ul class="uwp-profile-item-ul">';
            while ($the_query->have_posts()) {
                $the_query->the_post();
                ?>
                <li class="uwp-profile-item-li uwp-profile-item-clearfix">
                    <div class="uwp_generic_thumb_wrap">
                        <a class="uwp-profile-item-img" href="<?php echo get_the_permalink(); ?>">
                            <?php
                            if ( has_post_thumbnail() ) {
                                $thumb_url = get_the_post_thumbnail_url(get_the_ID(), array(80, 80));
                            } else {
                                $thumb_url = USERSWP_PLUGIN_URL."/public/assets/images/no_thumb.png";
                            }
                            ?>
                            <img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo $thumb_url; ?>">
                        </a>
                    </div>

                    <h3 class="uwp-profile-item-title">
                        <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                    </h3>
                    <time class="uwp-profile-item-time published" datetime="<?php echo get_the_time('c'); ?>">
                        <?php echo get_the_date(); ?>
                    </time>
                    <div class="uwp-profile-item-summary">
                        <?php
                        $excerpt = strip_shortcodes(wp_trim_words( get_the_excerpt(), 15, '...' ));
                        echo $excerpt;
                        if ($excerpt) {
                            ?>
                            <a href="<?php echo get_the_permalink(); ?>" class="more-link"><?php echo  __( 'Read More »', 'userswp' ); ?></a>
                            <?php
                        }
                        ?>
                    </div>
                </li>
                <?php
            }
            echo '</ul>';
            /* Restore original Post Data */
            wp_reset_postdata();
        } else {
            // no posts found
            echo "<p>".__('No '.$title.' Found', 'userswp')."</p>";
        }
        do_action('uwp_profile_pagination', $the_query->max_num_pages);
        ?>
    </div>
    <?php
}

/**
 * Logs the error message.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * Displays UsersWP admin notices
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      void
 */
function uwp_admin_notices() {
    $errors = get_option( 'uwp_admin_notices' );

    if ( ! empty( $errors ) ) {

        echo '<div id="uwp_admin_errors" class="notice-error notice is-dismissible">';

        echo '<p>' . $errors . '</p>';

        echo '</div>';

        // Clear
        delete_option( 'uwp_admin_notices' );
    }
}
add_action( 'admin_notices', 'uwp_admin_notices' );

/**
 * Returns the register form fields.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Form fields.
 */
function get_register_form_fields() {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
    $fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.is_active = '1' AND fields.is_register_field = '1' AND extras.form_type = 'register' ORDER BY extras.sort_order ASC", array('account')));
    $fields = apply_filters('uwp_get_register_form_fields', $fields);
    return $fields;
}

/**
 * Returns the register form validate-able fields.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       int         $role_id    Role ID from role addons. Default 0.
 *
 * @return      array                   Validate-able fields.
 */
function get_register_validate_form_fields($role_id) {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
    if ($role_id == 0) {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.field_type != 'fieldset' AND fields.field_type != 'file' AND fields.is_active = '1' AND fields.is_register_field = '1' ORDER BY extras.sort_order ASC", array('account')));
    } else {
        $slug = get_post_field( 'post_name', $role_id );
        $fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.field_type != 'fieldset' AND fields.field_type != 'file' AND fields.is_active = '1' AND fields.is_register_field = '1' AND FIND_IN_SET(%s, fields.user_roles) ORDER BY extras.sort_order ASC", array('account', $slug)));
    }

    $fields = apply_filters('uwp_get_register_validate_form_fields', $fields, $role_id);
    return $fields;
}

/**
 * Returns the change password form validate-able fields.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Validate-able fields
 */
function get_change_validate_form_fields() {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $enable_old_password = uwp_get_option('change_enable_old_password', false);
    if ($enable_old_password == '1') {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' ORDER BY sort_order ASC", array('change')));
    } else {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND htmlvar_name != 'uwp_change_old_password' ORDER BY sort_order ASC", array('change')));
    }
    return $fields;
}

/**
 * Returns the account form fields.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string      $extra_where    Extra where query.
 *
 * @return      array                       Form fields.
 */
function get_account_form_fields($extra_where = '') {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND is_register_only_field = '0' " . $extra_where . " ORDER BY sort_order ASC", array('account', $extra_where)));
    return $fields;
}

/**
 * Returns the change password form fields.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Form fields.
 */
function get_change_form_fields() {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $enable_old_password = uwp_get_option('change_enable_old_password', false);
    if ($enable_old_password == '1') {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' ORDER BY sort_order ASC", array('change')));
    } else {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND htmlvar_name != 'uwp_change_old_password' ORDER BY sort_order ASC", array('change')));
    }
    return $fields;
}

/**
 * Prints the users page main content.
 *
 * @since       1.0.0
 * @package     UsersWP
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
    //var_dump($where);

    if ($keyword || $where) {
        if (empty($where)) {
            $users = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.*
            FROM $wpdb->users
            INNER JOIN $wpdb->usermeta
            ON ( $wpdb->users.ID = $wpdb->usermeta.user_id )
            WHERE 1=1
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
            $usermeta_table = uwp_get_table_prefix() . 'uwp_usermeta';

            $users = $wpdb->get_results(
                "SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.*
            FROM $wpdb->users
            INNER JOIN $usermeta_table
            ON ( $wpdb->users.ID = $usermeta_table.user_id )
            WHERE 1=1
            $where
            ORDER BY display_name ASC
            LIMIT 0, 20");
        }

    } else {

        $args = array(
            'number' => (int) $number,
            'paged' => $paged
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

    }


    $result = count_users();
    $total_user = $result['total_users'];
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
                            <h3>
                                <a href="<?php echo apply_filters('uwp_profile_link', get_author_posts_url($user_obj->ID), $user_obj->ID); ?>"><?php echo $user_obj->display_name; ?></a>
                                <?php do_action('uwp_users_after_title', $user_obj ); ?>
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
    if (!$keyword) {
        do_action('uwp_profile_pagination', $total_pages);
    }
    ?>
    <?php
}

/**
 * Validates the submitted form data.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       array       $data           Submitted form data
 * @param       string      $type           Form type.
 * @param       array|bool  $fields         Fields applicable for validation.
 *
 * @return      array|mixed|void|WP_Error   Validated form data.
 */
function uwp_validate_fields($data, $type, $fields = false) {

    $errors = new WP_Error();

    $errors = apply_filters('uwp_validate_fields_before', $errors, $data, $type);

    $error_code = $errors->get_error_code();
    if (!empty($error_code)) {
        return $errors;
    }


    if (!$fields) {
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        if ($type == 'register') {
            if (isset($data["uwp_role_id"])) {
                $role_id = (int) strip_tags(esc_sql($data["uwp_role_id"]));
            } else {
                $role_id = 0;
            }
            $fields = get_register_validate_form_fields($role_id);
        } elseif ($type == 'change') {
            $fields = get_change_validate_form_fields();
        } elseif ($type == 'account') {
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND is_register_only_field = '0' ORDER BY sort_order ASC", array('account')));
        } else {
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));
        }
    }


    $validated_data = array();



    $email_field = uwp_get_custom_field_info('uwp_account_email');
    $email_extra = array();
    if (isset($email_field->extra_fields) && $email_field->extra_fields != '') {
        $email_extra = unserialize($email_field->extra_fields);
    }
    $enable_confirm_email_field = isset($email_extra['confirm_email']) ? $email_extra['confirm_email'] : '0';

    $password_field = uwp_get_custom_field_info('uwp_account_password');
    $enable_password = $password_field->is_active;
    $password_extra = array();
    if (isset($password_field->extra_fields) && $password_field->extra_fields != '') {
        $password_extra = unserialize($password_field->extra_fields);
    }
    $enable_confirm_password_field = isset($password_extra['confirm_password']) ? $password_extra['confirm_password'] : '0';

    $enable_old_password = uwp_get_option('change_enable_old_password', false);

    if ($type == 'account' || $type == 'change') {
        if (!is_user_logged_in()) {
            $errors->add('not_logged_in', __('<strong>Error</strong>: Permission denied.', 'userswp'));
        }
    }

    if (!empty($fields)) {
        foreach ($fields as $field) {

            if (!isset($data[$field->htmlvar_name]) && $field->is_required != 1) {
                continue;
            }


            if ($type == 'register') {

                if ($enable_password != '1') {
                    if ( ($field->htmlvar_name == 'uwp_account_password') OR ($field->htmlvar_name == 'uwp_account_confirm_password') ) {
                        continue;
                    }
                }

                if ($enable_confirm_email_field != '1') {
                    if ( $field->htmlvar_name == 'uwp_account_confirm_email' ) {
                        continue;
                    }
                }
            }


            if (!isset($data[$field->htmlvar_name]) && $field->is_required == 1) {
                if (is_admin()) {
                    //do nothing since admin edit fields can be empty
                } else {
                    if ($field->required_msg) {
                        $errors->add('empty_'.$field->htmlvar_name,  __('<strong>Error</strong>: '.$field->site_title.' '.$field->required_msg, 'userswp'));
                    } else {
                        $errors->add('empty_'.$field->htmlvar_name, __('<strong>Error</strong>: '.$field->site_title.' cannot be empty.', 'userswp'));
                    }
                }
            }

            $error_code = $errors->get_error_code();
            if (!empty($error_code)) {
                return $errors;
            }


            $value = isset($data[$field->htmlvar_name]) ? $data[$field->htmlvar_name] : '';
            $sanitized_value = $value;

            if ($field->field_type == 'password') {
                continue;
            }

            $sanitized = false;

            // sanitize our default fields
            switch($field->htmlvar_name) {

                case 'uwp_register_username':
                case 'uwp_account_username':
                case 'uwp_login_username':
                case 'uwp_reset_username':
                    $sanitized_value = sanitize_user($value);
                    $sanitized = true;
                    break;

                case 'uwp_register_first_name':
                case 'uwp_register_last_name':
                case 'uwp_account_first_name':
                case 'uwp_account_last_name':
                    $sanitized_value = sanitize_text_field($value);
                    $sanitized = true;
                    break;

                case 'uwp_register_email':
                case 'uwp_forgot_email':
                case 'uwp_account_email':
                    $sanitized_value = sanitize_email($value);
                    $sanitized = true;
                    break;

            }

            if (!$sanitized && !empty($value)) {
                // sanitize by field type
                switch($field->field_type) {

                    case 'text':
                        $sanitized_value = sanitize_text_field($value);
                        break;

                    case 'checkbox':
                        $sanitized_value = sanitize_text_field($value);
                        break;

                    case 'email':
                        $sanitized_value = sanitize_email($value);
                        break;

                    case 'multiselect':
                        $sanitized_value = array_map( 'sanitize_text_field', $value );
                        break;

                    case 'datepicker':
                        $sanitized_value = sanitize_text_field($value);
                        $extra_fields = unserialize($field->extra_fields);

                        if ($extra_fields['date_format'] == '')
                            $extra_fields['date_format'] = 'yy-mm-dd';

                        $date_format = $extra_fields['date_format'];

                        if (!empty($sanitized_value)) {
                            $date_value = uwp_date($sanitized_value, 'Y-m-d', $date_format);
                            $sanitized_value = strtotime($date_value);
                        }
                        break;

                    default:
                        $sanitized_value = sanitize_text_field($value);

                }
            }


            if ($field->is_required == 1 && $sanitized_value == '') {
                if (is_admin()) {
                    //do nothing since admin edit fields can be empty
                } else {
                    if ($field->required_msg) {
                        $errors->add('empty_'.$field->htmlvar_name,  __('<strong>Error</strong>: '.$field->site_title.' '.$field->required_msg, 'userswp'));
                    } else {
                        $errors->add('empty_'.$field->htmlvar_name, __('<strong>Error</strong>: '.$field->site_title.' cannot be empty.', 'userswp'));
                    }
                }
            }

            if ($field->field_type == 'email' && !empty($sanitized_value) && !is_email($sanitized_value)) {
                $incorrect_email_error_msg = apply_filters('uwp_incorrect_email_error_msg', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'userswp'));
                $errors->add('invalid_email', $incorrect_email_error_msg);
            }

            //register email
            if ($type == 'register' && $field->htmlvar_name == 'uwp_account_email' && email_exists($sanitized_value)) {
                $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered, please choose another one.', 'userswp'));
            }

            //forgot email
            if ($field->htmlvar_name == 'uwp_forgot_email' && !email_exists($sanitized_value)) {
                $errors->add('email_exists', __('<strong>Error</strong>: This email doesn\'t exists.', 'userswp'));
            }

            $incorrect_username_error_msg = apply_filters('uwp_incorrect_username_error_msg', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'userswp'));

            // Check the username for register
            if ($field->htmlvar_name == 'uwp_account_username') {
                if (!is_admin()) {
                    if (!validate_username($sanitized_value)) {
                        $errors->add('invalid_username', $incorrect_username_error_msg);
                    }
                    if (username_exists($sanitized_value)) {
                        $errors->add('username_exists', __('<strong>Error</strong>: This username is already registered. Please choose another one.', 'userswp'));
                    }
                }
            }

            // Check the username for login
            if ($field->htmlvar_name == 'uwp_login_username') {
                if (!validate_username($sanitized_value)) {
                    $errors->add('invalid_username', $incorrect_username_error_msg);
                }
            }


            $validated_data[$field->htmlvar_name] = $sanitized_value;

        }
    }

    $error_code = $errors->get_error_code();
    if (!empty($error_code)) {
        return $errors;
    }

    if ($type == 'login') {
        $password_type = 'login';
    } elseif ($type == 'reset') {
        $password_type = 'reset';
    } elseif ($type == 'change') {
        $password_type = 'change';
    } else {
        $password_type = 'account';
    }

    if (($type == 'change' && $enable_old_password == '1')) {
        //check old password
        if( empty( $data['uwp_'.$password_type.'_old_password'] ) ) {
            $errors->add( 'empty_password', __( '<strong>Error</strong>: Please enter your old password', 'userswp' ) );
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        $pass = $data['uwp_'.$password_type.'_old_password'];
        $user = get_user_by( 'id', get_current_user_id() );
        if ( !wp_check_password( $pass, $user->data->user_pass, $user->ID) ) {
            $errors->add( 'invalid_password', __( '<strong>Error</strong>: Incorrect old password', 'userswp' ) );
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        if( $data['uwp_'.$password_type.'_old_password'] == $data['uwp_'.$password_type.'_password'] ) {
            $errors->add( 'invalid_password', __( '<strong>Error</strong>: Old password and new password are same', 'userswp' ) );
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

    }

    if (($type == 'register' && $enable_confirm_email_field == '1')) {
        //check confirm email
        if( empty( $data['uwp_account_email'] ) ) {
            $errors->add( 'empty_email', __( '<strong>Error</strong>: Please enter your Email', 'userswp' ) );
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        if( !isset($data['uwp_account_confirm_email']) || empty( $data['uwp_account_confirm_email'] ) ) {
            $errors->add( 'empty_confirm_email', __( '<strong>Error</strong>: Please fill Confirm Email field', 'userswp' ) );
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        if( $data['uwp_account_email'] != $data['uwp_account_confirm_email'] ) {
            $errors->add( 'email_mismatch', __( '<strong>Error</strong>: Email and Confirm email not match', 'userswp' ) );
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

    }

    if ($type == 'change' || $type == 'reset' || $type == 'login' || ($type == 'register' && $enable_password == '1')) {
        //check password
        if( empty( $data['uwp_'.$password_type.'_password'] ) ) {
            $errors->add( 'empty_password', __( 'Please enter a password', 'userswp' ) );
        }

        if ($type != 'login' && strlen($data['uwp_'.$password_type.'_password']) < 7) {
            $errors->add('pass_match', __('ERROR: Password must be 7 characters or more.', 'userswp'));
        }

        $validated_data['password'] = $data['uwp_'.$password_type.'_password'];
    }

    $error_code = $errors->get_error_code();
    if (!empty($error_code)) {
        return $errors;
    }

    if (($type == 'register' && $enable_password == '1') || $type == 'reset' || $type == 'change') {

        if (($type == 'register' && $enable_confirm_password_field != '1')) {
            $validated_data['password'] = $data['uwp_'.$password_type.'_password'];
        } else {
            //check password
            if ($data['uwp_'.$password_type.'_password'] != $data['uwp_'.$password_type.'_confirm_password']) {
                $errors->add('pass_match', __('ERROR: Passwords do not match.', 'userswp'));
            }

            $validated_data['password'] = $data['uwp_'.$password_type.'_password'];
        }
    }


    $error_code = $errors->get_error_code();
    if (!empty($error_code)) {
        return $errors;
    }

    return $validated_data;
}

/**
 * Returns form field label. If empty returns the field title.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       object      $field      Field info.
 *
 * @return      string                  Label.
 */
function uwp_get_form_label($field) {
    if (isset($field->form_label) && !empty($field->form_label)) {
        $label = __($field->form_label, 'userswp');
    } else {
        $label = __($field->site_title, 'userswp');
    }
    return $label;
}

/**
 * Loads the font-awesome css files.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
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
 * Displays admin settings form.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      string      Form html.
 */
function uwp_display_form() {

    $page = isset( $_GET['page'] ) ? $_GET['page'] : 'userswp';
    $settings_array = uwp_get_settings_tabs();

    $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $settings_array[$page] ) ? $_GET['tab'] : 'main';
    ob_start();
    ?>
    <form method="post" action="options.php">
        <?php
        $title = apply_filters('uwp_display_form_title', false, $page, $active_tab);
        if ($title) { ?>
            <h2 class="title"><?php echo $title; ?></h2>
        <?php } ?>

        <table class="uwp-form-table">
            <?php
            settings_fields( 'uwp_settings' );
            do_settings_fields( 'uwp_settings_' . $page .'_'.$active_tab, 'uwp_settings_' . $page .'_'.$active_tab );
            ?>
        </table>
        <?php submit_button(); ?>
    </form>

    <?php
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

/**
 * Returns the setting page tab list for each page.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Tabs list.
 */
function uwp_get_settings_tabs() {

    $tabs = array();

    // wp-admin/admin.php?page=uwp
    $tabs['userswp']  = array(
        'main' => __( 'General', 'userswp' ),
        'register' => __( 'Register', 'userswp' ),
        'login' => __( 'Login', 'userswp' ),
        'change' => __( 'Change Password', 'userswp' ),
        'profile' => __( 'Profile', 'userswp' ),
        'users' => __( 'Users', 'userswp' ),
        'uninstall' => __( 'Uninstall', 'userswp' ),
    );

    // wp-admin/admin.php?page=uwp_form_builder
    $tabs['uwp_form_builder'] = array(
        'main' => __( 'Form Builder', 'userswp' ),
    );

    // wp-admin/admin.php?page=uwp_notifications
    $tabs['uwp_notifications'] = array(
        'main' => __( 'Users', 'userswp' ),
    );

    return apply_filters( 'uwp_settings_tabs', $tabs );
}

/**
 * Returns the Users page layout class based on the setting.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
 *
 * @param       mixed       $result     Value for the user's option.
 * @param       string      $option     Name of the option being retrieved.
 * @param       WP_User     $user       WP_User object of the user whose option is being retrieved.
 *
 * @return      array                   Filtered value.
 */
function uwp_always_nav_menu_visibility( $result, $option, $user )
{
    if( in_array( 'add-users-wp-nav-menu', $result ) ) {
        $result = array_diff( $result, array( 'add-users-wp-nav-menu' ) );
    }

    return $result;
}

add_filter('user_profile_picture_description', 'uwp_admin_user_profile_picture_description');

/**
 * Filters the user profile picture description displayed under the Gravatar.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
 *
 * @param       object      $user       User object.
 *
 * @return      void
 */
function uwp_admin_edit_banner_fields($user) {
    global $wpdb;

    $file_obj = new Users_WP_Files();

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
                    if ($field->field_icon) {
                        $icon = '<i class="uwp_field_icon '.$field->field_icon.'"></i>';
                    } else {
                        $icon = '';
                    }

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



// Privacy
add_filter('uwp_account_page_title', 'uwp_account_privacy_page_title', 10, 2);

/**
 * Adds Privacy tab title in Account page.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
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
        if ($fields || $make_profile_private) {
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
                        <?php } ?>
                        <?php foreach ($fields as $field) { ?>
                            <div class="uwp-profile-extra-wrap">
                                <div class="uwp-profile-extra-key"><?php echo $field->site_title; ?>
                                    <span class="uwp-profile-extra-sep">:</span></div>
                                <div class="uwp-profile-extra-value">
                                    <?php
                                    $field_name = $field->htmlvar_name.'_privacy';
                                    $value = uwp_get_usermeta($user_id, $field_name, false);
                                    if ($value === false) {
                                        $value = '1';
                                    }
                                    ?>
                                    <select name="<?php echo $field_name; ?>" class="uwp_privacy_field" style="margin: 0;">
                                        <option value="0" <?php selected( $value, "0" ); ?>><?php echo __("No", "userswp") ?></option>
                                        <option value="1" <?php selected( $value, "1" ); ?>><?php echo __("Yes", "userswp") ?></option>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>

                        <?php
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
                                Make the whole profile Private
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
        }
        echo '</div>';
    }
}

add_action('uwp_account_menu_display', 'uwp_add_account_menu_links');

/**
 * Prints "Edit account" page subtab / submenu links. Ex: Privacy
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      void
 */
function uwp_add_account_menu_links() {

    if (isset($_GET['type'])) {
        $type = strip_tags(esc_sql($_GET['type']));
    } else {
        $type = 'account';
    }

    $account_page = uwp_get_option('account_page', false);
    $account_page_link = get_permalink($account_page);

    $account_available_tabs = uwp_account_get_available_tabs();

    if (!is_array($account_available_tabs) || count($account_available_tabs) <= 1) {
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

        $active = $type == $tab_id ? ' active' : '';
        ?>
        <li id="uwp-account-<?php echo $tab_id; ?>">
            <a class="<?php echo $active; ?>" href="<?php echo esc_url( $tab_url ); ?>">
                <i class="<?php echo $tab['icon']; ?>"></i> <?php echo $tab['title']; ?>
            </a>
        </li>
        <?php
    }

    echo '</ul>';
}

/**
 * Adds Privacy tab to available account tabs if privacy enabled.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Account Tabs.
 */
function uwp_account_get_available_tabs() {

    $tabs = array();

    $tabs['account']  = array(
        'title' => __( 'Edit Account', 'userswp' ),
        'icon' => 'fa fa-user',
    );

    $extra_where = "AND is_public='2'";
    $fields = get_account_form_fields($extra_where);
    $fields = apply_filters('uwp_account_privacy_fields', $fields);

    $make_profile_private = uwp_can_make_profile_private();

    if ((is_array($fields) && count($fields) > 0) || $make_profile_private) {
        $tabs['privacy']  = array(
            'title' => __( 'Privacy', 'userswp' ),
            'icon' => 'fa fa-lock',
        );
    }

    return apply_filters( 'uwp_account_available_tabs', $tabs );
}

add_action('init', 'uwp_privacy_submit_handler');

/**
 * Handles the privacy form submission.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      void
 */
function uwp_privacy_submit_handler() {
    if (isset($_POST['uwp_privacy_submit'])) {
        if( ! isset( $_POST['uwp_privacy_nonce'] ) || ! wp_verify_nonce( $_POST['uwp_privacy_nonce'], 'uwp-privacy-nonce' ) ) {
            return;
        }

        $extra_where = "AND is_public='2'";
        $fields = get_account_form_fields($extra_where);
        $fields = apply_filters('uwp_account_privacy_fields', $fields);
        if ($fields) {
            foreach ($fields as $field) {
                $field_name = $field->htmlvar_name.'_privacy';
                if (isset($_POST[$field_name])) {
                    $value = strip_tags(esc_sql($_POST[$field_name]));
                    $user_id = get_current_user_id();
                    uwp_update_usermeta($user_id, $field_name, $value);
                }
            }
        }

        $make_profile_private = uwp_can_make_profile_private();
        if ($make_profile_private) {
            $field_name = 'uwp_make_profile_private';
            if (isset($_POST[$field_name])) {
                $value = strip_tags(esc_sql($_POST[$field_name]));
                $user_id = get_current_user_id();
                update_user_meta($user_id, $field_name, $value);
            }
        }



    }
}

add_action('admin_head', 'uwp_admin_only_css');

/**
 * Adds UsersWP css to admin area
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      void
 */
function uwp_admin_only_css() {
    ?>
    <style type="text/css">
        .uwp_page .uwp-bs-modal input[type="submit"].button,
        .uwp_page .uwp-bs-modal button.button {
            padding: 0 10px 1px;
        }
    </style>
    <?php
}

/**
 * Updates extras fields sort order.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
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
 * @package     UsersWP
 *
 * @param       string      $db             Table name.
 * @param       string      $column         Column name.
 *
 * @return      bool
 */
function uwp_column_exist($db, $column)
{
    global $wpdb;
    $exists = false;
    $columns = $wpdb->get_col("show columns from $db");
    foreach ($columns as $c) {
        if ($c == $column) {
            $exists = true;
            break;
        }
    }
    return $exists;
}

/**
 * Adds column if not exist in the table.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string      $db             Table name.
 * @param       string      $column         Column name.
 * @param       string      $column_attr    Column attributes.
 *
 * @return      bool|int                    True when success.
 */
function uwp_add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NOT NULL")
{
    $excluded = uwp_get_excluded_fields();

    $starts_with = "uwp_account_";

    if ((substr($column, 0, strlen($starts_with)) === $starts_with) && !in_array($column, $excluded)) {
        global $wpdb;
        $result = 0;// no rows affected
        if (!uwp_column_exist($db, $column)) {
            if (!empty($db) && !empty($column))
                $result = $wpdb->query("ALTER TABLE `$db` ADD `$column`  $column_attr");
        }
        return $result;
    } else {
        return true;
    }
}

/**
 * Returns excluded custom fields.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
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
 * Returns the General > Regsiter tab setting fields
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_register_fields() {
    $fields =  array(
        'uwp_registration_action' => array(
            'id' => 'uwp_registration_action',
            'name' => __('Registration Action', 'userswp'),
            'desc' => __('Select how registration should be handled.', 'userswp'),
            'type' => 'select',
            'global' => false,
            'options' => uwp_registration_status_options(),
            'chosen' => true,
            'placeholder' => __( 'Select Option', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
        'register_redirect_to' => array(
            'id' => 'register_redirect_to',
            'name' => __( 'Register Redirect Page', 'userswp' ),
            'desc' => __( 'Set the page to redirect the user to after signing up. If no page has been set WordPress default will be used.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
        'register_terms_page' => array(
            'id' => 'register_terms_page',
            'name' => __( 'Register TOS Page', 'userswp' ),
            'desc' => __( 'Terms of Service page. When set "Accept terms and Conditions" checkbox will appear on the register form.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
    );
    return $fields;
}

/**
 * Returns the General > Login tab setting fields
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_login_fields() {
    $fields =  array(
        'login_redirect_to' => array(
            'id' => 'login_redirect_to',
            'name' => __( 'Login Redirect Page', 'userswp' ),
            'desc' => __( 'Set the page to redirect the user to after logging in. If no page has been set WordPress default will be used.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
    );
    return $fields;
}

/**
 * Returns the General > Logout tab setting fields
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_logout_fields() {
    $fields =  array(
        'logout_redirect_to' => array(
            'id' => 'logout_redirect_to',
            'name' => __( 'Logout Redirect Page', 'userswp' ),
            'desc' => __( 'Set the page to redirect the user to after logging out. If no page has been set WordPress default will be used.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
    );
    return $fields;
}

/**
 * Returns the General > Delete tab setting fields
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_delete_fields() {
    $fields =  array(
        'delete_redirect_to' => array(
            'id' => 'delete_redirect_to',
            'name' => __( 'Delete Redirect Page', 'userswp' ),
            'desc' => __( 'Set the page to redirect the user to after after they delete account. If no page has been set WordPress default will be used.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
    );
    return $fields;
}

/**
 * Returns merged login and logout tab setting fields
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_loginout_fields() {
    $login = uwp_settings_general_login_fields();
    $logout = uwp_settings_general_logout_fields();

    $fields = array_merge($login, $logout);
    return $fields;
}

add_filter('uwp_get_max_upload_size', 'uwp_modify_get_max_upload_size', 10, 2);

/**
 * Modifies the maximum file upload size based on the setting.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       int         $bytes      Size in bytes.
 * @param       string      $type       File upload type.
 *
 * @return      int                     Size in bytes.
 */
function uwp_modify_get_max_upload_size($bytes, $type) {

    if ($type == 'avatar') {
        $kb = uwp_get_option('profile_avatar_size', false);
        if ($kb) {
            $bytes = intval($kb) * 1024;
        }
    }

    if ($type == 'banner') {
        $kb = uwp_get_option('profile_banner_size', false);
        if ($kb) {
            $bytes = intval($kb) * 1024;
        }
    }

    return $bytes;

}

/**
 * Checks whether the user can make his/her own profile private or not.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
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
 * @package     UsersWP
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

add_action('init', 'uwp_process_activation_link');

/**
 * Handles the activation request coming via email activation link.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      void
 */
function uwp_process_activation_link() {
    if (isset($_GET['uwp_activate']) && $_GET['uwp_activate'] == 'yes') {
        $key =  strip_tags(esc_sql($_GET['key']));
        $login =  strip_tags(esc_sql($_GET['login']));
        $login_page = uwp_get_option('login_page', false);
        $result = uwp_check_activation_key($key, $login);

        if (is_wp_error($result)) {
            if ($login_page) {
                $redirect_to = add_query_arg(array('uwp_err' => 'act_wrong'), get_permalink($login_page));
                wp_redirect($redirect_to);
                exit();
            }
        } else {
            if (!$result) {
                if ($login_page) {
                    $redirect_to = add_query_arg(array('uwp_err' => 'act_error'), get_permalink($login_page));
                    wp_redirect($redirect_to);
                    exit();
                }
            } else {
                if ($login_page) {
                    $user_data = get_user_by('login', $login);
                    update_user_meta( $user_data->ID, 'uwp_mod', '0' );
                    $redirect_to = add_query_arg(array('uwp_err' => 'act_success'), get_permalink($login_page));
                    wp_redirect($redirect_to);
                    exit();
                }
            }
        }
    }
}

add_action('uwp_template_fields', 'uwp_template_fields_terms_check', 100, 1);

/**
 * Adds "Accept terms and conditions" checkbox in register form.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string      $form_type      Form type.
 *
 * @return      void
 */
function uwp_template_fields_terms_check($form_type) {
    if ($form_type == 'register') {
        $terms_page = false;
        $reg_terms_page_id = uwp_get_option('register_terms_page', '');
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

add_filter('uwp_form_input_email_uwp_account_email_after', 'uwp_register_confirm_email_field', 10, 4);

/**
 * Adds confirm email field in forms.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string          $html           Form field html
 * @param       object          $field          Field info.
 * @param       string          $value          Form field default value.
 * @param       string          $form_type      Form type
 *
 * @return      string                          Modified form field html.
 */
function uwp_register_confirm_email_field($html, $field, $value, $form_type) {
    if ($form_type == 'register') {
        //confirm email field
        $extra = array();
        if (isset($field->extra_fields) && $field->extra_fields != '') {
            $extra = unserialize($field->extra_fields);
        }
        $enable_confirm_email_field = isset($extra['confirm_email']) ? $extra['confirm_email'] : '0';
        if ($enable_confirm_email_field == '1') {
            ob_start(); // Start  buffering;
            ?>
            <div id="uwp_account_confirm_email_row"
                 class="<?php echo 'required_field';?> uwp_form_email_row">

                <?php
                $site_title = __("Confirm Email", 'userswp');
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php echo '<span>*</span>'; ?>
                    </label>
                <?php } ?>

                <input name="uwp_account_confirm_email"
                       class="uwp_textfield"
                       id="uwp_account_confirm_email"
                       placeholder="<?php echo $site_title; ?>"
                       value=""
                       title="<?php echo $site_title; ?>"
                    <?php echo 'required="required"'; ?>
                       type="email"
                />
            </div>

            <?php
            $confirm_html = ob_get_clean();
            $html = $html.$confirm_html;
        }
    }
    return $html;
}

add_filter('uwp_form_input_password_uwp_account_password_after', 'uwp_register_confirm_password_field', 10, 4);

/**
 * Adds confirm password field in forms.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string          $html           Form field html
 * @param       object          $field          Field info.
 * @param       string          $value          Form field default value.
 * @param       string          $form_type      Form type
 *
 * @return      string                          Modified form field html.
 */
function uwp_register_confirm_password_field($html, $field, $value, $form_type) {
    if ($form_type == 'register') {
        //confirm password field
        $extra = array();
        if (isset($field->extra_fields) && $field->extra_fields != '') {
            $extra = unserialize($field->extra_fields);
        }
        $enable_confirm_password_field = isset($extra['confirm_password']) ? $extra['confirm_password'] : '0';
        if ($enable_confirm_password_field == '1') {
            ob_start(); // Start  buffering;
            ?>
            <div id="uwp_account_confirm_password_row"
                 class="<?php echo 'required_field';?> uwp_form_password_row">

                <?php
                $site_title = __("Confirm Password", 'userswp');
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php echo '<span>*</span>'; ?>
                    </label>
                <?php } ?>

                <input name="uwp_account_confirm_password"
                       class="uwp_textfield"
                       id="uwp_account_confirm_password"
                       placeholder="<?php echo $site_title; ?>"
                       value=""
                       title="<?php echo $site_title; ?>"
                    <?php echo 'required="required"'; ?>
                       type="password"
                />
            </div>

            <?php
            $confirm_html = ob_get_clean();
            $html = $html.$confirm_html;
        }
    }
    return $html;
}

/**
 * Redirects the user to login page when email not confirmed.
 *
 * @since       1.0.0
 * @package     UsersWP
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
                $login_page = uwp_get_option('login_page', false);
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
 * @package     UsersWP
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
 * @package     UsersWP
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
 * @package     UsersWP
 *
 * @return      string      Table prefix
 */
function uwp_get_table_prefix() {
    global $wpdb;
    $install_type = uwp_get_installation_type();
    if ($install_type == "multi_not_na") {
        return $wpdb->prefix;
    } else {
        return $wpdb->base_prefix;
    }
}


/**
 * Deleting the table whenever a blog is deleted
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       array       $tables     Tables to delete.
 *
 * @return      array                   Modified table array to delete
 */
function uwp_drop_tables_on_delete_blog( $tables ) {
    global $wpdb;
    $tables[] = $wpdb->prefix . 'uwp_form_fields';
    $tables[] = $wpdb->prefix . 'uwp_form_extras';
    $tables[] = $wpdb->prefix . 'uwp_usermeta';
    return $tables;
}
add_filter( 'wpmu_drop_tables', 'uwp_drop_tables_on_delete_blog' );

/**
 * Returns the page link for register page.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array|bool|mixed    Page link.
 */
function uwp_get_register_page_url() {
    return uwp_get_page_url_data('register_page');
}

/**
 * Returns the page link for login page.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array|bool|mixed    Page link.
 */
function uwp_get_login_page_url() {
    return uwp_get_page_url_data('login_page');
}

/**
 * Returns the page link for forgot password page.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array|bool|mixed    Page link.
 */
function uwp_get_forgot_page_url() {
    return uwp_get_page_url_data('forgot_page');
}

/**
 * Returns the page link for change password page.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array|bool|mixed    Page link.
 */
function uwp_get_change_page_url() {
    return uwp_get_page_url_data('change_page');
}

/**
 * Returns the page link for reset password page.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array|bool|mixed    Page link.
 */
function uwp_get_reset_page_url() {
    return uwp_get_page_url_data('reset_page');
}

/**
 * Returns the page link for account page.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array|bool|mixed    Page link.
 */
function uwp_get_account_page_url() {
    return uwp_get_page_url_data('account_page');
}

/**
 * Returns the page link for profile page.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array|bool|mixed    Page link.
 */
function uwp_get_profile_page_url() {
    return uwp_get_page_url_data('profile_page');
}

/**
 * Returns the page link for users page.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array|bool|mixed    Page link.
 */
function uwp_get_users_page_url() {
    return uwp_get_page_url_data('users_page');
}

/**
 * Returns the page info like page title, slug and link.
 * If output type is "link", then only the link is returned.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string              $page_type      Page type.
 * @param       string              $output_type    Link or Array?
 *
 * @return      array|bool|mixed                    Page info or link.
 */
function uwp_get_page_url_data($page_type, $output_type = 'link') {

    $install_type = uwp_get_installation_type();

    $page_data = array();
    switch ($install_type) {
        case "single":
            $page_data = uwp_get_page_url_page_data($page_data, $page_type);
            break;
        case "multi_na_all":
            $page_data = uwp_get_page_url_page_data($page_data, $page_type);
            break;
        case "multi_na_site_id":
            if (defined('UWP_ROOT_PAGES')) {
                $blog_id = UWP_ROOT_PAGES;
            } else {
                $blog_id = (int) get_network()->site_id;
            }
            $current_blog_id = get_current_blog_id();
            if (!is_int($blog_id)) {
                $page_data = array();
            } else {
                if ($blog_id == $current_blog_id) {
                    $page_data = uwp_get_page_url_page_data($page_data, $page_type);
                } else {
                    // Switch to the new blog.
                    switch_to_blog( $blog_id );
                    $page_data = uwp_get_page_url_page_data($page_data, $page_type);
                    // Restore original blog.
                    restore_current_blog();
                }
            }
            break;
        case "multi_na_default":
            $is_main_site = is_main_site();
            if ($is_main_site) {
                $page_data = uwp_get_page_url_page_data($page_data, $page_type);
            } else {
                $main_blog_id = (int) get_network()->site_id;
                // Switch to the new blog.
                switch_to_blog( $main_blog_id );
                $page_data = uwp_get_page_url_page_data($page_data, $page_type);
                // Restore original blog.
                restore_current_blog();
            }
            break;
        case "multi_not_na":
            $page_data = uwp_get_page_url_page_data($page_data, $page_type);
            break;
        default:
            $page_data = array();

    }

    if ($output_type == 'link') {
        if (empty($page_data)) {
            return false;
        } else {
            return $page_data['link'];
        }
    } else {
        return $page_data;
    }
}

/**
 * Gets random date.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       int $days_from      Random days from.
 * @param       int $days_to        Random days to.
 *
 * @return      string              Formatted date string.
 */
function uwp_get_random_date( $days_from = 30, $days_to = 0 ) {
    // 1 day in seconds is 86400
    $from = $days_from * rand( 10000, 99999 );

    // $days_from should always be less than $days_to
    if ( $days_to > $days_from ) {
        $days_to = $days_from - 1;
    }

    $to        = $days_to * rand( 10000, 99999 );
    $date_from = time() - $from;
    $date_to   = time() - $to;

    return date( 'Y-m-d H:i:s', rand( $date_from, $date_to ) );
}

/**
 * Converts array to comma separated string.
 *
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
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
 * @package     UsersWP
 *
 * @return      void
 */
function uwp_create_tables()
{

    global $wpdb;

    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';

    $wpdb->hide_errors();

    $collate = '';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
    }

    /**
     * Include any functions needed for upgrades.
     *
     * @since 1.0.0
     */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $form_fields = "CREATE TABLE " . $table_name . " (
							  id int(11) NOT NULL AUTO_INCREMENT,
							  form_type varchar(100) NULL,
							  data_type varchar(100) NULL,
							  field_type varchar(255) NOT NULL COMMENT 'text,checkbox,radio,select,textarea',
							  field_type_key varchar(255) NOT NULL,
							  site_title varchar(255) NULL DEFAULT NULL,
							  form_label varchar(255) NULL DEFAULT NULL,
							  help_text varchar(255) NULL DEFAULT NULL,
							  htmlvar_name varchar(255) NULL DEFAULT NULL,
							  default_value text NULL DEFAULT NULL,
							  sort_order int(11) NOT NULL,
							  option_values text NULL DEFAULT NULL,
							  is_active enum( '0', '1' ) NOT NULL DEFAULT '1',
							  is_default enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_dummy enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_public enum( '0', '1', '2' ) NOT NULL DEFAULT '0',
							  is_required enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_register_field enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_search_field enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_register_only_field enum( '0', '1' ) NOT NULL DEFAULT '0',
							  required_msg varchar(255) NULL DEFAULT NULL,
							  show_in text NULL DEFAULT NULL,
							  user_roles text NULL DEFAULT NULL,
							  extra_fields text NULL DEFAULT NULL,
							  field_icon varchar(255) NULL DEFAULT NULL,
							  css_class varchar(255) NULL DEFAULT NULL,
							  decimal_point varchar( 10 ) NOT NULL,
							  validation_pattern varchar( 255 ) NOT NULL,
							  validation_msg text NULL DEFAULT NULL,
							  PRIMARY KEY  (id)
							  ) $collate";

    $form_fields = apply_filters('uwp_before_form_field_table_create', $form_fields);

    dbDelta($form_fields);

    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

    $form_extras = "CREATE TABLE " . $extras_table_name . " (
									  id int(11) NOT NULL AUTO_INCREMENT,
									  form_type varchar(255) NOT NULL,
									  field_type varchar(255) NOT NULL COMMENT 'text,checkbox,radio,select,textarea',
									  site_htmlvar_name varchar(255) NOT NULL,
									  sort_order int(11) NOT NULL,
									  is_default enum( '0', '1' ) NOT NULL DEFAULT '0',
									  is_dummy enum( '0', '1' ) NOT NULL DEFAULT '0',
									  expand_custom_value int(11) NULL DEFAULT NULL,
									  searching_range_mode int(11) NULL DEFAULT NULL,
									  expand_search int(11) NULL DEFAULT NULL,
									  front_search_title varchar(255) CHARACTER SET utf8 NULL DEFAULT NULL,
									  first_search_value int(11) NULL DEFAULT NULL,
									  first_search_text varchar(255) CHARACTER SET utf8 NULL DEFAULT NULL,
									  last_search_text varchar(255) CHARACTER SET utf8 NULL DEFAULT NULL,
									  search_min_value int(11) NULL DEFAULT NULL,
									  search_max_value int(11) NULL DEFAULT NULL,
									  search_diff_value int(11) NULL DEFAULT NULL,
									  search_condition varchar(100) NULL DEFAULT NULL,
									  field_input_type varchar(255) NULL DEFAULT NULL,
									  field_data_type varchar(255) NULL DEFAULT NULL,
									  PRIMARY KEY  (id)
									) $collate AUTO_INCREMENT=1 ;";

    $form_extras = apply_filters('uwp_before_form_extras_table_create', $form_extras);

    dbDelta($form_extras);


    // Table for storing userswp usermeta
    $usermeta_table_name = uwp_get_table_prefix() . 'uwp_usermeta';
    $user_meta = "CREATE TABLE " . $usermeta_table_name . " (
						user_id int(20) NOT NULL,
						user_ip varchar(20) NULL DEFAULT NULL,
						user_privacy varchar(255) NULL DEFAULT NULL,
						uwp_account_username varchar(255) NULL DEFAULT NULL,
						uwp_account_email varchar(255) NULL DEFAULT NULL,
						uwp_account_first_name varchar(255) NULL DEFAULT NULL,
						uwp_account_last_name varchar(255) NULL DEFAULT NULL,
						uwp_account_bio varchar(255) NULL DEFAULT NULL,
						uwp_account_avatar_thumb varchar(255) NULL DEFAULT NULL,
						uwp_account_banner_thumb varchar(255) NULL DEFAULT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

    $user_meta = apply_filters('uwp_before_usermeta_table_create', $user_meta);

    dbDelta($user_meta);

}

/**
 * Creates uwp_usermeta table which introduced in version 1.0.1
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      void
 */
function uwp101_create_tables() {
    global $wpdb;


    $wpdb->hide_errors();

    $collate = '';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
    }

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


    // Table for storing userswp usermeta
    $usermeta_table_name = uwp_get_table_prefix() . 'uwp_usermeta';
    $user_meta = "CREATE TABLE " . $usermeta_table_name . " (
						user_id int(20) NOT NULL,
						user_ip varchar(20) NULL DEFAULT NULL,
						uwp_account_username varchar(255) NULL DEFAULT NULL,
						uwp_account_email varchar(255) NULL DEFAULT NULL,
						uwp_account_first_name varchar(255) NULL DEFAULT NULL,
						uwp_account_last_name varchar(255) NULL DEFAULT NULL,
						uwp_account_bio varchar(255) NULL DEFAULT NULL,
						uwp_account_avatar_thumb varchar(255) NULL DEFAULT NULL,
						uwp_account_banner_thumb varchar(255) NULL DEFAULT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

    $user_meta = apply_filters('uwp_before_usermeta_table_create', $user_meta);

    dbDelta($user_meta);
}

/**
 * Returns tye client IP.
 *
 * @since       1.0.0
 * @package     UsersWP
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
 * @package     UsersWP
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
 * @package     UsersWP
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
 * Returns the page info like page title, slug and link.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string      $page_data      Page data array.
 * @param       string      $page_type      Page type.
 *
 * @return      array                       Page data array.
 */
function uwp_get_page_url_page_data($page_data, $page_type) {
    $page_id = uwp_get_option($page_type, false, false);
    if ($page_id) {
        $page = get_post($page_id);
        $page_data = array(
            'name' => $page->post_title,
            'slug' => $page->post_name,
            'link' => get_permalink( $page->ID ),
        );
    }
    return $page_data;
}

add_action('uwp_template_form_title_after', 'uwp_display_username_in_account', 10, 1);

/**
 * Prints the username link in "Edit Account" page
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string      $type       Page type.
 *
 * @return      void
 */
function uwp_display_username_in_account($type) {
    if ($type == 'account') {
        $user_id = get_current_user_id();
        $user_info = get_userdata($user_id);
        $display_name = $user_info->user_login;
        ?>
        <span class="uwp_account_page_username">
            <a href="<?php echo uwp_build_profile_tab_url($user_id); ?>">( @<?php echo $display_name; ?> )</a>
        </span>
        <?php
    }
}

/**
 * Returns the country array.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Country array.
 */
function uwp_get_country_data(){
    return array(
        'af' => 'Afghanistan (‫افغانستان‬‎)',
        'ax' => 'Åland Islands (Åland)',
        'al' => 'Albania (Shqipëri)',
        'dz' => 'Algeria (‫الجزائر‬‎)',
        'as' => 'American Samoa',
        'ad' => 'Andorra',
        'ao' => 'Angola',
        'ai' => 'Anguilla',
        'ag' => 'Antigua and Barbuda',
        'ar' => 'Argentina',
        'am' => 'Armenia (Հայաստան)',
        'aw' => 'Aruba',
        'au' => 'Australia',
        'at' => 'Austria (Österreich)',
        'az' => 'Azerbaijan (Azərbaycan)',
        'bs' => 'Bahamas',
        'bh' => 'Bahrain (‫البحرين‬‎)',
        'bd' => 'Bangladesh (বাংলাদেশ)',
        'bb' => 'Barbados',
        'by' => 'Belarus (Беларусь)',
        'be' => 'Belgium (België)',
        'bz' => 'Belize',
        'bj' => 'Benin (Bénin)',
        'bm' => 'Bermuda',
        'bt' => 'Bhutan (འབྲུག)',
        'bo' => 'Bolivia',
        'ba' => 'Bosnia and Herzegovina (Босна и Херцеговина)',
        'bw' => 'Botswana',
        'br' => 'Brazil (Brasil)',
        'io' => 'British Indian Ocean Territory',
        'vg' => 'British Virgin Islands',
        'bn' => 'Brunei',
        'bg' => 'Bulgaria (България)',
        'bf' => 'Burkina Faso',
        'bi' => 'Burundi (Uburundi)',
        'kh' => 'Cambodia (កម្ពុជា)',
        'cm' => 'Cameroon (Cameroun)',
        'ca' => 'Canada',
        'cv' => 'Cape Verde (Kabu Verdi)',
        'bq' => 'Caribbean Netherlands',
        'ky' => 'Cayman Islands',
        'cf' => 'Central African Republic (République Centrafricaine)',
        'td' => 'Chad (Tchad)',
        'cl' => 'Chile',
        'cn' => 'China (中国)',
        'cx' => 'Christmas Island',
        'cc' => 'Cocos (Keeling) Islands (Kepulauan Cocos (Keeling))',
        'co' => 'Colombia',
        'km' => 'Comoros (‫جزر القمر‬‎)',
        'cd' => 'Congo (DRC) (Jamhuri ya Kidemokrasia ya Kongo)',
        'cg' => 'Congo (Republic) (Congo-Brazzaville)',
        'ck' => 'Cook Islands',
        'cr' => 'Costa Rica',
        'ci' => 'Côte d’Ivoire',
        'hr' => 'Croatia (Hrvatska)',
        'cu' => 'Cuba',
        'cw' => 'Curaçao',
        'cy' => 'Cyprus (Κύπρος)',
        'cz' => 'Czech Republic (Česká republika)',
        'dk' => 'Denmark (Danmark)',
        'dj' => 'Djibouti',
        'dm' => 'Dominica',
        'do' => 'Dominican Republic (República Dominicana)',
        'ec' => 'Ecuador',
        'eg' => 'Egypt (‫مصر‬‎)',
        'sv' => 'El Salvador',
        'gq' => 'Equatorial Guinea (Guinea Ecuatorial)',
        'er' => 'Eritrea',
        'ee' => 'Estonia (Eesti)',
        'et' => 'Ethiopia',
        'fk' => 'Falkland Islands (Islas Malvinas)',
        'fo' => 'Faroe Islands (Føroyar)',
        'fj' => 'Fiji',
        'fi' => 'Finland (Suomi)',
        'fr' => 'France',
        'gf' => 'French Guiana (Guyane française)',
        'pf' => 'French Polynesia (Polynésie française)',
        'ga' => 'Gabon',
        'gm' => 'Gambia',
        'ge' => 'Georgia (საქართველო)',
        'de' => 'Germany (Deutschland)',
        'gh' => 'Ghana (Gaana)',
        'gi' => 'Gibraltar',
        'gr' => 'Greece (Ελλάδα)',
        'gl' => 'Greenland (Kalaallit Nunaat)',
        'gd' => 'Grenada',
        'gp' => 'Guadeloupe',
        'gu' => 'Guam',
        'gt' => 'Guatemala',
        'gg' => 'Guernsey',
        'gn' => 'Guinea (Guinée)',
        'gw' => 'Guinea-Bissau (Guiné Bissau)',
        'gy' => 'Guyana',
        'ht' => 'Haiti',
        'hn' => 'Honduras',
        'hk' => 'Hong Kong (香港)',
        'hu' => 'Hungary (Magyarország)',
        'is' => 'Iceland (Ísland)',
        'in' => 'India (भारत)',
        'id' => 'Indonesia',
        'ir' => 'Iran (‫ایران‬‎)',
        'iq' => 'Iraq (‫العراق‬‎)',
        'ie' => 'Ireland',
        'im' => 'Isle of Man',
        'il' => 'Israel (‫ישראל‬‎)',
        'it' => 'Italy (Italia)',
        'jm' => 'Jamaica',
        'jp' => 'Japan (日本)',
        'je' => 'Jersey',
        'jo' => 'Jordan (‫الأردن‬‎)',
        'kz' => 'Kazakhstan (Казахстан)',
        'ke' => 'Kenya',
        'ki' => 'Kiribati',
        'xk' => 'Kosovo (Kosovë)',
        'kw' => 'Kuwait (‫الكويت‬‎)',
        'kg' => 'Kyrgyzstan (Кыргызстан)',
        'la' => 'Laos (ລາວ)',
        'lv' => 'Latvia (Latvija)',
        'lb' => 'Lebanon (‫لبنان‬‎)',
        'ls' => 'Lesotho',
        'lr' => 'Liberia',
        'ly' => 'Libya (‫ليبيا‬‎)',
        'li' => 'Liechtenstein',
        'lt' => 'Lithuania (Lietuva)',
        'lu' => 'Luxembourg',
        'mo' => 'Macau (澳門)',
        'mk' => 'Macedonia (FYROM) (Македонија)',
        'mg' => 'Madagascar (Madagasikara)',
        'mw' => 'Malawi',
        'my' => 'Malaysia',
        'mv' => 'Maldives',
        'ml' => 'Mali',
        'mt' => 'Malta',
        'mh' => 'Marshall Islands',
        'mq' => 'Martinique',
        'mr' => 'Mauritania (‫موريتانيا‬‎)',
        'mu' => 'Mauritius (Moris)',
        'yt' => 'Mayotte',
        'mx' => 'Mexico (México)',
        'fm' => 'Micronesia',
        'md' => 'Moldova (Republica Moldova)',
        'mc' => 'Monaco',
        'mn' => 'Mongolia (Монгол)',
        'me' => 'Montenegro (Crna Gora)',
        'ms' => 'Montserrat',
        'ma' => 'Morocco (‫المغرب‬‎)',
        'mz' => 'Mozambique (Moçambique)',
        'mm' => 'Myanmar (Burma) (မြန်မာ)',
        'na' => 'Namibia (Namibië)',
        'nr' => 'Nauru',
        'np' => 'Nepal (नेपाल)',
        'nl' => 'Netherlands (Nederland)',
        'nc' => 'New Caledonia (Nouvelle-Calédonie)',
        'nz' => 'New Zealand',
        'ni' => 'Nicaragua',
        'ne' => 'Niger (Nijar)',
        'ng' => 'Nigeria',
        'nu' => 'Niue',
        'nf' => 'Norfolk Island',
        'kp' => 'North Korea (조선 민주주의 인민 공화국)',
        'mp' => 'Northern Mariana Islands',
        'no' => 'Norway (Norge)',
        'om' => 'Oman (‫عُمان‬‎)',
        'pk' => 'Pakistan (‫پاکستان‬‎)',
        'pw' => 'Palau',
        'ps' => 'Palestine (‫فلسطين‬‎)',
        'pa' => 'Panama (Panamá)',
        'pg' => 'Papua New Guinea',
        'py' => 'Paraguay',
        'pe' => 'Peru (Perú)',
        'ph' => 'Philippines',
        'pn' => 'Pitcairn Islands',
        'pl' => 'Poland (Polska)',
        'pt' => 'Portugal',
        'pr' => 'Puerto Rico',
        'qa' => 'Qatar (‫قطر‬‎)',
        're' => 'Réunion (La Réunion)',
        'ro' => 'Romania (România)',
        'ru' => 'Russia (Россия)',
        'rw' => 'Rwanda',
        'bl' => 'Saint Barthélemy (Saint-Barthélemy)',
        'sh' => 'Saint Helena',
        'kn' => 'Saint Kitts and Nevis',
        'lc' => 'Saint Lucia',
        'mf' => 'Saint Martin (Saint-Martin (partie française))',
        'pm' => 'Saint Pierre and Miquelon (Saint-Pierre-et-Miquelon)',
        'vc' => 'Saint Vincent and the Grenadines',
        'ws' => 'Samoa',
        'sm' => 'San Marino',
        'st' => 'São Tomé and Príncipe (São Tomé e Príncipe)',
        'sa' => 'Saudi Arabia (‫المملكة العربية السعودية‬‎)',
        'xs' => 'Scotland',
        'sn' => 'Senegal (Sénégal)',
        'rs' => 'Serbia (Србија)',
        'sc' => 'Seychelles',
        'sl' => 'Sierra Leone',
        'sg' => 'Singapore',
        'sx' => 'Sint Maarten',
        'sk' => 'Slovakia (Slovensko)',
        'si' => 'Slovenia (Slovenija)',
        'sb' => 'Solomon Islands',
        'so' => 'Somalia (Soomaaliya)',
        'za' => 'South Africa',
        'gs' => 'South Georgia & South Sandwich Islands',
        'kr' => 'South Korea (대한민국)',
        'ss' => 'South Sudan (‫جنوب السودان‬‎)',
        'es' => 'Spain (España)',
        'lk' => 'Sri Lanka (ශ්‍රී ලංකාව)',
        'sd' => 'Sudan (‫السودان‬‎)',
        'sr' => 'Suriname',
        'sj' => 'Svalbard and Jan Mayen (Svalbard og Jan Mayen)',
        'sz' => 'Swaziland',
        'se' => 'Sweden (Sverige)',
        'ch' => 'Switzerland (Schweiz)',
        'sy' => 'Syria (‫سوريا‬‎)',
        'tw' => 'Taiwan (台灣)',
        'tj' => 'Tajikistan',
        'tz' => 'Tanzania',
        'th' => 'Thailand (ไทย)',
        'tl' => 'Timor-Leste',
        'tg' => 'Togo',
        'tk' => 'Tokelau',
        'to' => 'Tonga',
        'tt' => 'Trinidad and Tobago',
        'tn' => 'Tunisia (‫تونس‬‎)',
        'tr' => 'Turkey (Türkiye)',
        'tm' => 'Turkmenistan',
        'tc' => 'Turks and Caicos Islands',
        'tv' => 'Tuvalu',
        'ug' => 'Uganda',
        'ua' => 'Ukraine (Україна)',
        'ae' => 'United Arab Emirates (‫الإمارات العربية المتحدة‬‎)',
        'gb' => 'United Kingdom',
        'us' => 'United States',
        'um' => 'U.S. Minor Outlying Islands',
        'vi' => 'U.S. Virgin Islands',
        'uy' => 'Uruguay',
        'uz' => 'Uzbekistan (Oʻzbekiston)',
        'vu' => 'Vanuatu',
        'va' => 'Vatican City (Città del Vaticano)',
        've' => 'Venezuela',
        'vn' => 'Vietnam (Việt Nam)',
        'xw' => 'Wales',
        'wf' => 'Wallis and Futuna',
        'eh' => 'Western Sahara (‫الصحراء الغربية‬‎)',
        'ye' => 'Yemen (‫اليمن‬‎)',
        'zm' => 'Zambia',
        'zw' => 'Zimbabwe',
    );
}

/**
 * Outputs country html.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string      $value      Country code.  
 *
 * @return      string                  Html string.
 */
function uwp_output_country_html($value){
    if($value){
        $countries = uwp_get_country_data();
        $country = isset($countries[$value]) ? $countries[$value]: '';
        $value = '<div class="country-select uwp-cs"><div class="flag '.esc_html($value).'"></div><div class="uwp-country-name">'.esc_html($country).'</div></div>';
    }
    return $value;
}