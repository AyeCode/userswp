<?php
/**
 * UsersWP extensions screen related functions
 *
 * All UsersWP extensions screen related functions can be found here.
 *
 * @since      1.0.24
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UsersWP_Admin_Addons Class.
 */
class UsersWP_Admin_Addons extends Ayecode_Addons {


	/**
	 * Get the extensions page tabs.
	 *
	 * @return array of tabs.
	 */
	public function get_tabs(){
		$tabs = array(
			'addons' => __("Addons", "userswp"),
            'recommended_plugins' => __("Recommended plugins", "userswp"),
            'membership' => __("Membership", "userswp"),
		);

		return $tabs;
	}

	/**
	 * Get section content for the addons screen.
	 *
	 * @param  string $section_id
	 *
	 * @return array
	 */
	public function get_section_data( $section_id ) {
		$section      = $this->get_tab( $section_id );
		$api_url = "https://userswp.io/edd-api/v2/products/";
		$section_data = new stdClass();

		//echo '###'.$section_id;

		if($section_id=='recommended_plugins'){
			$section_data->products = $this->get_recommend_wp_plugins_edd_formatted();
		}
		elseif ( ! empty( $section ) ) {
			if ( false === ( $section_data = get_transient( 'uwp_addons_section_' . $section_id ) ) ) { //@todo restore after testing
			//if ( 1==1) {

				$query_args = array( 'category' => $section_id, 'number' => 100);
				$query_args = apply_filters('wpeu_edd_api_query_args',$query_args,$api_url,$section_id);

				$raw_section = wp_safe_remote_get( esc_url_raw( add_query_arg($query_args ,$api_url) ), array( 'user-agent' => 'UsersWP Addons Page','timeout'     => 15, ) );

				if ( ! is_wp_error( $raw_section ) ) {
					$section_data = json_decode( wp_remote_retrieve_body( $raw_section ) );

					if ( ! empty( $section_data->products ) ) {
						set_transient( 'uwp_addons_section_' . $section_id, $section_data, DAY_IN_SECONDS );
					}
				}
			}
		}

		$products = isset($section_data->products) ? $section_data->products : '';

		return apply_filters( 'uwp_addons_section_data', $products, $section_id );
	}

