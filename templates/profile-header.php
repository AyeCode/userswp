<?php
/**
 * Profile header template (default)
 *
 * @ver 0.0.1
 */
global $uwp_in_user_loop;
$css_class    = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$hide_cover   = isset( $args['hide_cover'] ) ? $args['hide_cover'] : '';
$allow_change = isset( $args['allow_change'] ) ? $args['allow_change'] : '';
$hide_avatar  = isset( $args['hide_avatar'] ) ? $args['hide_avatar'] : '';
$avatar_url   = isset( $args['avatar_url'] ) ? $args['avatar_url'] : '';
$banner_url   = isset( $args['banner_url'] ) ? $args['banner_url'] : '';
$user_id      = isset( $args['user_id'] ) ? $args['user_id'] : '';

do_action( 'uwp_template_before', 'profile-header' );
if($user_id){
	$user = get_userdata($user_id);
} else {
	$user = uwp_get_displayed_user();
}

if(!$user){
	return;
}

$class = "";

if($hide_cover) {
	$class = "uwp-avatar-only";
}

?>
<div class="uwp-profile-header <?php echo esc_attr( $class ); ?> clearfix">
	<?php if(!$hide_cover) {
		?>
		<div class="uwp-profile-header-img clearfix">
			<?php
			if ($uwp_in_user_loop) {
				echo '<a href="'.esc_url(uwp_build_profile_tab_url($user->ID)).'" title="'.esc_attr( $user->display_name ).'">';
			}
			?>
			<img src="<?php echo esc_url( $banner_url ); ?>" alt="<?php esc_html_e( "User banner image", "userswp" ); ?>" class="uwp-profile-header-img-src" />
			<?php
			if ($uwp_in_user_loop) {
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
	<?php } ?>

	<div class="uwp-profile-avatar clearfix">
		<?php
		if(!$hide_avatar) {
			if ($uwp_in_user_loop) {
				echo '<a href="' . esc_url (uwp_build_profile_tab_url($user->ID) ) . '" title="' . esc_attr( $user->display_name ) . '">';
			}
			?>
			<div class="uwp-profile-avatar-inner">
                <img class="avatar avatar-150 photo" src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php esc_attr_e("User avatar","userswp");?>" width="150" height="150">
				<?php if (!$uwp_in_user_loop && is_user_logged_in() && (get_current_user_id() == $user->ID) && $allow_change) { ?>
					<div class="uwp-profile-avatar-change">
						<div class="uwp-profile-avatar-change-inner">
							<i class="fas fa-camera" aria-hidden="true"></i>
							<a id="uwp-profile-picture-change" data-type="avatar"
							   class="uwp-profile-modal-form-trigger"
							   href="#"><?php esc_html_e('Update', 'userswp'); ?></a>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php
			if ($uwp_in_user_loop) {
				echo '</a>';
			}
		}
		?>
	</div>
</div>
<?php do_action( 'uwp_template_after', 'profile-header' ); ?>