<?php

global $current_user;

$template = new UsersWP_Templates();

$logout_url = $template->uwp_logout_url();

echo '<div class="uwp-login-widget user-loggedin">';

echo '<p>'.__( 'Logged in as ', 'userswp' );

echo '<a href="'. apply_filters('uwp_profile_link', get_author_posts_url($current_user->ID), $current_user->ID).'">' . get_avatar( $current_user->ID, 35 ). '<strong>'. apply_filters('uwp_profile_display_name', $current_user->display_name).'</strong></a>';

echo '<span>';

echo '<a href="'.wp_logout_url().'" class="btn btn-sm btn-outline-primary">'.__("Logout","userswp").'</a>';

echo '</span>';

echo '</p>';

echo '</div>';