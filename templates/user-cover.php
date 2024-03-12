<?php
/**
 * Profile header template (default)
 *
 * @ver 0.0.1
 */
global $userswp, $uwp_in_user_loop;
$css_class    = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$allow_change = $args['allow_change'];
$banner_url   = $args['banner_url'];
$user_id      = $args['user_id'];
$link         = $args['link'];

if($user_id){
    $user = get_userdata($user_id);
} else {
	$user = uwp_get_displayed_user();
}

if ( ! $user ) {
	return;
}
?>

<div class="uwp-profile-header-img clearfix">
	<?php
	if ($uwp_in_user_loop || 1==$link) {
		echo '<a href="'.esc_url( uwp_build_profile_tab_url($user->ID) ).'" title="'.esc_attr( $user->display_name ).'">';
	}
	?>
    <img src="<?php echo esc_url( $banner_url ); ?>" alt="<?php esc_attr_e( "User banner image", "userswp" ); ?>" class="uwp-profile-header-img-src" />
	<?php
	if ($uwp_in_user_loop || 1==$link) {
		echo '</a>';
	}
	?>
	<?php if (! $uwp_in_user_loop && is_user_logged_in() && $allow_change && (get_current_user_id() == $user->ID)) { ?>
        <div class="uwp-banner-change-icon">
            <i class="fas fa-camera" aria-hidden="true"></i>
            <div data-type="banner" class="uwp-profile-banner-change uwp-profile-modal-form-trigger">
                    <span class="uwp-profile-banner-change-inner">
                        <?php esc_html_e( 'Update Cover Photo', 'userswp' ); ?>
                    </span>
            </div>
        </div>
	<?php } ?>
</div>