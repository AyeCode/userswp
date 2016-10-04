<?php
function uwp_get_page_link($page) {

    $link = "";

    switch ($page) {
        case 'register':
            $page_id = esc_attr( get_option('uwp_register_page', false));
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'login':
            $page_id = esc_attr( get_option('uwp_login_page', false));
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'forgot':
            $page_id = esc_attr( get_option('uwp_forgot_pass_page', false));
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'account':
            $page_id = esc_attr( get_option('uwp_account_page', false));
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'profile':
            $page_id = esc_attr( get_option('uwp_user_profile_page', false));
            if ($page_id) {
                $link = get_permalink($page_id);
            }
            break;

        case 'users':
            $page_id = esc_attr( get_option('uwp_users_list_page', false));
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