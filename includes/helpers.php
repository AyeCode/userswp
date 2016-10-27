<?php
function uwp_get_page_link($page) {
    global $uwp_options;

    $link = "";

    switch ($page) {
        case 'register':
            $page_id = isset($uwp_options['register_page']) ? esc_attr( $uwp_options['register_page']) : false;
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'login':
            $page_id = isset($uwp_options['login_page']) ? esc_attr( $uwp_options['login_page']) : false;
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'forgot':
            $page_id = isset($uwp_options['forgot_pass_page']) ? esc_attr( $uwp_options['forgot_pass_page']) : false;
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'account':
            $page_id = isset($uwp_options['account_page']) ? esc_attr( $uwp_options['account_page']) : false;
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'profile':
            $page_id = isset($uwp_options['user_profile_page']) ? esc_attr( $uwp_options['user_profile_page']) : false;
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'users':
            $page_id = isset($uwp_options['users_list_page']) ? esc_attr( $uwp_options['users_list_page']) : false;
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
        $chosen = "class='uwp-chosen'".($args['multiple'] ? "[]' multiple='multiple' style='height:auto'" : "'");
    } else {
        $chosen = '';
    }

    $html = '<select id="uwp_settings[' . $args['id'] . ']" name="uwp_settings[' . $args['id'] . ']" ' . $chosen . 'data-placeholder="' . $placeholder . '" />';

    foreach ( $args['options'] as $option => $name ) {
        $selected = selected( $option, $value, false );
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