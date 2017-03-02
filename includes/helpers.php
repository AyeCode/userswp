<?php
function uwp_get_page_link($page) {

    $link = "";
    $page_id = false;

    switch ($page) {
        case 'register':
            $page_id = uwp_get_option('register_page', false);
            break;

        case 'login':
            $page_id = uwp_get_option('login_page', false);
            break;

        case 'forgot':
            $page_id = uwp_get_option('forgot_page', false);
            break;

        case 'account':
            $page_id = uwp_get_option('account_page', false);
            break;

        case 'profile':
            $page_id = uwp_get_option('profile_page', false);
            break;

        case 'users':
            $page_id = uwp_get_option('users_page', false);
            break;
    }

    if ($page_id) {
        $link = get_permalink($page_id);
    }

    return $link;
}

function uwp_post_count($user_id, $post_type, $extra_post_status = '') {
    global $wpdb;

    $post_status = "";
    if ($user_id == get_current_user_id()) {
        $post_status = ' OR post_status = "draft" OR post_status = "private"';
    }
    
    if (!empty($extra_post_status)) {
        $post_status .= $extra_post_status;
    }

    $post_status_where = ' AND ( post_status = "publish" ' . $post_status . ' )';

    $count = $wpdb->get_var('
             SELECT COUNT(ID)
             FROM ' . $wpdb->posts. '
             WHERE post_author = "' . $user_id . '"
             ' . $post_status_where . '
             AND post_type = "' . $post_type . '"'
    );
    return $count;
}

function uwp_comment_count($user_id) {
    global $wpdb;

    $count = $wpdb->get_var('
             SELECT COUNT(comment_ID)
             FROM ' . $wpdb->comments. '
             WHERE user_id = "' . $user_id . '"'
    );
    return $count;
}

function uwp_missing_callback($args) {
    printf(
        __( 'The callback function used for the %s setting is missing.', 'userswp' ),
        '<strong>' . $args['id'] . '</strong>'
    );
}

