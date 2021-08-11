<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$type  = isset( $_POST['uwp_popup_type'] ) && $_POST['uwp_popup_type'] == 'avatar' ? 'avatar' : 'banner';
$image_url = !empty($args['image_url']) ? esc_url( $args['image_url'] ) : '';
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
				<?php
                echo aui()->input(array(
	                'type'  =>  'hidden',
	                'id'    =>  $type.'-x',
	                'name'  =>  'x',
	                'value' =>  '',
	                'no_wrap' => true,
                ));
                echo aui()->input(array(
	                'type'  =>  'hidden',
	                'id'    =>  $type.'-y',
	                'name'  =>  'y',
	                'value' =>  '',
	                'no_wrap' => true,
                ));
                echo aui()->input(array(
	                'type'  =>  'hidden',
	                'id'    =>  $type.'-w',
	                'name'  =>  'w',
	                'value' =>  '',
	                'no_wrap' => true,
                ));
                echo aui()->input(array(
	                'type'  =>  'hidden',
	                'id'    =>  $type.'-h',
	                'name'  =>  'h',
	                'value' =>  '',
	                'no_wrap' => true,
                ));
                echo aui()->input(array(
	                'type'  =>  'hidden',
	                'id'    =>  'uwp-'.$type.'-crop-image',
	                'name'  =>  'uwp_crop',
	                'value' =>  $image_url,
	                'no_wrap' => true,
                ));
                echo aui()->input(array(
	                'type'  =>  'hidden',
	                'name'  =>  'uwp_crop_nonce',
	                'value' =>  wp_create_nonce( 'uwp_crop_nonce_'.$type ),
	                'no_wrap' => true,
                ));
                echo aui()->button(array(
	                'type'       =>  'submit',
	                'id'         =>  'save_uwp_'.$type,
	                'content'    => __( 'Apply', 'userswp' ),
	                'name'       => 'uwp_'.$type.'_crop',
                ));
                ?>
			</form>
		</div>
	</div>
</div>
