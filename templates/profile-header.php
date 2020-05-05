<?php
/**
 * Profile header template (default)
 *
 * @ver 0.0.1
 */
$css_class    = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$hide_cover   = $args['hide_cover'];
$allow_change = $args['allow_change'];
$hide_avatar  = $args['hide_avatar'];
$avatar_url   = $args['avatar_url'];
$banner_url   = $args['banner_url'];
$user_id      = $args['user_id'];

do_action( 'uwp_template_before', 'profile-header' );
$user = uwp_get_displayed_user();
if(!$user){
	return;
}

add_filter( 'upload_dir', 'uwp_handle_multisite_profile_image', 10, 1 );
$uploads = wp_upload_dir();
remove_filter( 'upload_dir', 'uwp_handle_multisite_profile_image' );
$upload_url = $uploads['baseurl'];
$class = "";

if($hide_cover) {
	$class = "uwp-avatar-only";
}

?>
<div class="uwp-profile-header <?php echo $class; ?> clearfix">
	<?php if(!$hide_cover) {
		$banner = uwp_get_usermeta($user->ID, 'banner_thumb', '');
		if (empty($banner)) {
			$banner = uwp_get_default_banner_uri();
		} else {
			$banner = $upload_url.$banner;
		}
		?>
		<div class="uwp-profile-header-img clearfix">
			<?php
			if (!is_uwp_profile_page()) {
				echo '<a href="'.apply_filters('uwp_profile_link', get_author_posts_url($user->ID), $user->ID).'" title="'.$user->display_name.'">';
			}
			?>
			<img src="<?php echo $banner; ?>" alt="" class="uwp-profile-header-img-src" data-recalc-dims="0" />
			<?php
			if (!is_uwp_profile_page()) {
				echo '</a>';
			}
			?>
			<?php if (is_user_logged_in() && is_uwp_profile_page() && $allow_change && (get_current_user_id() == $user->ID)) { ?>
				<div class="uwp-banner-change-icon">
					<i class="fas fa-camera" aria-hidden="true"></i>
					<div data-type="banner" class="uwp-profile-banner-change uwp-profile-modal-form-trigger">
                    <span class="uwp-profile-banner-change-inner">
                        <?php echo __( 'Update Cover Photo', 'userswp' ); ?>
                    </span>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php }

	// avatar of user
	?>
	<div class="uwp-profile-avatar clearfix">
		<?php
		if(!$hide_avatar) {
			if (!is_uwp_profile_page()) {
				echo '<a href="' . apply_filters('uwp_profile_link', get_author_posts_url($user->ID), $user->ID) . '" title="' . $user->display_name . '">';
			}
			$avatar = uwp_get_usermeta($user->ID, 'avatar_thumb', '');
			if (empty($avatar)) {
				$avatar = get_avatar($user->user_email, 150);
			} else {
				// check the image is not a full url before adding the local upload url
				if (strpos($avatar, 'http:') === false && strpos($avatar, 'https:') === false) {
					$avatar = $upload_url . $avatar;
				}
				$avatar = '<img src="' . $avatar . '" class="avatar avatar-150 photo" width="150" height="150">';
			}
			?>
			<div class="uwp-profile-avatar-inner">
				<?php echo $avatar; ?>
				<?php if (is_user_logged_in() && (get_current_user_id() == $user->ID) && is_uwp_profile_page() && $allow_change) { ?>
					<div class="uwp-profile-avatar-change">
						<div class="uwp-profile-avatar-change-inner">
							<i class="fas fa-camera" aria-hidden="true"></i>
							<a id="uwp-profile-picture-change" data-type="avatar"
							   class="uwp-profile-modal-form-trigger"
							   href="#"><?php echo __('Update', 'userswp'); ?></a>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php
			if (!is_uwp_profile_page()) {
				echo '</a>';
			}
		}
		?>
	</div>
</div>
<?php do_action( 'uwp_template_after', 'profile-header' ); ?>