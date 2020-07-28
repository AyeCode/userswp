<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$type  = isset( $_POST['uwp_popup_type'] ) && $_POST['uwp_popup_type'] == 'avatar' ? 'avatar' : 'banner';
$image_url = !empty($args['image_url']) ? esc_attr( $args['image_url'] ) : '';
?>
<div class="modal-header" xmlns="http://www.w3.org/1999/html">
	<h5 class="modal-title" id="uwp-profile-modal-title">
		<?php
		if ($type == 'avatar') {
			_e( 'Change your profile photo', 'userswp' );
		} else {
			_e( 'Change your cover photo', 'userswp' );
		}
		?>
	</h5>
</div>
<div class="modal-body text-center">
	<div id="uwp-bs-modal-notice"></div>
	<div align="center">
		<img src="<?php echo $image_url; ?>" id="uwp-<?php echo $type; ?>-to-crop" />
	</div>
</div>

<div class="modal-footer">
	<button type="button" data-type="<?php echo $type; ?>" class="btn btn-outline-primary uwp_modal_btn uwp-modal-close" data-dismiss="modal"><?php _e( 'Cancel', 'userswp' ); ?></button>
	<div class="uwp-<?php echo $type; ?>-crop-p-wrap">
		<div id="<?php echo $type; ?>-crop-actions">
			<form class="uwp-crop-form" method="post">
				<input type="hidden" name="x" value="" id="<?php echo $type; ?>-x" />
				<input type="hidden" name="y" value="" id="<?php echo $type; ?>-y" />
				<input type="hidden" name="w" value="" id="<?php echo $type; ?>-w" />
				<input type="hidden" name="h" value="" id="<?php echo $type; ?>-h" />
				<input type="hidden" id="uwp-<?php echo $type; ?>-crop-image" name="uwp_crop" value="<?php echo $image_url; ?>" />
				<input type="hidden" name="uwp_crop_nonce" value="<?php echo wp_create_nonce( 'uwp-crop-nonce' ); ?>" />
				<button type="submit" name="uwp_<?php echo $type; ?>_crop" class="btn btn-primary" id="save_uwp_<?php echo $type; ?>" ><?php _e('Apply', 'userswp'); ?></button>
			</form>
		</div>
	</div>
</div>
