<?php
function uwp_get_page_link($page) {

    $link = "";

    switch ($page) {
        case 'register':
            $page_id = uwp_get_option('register_page', false);
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'login':
            $page_id = uwp_get_option('login_page', false);
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'forgot':
            $page_id = uwp_get_option('forgot_pass_page', false);
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'account':
            $page_id = uwp_get_option('account_page', false);
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'profile':
            $page_id = uwp_get_option('user_profile_page', false);
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'users':
            $page_id = uwp_get_option('users_list_page', false);
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;
    }

    return $link;
}

function uwp_get_users() {

    $uwp_users = array();

    $users = get_users( array( 'number' => '20' ) );
    foreach ( $users as $user ) {
        $uwp_users[] = array(
            'id' => $user->ID,
            'name' => $user->display_name,
            'avatar' => get_avatar( $user->user_email, 128 ),
            'link'  => get_author_posts_url($user->ID),
            'facebook' => '',
            'twitter'  => '',
            'description'  => ''
        );
    }

    return $uwp_users;
}

function uwp_post_count($user_id, $post_type) {
    global $wpdb;

    $post_status = "";
    if ($user_id == get_current_user_id()) {
        $post_status = ' OR post_status = "draft" OR post_status = "private"';
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
        __( 'The callback function used for the %s setting is missing.', 'uwp' ),
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
        $chosen = ($args['multiple'] ? '[]" multiple="multiple" class="uwp-chosen" style="height:auto"' : "'");
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

function uwp_update_usermeta( $user_id = false, $key, $value ) {

    if (!$user_id || !$key || !$value) {
        return false;
    }

    $usermeta = get_user_meta( $user_id, 'uwp_usermeta', true );

    if( !is_array( $usermeta ) ) {
        $usermeta = array();
    }

    $usermeta[ $key ] = $value;

    $usermeta = apply_filters( 'uwp_update_usermeta', $usermeta, $user_id, $key, $value );
    $usermeta =  apply_filters( 'uwp_update_usermeta_' . $key, $usermeta, $user_id, $key, $value );

    update_user_meta($user_id, 'uwp_usermeta', $usermeta);

    return true;
}


function uwp_get_usermeta( $user_id = false, $key = '', $default = false ) {

    if (!$user_id) {
        return $default;
    }

    $usermeta = get_user_meta( $user_id, 'uwp_usermeta', true );

    if( !is_array( $usermeta ) ) {
        $usermeta = array();
    }

    $value = ! empty( $usermeta[ $key ] ) ? $usermeta[ $key ] : $default;
    $value = apply_filters( 'uwp_get_usermeta', $value, $user_id, $key, $default );
    return apply_filters( 'uwp_get_usermeta_' . $key, $value, $user_id, $key, $default );
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
                            $optgroup_label = __($optgroup_label, 'uwp');
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
                    $label = __($label, 'uwp');
                }
                $label = ucfirst($label);
                $value = trim($input_str[1]);
            } else {
                if ($translated && $input_str != '') {
                    $input_str = __($input_str, 'uwp');
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

add_filter('uwp_allowed_image_types', 'uwp_allowed_image_types', 10, 2);
function uwp_allowed_image_types( $image_mimes, $field_key ) {

    if ($field_key != 'uwp_avatar') {
        return $image_mimes;
    }

    $allowed_types = array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif'          => 'image/gif',
        'png'          => 'image/png'
    );

    $image_mimes = array_intersect_key( $image_mimes, $allowed_types );

    return $image_mimes;
}