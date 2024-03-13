<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $aui_bs5;

$files = new UsersWP_Files();
$type = isset($_POST['type']) && $_POST['type'] == 'avatar' ? 'avatar' : 'banner';
?>
<div class="uwp-bs-modal uwp_fade uwp_show" id="uwp-popup-modal-wrap">
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
				<form id="uwp-upload-<?php echo esc_attr( $type ); ?>-form" method="post" enctype="multipart/form-data">
					<input type="hidden" name="uwp_upload_nonce" value="<?php echo esc_attr( wp_create_nonce( 'uwp-upload-nonce' ) ); ?>" />
					<input type="hidden" name="uwp_<?php echo esc_attr( $type ); ?>_submit" value="" />
					<button type="button" class="uwp_upload_button" onclick="document.getElementById('uwp_upload_<?php echo esc_js( $type ); ?>').click();"><?php echo esc_html__( 'Upload', 'userswp' ); ?> <?php echo esc_html( $type ); ?></button>
					<p style="text-align: center"><?php echo esc_html__('Note: Max upload image size:', 'userswp') . ' ' . esc_html( $files->uwp_formatSizeUnits($files->uwp_get_max_upload_size($type)) ); ?></p>
					<div class="uwp_upload_field" style="display: none">
						<input name="uwp_<?php echo esc_attr( $type ); ?>_file" id="uwp_upload_<?php echo esc_attr( $type ); ?>" required="required" type="file" value="">
					</div>
				</form>
				<div id="progressBar" class="tiny-green progressBar" style="display: none;"><div></div></div>
			</div>
			<div class="uwp-bs-modal-footer">
				<div class="uwp-<?php echo esc_attr( $type ); ?>-crop-p-wrap">
					<div id="<?php echo esc_attr( $type ); ?>-crop-actions">
						<form class="uwp-crop-form" method="post">
                            <input type="hidden" name="uwp_reset_nonce" id="uwp_reset_nonce" value="<?php echo esc_attr( wp_create_nonce( 'uwp_reset_nonce_'.$type ) ); ?>">
							<input type="submit" name="uwp_<?php echo esc_attr( $type ); ?>_reset" value="<?php esc_html_e('Reset to Default', 'userswp'); ?>" class="button button-primary" id="reset_uwp_<?php echo esc_attr( $type ); ?>" />
							<input type="submit" name="uwp_<?php echo esc_attr( $type ); ?>_crop" disabled="disabled" value="<?php esc_html_e('Apply', 'userswp'); ?>" class="button button-primary" id="save_uwp_<?php echo esc_attr( $type ); ?>" />
						</form>
					</div>
				</div>
				<button type="button" data-type="<?php echo esc_attr( $type ); ?>" class="button uwp_modal_btn uwp-modal-close" data-<?php echo ( $aui_bs5 ? 'bs-' : '' ); ?>dismiss="modal"><?php esc_html_e( 'Cancel', 'userswp' ); ?></button>
			</div>
		</div>
	</div>
</div>