function uwp_select_callback($args) {
    global $uwp_options;

    if ( isset( $uwp_options[ $args['id'] ] ) ) {
        $value = $uwp_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    if ( isset( $args['placeholder'] ) ) {
        $placeholder = $args['placeholder'];
    } else {
        $placeholder = '';
    }

    if ( isset( $args['chosen'] ) ) {
        $chosen = ($args['multiple'] ? '[]" multiple="multiple" class="uwp_chosen_select" style="height:auto"' : "'");
    } else {
        $chosen = '';
    }

    $html = '<select id="uwp_settings[' . $args['id'] . ']" name="uwp_settings[' . $args['id'] . ']' . $chosen . ' data-placeholder="' . $placeholder . '" />';

    foreach ( $args['options'] as $option => $name ) {
        if (is_array($value)) {
            if (in_array($option, $value)) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
        } else {
            $selected = selected( $option, $value, false );
        }
        $html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
    }

    $html .= '</select>';
    $html .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

function uwp_text_callback( $args ) {
    global $uwp_options;

    if ( isset( $uwp_options[ $args['id'] ] ) ) {
        $value = $uwp_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $args['readonly'] = true;
        $value = isset( $args['std'] ) ? $args['std'] : '';
        $name  = '';
    } else {
        $name = 'name="uwp_settings[' . $args['id'] . ']"';
    }

    $readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
    $size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html     = '<input type="text" class="' . $size . '-text" id="uwp_settings[' . $args['id'] . ']"' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . '/>';
    $html    .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

function uwp_textarea_callback( $args ) {
    global $uwp_options;

    if ( isset( $uwp_options[ $args['id'] ] ) ) {
        $value = $uwp_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $html = '<textarea class="large-text" cols="50" rows="5" id="uwp_settings[' . $args['id'] . ']" name="uwp_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
    $html .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

function uwp_checkbox_callback( $args ) {
    global $uwp_options;

    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $name = '';
    } else {
        $name = 'name="uwp_settings[' . $args['id'] . ']"';
    }

    $checked = isset( $uwp_options[ $args['id'] ] ) ? checked( 1, $uwp_options[ $args['id'] ], false ) : '';
    $html = '<input type="checkbox" id="uwp_settings[' . $args['id'] . ']"' . $name . ' value="1" ' . $checked . '/>';
    $html .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

function uwp_number_callback( $args ) {
    global $uwp_options;

    if ( isset( $uwp_options[ $args['id'] ] ) ) {
        $value = $uwp_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $args['readonly'] = true;
        $value = isset( $args['std'] ) ? $args['std'] : '';
        $name  = '';
    } else {
        $name = 'name="uwp_settings[' . $args['id'] . ']"';
    }

    $max  = isset( $args['max'] ) ? $args['max'] : 999999;
    $min  = isset( $args['min'] ) ? $args['min'] : 0;
    $step = isset( $args['step'] ) ? $args['step'] : 1;

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="uwp_settings[' . $args['id'] . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

function uwp_build_profile_tab_url($user_id, $tab = false, $subtab = false) {

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

    return $link;

}

function uwp_get_option( $key = '', $default = false ) {
    global $uwp_options;
    $value = ! empty( $uwp_options[ $key ] ) ? $uwp_options[ $key ] : $default;
    $value = apply_filters( 'uwp_get_option', $value, $key, $default );
    return apply_filters( 'uwp_get_option_' . $key, $value, $key, $default );
}

function uwp_update_option( $key = false, $value = '') {

    if (!$key ) {
        return false;
    }

    $settings = get_option( 'uwp_settings', array());

    if( !is_array( $settings ) ) {
        $settings = array();
    }

    $settings[ $key ] = $value;

    $settings = apply_filters( 'uwp_update_option', $settings, $key, $value );
    $settings =  apply_filters( 'uwp_update_option_' . $key, $settings, $key, $value );

    update_option( 'uwp_settings', $settings );

    return true;
}

function uwp_get_usermeta( $user_id = false, $key = '', $default = false ) {

    if (!$user_id) {
        return $default;
    }

    $user_data = get_userdata($user_id);
    $usermeta = get_user_meta( $user_id, 'uwp_usermeta', true );

    if ($key == 'uwp_account_email') {
        $value = $user_data->user_email;
    } elseif ($key == 'uwp_account_first_name') {
        $value = $user_data->first_name;
    } elseif ($key == 'uwp_account_last_name') {
        $value = $user_data->last_name;
    } elseif ($key == 'uwp_account_bio') {
        $value = $user_data->description;
    } else {
        if( !is_array( $usermeta ) ) {
            $usermeta = array();
        }
        $value = isset( $usermeta[ $key ] ) ? $usermeta[ $key ] : $default;
    }
    $value = apply_filters( 'uwp_get_usermeta', $value, $user_id, $key, $default );
    return apply_filters( 'uwp_get_usermeta_' . $key, $value, $user_id, $key, $default );
}

function uwp_update_usermeta( $user_id = false, $key, $value ) {

    if (!$user_id || !$key ) {
        return false;
    }

    $usermeta = get_user_meta( $user_id, 'uwp_usermeta', true );

    if( !is_array( $usermeta ) ) {
        $usermeta = array();
    }

    $usermeta[ $key ] = $value;

    $usermeta = apply_filters( 'uwp_update_usermeta', $usermeta, $user_id, $key, $value );
    $usermeta =  apply_filters( 'uwp_update_usermeta_' . $key, $usermeta, $user_id, $key, $value );

    do_action( 'uwp_before_update_usermeta', $usermeta, $user_id, $key, $value );

    update_user_meta($user_id, 'uwp_usermeta', $usermeta);

    return true;
}

function uwp_date_format_php_to_jqueryui( $php_format ) {
    $symbols = array(
        // Day
        'd' => 'dd',
        'D' => 'D',
        'j' => 'd',
        'l' => 'DD',
        'N' => '',
        'S' => '',
        'w' => '',
        'z' => 'o',
        // Week
        'W' => '',
        // Month
        'F' => 'MM',
        'm' => 'mm',
        'M' => 'M',
        'n' => 'm',
        't' => '',
        // Year
        'L' => '',
        'o' => '',
        'Y' => 'yy',
        'y' => 'y',
        // Time
        'a' => 'tt',
        'A' => 'TT',
        'B' => '',
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => '',
        'u' => ''
    );

    $jqueryui_format = "";
    $escaping = false;

    for ( $i = 0; $i < strlen( $php_format ); $i++ ) {
        $char = $php_format[$i];

        // PHP date format escaping character
        if ( $char === '\\' ) {
            $i++;

            if ( $escaping ) {
                $jqueryui_format .= $php_format[$i];
            } else {
                $jqueryui_format .= '\'' . $php_format[$i];
            }

            $escaping = true;
        } else {
            if ( $escaping ) {
                $jqueryui_format .= "'";
                $escaping = false;
            }

            if ( isset( $symbols[$char] ) ) {
                $jqueryui_format .= $symbols[$char];
            } else {
                $jqueryui_format .= $char;
            }
        }
    }

    return $jqueryui_format;
}

function uwp_date($date_input, $date_to, $date_from = '') {
    if (empty($date_input) || empty($date_to)) {
        return NULL;
    }

    $date = '';
    if (!empty($date_from)) {
        $datetime = date_create_from_format($date_from, $date_input);

        if (!empty($datetime)) {
            $date = $datetime->format($date_to);
        }
    }

    if (empty($date)) {
        $date = strpos($date_input, '/') !== false ? str_replace('/', '-', $date_input) : $date_input;
        $date = date_i18n($date_to, strtotime($date));
    }

    $date = uwp_maybe_untranslate_date($date);

    return apply_filters('uwp_date', $date, $date_input, $date_to, $date_from);
}

function uwp_maybe_untranslate_date($date){
    $english_long_months = array(
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
    );

    $non_english_long_months  = array(
        __('January'),
        __('February'),
        __('March'),
        __('April'),
        __('May'),
        __('June'),
        __('July'),
        __('August'),
        __('September'),
        __('October'),
        __('November'),
        __('December'),
    );
    $date = str_replace($non_english_long_months,$english_long_months,$date);


    $english_short_months = array(
        ' Jan ',
        ' Feb ',
        ' Mar ',
        ' Apr ',
        ' May ',
        ' Jun ',
        ' Jul ',
        ' Aug ',
        ' Sep ',
        ' Oct ',
        ' Nov ',
        ' Dec ',
    );

    $non_english_short_months = array(
        ' '._x( 'Jan', 'January abbreviation' ).' ',
        ' '._x( 'Feb', 'February abbreviation' ).' ',
        ' '._x( 'Mar', 'March abbreviation' ).' ',
        ' '._x( 'Apr', 'April abbreviation' ).' ',
        ' '._x( 'May', 'May abbreviation' ).' ',
        ' '._x( 'Jun', 'June abbreviation' ).' ',
        ' '._x( 'Jul', 'July abbreviation' ).' ',
        ' '._x( 'Aug', 'August abbreviation' ).' ',
        ' '._x( 'Sep', 'September abbreviation' ).' ',
        ' '._x( 'Oct', 'October abbreviation' ).' ',
        ' '._x( 'Nov', 'November abbreviation' ).' ',
        ' '._x( 'Dec', 'December abbreviation' ).' ',
    );

    $date = str_replace($non_english_short_months,$english_short_months,$date);


    return $date;
}

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

function uwp_get_file_type($ext) {
    $allowed_file_types = uwp_allowed_mime_types();
    $file_types = array();
    foreach ( $allowed_file_types as $format => $types ) {
        $file_types = array_merge($file_types, $types);
    }
    $file_types = array_flip($file_types);
    return $file_types[$ext];
}

function uwp_resizeImage($image,$width,$height,$scale) {
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

function uwp_resizeThumbnailImage($thumb_image_name, $image, $x, $y, $src_w, $src_h, $scale){
    // ignore image createion warnings
    @ini_set('gd.jpeg_ignore_warning', 1);
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

function is_uwp_page($type = false) {
    if (is_page()) {
        global $post;
        $current_page_id = $post->ID;
        if ($type) {
            $uwp_page = uwp_get_option($type, false);
            if ( $uwp_page && ((int) $uwp_page ==  $current_page_id ) ) {
                return true;
            } else {
                return false;
            }    
        } else {
            if (is_uwp_register_page() ||
                is_uwp_login_page() ||
                is_uwp_forgot_page() ||
                is_uwp_change_page() ||
                is_uwp_reset_page() ||
                is_uwp_account_page() ||
                is_uwp_profile_page() ||
                is_uwp_users_page()) {
                return true;
            } else {
                return false;
            }
        }

    } else {
        return false;
    }
}

function is_uwp_register_page() {
    return is_uwp_page('register_page');
}

function is_uwp_login_page() {
    return is_uwp_page('login_page');
}

function is_uwp_forgot_page() {
    return is_uwp_page('forgot_page');
}

function is_uwp_change_page() {
    return is_uwp_page('change_page');
}

function is_uwp_reset_page() {
    return is_uwp_page('reset_page');
}

function is_uwp_account_page() {
    return is_uwp_page('account_page');
}

function is_uwp_profile_page() {
    return is_uwp_page('profile_page');
}

function is_uwp_users_page() {
    return is_uwp_page('users_page');
}

function is_uwp_current_user_profile_page() {
    if (is_user_logged_in() && 
        is_uwp_profile_page()
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

function get_uwp_register_permalink() {
    return get_uwp_page_permalink('register_page');
}

function get_uwp_login_permalink() {
    return get_uwp_page_permalink('login_page');
}

function get_uwp_forgot_permalink() {
    return get_uwp_page_permalink('forgot_page');
}

function get_uwp_reset_permalink() {
    return get_uwp_page_permalink('reset_page');
}

function get_uwp_account_permalink() {
    return get_uwp_page_permalink('account_page');
}

function get_uwp_profile_permalink() {
    return get_uwp_page_permalink('profile_page');
}

function get_uwp_users_permalink() {
    return get_uwp_page_permalink('users_page');
}

function get_uwp_page_permalink($type) {
    $link = false;
    $page_id = uwp_get_option($type, false);
    if ($page_id) {
        $link = get_permalink($page_id);
    }
    return $link;
}

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
                            <a href="<?php echo get_the_permalink(); ?>" class="more-link">Read More »</a>
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

//File uploads
function handle_file_upload( $field, $files ) {

    if ( isset( $files[ $field->htmlvar_name ] ) && ! empty( $files[ $field->htmlvar_name ] ) && ! empty( $files[ $field->htmlvar_name ]['name'] ) ) {

        $extra_fields = unserialize($field->extra_fields);

        $allowed_mime_types = array();
        if (isset($extra_fields['uwp_file_types']) && !in_array("*", $extra_fields['uwp_file_types'])) {
            $allowed_mime_types = $extra_fields['uwp_file_types'];
        }

        $allowed_mime_types = apply_filters('uwp_allowed_mime_types', $allowed_mime_types, $field->htmlvar_name);

        $file_urls       = array();
        $files_to_upload = uwp_prepare_files( $files[ $field->htmlvar_name ] );

        foreach ( $files_to_upload as $file_key => $file_to_upload ) {

            if (!empty($allowed_mime_types)) {
                $ext = uwp_get_file_type($file_to_upload['type']);

                $allowed_error_text = implode(', ', $allowed_mime_types);
                if ( !in_array( $ext , $allowed_mime_types ) )
                    return new WP_Error( 'validation-error', sprintf( __( 'Allowed files types are: %s', 'userswp' ),  $allowed_error_text) );
            }


            $max_upload_size = uwp_get_max_upload_size();


            if ( ! $max_upload_size ) {
                $max_upload_size = 0;
            }

            if ( $file_to_upload['size'] >  $max_upload_size) {
                return new WP_Error( 'file-too-big', __( 'The uploaded file is too big. Maximum size allowed:'. uwp_formatSizeUnits($max_upload_size), 'userswp' ) );
            }



//            $upload_max_ini_size = uwp_get_size_in_bytes(ini_get('upload_max_filesize'));
//            if ($upload_max_ini_size && $file_to_upload['size'] > $upload_max_ini_size) {
//                return new WP_Error( 'file-too-big', __( 'The uploaded file is too big. Maximum size allowed:'. uwp_formatSizeUnits($max_upload_size), 'userswp' ) );
//            }

            
            $error_result = apply_filters('uwp_handle_file_upload_error_checks', true, $field, $file_key, $file_to_upload);
            if (is_wp_error($error_result)) {
                return $error_result;
            }

            remove_filter( 'wp_handle_upload_prefilter', 'uwp_wp_media_restrict_file_types' );
            $uploaded_file = uwp_upload_file( $file_to_upload, array( 'file_key' => $file_key ) );
            add_filter( 'wp_handle_upload_prefilter', 'uwp_wp_media_restrict_file_types' );

            if ( is_wp_error( $uploaded_file ) ) {

                return new WP_Error( 'validation-error', $uploaded_file->get_error_message() );

            } else {

                $file_urls[] = array(
                    'url'  => $uploaded_file->url,
                    'path' => $uploaded_file->path,
                    'size' => $uploaded_file->size,
                    'name' => $uploaded_file->name,
                    'type' => $uploaded_file->type,
                );

            }

        }

        return current( $file_urls );

    }
    return true;

}

function uwp_formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' kB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' byte';
    }
    else
    {
        $bytes = '0 bytes';
    }

    return $bytes;
}

function uwp_get_size_in_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= (1024 * 1024 * 1024); //1073741824
            break;
        case 'm':
            $val *= (1024 * 1024); //1048576
            break;
        case 'k':
            $val *= 1024;
            break;
    }

    return $val;
}



function uwp_upload_file( $file, $args = array() ) {

    include_once ABSPATH . 'wp-admin/includes/file.php';
    include_once ABSPATH . 'wp-admin/includes/media.php';

    $args = wp_parse_args( $args, array(
        'file_key'           => '',
        'file_label'         => '',
        'allowed_mime_types' => get_allowed_mime_types()
    ) );

    $uploaded_file              = new stdClass();

    if ( ! in_array( $file['type'], $args['allowed_mime_types'] ) ) {
        if ( $args['file_label'] ) {
            return new WP_Error( 'upload', sprintf( __( '"%s" (filetype %s) needs to be one of the following file types: %s', 'userswp' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
        } else {
            return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'userswp' ), implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
        }
    } else {
        $upload = wp_handle_upload( $file, apply_filters( 'uwp_handle_upload_overrides', array( 'test_form' => false ) ) );
        if ( ! empty( $upload['error'] ) ) {
            return new WP_Error( 'upload', $upload['error'] );
        } else {
            $uploaded_file->url       = $upload['url'];
            $uploaded_file->name      = basename( $upload['file'] );
            $uploaded_file->path      = $upload['file'];
            $uploaded_file->type      = $upload['type'];
            $uploaded_file->size      = $file['size'];
            $uploaded_file->extension = substr( strrchr( $uploaded_file->name, '.' ), 1 );
        }
    }


    return $uploaded_file;
}


function uwp_prepare_files( $file_data ) {
    $files_to_upload = array();

    if ( is_array( $file_data['name'] ) ) {
        foreach ( $file_data['name'] as $file_data_key => $file_data_value ) {

            if ( $file_data['name'][ $file_data_key ] ) {
                $files_to_upload[] = array(
                    'name'     => $file_data['name'][ $file_data_key ],
                    'type'     => $file_data['type'][ $file_data_key ],
                    'tmp_name' => $file_data['tmp_name'][ $file_data_key ],
                    'error'    => $file_data['error'][ $file_data_key ],
                    'size'     => $file_data['size'][ $file_data_key ]
                );
            }
        }
    } else {
        $files_to_upload[] = $file_data;
    }

    return $files_to_upload;
}


function uwp_validate_uploads($files, $type, $url_only = true, $fields = false) {

    $validated_data = array();

    if (empty($files)) {
        return $validated_data;
    }

    if (!$fields) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';

        if ($type == 'register') {
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' AND is_register_field = '1' ORDER BY sort_order ASC", array('account')));
        } elseif ($type == 'account') {
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' AND is_register_only_field = '0' ORDER BY sort_order ASC", array('account')));
        } else {
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));
        }    
    }


    if (!empty($fields)) {
        foreach ($fields as $field) {
            if(isset($files[$field->htmlvar_name])) {

                $file_urls = handle_file_upload($field, $files);

                if (is_wp_error($file_urls)) {
                    return $file_urls;
                }

                if ($url_only) {
                    $validated_data[$field->htmlvar_name] = $file_urls['url'];    
                } else {
                    $validated_data[$field->htmlvar_name] = $file_urls;
                }
            }

        }
    }

    return $validated_data;
}

function get_register_form_fields() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'uwp_form_fields';
    $extras_table_name = $wpdb->prefix . 'uwp_form_extras';
    $fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.is_active = '1' AND fields.is_register_field = '1' ORDER BY extras.sort_order ASC", array('account')));
    return $fields;
}

function get_register_validate_form_fields() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'uwp_form_fields';
    $extras_table_name = $wpdb->prefix . 'uwp_form_extras';
    $fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.field_type != 'fieldset' AND fields.field_type != 'file' AND fields.is_active = '1' AND fields.is_register_field = '1' ORDER BY extras.sort_order ASC", array('account')));
    return $fields;
}


function get_change_validate_form_fields() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'uwp_form_fields';
    $enable_old_password = uwp_get_option('change_enable_old_password', false);
    if ($enable_old_password == '1') {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' ORDER BY sort_order ASC", array('change')));
    } else {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND htmlvar_name != 'uwp_change_old_password' ORDER BY sort_order ASC", array('change')));
    }
    return $fields;
}

function get_account_form_fields($extra_where = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'uwp_form_fields';
    $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND is_register_only_field = '0' " . $extra_where . " ORDER BY sort_order ASC", array('account', $extra_where)));
    return $fields;
}

function get_change_form_fields() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'uwp_form_fields';
    $enable_old_password = uwp_get_option('change_enable_old_password', false);
    if ($enable_old_password == '1') {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' ORDER BY sort_order ASC", array('change')));
    } else {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND htmlvar_name != 'uwp_change_old_password' ORDER BY sort_order ASC", array('change')));
    }
    return $fields;
}

function get_uwp_users_list() {

    global $wpdb;

    $keyword = false;
    if (isset($_GET['uwps']) && $_GET['uwps'] != '') {
        $keyword = strip_tags(esc_sql($_GET['uwps']));
    }

    $sort_by = false;
    if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
        $sort_by = strip_tags(esc_sql($_GET['uwp_sort_by']));
    }

    $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

    $number = uwp_get_option('profile_no_of_items', 10);


    if ($keyword) {
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
                if ($user->ID == get_current_user_id()) {
                    continue;
                }
                ?>
                <li class="uwp-users-list-user">
                    <div class="uwp-users-list-user-left">
                        <?php do_action('uwp_users_profile_header', $user); ?>
                    </div>
                    <div class="uwp-users-list-user-right">
                        <div class="uwp-users-list-user-name">
                            <h3>
                                <a href="<?php echo apply_filters('uwp_profile_link', get_author_posts_url($user->ID), $user->ID); ?>"><?php echo $user->display_name; ?></a>
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

function uwp_file_upload_preview($field, $value, $removable = true) {
    $output = '';

    if ($field->htmlvar_name == "uwp_banner_file") {
        $htmlvar = "uwp_account_banner_thumb";
    } elseif ($field->htmlvar_name == "uwp_avatar_file") {
        $htmlvar = "uwp_account_avatar_thumb";
    } else {
        $htmlvar = $field->htmlvar_name;
    }

    // If is current user's profile (profile.php)
    if ( is_admin() && defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ) {
        $user_id = get_current_user_id();
        // If is another user's profile page
    } elseif (is_admin() && ! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
        $user_id = $_GET['user_id'];
        // Otherwise something is wrong.
    } else {
        $user_id = get_current_user_id();
    }

    if ($value) {
        $file = basename( $value );
        $filetype = wp_check_filetype($file);
        $image_types = array('png', 'jpg', 'jpeg', 'gif');
        if (in_array($filetype['ext'], $image_types)) {
            $output .= '<div class="uwp_file_preview_wrap">';
            $output .= '<a href="'.$value.'" class="uwp_upload_file_preview"><img style="max-width:100px;" src="'.$value.'" /></a>';
            if ($removable) {
                $output .= '<a onclick="return confirm(\'are you sure?\')" style="display: block;margin: 5px 0;" href="#" id="'.$htmlvar.'" data-htmlvar="'.$htmlvar.'" data-uid="'.$user_id.'" class="uwp_upload_file_remove">'. __( 'Remove Image' , 'userswp' ).'</a>';
            }
            $output .= '</div>';
            ?>
            <?php
        } else {
            $output .= '<div class="uwp_file_preview_wrap">';
            $output .= '<a href="'.$value.'" class="uwp_upload_file_preview">'.$file.'</a>';
            if ($removable) {
                $output .= '<a onclick="return confirm(\'are you sure?\')" style="display: block;margin: 5px 0;" href="#" id="'.$htmlvar.'" data-htmlvar="'.$htmlvar.'" data-uid="'.$user_id.'" class="uwp_upload_file_remove">'. __( 'Remove File' , 'userswp' ).'</a>';
            }
            $output .= '</div>';
            ?>
            <?php
        }
    }
    return $output;
}

function uwp_validate_fields($data, $type, $fields = false) {

    $errors = new WP_Error();

    if (!$fields) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';
        if ($type == 'register') {
            $fields = get_register_validate_form_fields();
        } elseif ($type == 'change') {
            $fields = get_change_validate_form_fields();
        } elseif ($type == 'account') {
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND is_register_only_field = '0' ORDER BY sort_order ASC", array('account')));
        } else {
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));
        }
    }
    

    $validated_data = array();
    $enable_password = uwp_get_option('enable_register_password', false);
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
            }


            if (!isset($data[$field->htmlvar_name]) && $field->is_required == 1) {
                if ($field->required_msg) {
                    $errors->add('empty_'.$field->htmlvar_name,  __('<strong>Error</strong>: '.$field->site_title.' '.$field->required_msg, 'userswp'));
                } else {
                    $errors->add('empty_'.$field->htmlvar_name, __('<strong>Error</strong>: '.$field->site_title.' cannot be empty.', 'userswp'));
                }
            }

            if ($errors->get_error_code())
                return $errors;


            $value = $data[$field->htmlvar_name];
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
                if ($field->required_msg) {
                    $errors->add('empty_'.$field->htmlvar_name,  __('<strong>Error</strong>: '.$field->site_title.' '.$field->required_msg, 'userswp'));
                } else {
                    $errors->add('empty_'.$field->htmlvar_name, __('<strong>Error</strong>: '.$field->site_title.' cannot be empty.', 'userswp'));
                }

            }

            if ($field->field_type == 'email' && !empty($sanitized_value) && !is_email($sanitized_value)) {
                $errors->add('invalid_email', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'userswp'));
            }

            //register email
            if ($type == 'register' && $field->htmlvar_name == 'uwp_account_email' && email_exists($sanitized_value)) {
                $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered, please choose another one.', 'userswp'));
            }

            //forgot email
            if ($field->htmlvar_name == 'uwp_forgot_email' && !email_exists($sanitized_value)) {
                $errors->add('email_exists', __('<strong>Error</strong>: This email doesn\'t exists.', 'userswp'));
            }

            // Check the username for register
            if ($field->htmlvar_name == 'uwp_account_username') {
                if (!validate_username($sanitized_value)) {
                    $errors->add('invalid_username', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'userswp'));
                }
                if (username_exists($sanitized_value)) {
                    $errors->add('username_exists', __('<strong>Error</strong>: This username is already registered. Please choose another one.', 'userswp'));
                }
            }

            // Check the username for login
            if ($field->htmlvar_name == 'uwp_login_username') {
                if (!validate_username($sanitized_value)) {
                    $errors->add('invalid_username', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'userswp'));
                }
            }


            $validated_data[$field->htmlvar_name] = $sanitized_value;

        }
    }

    if ($errors->get_error_code())
        return $errors;

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

        if ($errors->get_error_code())
            return $errors;

        $pass = $data['uwp_'.$password_type.'_old_password'];
        $user = get_user_by( 'id', get_current_user_id() );
        if ( !wp_check_password( $pass, $user->data->user_pass, $user->ID) ) {
            $errors->add( 'invalid_password', __( '<strong>Error</strong>: Incorrect old password', 'userswp' ) );
        }

        if ($errors->get_error_code())
            return $errors;

        if( $data['uwp_'.$password_type.'_old_password'] == $data['uwp_'.$password_type.'_password'] ) {
            $errors->add( 'invalid_password', __( '<strong>Error</strong>: Old password and new password are same', 'userswp' ) );
        }

        if ($errors->get_error_code())
            return $errors;

    }


    if ($type == 'change' || $type == 'reset' || $type == 'login' || ($type == 'register' && $enable_password == '1')) {
        //check password
        if( empty( $data['uwp_'.$password_type.'_password'] ) ) {
            $errors->add( 'empty_password', __( 'Please enter a password', 'userswp' ) );
        }

        if (strlen($data['uwp_'.$password_type.'_password']) < 7) {
            $errors->add('pass_match', __('ERROR: Password must be 7 characters or more.', 'userswp'));
        }

        $validated_data['password'] = $data['uwp_'.$password_type.'_password'];
    }

    if ($errors->get_error_code())
        return $errors;

    if (($type == 'register' && $enable_password == '1') || $type == 'reset' || $type == 'change') {
        //check password
        if ($data['uwp_'.$password_type.'_password'] != $data['uwp_'.$password_type.'_confirm_password']) {
            $errors->add('pass_match', __('ERROR: Passwords do not match.', 'userswp'));
        }

        $validated_data['password'] = $data['uwp_'.$password_type.'_password'];
    }


    if ($errors->get_error_code())
        return $errors;

    return $validated_data;
}


