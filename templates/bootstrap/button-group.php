<?php
/**
 * Button Group template (default)
 * 
 * @ver 1.0.0
 */
echo '@@@';
global $uwp_widget_args;
$css_class = !empty($uwp_widget_args['css_class']) ? esc_attr( $uwp_widget_args['css_class'] ) : 'border-0';
$buttons = $uwp_widget_args['buttons'];
do_action( 'uwp_template_before', 'login' ); ?>
<div class="button-group">
	<?php
//	print_r( $buttons );
	foreach($buttons as $button){
		$icon_class = !empty($button->field_icon) ? esc_attr($button->field_icon) : 'fas fa-link';
		$icon_class .= !empty($button->css_class) ? " ".esc_attr($button->css_class) : '';
		?>
		<a href="#" class="iconbox iconsmall fill rounded-circle bg-primary text-white shadow border-0">
			<i class="<?php echo $icon_class;?>"></i>
		</a>
		<?php

	}
	?>

</div>
<?php do_action( 'uwp_template_after', 'login' ); ?>