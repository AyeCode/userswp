<?php
/**
 * Admin View: Page - Addons
 *
 * @var string $view
 * @var object $addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
add_ThickBox();
wp_enqueue_style( 'uwp-extensions-style', USERSWP_PLUGIN_URL . 'admin/assets/css/extensions.css',array(),'','' );
?>
<div class="wrap uwp_addons_wrap">
	<h1><?php echo get_admin_page_title(); ?></h1>

	<?php if ( $tabs ){ ?>
		<nav class="nav-tab-wrapper uwp-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . admin_url( 'admin.php?page=uwp-addons&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
			}
			do_action( 'uwp_addons_tabs' );
			?>
		</nav>

		<?php

		if($current_tab == 'membership'){

			?>

			<div class="uwp-membership-tab-conatiner">
				<h2>With our UsersWP Membership you get access to all our products!</h2>
				<p><a class="button button-primary" href="https://userswp.io/downloads/membership/">View Memberships</a></p>
				<?php if(defined('WP_EASY_UPDATES_ACTIVE')){?>

					<h2>Have a membership key?</h2>

					<p>
						<?php
						$wpeu_admin = new External_Updates_Admin('userswp.io','1');
						echo $wpeu_admin->render_licence_actions('userswp.io', 'membership',array(238,239,240));
						?>
					</p>
				<?php }?>
			</div>

			<?php
		}else{
			$installed_plugins = get_plugins();
            $addon_obj = new UsersWP_Admin_Addons();
			if ($addons = $addon_obj->get_section_data( $current_tab ) ) :
				?>
				<ul class="uwp-products"><?php foreach ( $addons as $addon ) :
						?><li class="uwp-product">
								<div class="uwp-product-title">
									<h3><?php
										if ( ! empty( $addon->info->excerpt) ){
											echo uwp_help_tip( $addon->info->excerpt );
										}
										echo esc_html( $addon->info->title ); ?></h3>
								</div>

								<span class="uwp-product-image">
									<?php if ( ! empty( $addon->info->thumbnail) ) : ?>
										<img src="<?php echo esc_attr( $addon->info->thumbnail ); ?>"/>
									<?php endif;

									if(isset($addon->info->link) && substr( $addon->info->link, 0, 21 ) === "https://wordpress.org"){
										echo '<a href="'.admin_url('/plugin-install.php?tab=plugin-information&plugin='.$addon->info->slug).'&width=770&height=660&TB_iframe=true" class="thickbox" >';
										echo '<span class="uwp-product-info">'.__('More info','userswp').'</span>';
										echo '</a>';
									}elseif(isset($addon->info->link) && substr( $addon->info->link, 0, 18 ) === "https://userswp.io"){
										if(defined('WP_EASY_UPDATES_ACTIVE')){
											$url = admin_url('/plugin-install.php?tab=plugin-information&plugin='.$addon->info->slug.'&width=770&height=660&item_id='.$addon->info->id.'&update_url=https://userswp.io&TB_iframe=true');
										}else{
											// if installed show activation link
											if(isset($installed_plugins['wp-easy-updates/external-updates.php'])){
												$url = '#TB_inline?width=600&height=50&inlineId=uwp-wpeu-required-activation';
											}else{
												$url = '#TB_inline?width=600&height=50&inlineId=uwp-wpeu-required-for-external';
											}
										}
										echo '<a href="'.$url.'" class="thickbox">';
										echo '<span class="uwp-product-info">'.__('More info','userswp').'</span>';
										echo '</a>';
									}

									?>

								</span>


								<span class="uwp-product-button">
									<?php
                                    $addon_obj->output_button( $addon );
									?>
								</span>

								<span class="uwp-price"><?php //print_r($addon); //echo wp_kses_post( $addon->price ); ?></span></li><?php endforeach; ?></ul>
			<?php endif;
		}

	}
	?>


	<div class="clearfix" ></div>

	<?php if($current_tab =='addons'){?>
	<p><?php printf( __( 'All of our UsersWP Addons can be found on UsersWP.io here: <a href="%s">UsersWP Addons</a>', 'userswp' ), 'https://userswp.io/downloads/category/addons/' ); ?></p>
	<?php } ?>

	<div id="uwp-wpeu-required-activation" style="display:none;"><span class="uwp-notification "><?php printf( __("The plugin <a href='https://wpeasyupdates.com/' target='_blank'>WP Easy Updates</a> is required to check for and update some installed plugins/themes, please <a href='%s'>activate</a> it now.",'userswp'),wp_nonce_url(admin_url('plugins.php?action=activate&plugin=wp-easy-updates/external-updates.php'), 'activate-plugin_wp-easy-updates/external-updates.php'));?></span></div>
	<div id="uwp-wpeu-required-for-external" style="display:none;"><span class="uwp-notification "><?php printf(  __("The plugin <a href='https://wpeasyupdates.com/' target='_blank'>WP Easy Updates</a> is required to check for and update some installed plugins/themes, please <a href='%s' onclick='window.open(\"https://wpeasyupdates.com/wp-easy-updates.zip\", \"_blank\");' >download</a> and install it now.",'userswp'),admin_url("plugin-install.php?tab=upload&wpeu-install=true"));?></span></div>
	<div id="wpeu-licence-popup" style="display:none;">
		<span class="uwp-notification noti-white">
			<h3 class="wpeu-licence-title"><?php _e("Licence key",'userswp');?></h3>
			<input class="wpeu-licence-key" type="text" placeholder="<?php _e("Enter your licence key",'userswp');?>"> <button class="button-primary wpeu-licence-popup-button" ><?php _e("Install",'userswp');?></button>
			<br>
			<?php
			echo sprintf( __('%sFind your licence key here%s OR %sBuy one here%s', 'userswp'), '<a href="https://userswp.io/your-account/" target="_blank">','</a>','<a class="wpeu-licence-link" href="https://userswp.io/downloads/category/addons/" target="_blank">','</a>' );
			?>
		</span>
	</div>

</div>
