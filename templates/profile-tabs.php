<?php
/**
 * Profile tabs template (default)
 *
 * @ver 0.0.1
 */
global $uwp_widget_args;
$css_class = ! empty( $uwp_widget_args['css_class'] ) ? esc_attr( $uwp_widget_args['css_class'] ) : 'border-0';

$account_page = uwp_get_page_id('account_page', false);
$tabs_array = $uwp_widget_args['tabs_array'];
//print_r($tabs_array);exit;
$active_tab = $uwp_widget_args['active_tab'];

do_action( 'uwp_template_before', 'profile-tabs' );
$user = uwp_get_displayed_user();
if(!$user){
	return;
}



?>
	<div class="uwp-profile-content">
		<div class="uwp-profile-nav">
			<ul class="item-list-tabs-ul">
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

						?>
						<li id="uwp-profile-<?php echo $tab_id; ?>"
						    class="<?php echo $active; ?>">
							<a href="<?php echo esc_url($tab_url); ?>">
								<span class="uwp-profile-tab-label uwp-profile-<?php echo $tab_id; ?>-label "><?php echo esc_html($tab['tab_name']); ?></span>
<!--								<span class="uwp-profile-tab-count uwp-profile---><?php //echo $tab_id; ?><!---count">--><?php //echo '0';//$tab['count']; ?><!--</span>-->
							</a>
						</li>
						<?php
					}
				}
				?>
			</ul>
			<?php
			$can_user_edit_account = apply_filters('uwp_user_can_edit_own_profile', true, $user->ID);
			?>
			<?php if ($account_page && is_user_logged_in() && (get_current_user_id() == $user->ID) && $can_user_edit_account) { ?>
				<div class="uwp-edit-account">
					<a href="<?php echo get_permalink( $account_page ); ?>" title="<?php echo  __( 'Edit Account', 'userswp' ); ?>"><i class="fas fa-cog"></i></a>
				</div>
			<?php } ?>
		</div>

		<div class="uwp-profile-entries">
			<?php
			if(isset($active_tab_content) && !empty($active_tab_content)){
				echo $active_tab_content;
			}
			?>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'profile-tabs' ); ?>