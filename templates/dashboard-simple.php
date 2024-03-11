<?php
global $current_user;

echo '<div class="uwp-login-widget user-loggedin">';

echo '<p>'.esc_html__( 'Logged in as ', 'userswp' );

echo '<a href="'. esc_url( uwp_build_profile_tab_url($current_user->ID) ).'">' . get_avatar( $current_user->ID, 35 ). '<strong>'. apply_filters('uwp_profile_display_name', esc_attr( $current_user->display_name ) ).'</strong></a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo '<span>';

echo '<a href="'.esc_url( wp_logout_url() ).'" class="uwp-logout">'.esc_html__("Logout","userswp").'</a>';

echo '</span>';

echo '</p>';

echo '</div>';