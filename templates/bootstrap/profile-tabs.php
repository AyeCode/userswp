<?php
/**
 * Profile tabs template (default)
 *
 * @ver 0.0.1
 */
$css_class = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$output = ! empty( $args['output'] ) ? esc_attr( $args['output'] ) : '';
$account_page = uwp_get_page_id('account_page', false);
$tabs_array = $args['tabs_array'];
$active_tab = $args['active_tab'];
$greedy_menu_class = empty($args['disable_greedy']) ? 'greedy' : '';

do_action( 'uwp_template_before', 'profile-tabs' );
$user = uwp_get_displayed_user();
if(!$user){
	return;
}

if($output === '' || $output=='head'){
?>
<nav class="navbar navbar-expand-xl navbar-light bg-white  mb-4 p-xl-0 <?php echo esc_attr($greedy_menu_class); ?>">
	<div class="w-100 justify-content-center p-xl-0 border-bottom">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#uwp-profile-tabs-nav" aria-controls="navbarNavDropdown-1" aria-expanded="false" aria-label="Toggle navigation" style=""><span class="navbar-toggler-icon"></span></button>
		<div class="collapse navbar-collapse" id="uwp-profile-tabs-nav">
			<ul class="navbar-nav flex-wrap m-0 list-unstyled">
				<?php
				if(!empty($tabs_array)) {
					foreach ($tabs_array as $tab) {
						$tab_id = $tab['tab_key'];
						$tab_url = uwp_build_profile_tab_url($user->ID, $tab_id, false);

						$active = $active_tab == $tab_id ? ' active border-bottom border-primary border-width-2' : '';

						if ($active_tab == $tab_id) {
							$active_tab_content = $tab['tab_content_rendered'];
						}

						$append_hash = apply_filters('uwp_add_tab_content_hashtag', true, $tab, $user);
						$tab_url = $append_hash ? esc_url($tab_url).'#tab-content' : esc_url($tab_url);

						?>
						<li id="uwp-profile-<?php echo esc_attr( $tab_id ); ?>"
						    class="nav-item <?php echo $active; ?> list-unstyled m-0">
								<?php
                                $content = '<span class="uwp-profile-tab-label uwp-profile-'.esc_attr( $tab_id ).'-label">'.esc_html__($tab['tab_name'], 'userswp').'</span>';
                                echo aui()->button(array(
									'type'       =>  'a',
									'href'       => $tab_url,
									'class'      => 'nav-link',
									'icon'       => esc_attr($tab['tab_icon']),
									'content'    => $content,
								));
								?>
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

if ( $output === '' || $output == 'body' ) {
	if ( isset( $active_tab_content ) && ! empty( $active_tab_content ) ) {
		?>
        <div id="tab-content" class="uwp-profile-content">
            <div class="uwp-profile-entries">
				<?php

				echo $active_tab_content;
				?>
            </div>
        </div>
		<?php
	}
}
?>