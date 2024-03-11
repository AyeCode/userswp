<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $aui_bs5;

$type  = isset( $_POST['uwp_popup_type'] ) && $_POST['uwp_popup_type'] == 'avatar' ? 'avatar' : 'banner';
$image_url = !empty($args['image_url']) ? esc_url( $args['image_url'] ) : '';
?>
<div class="modal-header" xmlns="http://www.w3.org/1999/html">
	<h5 class="modal-title" id="uwp-profile-modal-title">
		<?php
		if ($type == 'avatar') {
			esc_html_e( 'Change your profile photo', 'userswp' );
		} else {
			esc_html_e( 'Change your cover photo', 'userswp' );
		}
		?>
	</h5>
</div>
<div class="modal-body text-center">
	<div id="uwp-bs-modal-notice"></div>
	<div align="center">
		<img src="<?php echo esc_url( $image_url ); ?>" id="uwp-<?php echo esc_attr( $type ); ?>-to-crop" />
	</div>
</div>

<div class="modal-footer">
	<button type="button" data-type="<?php echo esc_attr( $type ); ?>" class="btn btn-outline-primary uwp_modal_btn uwp-modal-close" data-<?php echo ( $aui_bs5 ? 'bs-' : '' ); ?>dismiss="modal"><?php esc_html_e( 'Cancel', 'userswp' ); ?></button>
	<div class="uwp-<?php echo esc_attr( $type ); ?>-crop-p-wrap">
		<div id="<?php echo esc_attr( $type ); ?>-crop-actions">
			<form class="uwp-crop-form" method="post">
				<?php
                echo aui()->input(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                'type'  =>  'hidden',
	                'id'    =>  esc_html( $type.'-x' ),
	                'name'  =>  'x',
	                'value' =>  '',
	                'no_wrap' => true,
                ));
                echo aui()->input(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                'type'  =>  'hidden',
	                'id'    =>  esc_html( $type.'-y' ),
	                'name'  =>  'y',
	                'value' =>  '',
	                'no_wrap' => true,
                ));
                echo aui()->input(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                'type'  =>  'hidden',
	                'id'    =>  esc_html( $type.'-w' ),
	                'name'  =>  'w',
	                'value' =>  '',
	                'no_wrap' => true,
                ));
                echo aui()->input(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                'type'  =>  'hidden',
	                'id'    =>  esc_html( $type.'-h' ),
	                'name'  =>  'h',
	                'value' =>  '',
	                'no_wrap' => true,
                ));
                echo aui()->input(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                'type'  =>  'hidden',
	                'id'    =>  esc_html( 'uwp-'.$type.'-crop-image' ),
	                'name'  =>  'uwp_crop',
	                'value' =>  esc_attr( $image_url ),
	                'no_wrap' => true,
                ));
                echo aui()->input(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                'type'  =>  'hidden',
	                'name'  =>  'uwp_crop_nonce',
	                'value' => esc_html( wp_create_nonce( 'uwp_crop_nonce_'.$type ) ),
	                'no_wrap' => true,
                ));
                echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                'type'       =>  'submit',
	                'id'         => esc_html( 'save_uwp_'.$type ),
	                'content'    => esc_html__( 'Apply', 'userswp' ),
	                'name'       => esc_html( 'uwp_'.$type.'_crop' ),
                ));
                ?>
			</form>
		</div>
	</div>
</div>
