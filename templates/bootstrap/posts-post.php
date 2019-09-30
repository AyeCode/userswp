<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $post;
$user = uwp_get_displayed_user();
if ( has_post_thumbnail() ) {
	$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
} else {
	$thumb_url = uwp_get_default_thumb_uri();
}
?>
<div class="col mb-4">
	<div class="card h-100">

		<div class="embed-responsive embed-responsive-16by9">
				<img alt="" class="card-img-top embed-responsive-item" src="<?php echo esc_url_raw( $thumb_url ); ?>" />
		</div>

		<div class="card-body">
			<h3 class="card-title h5">
				<a href="<?php echo esc_url_raw( get_the_permalink() ); ?>"><?php echo get_the_title(); ?></a>
			</h3>
			<div class="uwp-profile-item-summary card-text">
				<?php
				do_action( 'uwp_before_profile_summary', get_the_ID(), $post->post_author, $post->post_type );
				$excerpt = strip_shortcodes( wp_trim_words( get_the_excerpt(), 15, '...' ) );
				echo $excerpt;
				do_action( 'uwp_after_profile_summary', get_the_ID(), $post->post_author, $post->post_type );
				?>
			</div>
		</div>

		<div class="card-footer">
			<time class="uwp-profile-item-time published" datetime="<?php echo get_the_time( 'c' ); ?>">
				<?php echo get_the_date(); ?>
			</time>
		</div>

	</div>
</div>
