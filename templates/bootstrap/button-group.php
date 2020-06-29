<?php
/**
 * Button Group template (default)
 * 
 * @ver 1.0.0
 */
$css_class = !empty($args['css_class']) ? esc_attr( $args['css_class'] ) : 'border-0';
$buttons = $args['buttons'];
do_action( 'uwp_template_before', 'button-group' ); ?>
<div class="bsui-button-group">
	<?php
	foreach($buttons as $button){
		$icon_class = !empty($button->field_icon) ? esc_attr($button->field_icon) : 'fas fa-link';
		$button_class = !empty($button->css_class) ? " ".esc_attr($button->css_class) : 'btn-secondary';
		$button_class .= " ".$css_class;
		$button_url = !empty($button->url) ? esc_url($button->url) : '#';
		$tooltip_text = !empty($button->site_title) ? esc_attr($button->site_title) : '';

		?>
		<a href="<?php echo $button_url;?>" class="ml-1 mb-1 border-0 btn <?php echo $button_class;?>" data-toggle="tooltip" title="<?php echo $tooltip_text;?>">
			<i class="<?php echo $icon_class;?>  fa-fw fa-lg"></i>
		</a>
		<?php
	}
	?>
</div>
<?php do_action( 'uwp_template_after', 'button-group' ); ?>