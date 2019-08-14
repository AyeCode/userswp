<?php
/**
 * Profile header template (default)
 *
 * @ver 0.0.1
 */
global $uwp_widget_args,$userswp;
$css_class = ! empty( $uwp_widget_args['css_class'] ) ? esc_attr( $uwp_widget_args['css_class'] ) : 'border-0';
$hide_cover = $uwp_widget_args['hide_cover'];
$allow_change = $uwp_widget_args['allow_change'];
$hide_avatar = $uwp_widget_args['hide_avatar'];



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

$banner = '';
if(!$hide_cover) {
	$banner = uwp_get_usermeta($user->ID, 'banner_thumb', '');
	if (empty($banner)) {
		$banner = uwp_get_option('profile_default_banner', '');
		if(empty($banner)){
			$banner = uwp_get_default_banner_uri();
		} else {
			$banner = wp_get_attachment_url($banner);
		}
	} else {
		$banner = $upload_url.$banner;
	}


	$avatar = uwp_get_usermeta($user->ID, 'avatar_thumb', '');
	if (empty($avatar)) {
		$avatar = get_avatar_url($user->user_email, 150);
	} else {
		// check the image is not a full url before adding the local upload url
		if (strpos($avatar, 'http:') === false && strpos($avatar, 'https:') === false) {
			$avatar = $upload_url . $avatar;
		}
		//$avatar = '<img src="' . $avatar . '" class="avatar avatar-150 photo" width="150" height="150">';
	}
?>
	<div class="row">
	<div class="col-md-12">
		<div class="card shadow-0 border-0">
			<img class="card-img-top" src="<?php echo esc_url($banner);?>" alt="Card image cap">

	<div class="card-body">

			<div class="row">
				<div class="col-sm text-center">
					<img class="rounded-circle shadow mt-neg5 " src="<?php echo esc_url($avatar);?>" >

				</div>
				<div class="col-8">
					<ul class="list-group border-0 m-0 p-0">
						<li class="list-group-item border-0 m-0 p-0 uwp-profile-title"><h2 class="card-title font-weight-bold"><?php echo esc_attr($user->display_name);?></h2></li>
						<li class="list-group-item border-0 m-0 p-0 uwp-profile-post-counts">
							<?php
							// User post counts
							$user_post_counts = $userswp->profile->get_user_post_counts($user->ID);
							if(!empty($user_post_counts)){
								echo '<ul class="list-inline text-muted">';
								$separator = '';
								foreach ($user_post_counts as $cpt => $post_type){
									$post_count_text = $post_type['count'] > 1 ? esc_attr($post_type['count'] . " " . $post_type['name']) : esc_attr($post_type['count'] . " " . $post_type['singular_name']);
									echo '<li class="list-inline-item" >'.$separator . $post_count_text.'</li>';
									$separator = '<i class="fas fa-circle text-small" style="font-size: 5px;vertical-align: middle;"></i> ';
								}
								echo '</ul>';
							}
							?>
						</li>
						<li class="list-group-item border-0 m-0 p-0 uwp-profile-top-output-location ">
							<?php
//							global $uwp_user;
//
//							$uwp_user = uwp_get_displayed_user();
//							print_r($uwp_user);
							$user_meta = uwp_get_usermeta_row($user->ID);
//							print_r($user_meta);echo '###';
							// User post counts
							$user_post_counts = $userswp->profile->get_user_post_counts($user->ID);
							if(!empty($user_post_counts)){
								echo '<ul class="list-inline text-muted">';
								$separator = '';
								foreach ($user_post_counts as $cpt => $post_type){
									$post_count_text = $post_type['count'] > 1 ? esc_attr($post_type['count'] . " " . $post_type['name']) : esc_attr($post_type['count'] . " " . $post_type['singular_name']);
									echo '<li class="list-inline-item" >'.$separator . $post_count_text.'</li>';
									$separator = '<i class="fas fa-circle text-small" style="font-size: 5px;vertical-align: middle;"></i> ';
								}
								echo '</ul>';
							}
							?>
						</li>
					</ul>
				</div>
				<div class="col-sm">
					<?php
					echo do_shortcode("[uwp_profile_social]");
					?>
				</div>
			</div>


	</div>



	<div class="uwp-profile-header <?php echo $class; ?> clearfix">

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


	</div>
	</div>
	</div>
<?php do_action( 'uwp_template_after', 'profile-header' ); ?>