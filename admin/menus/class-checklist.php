<?php
/**
 * Create a set of Users WP specific links for use in the Menus admin UI.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    userswp
 * @subpackage userswp/admin/menus
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Create a set of Users WP specific links for use in the Menus admin UI.
 *
 * Borrowed heavily from {@link Walker_Nav_Menu_Checklist}, but modified so as not
 * to require an actual post type or taxonomy, and to force certain CSS classes.
 *
 * @since 1.0.0
 */
class UsersWP_Walker_Nav_Menu_Checklist extends Walker_Nav_Menu {

    /**
     * Constructor.
     *
     * @see Walker_Nav_Menu::__construct() for a description of parameters.
     *
     * @param array|bool $fields See {@link Walker_Nav_Menu::__construct()}.
     */
    public function __construct( $fields = false ) {
        if ( $fields ) {
            $this->db_fields = $fields;
        }
    }

    /**
     * Create the markup to start a tree level.
     *
     * @see Walker_Nav_Menu::start_lvl() for description of parameters.
     *
     * @param string $output See {@Walker_Nav_Menu::start_lvl()}.
     * @param int    $depth  See {@Walker_Nav_Menu::start_lvl()}.
     * @param array  $args   See {@Walker_Nav_Menu::start_lvl()}.
     */
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "\n$indent<ul class='children'>\n";
    }

    /**
     * Create the markup to end a tree level.
     *
     * @see Walker_Nav_Menu::end_lvl() for description of parameters.
     *
     * @param string $output See {@Walker_Nav_Menu::end_lvl()}.
     * @param int    $depth  See {@Walker_Nav_Menu::end_lvl()}.
     * @param array  $args   See {@Walker_Nav_Menu::end_lvl()}.
     */
    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "\n$indent</ul>";
    }

    /**
     * Create the markup to start an element.
     *
     * @see Walker::start_el() for description of parameters.
     *
     * @param string       $output Passed by reference. Used to append additional
     *                             content.
     * @param object       $item   Menu item data object.
     * @param int          $depth  Depth of menu item. Used for padding.
     * @param object|array $args   See {@Walker::start_el()}.
     * @param int          $id     See {@Walker::start_el()}.
     */
    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
//        print_r($item);
//        print_r($args);
        global $_nav_menu_placeholder;

        $_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;
        $possible_object_id = isset( $item->post_type ) && 'nav_menu_item' == $item->post_type ? $item->object_id : $_nav_menu_placeholder;
        $possible_db_id = ( ! empty( $item->ID ) ) && ( 0 < $possible_object_id ) ? (int) $item->ID : 0;

        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $output .= $indent . '<li>';
        $output .= '<label class="menu-item-title">';
        $output .= '<input type="checkbox" class="menu-item-checkbox';

        if ( property_exists( $item, 'label' ) ) {
            $title = $item->label;
        }

        $output .= '" name="menu-item[' . $possible_object_id . '][menu-item-object-id]" value="'. esc_attr( $item->object_id ) .'" /> ';
        $output .= isset( $title ) ? esc_html( $title ) : esc_html( $item->title );
        $output .= '</label>';

        if ( empty( $item->url ) ) {
            $item->url = $item->guid;
        }

        if ( ! in_array( array( 'users-wp-menu', 'users-wp-'. $item->post_excerpt .'-nav' ), $item->classes ) ) {
            $item->classes[] = 'users-wp-menu';
            $item->classes[] = 'users-wp-'. $item->post_excerpt .'-nav';
        }

        // maybe add a specific lightbox class
        if ( isset($item->lightbox_class) && ! in_array( $item->lightbox_class , $item->classes ) ) {
            $item->classes[] = $item->lightbox_class;
        }

        // Menu item hidden fields.
        $output .= '<input type="hidden" class="menu-item-db-id" name="menu-item[' . $possible_object_id . '][menu-item-db-id]" value="' . $possible_db_id . '" />';
        $output .= '<input type="hidden" class="menu-item-object" name="menu-item[' . $possible_object_id . '][menu-item-object]" value="'. esc_attr( $item->object ) .'" />';
        $output .= '<input type="hidden" class="menu-item-parent-id" name="menu-item[' . $possible_object_id . '][menu-item-parent-id]" value="'. esc_attr( $item->menu_item_parent ) .'" />';
        $output .= '<input type="hidden" class="menu-item-type" name="menu-item[' . $possible_object_id . '][menu-item-type]" value="custom" />';
        $output .= '<input type="hidden" class="menu-item-title" name="menu-item[' . $possible_object_id . '][menu-item-title]" value="'. esc_attr( $item->title ) .'" />';
        $output .= '<input type="hidden" class="menu-item-url" name="menu-item[' . $possible_object_id . '][menu-item-url]" value="'. esc_attr( $item->url ) .'" />';
        $output .= '<input type="hidden" class="menu-item-target" name="menu-item[' . $possible_object_id . '][menu-item-target]" value="'. esc_attr( $item->target ) .'" />';
        $output .= '<input type="hidden" class="menu-item-attr_title" name="menu-item[' . $possible_object_id . '][menu-item-attr_title]" value="'. esc_attr( $item->attr_title ) .'" />';
        $output .= '<input type="hidden" class="menu-item-classes" name="menu-item[' . $possible_object_id . '][menu-item-classes]" value="'. esc_attr( implode( ' ', $item->classes ) ) .'" />';
        $output .= '<input type="hidden" class="menu-item-xfn" name="menu-item[' . $possible_object_id . '][menu-item-xfn]" value="'. esc_attr( $item->xfn ) .'" />';
    }
}
