<?php do_action('uwp_template_before', 'dashboard');
$user_id = get_current_user_id();
$user_info = get_userdata($user_id);
$display_name = esc_attr( $user_info->data->display_name );
$profile_link = uwp_build_profile_tab_url($user_id);
$hello_text = !empty($args['dashboard_text']) ? esc_attr__($args['dashboard_text'],'userswp') : __( 'Hello, %s', 'userswp' );
$display_name = "<a href='".esc_url($profile_link)."' >".esc_attr($display_name)."</a>";
$hello_text = sprintf($hello_text,$display_name);
$dashboard_links = !empty($args['template_args']['dashboard_links']) ? $args['template_args']['dashboard_links'] : '';
?>
    <div class="uwp-content-wrap">
        <div class="uwp-account" <?php if(!empty($args['form_padding'])){echo "style='padding:".absint($args['form_padding'])."px'";}?>>
            <div class="uwp-account-avatar"><a href="<?php echo esc_url( $profile_link );?>"><?php echo get_avatar( get_current_user_id(), 100 ); ?></a></div>
            <?php do_action('uwp_template_form_title_before', 'dashboard'); ?>
            <h2><?php
                echo apply_filters('uwp_template_form_title',  esc_attr( $hello_text ), 'dashboard'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?></h2>
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
            <div class="uwp-login-links">
                <div class="uwp-logout-link">
                    <a href="<?php echo esc_url( wp_logout_url() );?>"><?php esc_html_e("Logout","userswp");?></a>
                </div>
            </div>

        </div>
    </div>
<?php do_action('uwp_template_after', 'dashboard'); ?>