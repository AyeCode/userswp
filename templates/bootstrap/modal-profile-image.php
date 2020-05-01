<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$files = new UsersWP_Files();
$type  = isset( $_POST['type'] ) && $_POST['type'] == 'avatar' ? 'avatar' : 'banner';
?>
<div class="modal-header" xmlns="http://www.w3.org/1999/html">
	<h5 class="modal-title" id="uwp-profile-modal-title">
		<?php
		if ( $type == 'avatar' ) {
			echo __( 'Change your profile photo', 'userswp' );
		} else {
			echo __( 'Change your cover photo', 'userswp' );
		}
		?>
	</h5>
</div>
<div class="modal-body text-center">
	<div id="uwp-bs-modal-notice"></div>
	<form id="uwp-upload-<?php echo $type; ?>-form" method="post" enctype="multipart/form-data">
		<input type="hidden" name="uwp_upload_nonce" value="<?php echo wp_create_nonce( 'uwp-upload-nonce' ); ?>"/>
		<input type="hidden" name="uwp_<?php echo $type; ?>_submit" value=""/>
		<button type="button" class="btn btn-primary uwp_upload_button"
		        onclick="document.getElementById('uwp_upload_<?php echo $type; ?>').click();"><i
				class="fas fa-upload"></i> <?php echo __( 'Upload', 'userswp' ); ?> <?php echo $type; ?></button>
		<?php
		echo aui()->alert( array(
				'class'   => 'text-center text-center m-3 p-0 w-50 mx-auto',
				'type'    => 'info',
				'content' => sprintf( __( 'Note: Max upload image size: %s', 'userswp' ), $files->uwp_formatSizeUnits( $files->uwp_get_max_upload_size( $type ) ) )
			)
		);
		?>
		<div class="uwp_upload_field d-none">
			<input name="uwp_<?php echo $type; ?>_file" id="uwp_upload_<?php echo $type; ?>" required="required"
			       type="file" value="">
		</div>
	</form>
	<div id="progressBar" class="progress " style="display: none;">
		<div class="progress-bar bg-success" role="progressbar" style="width: 0" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
</div>

<div class="modal-footer">
	<button type="button" data-type="<?php echo $type; ?>" class="btn btn-outline-primary uwp_modal_btn uwp-modal-close"
	        data-dismiss="modal"><?php echo __( 'Cancel', 'userswp' ); ?></button>
	<div class="uwp-<?php echo $type; ?>-crop-p-wrap">
		<div id="<?php echo $type; ?>-crop-actions">
			<form class="uwp-crop-form" method="post">
				<button type="submit" name="uwp_<?php echo $type; ?>_crop" disabled="disabled" class="btn btn-primary"
				        id="save_uwp_<?php echo $type; ?>"><?php echo __( 'Apply', 'userswp' ); ?></button>
			</form>
		</div>
	</div>
</div>