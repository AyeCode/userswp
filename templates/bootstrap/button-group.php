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
		$icon_class = !empty($button->field_icon) ? $button->field_icon : 'fas fa-link';
		$button_class = !empty($button->css_class) ? " " . $button->css_class : 'btn-secondary';
		$button_class .= " ml-1 mb-1 border-0 btn ".$css_class;
		$button_url = !empty($button->url) ? $button->url : '';
		$tooltip_text = !empty($button->site_title) ? $button->site_title : '';

		echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'type'  =>  'a',
			'href'       => $button_url ? esc_url( $button_url ) : '#',
			'class'      => esc_attr( $button_class ),
			'title'      => esc_attr( $tooltip_text ),
			'icon'       => esc_attr( $icon_class.' fa-fw fa-lg' ),
			'style'  => '',
			'new_window'  => true,
			'extra_attributes'  => array('data-toggle'=>'tooltip')
		));
		?>
		<?php
	}
	?>
</div>
<?php do_action( 'uwp_template_after', 'button-group' ); ?>