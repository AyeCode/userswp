<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $aui_bs5;

$type  = isset( $_POST['uwp_popup_type'] ) && $_POST['uwp_popup_type'] == 'avatar' ? 'avatar' : 'banner';
$image_url = !empty($args['image_url']) ? esc_url( $args['image_url'] ) : '';
?>
<div class="uwp-bs-modal uwp_fade uwp_show" id="uwp-<?php echo esc_attr( $type ); ?>-modal">
	<div class="uwp-bs-modal-dialog">
		<div class="uwp-bs-modal-content">
			<div class="uwp-bs-modal-header">
				<h4 class="uwp-bs-modal-title">
					<?php
					if ($type == 'avatar') {
						esc_html_e( 'Change your profile photo', 'userswp' );
					} else {
						esc_html_e( 'Change your cover photo', 'userswp' );
					}
					?>
				</h4>
				<button type="button" class="close uwp-modal-close" data-type="<?php echo esc_attr( $type ); ?>" data-<?php echo ( $aui_bs5 ? 'bs-' : '' ); ?>dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="uwp-bs-modal-body">
				<div id="uwp-bs-modal-notice" class="bsui"></div>
				<div align="center">
					<img src="<?php echo esc_url( $image_url ); ?>" id="uwp-<?php echo esc_attr( $type ); ?>-to-crop" />
				</div>
			</div>
			<div class="uwp-bs-modal-footer">
				<div class="uwp-<?php echo esc_attr( $type ); ?>-crop-p-wrap">
					<div id="<?php echo esc_attr( $type ); ?>-crop-actions">
						<form class="uwp-crop-form" method="post">
							<input type="hidden" name="x" value="" id="<?php echo esc_attr( $type ); ?>-x" />
							<input type="hidden" name="y" value="" id="<?php echo esc_attr( $type ); ?>-y" />
							<input type="hidden" name="w" value="" id="<?php echo esc_attr( $type ); ?>-w" />
							<input type="hidden" name="h" value="" id="<?php echo esc_attr( $type ); ?>-h" />
							<input type="hidden" id="uwp-<?php echo esc_attr( $type ); ?>-crop-image" name="uwp_crop" value="<?php echo esc_attr( $image_url ); ?>" />
							<input type="hidden" name="uwp_crop_nonce" value="<?php echo esc_attr( wp_create_nonce( 'uwp_crop_nonce_'.$type ) ); ?>" />
							<input type="submit" name="uwp_<?php echo esc_attr( $type ); ?>_crop" value="<?php esc_attr_e('Apply', 'userswp'); ?>" class="button button-primary" id="save_uwp_<?php echo esc_attr( $type ); ?>" />
						</form>
					</div>
				</div>
				<button type="button" data-type="<?php echo esc_attr( $type ); ?>" class="button uwp_modal_btn uwp-modal-close" data-<?php echo ( $aui_bs5 ? 'bs-' : '' ); ?>dismiss="modal"><?php esc_html_e( 'Cancel', 'userswp' ); ?></button>
			</div>
		</div>
	</div>
</div>