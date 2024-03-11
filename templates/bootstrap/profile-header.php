<?php
/**
 * Profile header template (default)
 *
 * @ver 0.0.1
 */
global $userswp, $uwp_in_user_loop;
$css_class    = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$hide_cover   = $args['hide_cover'];
$allow_change = $args['allow_change'];
$hide_avatar  = $args['hide_avatar'];
$avatar_url   = $args['avatar_url'];
$banner_url   = $args['banner_url'];
$user_id      = $args['user_id'];

if ( $user_id ) {
	$user = get_userdata( $user_id );
} else {
	$user = uwp_get_displayed_user();
}

if ( ! $user ) {
	return;
}

if ( ! $uwp_in_user_loop ){ ?>
<div class="card shadow-0 border-0 mw-100"><?php }

	if ( ! $hide_cover ) {
		if ( $uwp_in_user_loop ) {
			echo '<a href="' . esc_url( uwp_build_profile_tab_url( $user->ID ) ) . '" title="' .  esc_attr( $user->display_name ). '">';
		} ?>
        <img class="card-img-top m-0 p-0 uwp-banner-image" src="<?php echo esc_url( $banner_url ); ?>"
             alt="<?php esc_attr_e( "User banner image", "userswp" ); ?>">
		<?php if ( $uwp_in_user_loop ) {
			echo '</a>';
		} ?>

		<?php if ( ! $uwp_in_user_loop && is_user_logged_in() && $allow_change && ( get_current_user_id() == $user->ID ) ) { ?>
            <div class="card-img-overlay p-1 bg-shadow-bottom-dd">
				<?php
				echo aui()->button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'type'             => 'a',
					'href'             => '#',
					'class'            => 'btn btn-sm uwp-banner-change-icon btn-outline-secondary border-0',
					'icon'             => 'fas fa-camera fa-fw',
					'title'            => '',
					'onclick'          => "uwp_profile_image_change('banner');return false;",
					'extra_attributes' => array( 'data-toggle' => 'tooltip', 'data-original-title' => esc_html__( 'Update Profile Image', 'userswp' )
					)
				) );
				?>
            </div>
		<?php }

	} ?>

    <div class="card-body  <?php if ( ! $uwp_in_user_loop ) { ?>mt-xl-0 pt-0<?php } else { ?>text-center pb-0<?php } ?>">

        <div class="row justify-content-center">

			<?php if ( ! $hide_avatar ) { ?>
                <div class="col <?php if ( $uwp_in_user_loop ) { ?>col-5<?php } ?> text-center tofront ">
					<?php if ( $uwp_in_user_loop ) {
						echo '<a href="' . esc_url( uwp_build_profile_tab_url( $user->ID ) ) . '" title="' .  esc_attr( $user->display_name ) . '">';
					} ?>
                    <img class="rounded-circle shadow border border-white border-width-4 p-0 mw-100 <?php if ( ! $hide_cover ) {
						echo "mt-neg5";
					} ?>"
                         src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php esc_html_e( "User avatar", "userswp" ); ?>">
					<?php if ( $uwp_in_user_loop ) {
						echo '</a>';
					} ?>

					<?php if ( ! $uwp_in_user_loop && is_user_logged_in() && ( get_current_user_id() == $user->ID ) && $allow_change ) { ?>
                        <div class="card-img-overlay p-0">
							<?php
							echo aui()->button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'type'             => 'a',
								'href'             => '#',
								'class'            => 'btn btn-sm uwp-banner-change-icon btn-outline-secondary btn-circle border-0 align-self-end mx-auto',
								'icon'             => 'fas fa-camera fa-fw',
								'title'            => '',
								'onclick'          => "uwp_profile_image_change('avatar');return false;",
								'extra_attributes' => array( 'data-toggle'         => 'tooltip', 'data-original-title' => esc_html__( 'Update Profile Image', 'userswp' )
								)
							) );
							?>
                        </div>
					<?php } ?>
                </div>
			<?php } ?>

            <div class="col-12 <?php if ( ! $uwp_in_user_loop ) {
				echo $hide_avatar ? "col-xl-8" : "col-xl-6"; ?> text-xl-left pt-xl-1<?php } ?> text-center ">
                <ul class="list-group border-0 m-0 p-0 bg-transparent">
                    <li class="list-group-item border-0 m-0 p-0 bg-transparent uwp-profile-title">
						<?php
						// User title.
						if ( $uwp_in_user_loop ) {
							$userswp->profile->get_profile_title( $user, $tag = 'h4', 'h4 card-title text-dark  font-weight-bold m-0 p-0', 'text-muted', true );
						} else {
							$userswp->profile->get_profile_title( $user, $tag = 'h2', 'h2 card-title text-dark  font-weight-bold m-0 p-0', '', false );
						}
						?>
                    </li>
                </ul>
				<?php echo do_shortcode( "[uwp_user_post_counts disable_greedy=".$args['disable_greedy']."]" ); ?>
            </div>
            <div class="col-12 <?php if ( ! $uwp_in_user_loop ) { ?>col-xl-4 text-xl-right <?php } ?> text-center pt-2">
				<?php
				echo do_shortcode( "[uwp_button_group]" );
				?>
            </div>
        </div>

		<?php if ( ! $uwp_in_user_loop && is_uwp_profile_page() ) { ?>

            <div class="row justify-content-center">
                <div class="col">
                </div>
                <div class="col-12 <?php echo $hide_avatar ? "col-xl-8" : "col-xl-6"; ?> text-xl-left pt-xl-1">
					<?php
					echo do_shortcode( "[uwp_user_actions]" );
					?>
                </div>
                <div class="col-12 col-xl-4 text-xl-right text-right">
					<?php
					echo do_shortcode( "[uwp_profile_actions]" );
					?>
                </div>
            </div>

		<?php } ?>

    </div>

	<?php if ( ! $uwp_in_user_loop ){ ?></div><?php } ?>
