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

function uwp_geodir_get_reviews_by_user_id($post_type = 'gd_place', $user_id, $count_only = false, $offset = 0, $limit = 20)
{
    global $wpdb;

    if ($count_only) {
        $results = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(overall_rating) FROM " . GEODIR_REVIEW_TABLE . " WHERE user_id = %d AND post_type = %s AND status=1 AND overall_rating>0",
                array($user_id, $post_type)
            )
        );
    } else {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . GEODIR_REVIEW_TABLE . " WHERE user_id = %d AND post_type = %s AND status=1 AND overall_rating>0 LIMIT %d OFFSET %d",
                array($user_id, $post_type, $limit, $offset )
            )
        );
    }


    if (!empty($results))
        return $results;
    else
        return false;
}

function uwp_geodir_count_favorite( $post_type, $user_id = 0 ) {
    global $wpdb;

    $post_status = is_super_admin() ? " OR " . $wpdb->posts . ".post_status = 'private'" : '';
    if ( $user_id && $user_id == get_current_user_id() ) {
        $post_status .= " OR " . $wpdb->posts . ".post_status = 'draft' OR " . $wpdb->posts . ".post_status = 'private'";
    }

    $user_fav_posts = get_user_meta( (int)$user_id, 'gd_user_favourite_post', true );
    $user_fav_posts = !empty( $user_fav_posts ) ? implode( "','", $user_fav_posts ) : "-1";

    $count = (int)$wpdb->get_var( "SELECT count( ID ) FROM ".$wpdb->posts." WHERE " . $wpdb->posts . ".ID IN ('" . $user_fav_posts . "') AND post_type='" . $post_type . "' AND ( post_status = 'publish' " . $post_status . " )" );

    return apply_filters( 'uwp_geodir_count_favorite', $count, $user_id );
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

    $usermeta = get_user_meta( $user_id, 'uwp_usermeta' );

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

    $usermeta = get_user_meta( $user_id, 'uwp_usermeta' );

    if( !is_array( $usermeta ) ) {
        $usermeta = array();
    }

    $value = ! empty( $usermeta[ $key ] ) ? $usermeta[ $key ] : $default;
    $value = apply_filters( 'uwp_get_usermeta', $value, $user_id, $key, $default );
    return apply_filters( 'uwp_get_usermeta_' . $key, $value, $user_id, $key, $default );
}