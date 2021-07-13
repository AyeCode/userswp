<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $post;
$user = uwp_get_displayed_user();
?>
<li class="uwp-profile-item-li uwp-profile-item-clearfix">
	<div class="uwp_generic_thumb_wrap">
		<a class="uwp-profile-item-img" href="<?php echo esc_url_raw( get_the_permalink() ); ?>">
			<?php
			if ( has_post_thumbnail() ) {
				$thumb_url = get_the_post_thumbnail_url( get_the_ID(), array( 80, 80 ) );
			} else {
				$thumb_url = uwp_get_default_thumb_uri();
			}
			?>
			<img class="uwp-profile-item-alignleft uwp-profile-item-thumb"
			     src="<?php echo esc_url_raw( $thumb_url ); ?>">
		</a>
	</div>

	<h3 class="uwp-profile-item-title">
		<a href="<?php echo esc_url_raw( get_the_permalink() ); ?>"><?php echo get_the_title(); ?></a>
	</h3>
	<time class="uwp-profile-item-time published" datetime="<?php echo get_the_time( 'c' ); ?>">
		<?php echo get_the_date(); ?>
	</time>
	<div class="uwp-profile-item-summary">
		<?php
		do_action( 'uwp_before_profile_summary', get_the_ID(), $post->post_author, $post->post_type );
		$excerpt = strip_shortcodes( wp_trim_words( get_the_excerpt(), 15, '...' ) );
		echo esc_attr( $excerpt );
		do_action( 'uwp_after_profile_summary', get_the_ID(), $post->post_author, $post->post_type );
		?>
	</div>
</li>