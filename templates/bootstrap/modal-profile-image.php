<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $aui_bs5;

$files = new UsersWP_Files();
$type  = isset( $_POST['type'] ) && $_POST['type'] == 'avatar' ? 'avatar' : 'banner';
?>
<div class="modal-header" xmlns="http://www.w3.org/1999/html">
	<h5 class="modal-title" id="uwp-profile-modal-title">
		<?php
		if ( $type == 'avatar' ) {
			esc_html_e( 'Change your profile photo', 'userswp' );
			$label = __( "Upload Avatar", "userswp" );
		} else {
			esc_html_e( 'Change your cover photo', 'userswp' );
			$label = __( "Upload Banner", "userswp" );
		}
		?>
	</h5>
</div>
<div class="modal-body text-center">
	<div id="uwp-bs-modal-notice"></div>
	<form id="uwp-upload-<?php echo esc_attr( $type ); ?>-form" method="post" enctype="multipart/form-data">
		<?php
		echo aui()->input(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'type'    =>  'hidden',
			'name'    =>  'uwp_upload_nonce',
			'no_wrap' =>  true,
			'value'   => esc_html( wp_create_nonce( 'uwp-upload-nonce' ) ),
		));
		echo aui()->input(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'type'    =>  'hidden',
			'name'    =>  esc_html( 'uwp_'.$type.'_submit' ),
			'no_wrap' =>  true,
			'value'   =>  '',
		));
		echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'type'       =>  'button',
			'class'      => 'btn btn-primary uwp_upload_button',
			'content'    => '<i class="fas fa-upload"></i>'. esc_html( $label ),
			'onclick'    => "document.getElementById('uwp_upload_".esc_js( $type )."').click();",
		));
		echo aui()->alert( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'class'   => 'text-center text-center m-3 p-0 w-50 mx-auto',
				'type'    => 'info',
				'content' => esc_html( sprintf( __( 'Note: Max upload image size: %s', 'userswp' ), $files->uwp_formatSizeUnits( $files->uwp_get_max_upload_size( $type ) ) ) )
			)
		);
		?>
		<div class="uwp_upload_field d-none">
            <?php
            echo aui()->input(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	            'type'       =>  'file',
	            'id'         =>  esc_html( 'uwp_upload_'.$type ),
	            'name'       =>  esc_html( 'uwp_'.$type.'_file' ),
	            'extra_attributes'  => array('required'=>'required')
            ));
            ?>
		</div>
	</form>
	<div id="progressBar" class="progress progressBar d-none">
		<div class="progress-bar bg-success w-0" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
</div>

<div class="modal-footer">
	<?php
	echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'type'       =>  'button',
		'class'      => 'btn btn-outline-primary uwp_modal_btn uwp-modal-close',
		'content'    => esc_html__( 'Cancel', 'userswp' ),
		'extra_attributes'  => array('data-type'=> esc_html( $type ), 'data-' . ( $aui_bs5 ? 'bs-' : '' ) . 'dismiss'=>"modal")
	));
	?>
	<div class="uwp-<?php echo esc_attr( $type ); ?>-crop-p-wrap">
		<div id="<?php echo esc_attr( $type ); ?>-crop-actions">
			<form class="uwp-crop-form" method="post">
				<?php
				echo aui()->input( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'type'    => 'hidden',
					'name'    => 'uwp_reset_nonce',
					'id'      => 'uwp_reset_nonce',
					'value'   => esc_html( wp_create_nonce( 'uwp_reset_nonce_'.$type ) ),
					'no_wrap' => true,
				) );
				echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'type'       => 'submit',
					'id'         => esc_html( 'reset_uwp_'.$type ),
					'name'       => esc_html( 'uwp_'.$type.'_reset' ),
					'class'      => 'btn btn-primary btn-danger',
					'content'    => esc_html__( 'Reset to Default', 'userswp' ),
				));
				echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'type'       =>  'submit',
					'id'         =>  esc_html( 'save_uwp_'.$type ),
					'name'       =>  esc_html( 'uwp_'.$type.'_crop' ),
					'class'      =>  'btn btn-primary',
					'content'    =>  esc_html__( 'Apply', 'userswp' ),
					'extra_attributes'  => array('disabled'=>'disabled')
				));
				?>
			</form>
		</div>
	</div>
</div>