function uwp_wp_media_restrict_file_types($file) {
    // This bit is for the flash uploader
    if ($file['type']=='application/octet-stream' && isset($file['tmp_name'])) {
        $file_size = getimagesize($file['tmp_name']);
        if (isset($file_size['error']) && $file_size['error']!=0) {
            $file['error'] = "Unexpected Error: {$file_size['error']}";
            return $file;
        } else {
            $file['type'] = $file_size['mime'];
        }
    }
    list($category,$type) = explode('/',$file['type']);
    if ('image'!=$category || !in_array($type,array('jpg','jpeg','gif','png'))) {
        $file['error'] = "Sorry, you can only upload a .GIF, a .JPG, or a .PNG image file.";
    } else if ($post_id = (isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : false)) {
        if (count(get_posts("post_type=attachment&post_parent={$post_id}"))>0)
            $file['error'] = "Sorry, you cannot upload more than one (1) image.";
    }
    return $file;
}

function uwp_get_form_label($field) {
    if (isset($field->form_label) && !empty($field->form_label)) {
        $label = __($field->form_label, 'userswp');
    } else {
        $label = __($field->site_title, 'userswp');
    }
    return $label;
}

function uwp_doing_upload(){
    return isset($_POST['uwp_profile_upload']) ? true : false;
}

