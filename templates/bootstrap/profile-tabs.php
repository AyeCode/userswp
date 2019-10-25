<?php
/**
 * Profile tabs template (default)
 *
 * @ver 0.0.1
 */
global $uwp_widget_args;
$css_class = ! empty( $uwp_widget_args['css_class'] ) ? esc_attr( $uwp_widget_args['css_class'] ) : 'border-0';
//print_r( $uwp_widget_args );
$output = ! empty( $uwp_widget_args['output'] ) ? esc_attr( $uwp_widget_args['output'] ) : '';
$account_page = uwp_get_page_id('account_page', false);
$tabs_array = $uwp_widget_args['tabs_array'];
//print_r($tabs_array);exit;
$active_tab = $uwp_widget_args['active_tab'];

do_action( 'uwp_template_before', 'profile-tabs' );
$user = uwp_get_displayed_user();
if(!$user){
	return;
}

if($output === '' || $output=='head'){
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white  mb-4 p-xl-0">
	<div class="w-100 justify-content-center p-xl-0 border-bottom">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#uwp-profile-tabs-nav" aria-controls="navbarNavDropdown-1" aria-expanded="false" aria-label="Toggle navigation" style=""><span class="navbar-toggler-icon"></span></button>
		<div class="collapse navbar-collapse" id="uwp-profile-tabs-nav">
			<ul class="navbar-nav flex-wrap m-0">
				<?php
//					print_r($tabs_array);
				if(!empty($tabs_array)) {
					foreach ($tabs_array as $tab) {
						$tab_id = $tab['tab_key'];
						$tab_url = uwp_build_profile_tab_url($user->ID, $tab_id, false);

						$active = $active_tab == $tab_id ? ' active border-bottom border-primary border-width-2' : '';

						if (1 == $tab['tab_login_only'] && !(is_user_logged_in() && get_current_user_id() == $user->ID)) {
							continue;
						}

						if ($active_tab == $tab_id) {
							$active_tab_content = $tab['tab_content_rendered'];
						}

						?>
						<li id="uwp-profile-<?php echo $tab_id; ?>"
						    class="nav-item <?php echo $active; ?>">
							<a href="<?php echo esc_url($tab_url); ?>" class="nav-link">
								<?php
								if(!empty($tab['tab_icon'])){
									echo '<i class="'.esc_attr($tab['tab_icon']).'"></i>';
								}
								?>
								<span class="uwp-profile-tab-label uwp-profile-<?php echo $tab_id; ?>-label "><?php echo esc_html($tab['tab_name']); ?></span>
							</a>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>
	</div>
</nav>

<?php
}

if($output === '' || $output=='body'){
?>
<div class="uwp-profile-content">
	<?php
	if(!empty($tabs_array)) {
		foreach ($tabs_array as $tab) {
			$tab_id = $tab['tab_key'];
			$tab_url = uwp_build_profile_tab_url($user->ID, $tab_id, false);

			$active = $active_tab == $tab_id ? ' active' : '';

			if (1 == $tab['tab_login_only'] && !(is_user_logged_in() && get_current_user_id() == $user->ID)) {
				continue;
			}

			if ($active_tab == $tab_id) {
				$active_tab_content = $tab['tab_content_rendered'];
			}

		}
	}
	?>
	<div class="uwp-profile-entries">
		<?php
		if(isset($active_tab_content) && !empty($active_tab_content)){
			echo $active_tab_content;
		}
		?>
	</div>
</div>
<?php
}
?>