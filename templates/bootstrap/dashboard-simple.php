<?php
global $current_user;

echo '<div class="uwp-login-widget user-loggedin">';

echo '<p>'.esc_html__( 'Logged in as ', 'userswp' );

$content = get_avatar( $current_user->ID, 35 ). '<strong>'. apply_filters('uwp_profile_display_name', esc_attr( $current_user->display_name )).'</strong>';

echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'type'  =>  'a',
	'href'       => esc_url( uwp_build_profile_tab_url($current_user->ID) ),
	'class'      => '',
	'content'    => $content, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
));

echo '<span>';

echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'type'  =>  'a',
	'href'       => esc_url( wp_logout_url() ),
	'class'      => 'btn btn-sm btn-outline-primary',
	'title'      => esc_html__("Logout","userswp"),
	'content'    => esc_html__("Logout","userswp"),
));

echo '</span>';

echo '</p>';

echo '</div>';