<?php do_action('uwp_template_before', 'dashboard');
global $uwp_login_widget_args;
$user_id = get_current_user_id();
$user_info = get_userdata($user_id);
$display_name = $user_info->data->display_name;
$profile_link = uwp_build_profile_tab_url($user_id);
$hello_text = !empty($uwp_login_widget_args['dashboard_text']) ? esc_attr__($uwp_login_widget_args['dashboard_text'],'userswp') : __( 'Hello, %s', 'userswp' );
$display_name = "<a href='$profile_link' >".esc_attr($display_name)."</a>";
$hello_text = sprintf($hello_text,$display_name);
?>
    <div class="uwp-content-wrap">
        <div class="uwp-account" <?php if(!empty($uwp_login_widget_args['form_padding'])){echo "style='padding:".absint($uwp_login_widget_args['form_padding'])."px'";}?>>
            <div class="uwp-account-avatar"><a href="<?php echo $profile_link;?>"><?php echo get_avatar( get_current_user_id(), 100 ); ?></a></div>
            <?php do_action('uwp_template_form_title_before', 'dashboard'); ?>
            <h2><?php
                echo apply_filters('uwp_template_form_title',  $hello_text, 'dashboard');
                ?></h2>
            <?php do_action('uwp_template_form_title_after', 'dashboard'); ?>
            <?php do_action('uwp_template_display_notices', 'dashboard'); ?>
            <div class="uwp-dashboard-links">
                <?php
                do_action('uwp_dashboard_links_before',$uwp_login_widget_args);
                
                $dashboard_links = array(
                    'placeholder' => array(
                        'url' => '',
                        'text' => __('Select an action','userswp'),
                        'disabled' => true,
                        'selected' => true,
                        'display_none' => true
                    )
                );

                $dashboard_links['uwp_profile'][] = array(
                    'optgroup' => 'open',
                    'text' => esc_attr( __( 'My Profile', 'userswp' ) )
                );
                $dashboard_links['uwp_profile'][] = array(
                    'url' => uwp_build_profile_tab_url( $user_id ),
                    'text' => esc_attr( __( 'View Profile', 'userswp' ) )
                );
                $account_page = uwp_get_page_id('account_page', false);
                $account_link = get_permalink( $account_page );
                if($account_link){
                    $dashboard_links['uwp_profile'][] = array(
                        'url' => $account_link,
                        'text' => esc_attr( __( 'Edit Profile', 'userswp' ) )
                    );
                }

                $dashboard_links['uwp_profile'][] = array(
                    'optgroup' => 'close',
                );
                $dashboard_links = apply_filters( 'uwp_dashboard_links',$dashboard_links,$uwp_login_widget_args);

//                print_r($dashboard_links);
                global $userswp;
                $userswp->forms->output_dashboard_links( $dashboard_links );
                
                do_action('uwp_dashboard_links_after',$uwp_login_widget_args);
                ?>
            </div>
            <div class="uwp-login-links">
                <div class="uwp-logout-link">
                    <?php
                    $template = new UsersWP_Templates();
                    $logout_url = $template->uwp_logout_url();
                    printf(__( '<a href="%1$s">Log out</a>', 'userswp'), esc_url( $logout_url ));
                    ?>
                </div>
            </div>

        </div>
    </div>
<?php do_action('uwp_template_after', 'dashboard'); ?>