function is_uwp_profile_tab($tab = false) {
    global $wp_query;
    if (is_uwp_profile_page()) {
        if (isset($wp_query->query_vars['uwp_tab']) && !empty($wp_query->query_vars['uwp_tab'])) {
            if ($wp_query->query_vars['uwp_tab'] == $tab) {
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

function is_uwp_profile_subtab($subtab = false) {
    global $wp_query;
    if (is_uwp_profile_page()) {
        if (isset($wp_query->query_vars['uwp_subtab']) && !empty($wp_query->query_vars['uwp_subtab'])) {
            if ($wp_query->query_vars['uwp_subtab'] == $subtab) {
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

function uwp_get_custom_field_info($htmlvar_name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'uwp_form_fields';
    $field = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE htmlvar_name = %s", array($htmlvar_name)));
    return $field;
}

function uwp_wrap_notice($message, $type) {
    $output = '<div class="uwp-alert-'.$type.' text-center">';
    $output .= $message;
    $output .= '</div>';
    return $output;    
    
}

function uwp_get_max_upload_size() {
    if (is_multisite()) {
        $network_setting_size = esc_attr( get_site_option( 'fileupload_maxk', 300 ) );
        $max_upload_size = uwp_get_size_in_bytes($network_setting_size.'k');
        if ($max_upload_size > wp_max_upload_size()) {
            $max_upload_size = wp_max_upload_size();
        }
    } else {
        $max_upload_size = wp_max_upload_size();
    }
    return $max_upload_size;
}


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
        'main' => __( 'Notifications', 'userswp' ),
    );

    return apply_filters( 'uwp_settings_tabs', $tabs );
}

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

function uwp_always_nav_menu_visibility( $result, $option, $user )
{
    if( in_array( 'add-users-wp-nav-menu', $result ) ) {
        $result = array_diff( $result, array( 'add-users-wp-nav-menu' ) );
    }

    return $result;
}

add_filter('user_profile_picture_description', 'uwp_admin_user_profile_picture_description');
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

function uwp_admin_edit_banner_fields($user) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'uwp_form_fields';
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
                                <?php echo uwp_file_upload_preview($field, $value); ?>
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
function uwp_account_privacy_page_title($title, $type) {
    if ($type == 'privacy') {
        $title = __( 'Privacy', 'userswp' );
    }

    return $title;
}

add_action('uwp_account_form_display', 'uwp_account_privacy_edit_form_display');
function uwp_account_privacy_edit_form_display($type) {
    if ($type == 'privacy') {
        echo '<div class="uwp-account-form uwp_wc_form">';
        $extra_where = "AND is_public='2'";
        $fields = get_account_form_fields($extra_where);
        if ($fields) {
            ?>
            <div class="uwp-profile-extra">
                <div class="uwp-profile-extra-div form-table">
                    <form class="uwp-account-form uwp_form" method="post">
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
                                $user_id = get_current_user_id();
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

function uwp_account_get_available_tabs() {

    $tabs = array();

    $tabs['account']  = array(
        'title' => __( 'Edit Account', 'userswp' ),
        'icon' => 'fa fa-user',
    );

    $extra_where = "AND is_public='2'";
    $fields = get_account_form_fields($extra_where);

    if (is_array($fields) && count($fields) > 0) {
        $tabs['privacy']  = array(
            'title' => __( 'Privacy', 'userswp' ),
            'icon' => 'fa fa-lock',
        );
    }

    return apply_filters( 'uwp_account_available_tabs', $tabs );
}

add_action('init', 'uwp_privacy_submit_handler');
function uwp_privacy_submit_handler() {
    if (isset($_POST['uwp_privacy_submit'])) {
        if( ! isset( $_POST['uwp_privacy_nonce'] ) || ! wp_verify_nonce( $_POST['uwp_privacy_nonce'], 'uwp-privacy-nonce' ) ) {
            return;
        }

        $extra_where = "AND is_public='2'";
        $fields = get_account_form_fields($extra_where);
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

    }
}

add_action('admin_head', 'uwp_admin_only_css');
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

function uwp_form_extras_field_order($field_ids = array(), $form_type = 'register')
{
    global $wpdb;
    $extras_table_name = $wpdb->prefix . 'uwp_form_extras';

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

function uwp_ucwords($string, $charset='UTF-8') {
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($string, MB_CASE_TITLE, $charset);
    } else {
        return ucwords($string);
    }
}