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
			_e( 'Change your profile photo', 'userswp' );
			$label = __( "Upload Avatar", "userswp" );
		} else {
			_e( 'Change your cover photo', 'userswp' );
			$label = __( "Upload Banner", "userswp" );
		}
		?>
	</h5>
</div>
<div class="modal-body text-center">
	<div id="uwp-bs-modal-notice"></div>
	<form id="uwp-upload-<?php echo $type; ?>-form" method="post" enctype="multipart/form-data">
		<?php
		echo aui()->input(array(
			'type'    =>  'hidden',
			'name'    =>  'uwp_upload_nonce',
			'no_wrap' =>  true,
			'value'   =>  wp_create_nonce( 'uwp-upload-nonce' ),
		));
		echo aui()->input(array(
			'type'    =>  'hidden',
			'name'    =>  'uwp_'.$type.'_submit',
			'no_wrap' =>  true,
			'value'   =>  '',
		));
		echo aui()->button(array(
			'type'       =>  'button',
			'class'      => 'btn btn-primary uwp_upload_button',
			'content'    => '<i class="fas fa-upload"></i>'.$label,
			'onclick'    => "document.getElementById('uwp_upload_".$type."').click();",
		));
		echo aui()->alert( array(
				'class'   => 'text-center text-center m-3 p-0 w-50 mx-auto',
				'type'    => 'info',
				'content' => sprintf( __( 'Note: Max upload image size: %s', 'userswp' ), $files->uwp_formatSizeUnits( $files->uwp_get_max_upload_size( $type ) ) )
			)
		);
		?>
		<div class="uwp_upload_field d-none">
            <?php
            echo aui()->input(array(
	            'type'       =>  'file',
	            'id'         =>  'uwp_upload_'.$type,
	            'name'       =>  'uwp_'.$type.'_file',
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
	echo aui()->button(array(
		'type'       =>  'button',
		'class'      => 'btn btn-outline-primary uwp_modal_btn uwp-modal-close',
		'content'    => __( 'Cancel', 'userswp' ),
		'extra_attributes'  => array('data-type'=>$type, 'data-dismiss'=>"modal")
	));
	?>
	<div class="uwp-<?php echo $type; ?>-crop-p-wrap">
		<div id="<?php echo $type; ?>-crop-actions">
			<form class="uwp-crop-form" method="post">
				<?php
				echo aui()->input( array(
					'type'    => 'hidden',
					'name'    => 'uwp_reset_nonce',
					'id'      => 'uwp_reset_nonce',
					'value'   => wp_create_nonce( 'uwp_reset_nonce_'.$type ),
					'no_wrap' => true,
				) );
				echo aui()->button(array(
					'type'       => 'submit',
					'id'         => 'reset_uwp_'.$type,
					'name'       => 'uwp_'.$type.'_reset',
					'class'      => 'btn btn-primary btn-danger',
					'content'    => __( 'Reset to Default', 'userswp' ),
				));
				echo aui()->button(array(
					'type'       =>  'submit',
					'id'         =>  'save_uwp_'.$type,
					'name'       =>  'uwp_'.$type.'_crop',
					'class'      =>  'btn btn-primary',
					'content'    =>  __( 'Apply', 'userswp' ),
					'extra_attributes'  => array('disabled'=>'disabled')
				));
				?>
			</form>
		</div>
	</div>
</div>