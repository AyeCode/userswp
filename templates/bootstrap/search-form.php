<?php
$keyword = "";
if ( isset( $_GET['uwps'] ) && $_GET['uwps'] != '' ) {
	$keyword = esc_attr( apply_filters( 'get_search_query', $_GET['uwps'] ) );
}

?>
<form class="uwp-user-search-form d-flex flex-row flex-wrap align-items-start" method="get"
      action="<?php echo uwp_get_page_link( 'users_page' ); ?>">
    <div class="form-group mb-2 mr-md-2">
        <label for="uwp-search-input" class="sr-only"><?php _e( 'Search for users...', 'userswp' ); ?></label>
		<?php
		echo aui()->input( array(
			'type'        => 'search',
			'id'          => 'uwp-search-input',
			'name'        => 'uwps',
			'class'       => 'form-control-sm',
			'value'       => $keyword,
			'label'       => __( 'Search for users...', 'userswp' ),
			'placeholder' => __( 'Search for users...', 'userswp' ),
		) );
		?>
    </div>
	<?php if ( ! empty( $_GET['uwp_sort_by'] ) ) {

		echo aui()->input( array(
			'type'  => 'hidden',
			'name'  => 'uwp_sort_by',
			'value' => esc_attr__( $_GET['uwp_sort_by'] ),
		) );
	}

	echo aui()->button( array(
		'type'    => 'submit ',
		'href'    => uwp_get_login_page_url(),
		'class'   => 'btn btn-sm btn-outline-primary mb-2 uwp-search-submit',
		'content' => __( 'Search', 'userswp' ),
	) );

	do_action( 'uwp_after_search_button' ); ?>
</form>