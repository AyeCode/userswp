<?php
/**
 * Add some content to the help tab
 *
 * @version 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists('UsersWP_Admin_Help') ) {

    class UsersWP_Admin_Help{

        public function __construct() {
            add_action( 'current_screen', array( $this, 'add_tabs' ), 50 );
            add_filter( 'aui_screen_ids', array( $this, 'add_aui_screens' ));
        }

        public function get_screen_ids() {

            $screen_ids = array(
                'user-edit',
                'users',
                'profile',
                'toplevel_page_userswp',
                'userswp_page_uwp_form_builder',
                'userswp_page_uwp_status',
                'userswp_page_uwp-addons',
                'userswp_page_uwp_tools',
            );

            $screen_ids = apply_filters('uwp_admin_help_screen_ids',$screen_ids);

            return $screen_ids;
        }

        /**
         * Tell AyeCode UI to load on certain admin pages.
         * 
         * @param $screen_ids
         *
         * @return array
         */
        public function add_aui_screens( $screen_ids ){

            // load on these pages if set
            $screen_ids = array_merge( $screen_ids, $this->get_screen_ids() );

            return $screen_ids;
        }

        public function add_tabs() {

            $screen = get_current_screen();

            $screen_ids = $this->get_screen_ids();

            if ( ! $screen || ! in_array( $screen->id,  $screen_ids) ) {
                return;
            }

            $screen->add_help_tab( array(
                'id'        => 'uwp_help_support_tab',
                'title'     => __( 'Help &amp; Support', 'userswp' ),
                'content'   => $this->get_support_tab_content()
            ) );

            $screen->add_help_tab( array(
                'id'        => 'uwp_help_bugs_tab',
                'title'     => __( 'Found a bug?', 'userswp' ),
                'content'   => $this->get_bugs_tab_content()
            ) );

            $screen->add_help_tab( array(
                'id'        => 'uwp_help_onboard_tab',
                'title'     => __( 'Setup wizard', 'userswp' ),
                'content'   => $this->get_onboard_tab_content()
            ) );

            $screen->set_help_sidebar( $this->get_help_tab_sidebar_content());

            $section = !empty( $_GET['section'] ) ? '_'.esc_attr($_GET['section']) : '';

            do_action( 'uwp_adds_help_screen_tabs'.$section,$screen );

        }

        public function get_support_tab_content() {

            $support_html = "<h2>".__('Help & Support','userswp')."</h2>";
            $support_html .= "<p>".sprintf(__('Should you need help understanding, using, or extending UsersWP <a href="%1$s">please read our documentation</a>. You will find all kinds of resources including snippets, tutorials and much more.,','userswp'),'https://userswp.io/docs/?utm_source=setupwizard&utm_medium=product&utm_content=getting-started&utm_campaign=userswpplugin')."</p>";
            $support_html .= "<p>".sprintf(__('For further assistance with UsersWP core or with premium extensions sold by UsersWP you can use our <a href="%1$s">support forums</a>.','userswp'),'https://userswp.io/support/?utm_source=setupwizard&utm_medium=product&utm_content=docs&utm_campaign=userswpplugin')."</p>";
            $support_html .= "<p>".__('Before asking for help we recommend checking the system status page to identify any problems with your configuration.','userswp')."</p>";
            $support_html .= "<p><a class='button button-primary' href='".admin_url( 'admin.php?page=uwp_status' )."'>".__( 'System status', 'userswp' )."</a> <a class='button' href='https://userswp.io/support/?utm_source=setupwizard&utm_medium=product&utm_content=docs&utm_campaign=userswpplugin'>".__( 'UsersWP support', 'userswp' )."</a></p>";
            return apply_filters('uwp_support_help_tab_content',$support_html);
        }

        public function get_bugs_tab_content() {

            $bugs_html = "<h2>".__('Found a bug?','userswp')."</h2>";
            $bugs_html .= "<p>".sprintf(__('If you find a bug within UsersWP core you can create a ticket via <a href="%1$s">Github issues</a>. Ensure you read the <a href="%2$s">contribution guide</a> prior to submitting your report. To help us solve your issue, please be as descriptive as possible and include your <a href="%3$s">system status report</a>','userswp'),'https://github.com/UsersWP/userswp/issues?state=open','https://github.com/UsersWP/userswp/blob/master/CONTRIBUTING.md',admin_url( 'admin.php?page=uwp_status' ))."</p>";
            $bugs_html .= "<p><a href='https://github.com/UsersWP/userwp/issues?state=open' class='button button-primary'>". __( 'Report a bug', 'userswp' ) ."</a> <a href='".admin_url( 'admin.php?page=uwp_status' )."' class='button'>".__( 'System status', 'userswp' )."</a></p>";

            return apply_filters('uwp_bugs_help_tab_content',$bugs_html);
        }

        public function get_onboard_tab_content() {

            $onboard_html = "<h2>".__('Setup wizard','userswp')."</h2>";
            $onboard_html .= "<p>".__('If you need to access the setup wizard again, please click on the button below.','userswp')."</p>";
            $onboard_html .= "<p>".__('Running the wizard again will not delete your current settings. You will have the option to change any current settings.','userswp')."</p>";
            $onboard_html .= "<p><a href='".admin_url( 'index.php?page=uwp-setup' )."' class='button button-primary'>".__('Setup wizard','userswp')."</a></p>";

            return apply_filters('uwp_setup_wizard_help_tab_content',$onboard_html);

        }

        public function get_help_tab_sidebar_content() {

            $sidebar_html = '<p><strong>' . __( 'For more information:', 'userswp' ) . '</strong></p>';
            $sidebar_html .= '<p><a target="_blank" href="https://userswp.io/?utm_source=helptab&utm_medium=product&utm_content=about&utm_campaign=userswpplugin">'.__('About UsersWP','userswp').'</a></p>';
            $sidebar_html .= '<p><a target="_blank" href="https://wordpress.org/plugins/userswp/">'.__('WordPress.org project','userswp').'</a></p>';
            $sidebar_html .= '<p><a target="_blank" href="https://github.com/UsersWP/userswp">'.__('Github project','userswp').'</a></p>';
            $sidebar_html .= '<p><a target="_blank" href="https://userswp.io/downloads/category/addons/?utm_source=helptab&utm_medium=product&utm_content=uwpextensions&utm_campaign=userswpplugin">'.__('Official extensions','userswp').'</a></p>';

            return apply_filters('uwp_sidebar_help_tab_content',$sidebar_html);
        }

    }

    new UsersWP_Admin_Help();
}