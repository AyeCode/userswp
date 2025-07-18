<?php
/**
 * Profile header template (default)
 *
 * @ver 0.0.1
 */
global $userswp, $uwp_in_user_loop;
$css_class    = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$allow_change = $args['allow_change'];
$avatar_url   = $args['avatar_url'];
$user_id      = $args['user_id'];
$link         = $args['link'];
$size         = isset($args['size']) ? $args['size'] : 50;

if($user_id){
    $user = get_userdata($user_id);
} else {
	$user = uwp_get_displayed_user();
}

if ( ! $user ) {
	return;
}

if(!$uwp_in_user_loop){ ?><div class="card shadow-0 border-0 mw-100 bg-transparent"><?php } ?>

<div class="card-body  <?php if(!$uwp_in_user_loop){ ?>mt-xl-0 pt-0<?php }else{?>text-center pb-0<?php }?>">

	<div class="row justify-content-center">

		<div class="col <?php if($uwp_in_user_loop){?>col-5<?php }?> text-center tofront ">
			<?php if ($uwp_in_user_loop || 1==$link) { echo '<a href="'.esc_url(get_author_posts_url($user->ID)).'" title="'.esc_attr( $user->display_name ).'">';} ?>
			<?php $avatar_args = array('class' => 'rounded-circle shadow border border-white border-width-4 p-0 mw-100', "width" => $size);
				echo get_avatar($user_id,$size,esc_url( $avatar_url ),'User avatar',$avatar_args); ?>
			<?php if ($uwp_in_user_loop || 1==$link) {echo '</a>';} ?>
			<?php do_action('uwp_after_user_avatar',$user->ID); ?>   
			<?php if (!$uwp_in_user_loop && is_user_logged_in() && ( get_current_user_id() == $user->ID ) && $allow_change ) { ?>
				<div class="card-img-overlay d-flex p-0">
					<?php
					echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'type'  =>  'a',
						'href'       => '#',
						'class'      => 'btn btn-sm uwp-banner-change-icon btn-outline-secondary btn-circle border-0 align-self-end mx-auto',
						'icon'       => 'fas fa-camera fa-fw',
						'onclick'    => "uwp_profile_image_change('avatar');return false;",
						'extra_attributes'  => array('data-toggle'=>'tooltip', 'data-original-title'=>esc_html__( 'Update Profile Image', 'userswp' ))
					));
					?>
				</div>
			<?php } ?>
		</div>

	</div>

</div>

<?php if(!$uwp_in_user_loop){ ?></div><?php } ?>
