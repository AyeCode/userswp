<?php
/**
 * Profile tabs template (default)
 *
 * @ver 0.0.1
 */
$css_class    = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : '';
$output       = ! empty( $args['output'] ) ? esc_attr( $args['output'] ) : '';
$account_page = uwp_get_page_id( 'account_page', false );
$tabs_array   = $args['tabs_array'];
$active_tab   = $args['active_tab'];

do_action( 'uwp_template_before', 'profile-tabs' );
$user = uwp_get_displayed_user();
if ( ! $user ) {
	return;
}

echo '<div class="uwp-profile-content">';
if ( $output === '' || $output == 'head' ) {
	?>
    <div class="uwp-profile-nav">
        <ul class="item-list-tabs-ul">
			<?php
			if ( ! empty( $tabs_array ) ) {
				foreach ( $tabs_array as $tab ) {
					$tab_id  = $tab['tab_key'];
					$tab_url = uwp_build_profile_tab_url( $user->ID, $tab_id, false );

					$active = $active_tab == $tab_id ? ' active' : '';

					if ( $active_tab == $tab_id ) {
						$active_tab_content = $tab['tab_content_rendered'];
					}

					?>
                    <li id="uwp-profile-<?php echo esc_attr( $tab_id ); ?>"
                        class="<?php echo esc_attr( $active ); ?>">
                        <a href="<?php echo esc_url( $tab_url ); ?>">
                            <span class="uwp-profile-tab-label uwp-profile-<?php echo esc_attr( $tab_id ); ?>-label "><?php esc_html_e( $tab['tab_name'], 'userswp' ); ?></span>
                        </a>
                    </li>
					<?php
				}
			}
			?>
        </ul>
		<?php
		$can_user_edit_account = apply_filters( 'uwp_user_can_edit_own_profile', true, $user->ID );
		?>
		<?php if ( $account_page && is_user_logged_in() && ( get_current_user_id() == $user->ID ) && $can_user_edit_account ) { ?>
            <div class="uwp-edit-account">
                <a href="<?php echo esc_url( get_permalink( $account_page ) ); ?>"
                   title="<?php esc_attr_e( 'Edit Account', 'userswp' ); ?>"><i class="fas fa-cog"></i></a>
            </div>
		<?php } ?>
    </div>
	<?php
}
if ( $output === '' || $output == 'body' ) {
	if ( isset( $active_tab_content ) && ! empty( $active_tab_content ) ) {
		?>
        <div class="uwp-profile-entries">
			<?php
			echo $active_tab_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
        </div>
	<?php } ?>
<?php }
echo '</div>';
do_action( 'uwp_template_after', 'profile-tabs' ); ?>