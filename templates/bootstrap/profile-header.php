<?php
/**
 * Profile header template (default)
 *
 * @ver 0.0.1
 */
global $uwp_widget_args, $userswp,$uwp_in_user_loop;
$css_class    = ! empty( $uwp_widget_args['css_class'] ) ? esc_attr( $uwp_widget_args['css_class'] ) : 'border-0';
$hide_cover   = $uwp_widget_args['hide_cover'];
$allow_change = $uwp_widget_args['allow_change'];
$hide_avatar  = $uwp_widget_args['hide_avatar'];
$avatar_url   = $uwp_widget_args['avatar_url'];
$banner_url   = $uwp_widget_args['banner_url'];


$user = uwp_get_displayed_user();
if ( ! $user ) {
	return;
}


if ( ! $hide_cover ) {

	?>

	<?php if(!$uwp_in_user_loop){ ?><div class="card shadow-0 border-0 mw-100"><?php }?>

	<?php if ($uwp_in_user_loop) {echo '<a href="'.esc_url_raw(get_author_posts_url($user->ID)).'" title="'.$user->display_name.'">';} ?>
	<img class="card-img-top m-0 p-0" src="<?php echo esc_url( $banner_url ); ?>" alt="Card image cap">
	<?php if ($uwp_in_user_loop) {echo '</a>';} ?>

	<?php if (!$uwp_in_user_loop && is_user_logged_in() && is_uwp_profile_page() && $allow_change && ( get_current_user_id() == $user->ID ) ) { ?>
		<div class="card-img-overlay p-1 bg-shadow-bottom-dd">
			<a onclick="uwp_profile_image_change('banner');return false;" href="#"
			   class="btn btn-sm uwp-banner-change-icon btn-outline-secondary uwp-profile-modal-form-trigger border-0"
			   data-toggle="tooltip" data-placement="right" title=""
			   data-original-title="<?php _e( 'Update Cover Image', 'userswp' ); ?>">
				<i class="fas fa-camera fa-fw"></i>
			</a>
		</div>
	<?php } ?>


	<div class="card-body  <?php if(!$uwp_in_user_loop){ ?>mt-xl-0 pt-0<?php }else{?>text-center pb-0<?php }?>">

		<div class="row justify-content-center">
			<div class="col <?php if($uwp_in_user_loop){?>col-5<?php }?> text-center tofront  d-table  ">
				<?php if ($uwp_in_user_loop) {echo '<a href="'.esc_url_raw(get_author_posts_url($user->ID)).'" title="'.$user->display_name.'">';} ?>
				<img class="rounded-circle shadow border border-white border-width-4 p-0 mt-neg5"
				     src="<?php echo esc_url( $avatar_url ); ?>">
				<?php if ($uwp_in_user_loop) {echo '</a>';} ?>

				<?php if (!$uwp_in_user_loop && is_user_logged_in() && ( get_current_user_id() == $user->ID ) && is_uwp_profile_page() && $allow_change ) { ?>
					<div class="card-img-overlay d-flex p-0">
						<a onclick="uwp_profile_image_change('avatar');return false;" href="#"
						   class="btn btn-sm uwp-banner-change-icon btn-outline-secondary btn-circle border-0 align-self-end mx-auto "
						   data-toggle="tooltip" title=""
						   data-original-title="<?php _e( 'Update Profile Image', 'userswp' ); ?>">
							<i class="fas fa-camera fa-fw"></i>
						</a>
					</div>
				<?php } ?>

			</div>
			<div class="col-12 <?php if(!$uwp_in_user_loop){ ?>col-xl-6 text-xl-left pt-xl-1<?php }?> text-center ">
				<ul class="list-group border-0 m-0 p-0 bg-transparent">
					<li class="list-group-item border-0 m-0 p-0 bg-transparent uwp-profile-title">
						<?php
						// User title.
						if($uwp_in_user_loop){
							$userswp->profile->get_profile_title($user, $tag = 'h4','h4 card-title text-dark  font-weight-bold m-0 p-0','text-muted',true);
						}else{
							$userswp->profile->get_profile_title($user, $tag = 'h2','h2 card-title text-dark  font-weight-bold m-0 p-0','',false);
						}
						?>
					</li>
					<li class="list-group-item border-0 m-0 p-0  bg-transparent uwp-profile-post-counts">
						<?php
						// User post counts
						$user_post_counts = $userswp->profile->get_user_post_counts( $user->ID );
						if ( ! empty( $user_post_counts ) ) {
							foreach ( $user_post_counts as $cpt => $post_type ) {
								$post_count_text = $post_type['count'] > 1 ? esc_attr( $post_type['name'] ) . '<span class="badge badge-dark ml-1">' . absint( $post_type['count'] ) . '</span>' : esc_attr( $post_type['singular_name'] ) . '<span class="badge badge-dark ml-1">' . absint( $post_type['count'] ) . '</span>';
								echo '<span class="badge badge-white text-muted pl-0">' . $post_count_text . '</span>' . " \n"; // needs line break for
							}
						}
						?>
					</li>
				</ul>
			</div>
			<div class="col-12 <?php if(!$uwp_in_user_loop){ ?>col-xl-4 text-xl-right <?php }?> text-center pt-2">
				<?php
				echo do_shortcode( "[uwp_button_group]" );

				if(!$uwp_in_user_loop){echo do_shortcode("[uwp_user_actions]");}
				?>
			</div>
		</div>


	</div>

	<?php if(!$uwp_in_user_loop){ ?></div><?php } ?>
<?php } ?>