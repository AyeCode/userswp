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

if(!$uwp_in_user_loop){ ?><div class="card shadow-0 border-0 mw-100"><?php }

	if ( $uwp_in_user_loop || 1==$link ) {
		echo '<a href="' . esc_url( get_author_posts_url( $user->ID ) ) . '" title="' . esc_attr( $user->display_name ) . '">';
	} ?>
	<img class="card-img-top m-0 p-0 uwp-banner-image" src="<?php echo esc_url( $banner_url ); ?>"
	     alt="<?php _e( "User banner image", "userswp" ); ?>">
	<?php if ( $uwp_in_user_loop || 1==$link ) {
		echo '</a>';
	} ?>

	<?php if ( ! $uwp_in_user_loop && is_user_logged_in() && $allow_change && ( get_current_user_id() == $user->ID ) ) { ?>
		<div class="card-img-overlay p-1 bg-shadow-bottom-dd">
            <?php
            echo aui()->button(array(
	            'type'  =>  'a',
	            'href'       => '#',
	            'class'      => 'btn btn-sm uwp-banner-change-icon btn-outline-secondary uwp-profile-modal-form-trigger border-0',
	            'icon'       => 'fas fa-camera fa-fw',
	            'onclick'    => "uwp_profile_image_change('banner');return false;",
	            'extra_attributes'  => array('data-toggle'=>'tooltip', 'data-placement'=>'right', 'data-original-title'=>__( 'Update Cover Image', 'userswp' ))
            ));
            ?>
		</div>
	<?php } ?>

<?php if(!$uwp_in_user_loop){ ?></div><?php } ?>
