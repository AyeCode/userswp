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

$link = get_the_permalink();
if('publish' != $post->post_status){
	$link = '#';
}

?>
<div class="col mb-4">
	<div class="card h-100">

		<div class="embed-responsive embed-responsive-16by9">
				<img alt="" class="card-img-top embed-responsive-item mw-100" src="<?php echo esc_url_raw( $thumb_url ); ?>" />
		</div>

		<div class="card-body">
			<h3 class="card-title h5">
				<?php

				echo aui()->button(array(
					'type'  =>  'a',
					'class'  =>  '',
					'href'       => $link,
					'content'    => get_the_title(),
				));
				?>
			</h3>
			<div class="uwp-profile-item-summary card-text">
				<?php
				do_action( 'uwp_before_profile_summary', get_the_ID(), $post->post_author, $post->post_type );
				echo $excerpt = esc_attr( strip_shortcodes( wp_trim_words( get_the_excerpt(), 25, '...' ) ) );
				do_action( 'uwp_after_profile_summary', get_the_ID(), $post->post_author, $post->post_type );
				?>
			</div>
		</div>

		<div class="card-footer text-muted">
			<?php
			$footer_html = '<div class="row">';
				$footer_html .= '<div class="col">';
					$footer_html .= apply_filters('uwp_tp_posts_post_footer_left', '<time class="uwp-profile-item-time published timeago" datetime="'.get_the_time( 'c' ).'">'.get_the_date().'</time>'); // time
				$footer_html .= '</div>';

				$footer_html .= '<div class="col text-right">';
					$footer_html .= apply_filters('uwp_tp_posts_post_footer_right', aui()->button( array(
						'href'  => get_the_permalink($post->ID),
						'class'     => 'btn btn-outline-primary  btn-sm',
						'content' => __('View', 'userswp'),
					) ));
				$footer_html .= '</div>';
			$footer_html .= '</div>';

			echo apply_filters('uwp_tp_posts_post_footer', $footer_html);
			?>
		</div>

	</div>
</div>
