<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$the_query = isset( $args['template_args']['the_query'] ) ? $args['template_args']['the_query'] : '';
$title = isset( $args['template_args']['title'] ) ? $args['template_args']['title'] : '';
$found_posts = ! empty( $the_query ) && ! empty( $the_query->found_posts ) ? $the_query->found_posts : 0;
?>
<div class="container mb-1">
	<div class="row">
		<div class="col-sm p-0 uwp-loop-posts-title">
			<h3><?php echo esc_html( $title ); ?></h3>
		</div>
		<?php if ( $found_posts ) { ?>
		<div class="col p-0 d-none d-sm-block uwp-loop-posts-toolbar">
			<div class="btn-toolbar justify-content-end" role="toolbar" aria-label="Toolbar with button groups">
				<div class="btn-group btn-group-sm uwp-list-view-select" role="group" aria-label="First group">
					<button type="button" class="btn btn-outline-primary active uwp-list-view-select-list" onclick="uwp_list_view_select(0);"><i class="fas fa-th-list"></i></button>
					<button type="button" class="btn btn-outline-primary uwp-list-view-select-grid" onclick="uwp_list_view_select(3);"><i class="fas fa-th"></i></button>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<?php do_action( 'uwp_profile_posts_loop_wrap_start', $args, $found_posts ); ?>
<div class="uwp-profile-cpt-loop">
	<?php
	do_action( 'uwp_profile_posts_loop_before', $args, $found_posts );

	if ( $found_posts ) {
		// The Loop
		if ( $the_query && $the_query->have_posts() ) {
			echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">';

			while ( $the_query->have_posts() ) {
				$the_query->the_post();

				uwp_get_template( 'bootstrap/posts-post.php', $args );
			}

			echo '</div>';

			/* Restore original Post Data */
			wp_reset_postdata();
		}

		do_action( 'uwp_profile_pagination', $the_query->max_num_pages );
	} else {
		$no_items_message = '<span class="alert alert-info d-block uwp-no-items">' . esc_html( __( 'No items found.', 'userswp' ) ) . '</span>';

		echo apply_filters( 'uwp_profile_posts_loop_no_items', $no_items_message, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	do_action( 'uwp_profile_posts_loop_after', $args, $found_posts );
	?>
</div>
<?php do_action( 'uwp_profile_posts_loop_wrap_end', $args, $found_posts ); ?>