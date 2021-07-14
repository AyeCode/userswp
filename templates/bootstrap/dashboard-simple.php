<?php
global $current_user;

echo '<div class="uwp-login-widget user-loggedin">';

echo '<p>'.__( 'Logged in as ', 'userswp' );

$content = get_avatar( $current_user->ID, 35 ). '<strong>'. apply_filters('uwp_profile_display_name', esc_attr( $current_user->display_name )).'</strong>';

echo aui()->button(array(
	'type'  =>  'a',
	'href'       => uwp_build_profile_tab_url($current_user->ID),
	'class'      => '',
	'content'    => $content,
));

echo '<span>';

echo aui()->button(array(
	'type'  =>  'a',
	'href'       => wp_logout_url(),
	'class'      => 'btn btn-sm btn-outline-primary',
	'title'      => __("Logout","userswp"),
	'content'    => __("Logout","userswp"),
));

echo '</span>';

echo '</p>';

echo '</div>';