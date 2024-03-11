<?php
$user_id = get_current_user_id();
$user_info = get_userdata($user_id);
$display_name = esc_attr( $user_info->data->display_name );
$profile_link = uwp_build_profile_tab_url($user_id);
$hello_text = !empty($args['dashboard_text']) ? esc_attr__($args['dashboard_text'],'userswp') : __( 'Hello, %s', 'userswp' );
$display_name = "<a href='".esc_url($profile_link)."' >".esc_attr($display_name)."</a>";
$hello_text = sprintf($hello_text,$display_name);
$dashboard_links = !empty($args['template_args']['dashboard_links']) ? $args['template_args']['dashboard_links'] : '';
?>
<div class="card text-center border-0">
	<a href="<?php echo esc_url( $profile_link );?>">
	<img src="<?php echo esc_url( get_avatar_url( get_current_user_id(), array('size' => 100) ) ); ?>" class="rounded-circle shadow border border-white border-width-4" alt="<?php echo esc_attr($user_info->data->display_name);?>">
	</a>
	<div class="card-body">
		<?php do_action('uwp_template_form_title_before', 'dashboard'); ?>
		<h5 class="card-title"><?php echo apply_filters('uwp_template_form_title',  $hello_text, 'dashboard'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h5>
		<?php do_action('uwp_template_form_title_after', 'dashboard'); ?>
		<?php do_action('uwp_template_display_notices', 'dashboard'); ?>

		<div class="uwp-dashboard-links">
			<?php
			do_action('uwp_dashboard_links_before',$args);

			global $userswp;
			$userswp->forms->output_dashboard_links( $dashboard_links );

			do_action('uwp_dashboard_links_after',$args);
			?>
		</div>

		<div class="uwp-logout-link pt-3">
			<?php
			echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'type'  =>  'a',
				'href'       => esc_url( wp_logout_url() ),
				'class'      => 'btn btn-sm btn-outline-primary',
				'content'    => esc_html__( 'Logout', 'userswp' ),
				'extra_attributes'  => array('rel'=>'nofollow')
			));
			?>
		</div>
	</div>
</div>