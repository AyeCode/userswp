<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('Ayecode_Addons')) {

    abstract class Ayecode_Addons
    {

        /**
         * Get things started
         *
         * @access  public
         */
        public function __construct()
        {
        }

        /**
         * Get the extensions page tabs.
         *
         * @return array of tabs.
         */
        public function get_tabs()
        {
            return array();
        }

        /**
         * Get sections for the addons screen
         *
         * @return array of objects
         */
        public function get_sections()
        {

            return array(); //@todo we prob don't need these yet.
        }

        /**
         * Get section content for the addons screen.
         *
         * @param  string $section_id
         *
         * @return array
         */
        public function get_section_data($section_id)
        {
            return array();
        }

        /**
         * Get section for the addons screen.
         *
         * @param  string $section_id
         *
         * @return object|bool
         */
        public function get_tab($tab_id)
        {
            $tabs = $this->get_tabs();
            if (isset($tabs[$tab_id])) {
                return $tabs[$tab_id];
            }
            return false;
        }

        /**
         * Get section for the addons screen.
         *
         * @param  string $section_id
         *
         * @return object|bool
         */
        public function get_section($section_id)
        {
            $sections = $this->get_sections();
            if (isset($sections[$section_id])) {
                return $sections[$section_id];
            }
            return false;
        }

        /**
         * Outputs a button.
         *
         * @param object $addon
         */
        public function output_button($addon)
        {
            // override this function to output action button for each add on
        }

        /**
         * Handles output of the addons page in admin.
         */
        public function output()
        {
            // override this function to output extensions screen
        }

        /**
         * Check if a plugin is installed (only works if WPEU is installed and active)
         *
         * @param $id
         *
         * @return bool
         */
        public function is_plugin_installed($id, $addon = '')
        {
            $all_plugins = get_plugins();

            $installed = false;

            foreach ($all_plugins as $p_slug => $plugin) {

                if (isset($plugin['Update ID']) && $id == $plugin['Update ID']) {
                    $installed = true;
                } elseif (!empty($addon)) {

                }

            }

            return $installed;
        }

        public function install_plugin_install_status($addon)
        {

            // Default to a "new" plugin
            $status = 'install';
            $url = isset($addon->info->link) ? $addon->info->link : false;
            $file = false;

            $slug = isset($addon->info->slug) ? $addon->info->slug : '';
            if (!empty($addon->licensing->edd_slug)) {
                $slug = $addon->licensing->edd_slug;
            }
            $id = !empty($addon->info->id) ? absint($addon->info->id) : '';
            $version = isset($addon->licensing->version) ? $addon->licensing->version : '';

            // get the slug

            $all_plugins = get_plugins();
            foreach ($all_plugins as $p_slug => $plugin) {

                if ($id && isset($plugin['Update ID']) && $id == $plugin['Update ID']) {
                    $status = 'installed';
                    $file = $p_slug;
                    break;
                } elseif (!empty($addon->licensing->edd_slug)) {
                    if (strpos($p_slug, $addon->licensing->edd_slug . '/') === 0) {
                        $status = 'installed';
                        $file = $p_slug;
                        break;
                    }
                }
            }

            return compact('status', 'url', 'version', 'file');
        }

        /**
         * Check if a theme is installed.
         *
         * @param $id
         *
         * @return bool
         */
        public function is_theme_installed($addon)
        {
            $all_themes = wp_get_themes();

            $slug = isset($addon->info->slug) ? $addon->info->slug : '';
            if (!empty($addon->licensing->edd_slug)) {
                $slug = $addon->licensing->edd_slug;
            }


            foreach ($all_themes as $key => $theme) {
                if ($slug == $key) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Check if a theme is active.
         *
         * @param $addon
         *
         * @return bool
         */
        public function is_theme_active($addon)
        {
            $theme = wp_get_theme();

            //manuall checks
            if ($addon->info->title == "Whoop!") {
                $addon->info->title = "Whoop";
            }


            if ($addon->info->title == $theme->get('Name')) {
                return true;
            }

            return false;
        }

        /**
         * Get theme activation url.
         *
         * @param $addon
         *
         * @return bool
         */
        public function get_theme_activation_url($addon)
        {
            $themes = wp_prepare_themes_for_js();

            //manuall checks
            if ($addon->info->title == "Whoop!") {
                $addon->info->title = "Whoop";
            }


            foreach ($themes as $theme) {
                if ($addon->info->title == $theme['name']) {
                    return $theme['actions']['activate'];
                }
            }

            return false;
        }

        /**
         * Get theme install url.
         *
         * @param $addon
         *
         * @return bool
         */
        public function get_theme_install_url($slug)
        {

            $install_url = add_query_arg(array(
                'action' => 'install-theme',
                'theme' => urlencode($slug),
            ), admin_url('update.php'));
            $install_url = wp_nonce_url($install_url, 'install-theme_' . $slug);

            return $install_url;
        }

        /**
         * A list of recommended wp.org plugins.
         * @return array
         */
        public function get_recommend_wp_plugins()
        {
            return array();
        }

        /**
         * Format the recommended list of wp.org plugins for our extensions section output.
         *
         * @return array
         */
        public function get_recommend_wp_plugins_edd_formatted()
        {
            $formatted = array();
            $plugins = $this->get_recommend_wp_plugins();

            foreach ($plugins as $plugin) {
                $product = new stdClass();
                $product->info = new stdClass();
                $product->info->id = '';
                $product->info->slug = isset($plugin['slug']) ? $plugin['slug'] : '';
                $product->info->title = isset($plugin['name']) ? $plugin['name'] : '';
                $product->info->excerpt = isset($plugin['desc']) ? $plugin['desc'] : '';
                $product->info->link = isset($plugin['url']) ? $plugin['url'] : '';
                $product->info->thumbnail = isset($plugin['thumbnail']) ? $plugin['thumbnail'] : "https://ps.w.org/" . $plugin['slug'] . "/assets/banner-772x250.png";
                $formatted[] = $product;
            }

            return $formatted;
        }
    }
}