	/**
	 * Outputs a button.
	 *
	 * @param string $url
	 * @param string $text
	 * @param string $theme
	 * @param string $plugin
	 */
	public function output_button( $addon ) {
		$current_tab     = empty( $_GET['tab'] ) ? 'addons' : sanitize_title( $_GET['tab'] );
//		$button_text = __('Free','userswp');
//		$licensing = false;
//		$installed = false;
//		$price = '';
//		$license = '';
//		$slug = '';
//		$url = isset($addon->info->link) ? $addon->info->link : '';
//		$class = 'button-primary';
//		$install_status = 'get';
//		$onclick = '';

		$wp_org_themes = array('supreme-directory','directory-starter');

		$button_args = array(
			'type' => $current_tab,
			'id' => isset($addon->info->id) ? absint($addon->info->id) : '',
			'title' => isset($addon->info->title) ? $addon->info->title : '',
			'button_text' => __('Free','userswp'),
			'price_text' => __('Free','userswp'),
			'link' => isset($addon->info->link) ? $addon->info->link : '', // link to product
			'url' => isset($addon->info->link) ? $addon->info->link : '', // button url
			'class' => 'button-primary',
			'install_status' => 'get',
			'installed' => false,
			'price' => '',
			'licensing' => isset($addon->licensing->enabled) && $addon->licensing->enabled ? true : false,
			'license' => isset($addon->licensing->license) && $addon->licensing->license ? $addon->licensing->license : '',
			'onclick' => '',
			'slug' => isset($addon->info->slug) ? $addon->info->slug : '',
			'active' => false,
			'file' => '',
			'update_url' => '',
		);

		if($current_tab == 'addons' && isset($addon->info->id) && $addon->info->id){
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
			if(!empty($addon->licensing->edd_slug)){$button_args['slug'] = $addon->licensing->edd_slug;}
			$status = $this->install_plugin_install_status($addon);
			$button_args['file'] = isset($status['file']) ? $status['file'] : '';
			if(isset($status['status'])){$button_args['install_status'] = $status['status'];}
			$button_args['update_url'] = "https://userswp.io";
		}elseif($current_tab == 'themes' && isset($addon->info->id) && $addon->info->id) {
			if(!empty($addon->licensing->edd_slug)){$button_args['slug'] = $addon->licensing->edd_slug;}
			$button_args['installed'] = $this->is_theme_installed($addon);
			if(!in_array($button_args['slug'],$wp_org_themes)){
				$button_args['update_url'] = "https://userswp.io";
			}
		}elseif($current_tab == 'recommended_plugins' && isset($addon->info->slug) && $addon->info->slug){
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
			$status = install_plugin_install_status(array("slug"=>$button_args['slug'],"version"=>""));
			$button_args['install_status'] = isset($status['status']) ? $status['status'] : 'install';
			$button_args['file'] = isset($status['file']) ? $status['file'] : '';
		}

		// set price
		if(isset($addon->pricing) && !empty($addon->pricing)){
			if(is_object($addon->pricing)){
				$prices = (Array)$addon->pricing;
				$button_args['price'] = reset($prices);
			}elseif(isset($addon->pricing)){
				$button_args['price'] = $addon->pricing;
			}
		}

		// set price text
		if( $button_args['price'] && $button_args['price'] != '0.00' ){
			$button_args['price_text'] = sprintf( __('From: $%d', 'userswp'), $button_args['price']);
		}


		// set if installed
		if(in_array($button_args['install_status'], array('installed','latest_installed','update_available','newer_installed'))){
			$button_args['installed'] = true;
		}

//		print_r($button_args);
		// set if active
		if($button_args['installed'] && ($button_args['file'] || $button_args['type'] == 'themes')){
			if($button_args['type'] != 'themes'){
				$button_args['active'] = is_plugin_active($button_args['file']);
			}else{
				$button_args['active'] = $this->is_theme_active($addon);
			}
		}

		// set button text and class
		if($button_args['active']){
			$button_args['button_text'] = __('Active','userswp');
			$button_args['class'] = ' button-secondary disabled ';
		}elseif($button_args['installed']){
			$button_args['button_text'] = __('Activate','userswp');

			if($button_args['type'] != 'themes'){
				if ( current_user_can( 'manage_options' ) ) {
					$button_args['url'] = wp_nonce_url(admin_url('plugins.php?action=activate&plugin='.$button_args['file']), 'activate-plugin_' . $button_args['file']);
				}else{
					$button_args['url'] = '#';
				}
			}else{
				if ( current_user_can( 'switch_themes' ) ) {
					$button_args['url'] = $this->get_theme_activation_url($addon);
				}else{
					$button_args['url'] = '#';
				}
			}

		}else{
			if($button_args['type'] == 'recommended_plugins'){
				$button_args['button_text'] = __('Install','userswp');
			}else{
				$button_args['button_text'] = __('Get it','userswp');
			}
		}
		
		// filter the button arguments
		$button_args = apply_filters('edd_api_button_args',$button_args);

		// set price text
		if(isset($button_args['price_text'])){
			?>
			<a
				target="_blank"
				class="addons-price-text"
				href="<?php echo esc_url( $button_args['link'] ); ?>">
				<?php echo esc_html( $button_args['price_text'] ); ?>
			</a>
			<?php
		}

		$target = '';
		if ( ! empty( $button_args['url'] ) ) {
			$target = strpos($button_args['url'], get_site_url()) !== false ? '' : ' target="_blank" ';
		}

		?>
		<a
			data-licence="<?php echo esc_attr($button_args['license']);?>"
			data-licensing="<?php echo $button_args['licensing'] ? 1 : 0;?>"
			data-title="<?php echo esc_attr($button_args['title']);?>"
			data-type="<?php echo esc_attr($button_args['type']);?>"
			data-text-error-message="<?php _e('Something went wrong!','userswp');?>"
			data-text-activate="<?php _e('Activate','userswp');?>"
			data-text-activating="<?php _e('Activating','userswp');?>"
			data-text-deactivate="<?php _e('Deactivate','userswp');?>"
			data-text-installed="<?php _e('Installed','userswp');?>"
			data-text-install="<?php _e('Install','userswp');?>"
			data-text-installing="<?php _e('Installing','userswp');?>"
			data-text-error="<?php _e('Error','userswp');?>"
			<?php if(!empty($button_args['onclick'])){echo " onclick='".$button_args['onclick']."' ";}?>
			<?php echo $target;?>
			class="addons-button  <?php echo esc_attr( $button_args['class'] ); ?>"
			href="<?php echo esc_url( $button_args['url'] ); ?>">
			<?php echo esc_html( $button_args['button_text'] ); ?>
		</a>
		<?php
	}


	/**
	 * Handles output of the addons page in admin.
	 */
	public function output() {
		$tabs            = $this->get_tabs();
		$sections        = $this->get_sections();
		$theme           = wp_get_theme();
		$section_keys    = array_keys( $sections );
		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : current( $section_keys );
		$current_tab     = empty( $_GET['tab'] ) ? 'addons' : sanitize_title( $_GET['tab'] );
		include_once( USERSWP_PATH . '/admin/views/html-admin-page-addons.php' );
	}

	/**
	 * A list of recommended wp.org plugins.
	 * @return array
	 */
	public function get_recommend_wp_plugins(){
		$plugins = array(
            'geodirectory' => array(
                'url'   => 'https://wordpress.org/plugins/geodirectory/',
                'slug'   => 'geodirectory',
                'name'   => 'GeoDirectory',
                'desc'   => __('Turn any WordPress theme into a global business directory portal.','userswp'),
            ),
			'invoicing' => array(
				'url'   => 'https://wordpress.org/plugins/invoicing/',
				'slug'   => 'invoicing',
				'name'   => 'Invoicing',
				'desc'   => __('Create & Send Invoices, Manage Taxes & VAT. Collect One Time & Recurring Payments.','userswp'),
			),
		);

		return $plugins;
	}
}
