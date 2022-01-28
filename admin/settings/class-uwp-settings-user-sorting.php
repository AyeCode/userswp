<?php
/**
 * UsersWP Tabs in form builder
 *
 * @author      AyeCode
 * @category    Admin
 * @package     userswp/Admin
 * @version     1.0.24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'UsersWP_Settings_User_Sorting', false ) ) {

	/**
	 * UsersWP_Settings_Email.
	 */
	class UsersWP_Settings_User_Sorting {

		public function __construct() {

			add_filter( 'uwp_form_builder_tabs_array', array( $this, 'form_builder_tab_items' ), 99 );
			add_filter( 'uwp_form_builder_available_fields_head', array( $this, 'available_fields_head' ), 10, 2 );
			add_filter( 'uwp_form_builder_available_fields_note', array( $this, 'available_fields_note' ), 10, 2 );
			add_filter( 'uwp_form_builder_selected_fields_head', array( $this, 'selected_fields_head' ), 10, 2 );
			add_filter( 'uwp_form_builder_selected_fields_note', array( $this, 'selected_fields_note' ), 10, 2 );
			add_action( 'uwp_manage_available_fields', array( $this, 'manage_available_fields' ), 10, 1 );
			add_action( 'uwp_manage_selected_fields', array( $this, 'manage_selected_fields' ), 10, 1 );
			add_action( 'wp_ajax_uwp_ajax_user_sorting_action', array( $this, 'ajax_handler' ) );
			add_action( 'uwp_add_custom_sort_options', array( $this, 'add_custom_sort_options' ) );
			add_action( 'pre_user_query', array( $this, 'pre_user_query' ), 99 );

		}

		public function pre_user_query( $vars ) {

			global $wpdb;

			if ( isset( $vars->query_vars['orderby'] ) && 'uwp_meta_value' == $vars->query_vars['orderby'] && is_uwp_users_page() ) {

				$sort_by = $meta_key = '';
				$order   = 'ASC';
				if ( isset( $_GET['uwp_sort_by'] ) && $_GET['uwp_sort_by'] != '' ) {
					$sort_by = strip_tags( esc_sql( $_GET['uwp_sort_by'] ) );
				}

				if ( empty( $sort_by ) ) {
					$sort_by = uwp_get_default_sort();
				}

				if ( $sort_by ) {

					if ( substr( strtolower( $sort_by ), - 5 ) == '_desc' ) {
						$order    = 'DESC';
						$meta_key = substr( $sort_by, 0, strlen( $sort_by ) - 5 );
						$order_by = 'meta_value';
					} else if ( substr( strtolower( $sort_by ), - 4 ) == '_asc' ) {
						$order    = 'ASC';
						$meta_key = substr( $sort_by, 0, strlen( $sort_by ) - 4 );
						$order_by = 'meta_value';
					}

					if ( ! empty( $order_by ) && $meta_key ) {
						$meta_table_name = uwp_get_table_prefix() . 'uwp_usermeta';
						$table_name      = uwp_get_table_prefix() . 'uwp_user_sorting';

						$vars->query_from    .= " INNER JOIN " . $meta_table_name . " ON (" . $meta_table_name . ".user_id = $wpdb->users.ID)  ";
						$vars->query_orderby = 'ORDER BY ' . $meta_key . ' ' . $order;

						$parent_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . $table_name . " WHERE htmlvar_name = %s AND sort = %s AND tab_parent = 0", $meta_key, $order ) );

						if ( $parent_id ) {
							$children = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE tab_parent = %d ORDER BY sort_order ASC", $parent_id ) );

							if ( $children ) {

								foreach ( $children as $child ) {
									if ( ! in_array( $child->field_type, array( 'newer', 'older' ) ) ) {
										$vars->query_orderby .= ' , ' . $child->htmlvar_name . ' ' . $order;
									}
								}
							}
						}
					}
				}
			}

			return $vars;
		}

		/**
		 * Add a tab to form builder
		 *
		 * @since       1.2.2.38
		 * @package     userswp
		 *
		 * @param       array $tabs Tabs
		 *
		 * @return      array   $tabs
		 */
		public function form_builder_tab_items( $tabs ) {
			$tabs['user-sorting'] = __( 'User Sorting', 'userswp' );

			return $tabs;

		}

		/**
		 * Add a tab to form builder
		 *
		 * @since       1.2.2.38
		 * @package     userswp
		 *
		 * @param       string $heading   Heading
		 * @param       string $form_type Form type
		 *
		 * @return      string
		 */
		public function available_fields_head( $heading, $form_type ) {
			switch ( $form_type ) {
				case 'user-sorting':
					$heading = __( 'Available sorting options for users listing and search results', 'userswp' );
					break;
			}

			return $heading;
		}

		/**
		 * Add a note above available fields.
		 *
		 * @since       1.2.2.38
		 * @package     userswp
		 *
		 * @param       string $note      Note to display
		 * @param       string $form_type Form type
		 *
		 * @return      string
		 */
		public function available_fields_note( $note, $form_type ) {
			switch ( $form_type ) {
				case 'user-sorting':
					$note = __( "Click on any box below to make it appear in the sorting option dropdown on users listing and search results. To make a field available here, go to account tab and expand any field from selected fields panel and tick the checkbox saying 'Include this field in sorting options'.", 'userswp' );
					break;
			}

			return $note;
		}

		/**
		 * Heading for the selected fields
		 *
		 * @since       1.2.2.38
		 * @package     userswp
		 *
		 * @param       string $heading   Heading to display
		 * @param       string $form_type Form type
		 *
		 * @return      string
		 */
		public function selected_fields_head( $heading, $form_type ) {
			switch ( $form_type ) {
				case 'user-sorting':
					$heading = __( 'List of fields that will appear in users listing and search results sorting option dropdown box.', 'userswp' );
					break;
			}

			return $heading;
		}

		/**
		 * Add a note above selected fields.
		 *
		 * @since       1.2.2.38
		 * @package     userswp
		 *
		 * @param       string $note      Note to display
		 * @param       string $form_type Form type
		 *
		 * @return      string
		 */
		public function selected_fields_note( $note, $form_type ) {
			switch ( $form_type ) {
				case 'user-sorting':
					$note = __( 'Click to expand and view field related settings. You may drag and drop to arrange fields order in sorting option dropdown box on users listing and search results page.', 'userswp' );
					break;
			}

			return $note;
		}

		/**
		 * Display available fields
		 *
		 * @since       1.2.2.38
		 * @package     userswp
		 *
		 * @param       string $form_type Form type
		 *
		 */
		public function manage_available_fields( $form_type ) {
			if ( 'user-sorting' == $form_type ) {
				$this->available_fields( $form_type );
			}
		}

		/**
		 * Display selected fields
		 *
		 * @since       1.2.2.38
		 * @package     userswp
		 *
		 * @param       string $form_type Form type
		 *
		 */
		public function manage_selected_fields( $form_type ) {
			if ( 'user-sorting' == $form_type ) {
				$this->selected_fields( $form_type );
			}
		}

		/**
		 * Display available sorting fields
		 *
		 * @since       1.2.2.38
		 * @package     userswp
		 *
		 * @param       string $form_type Form type
		 *
		 */
		public function available_fields( $form_type ) {
			global $wpdb;

			$fields = array();

			$fields['newer'] = array(
				'data_type'    => '',
				'field_type'   => 'newer',
				'site_title'   => __( 'Newer', 'userswp' ),
				'htmlvar_name' => 'newer',
				'field_icon'   => 'fas fa-calendar',
				'sort'         => 'asc',
				'description'  => __( 'Sort by new user registration', 'userswp' )
			);

			$fields['older'] = array(
				'data_type'    => '',
				'field_type'   => 'older',
				'site_title'   => __( 'Older', 'userswp' ),
				'htmlvar_name' => 'older',
				'field_icon'   => 'fas fa-calendar',
				'sort'         => 'desc',
				'description'  => __( 'Sort by new user registration in descending order', 'userswp' )
			);

			$fields['display_name'] = array(
				'data_type'    => '',
				'field_type'   => 'text',
				'site_title'   => __( 'Display Name', 'userswp' ),
				'htmlvar_name' => 'display_name',
				'field_icon'   => 'fas fa-sort-alpha-up',
				'sort'         => 'asc',
				'description'  => __( 'Sort alphabetically by display name in ascending order', 'userswp' )
			);

			$fields['first_name'] = array(
				'data_type'    => '',
				'field_type'   => 'text',
				'site_title'   => __( 'First Name', 'userswp' ),
				'htmlvar_name' => 'first_name',
				'field_icon'   => 'fas fa-sort-alpha-up',
				'sort'         => 'asc',
				'description'  => __( 'Sort alphabetically by first name in ascending order', 'userswp' )
			);

			$fields['last_name'] = array(
				'data_type'    => '',
				'field_type'   => 'text',
				'site_title'   => __( 'Last Name', 'userswp' ),
				'htmlvar_name' => 'last_name',
				'field_icon'   => 'fas fa-sort-alpha-up',
				'sort'         => 'asc',
				'description'  => __( 'Sort alphabetically by last name in ascending order', 'userswp' )
			);

			$fields = apply_filters( 'uwp_add_custom_sort_options', $fields );

			?>
            <input type="hidden" name="form_type" id="form_type" value="<?php echo esc_attr( $form_type ); ?>"/>
            <input type="hidden" name="manage_field_type" class="manage_field_type" value="user_sorting">
            <ul>
				<?php

				if ( ! empty( $fields ) ) {
					foreach ( $fields as $field ) {
						$field = stripslashes_deep( $field ); // strip slashes

						$icon = $field['field_icon'];
						if ( uwp_is_fa_icon( $icon ) ) {
							$field_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
						} elseif ( uwp_is_icon_url( $icon ) ) {
							$field_icon = '<b style="background-image: url("' . esc_url( $icon ) . '")"></b>';
						} else {
							$field_icon = '<i class="fas fa-sort" aria-hidden="true"></i>';
						}
						?>
                        <li>
                            <a id="uwp-<?php echo esc_attr( $field['htmlvar_name'] ); ?>"
                               class="uwp-draggable-form-items"
                               data-field_type="<?php echo isset( $field['field_type'] ) ? esc_attr( $field['field_type'] ) : ''; ?>"
                               data-site_title="<?php echo isset( $field['site_title'] ) ? esc_attr( $field['site_title'] ) : ''; ?>"
                               data-field_icon="<?php echo isset( $field['field_icon'] ) ? esc_attr( $field['field_icon'] ) : ''; ?>"
                               data-id="<?php echo isset( $field['htmlvar_name'] ) ? esc_attr( $field['htmlvar_name'] ) : ''; ?>"
                               data-data_type="<?php echo isset( $field['data_type'] ) ? esc_attr( $field['data_type'] ) : 'VARCHAR'; ?>"
                               data-tab_parent="<?php echo isset( $field['tab_parent'] ) ? esc_attr( $field['tab_parent'] ) : ''; ?>"
                               data-tab_level="<?php echo isset( $field['tab_content'] ) ? esc_attr( $field['tab_content'] ) : ''; ?>"
                               data-sort="<?php echo isset( $field['sort'] ) ? esc_attr( $field['sort'] ) : 'asc'; ?>"
                               href="javascript:void(0);">
								<?php echo $field_icon; ?>
								<?php echo esc_attr( $field['site_title'] ); ?>
                            </a>
                        </li>
						<?php
					}
				}
				?>
            </ul>
			<?php
		}

		/**
		 * Display selected tabs fields
		 *
		 * @since       2.0.0
		 * @package     userswp
		 *
		 * @param       string $form_type Form type
		 *
		 */
		public function selected_fields( $form_type ) {
			global $wpdb;
			$table_name = uwp_get_table_prefix() . 'uwp_user_sorting';
			?>
            <input type="hidden" name="form_type" id="form_type" value="<?php echo esc_attr( $form_type ); ?>"/>
            <input type="hidden" name="manage_field_type" class="manage_field_type" value="user_sorting">
            <ul class="uwp-profile-tabs-selected core uwp_form_extras">
				<?php
				$tabs = $wpdb->get_results( "SELECT * FROM  " . $table_name . " order by sort_order asc" );
				if ( ! empty( $tabs ) ) {
					foreach ( $tabs as $key => $tab ) {
						$field_ins_upd = 'display';

						if ( $tab->tab_level == '1' ) {
							continue;
						}

						ob_start();
						$this->field_adminhtml( $tab->id, $field_ins_upd );
						$tab_rendered = ob_get_clean();

						$tab_rendered = str_replace( "</li>", "", $tab_rendered );
						$child_tabs   = '';
						foreach ( $tabs as $child_tab ) {
							if ( $child_tab->tab_parent == $tab->id ) {
								ob_start();
								$this->field_adminhtml( $child_tab->id, $field_ins_upd );
								$child_tabs .= ob_get_clean();
							}
						}

						if ( $child_tabs ) {
							$tab_rendered .= "<ul>";
							$tab_rendered .= $child_tabs;
							$tab_rendered .= "</ul>";
						}

						echo $tab_rendered;
						echo "</li>";

						unset( $tabs[ $key ] );
					}
				} ?>
            </ul>
			<?php
		}

		/**
		 * Displays sorting field HTML
		 *
		 * @since       2.0.0
		 * @package     userswp
		 *
		 * @param       int    $field_id      Field ID
		 * @param       string $field_ins_upd Field action
		 * @param       array  $request       Request data
		 *
		 */
		public function field_adminhtml( $field_id, $field_ins_upd = '', $request = array() ) {
			global $wpdb;

			$table_name = uwp_get_table_prefix() . 'uwp_user_sorting';

			if ( ! is_object( $field_id ) && ( is_int( $field_id ) || ctype_digit( $field_id ) ) ) {
				$field = $wpdb->get_row( $wpdb->prepare( "select * from " . $table_name . " where id= %d", array( $field_id ) ) );
			} elseif ( is_object( $field_id ) ) {
				$field_id = $field_id->id;
				$field    = $wpdb->get_row( $wpdb->prepare( "select * from " . $table_name . " where id= %d", array( (int) $field_id->id ) ) );
			} elseif ( isset( $field_id ) && ! empty( $field_id ) ) {
				$field = $wpdb->get_row( $wpdb->prepare( "select * from " . $table_name . " where htmlvar_name= %s", array( $field_id ) ) );
			} else {
				$field = array();
			}

			if ( isset( $request['site_title'] ) ) {
				$site_title = esc_attr( $request['site_title'] );
			} elseif ( $field && isset( $field->site_title ) ) {
				$site_title = $field->site_title;
			} else {
				$site_title = '';
			}

			if ( isset( $request['htmlvar_name'] ) ) {
				$htmlvar_name = esc_attr( $request['htmlvar_name'] );
			} elseif ( $field && isset( $field->htmlvar_name ) ) {
				$htmlvar_name = $field->htmlvar_name;
			} else {
				$htmlvar_name = '';
			}

			if ( isset( $request['field_type'] ) ) {
				$field_type = esc_attr( $request['field_type'] );
			} elseif ( $field && isset( $field->field_type ) ) {
				$field_type = $field->field_type;
			} else {
				$field_type = '';
			}

			if ( isset( $request['data_type'] ) ) {
				$data_type = esc_attr( $request['data_type'] );
			} elseif ( $field && isset( $field->data_type ) ) {
				$data_type = $field->data_type;
			} else {
				$data_type = '';
			}

			if ( isset( $request['field_icon'] ) && $request['field_icon'] != '' ) {
				$icon = esc_attr( $request['field_icon'] );
			} elseif ( $field && isset( $field->field_icon ) ) {
				$icon = $field->field_icon;
			} else {
				$icon = 'fas fa-sort';
			}

			if ( uwp_is_fa_icon( $icon ) ) {
				$field_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
			} elseif ( uwp_is_icon_url( $icon ) ) {
				$field_icon = '<b style="background-image: url("' . esc_url( $icon ) . '")"></b>';
			} elseif ( isset( $field->tab_type ) && $field->tab_type == 'fieldset' ) {
				$field_icon = '<i class="fas fa-arrows-h" aria-hidden="true"></i>';
			} else {
				$field_icon = '<i class="fas fa-sort" aria-hidden="true"></i>';
			}

			if ( isset( $request['tab_parent'] ) && $request['tab_parent'] != '' ) {
				$tab_parent = esc_attr( $request['tab_parent'] );
			} elseif ( $field && isset( $field->tab_parent ) ) {
				$tab_parent = $field->tab_parent;
			} else {
				$tab_parent = 0;
			}

			if ( isset( $request['tab_level'] ) && $request['tab_level'] != '' ) {
				$tab_level = esc_attr( $request['tab_level'] );
			} elseif ( $field && isset( $field->tab_level ) ) {
				$tab_level = $field->tab_level;
			} else {
				$tab_level = 0;
			}

			?>
            <li class="text li-settings" id="licontainer_<?php echo $field_id; ?>">
                <i class="fas fa-caret-down toggle-arrow" aria-hidden="true" onclick="uwp_show_hide(this);"></i>
                <div class="title title<?php echo $field_id; ?> uwp-fieldset">
					<?php
					$nonce = wp_create_nonce( 'uwp_sort_extras_nonce_' . $field_id );
					echo $field_icon;
					?>
                    <b><?php echo uwp_ucwords( ' ' . $site_title ); ?></b>

                </div>
                <div id="field_frm<?php echo $field_id; ?>" class="field_frm"
                     style="display:<?php if ( $field_ins_upd == 'submit' ) {
					     echo 'block;';
				     } else {
					     echo 'none;';
				     } ?>">

                    <input type="hidden" name="_wpnonce" id="uwp_sort_extras_nonce"
                           value="<?php echo esc_attr( $nonce ); ?>"/>
                    <input type="hidden" name="field_id" id="field_id" value="<?php echo esc_attr( $field_id ); ?>"/>
                    <input type="hidden" name="form_type" id="form_type" value="user_sorting"/>
                    <input type="hidden" name="field_icon" id="field_icon" value="<?php echo $icon; ?>"/>
                    <input type="hidden" name="field_type" id="field_type"
                           value="<?php echo esc_attr( $field_type ); ?>"/>
                    <input type="hidden" name="data_type" id="data_type" value="<?php echo esc_attr( $data_type ); ?>"/>
                    <input type="hidden" name="tab_parent" value="<?php echo esc_attr( $tab_parent ); ?>"/>
                    <input type="hidden" name="tab_level" value="<?php echo esc_attr( $tab_level ); ?>"/>
                    <input type="hidden" name="htmlvar_name" value="<?php echo esc_attr( $htmlvar_name ) ?>"/>

                    <ul class="widefat post fixed" style="width:100%;">

                        <li class="uwp-setting-name">
                            <label for="site_title" class="uwp-tooltip-wrap">
								<?php
								echo uwp_help_tip( __( 'This is the text used for the sort option.', 'userswp' ) );
								_e( 'Frontend title', 'userswp' ); ?>
                            </label>
                            <div class="uwp-input-wrap">
                                <input type="text" name="site_title" id="site_title"
                                       value="<?php echo esc_attr( $site_title ); ?>"/>
                            </div>
                        </li>

                        <li class="uwp-setting-name">

                            <label for="sort" class="uwp-tooltip-wrap">
								<?php
								echo uwp_help_tip( __( 'Select the sort direction: (A-Z or Z-A).', 'userswp' ) );
								_e( 'Ascending or Descending', 'userswp' ); ?>
                            </label>
                            <div class="uwp-input-wrap">
                                <select name="sort" id="uwp-sort-<?php echo esc_attr( $field->id ); ?>">
									<?php $value = isset( $field->sort ) && $field->sort == 'desc' ? 'desc' : 'asc'; ?>
                                    <option value="asc" <?php selected( 'asc', $value, true ); ?>><?php _e( 'Ascending', 'userswp' ); ?></option>
                                    <option value="desc" <?php selected( 'desc', $value, true ); ?>><?php _e( 'Descending', 'userswp' ); ?></option>
                                </select>
                            </div>

                        </li>

                        <li class="uwp-setting-name">

                            <label for="is_default" class="uwp-tooltip-wrap">
								<?php
								echo uwp_help_tip( __( 'This sets the option as the overall default sort value, there can be only one.', 'userswp' ) );
								_e( 'Default sort?', 'userswp' ); ?>
                            </label>
                            <div class="uwp-input-wrap">
                                <input type="radio" name="is_default"
                                       value="1" <?php if ( isset( $field->is_default ) && $field->is_default == 1 ) {
									echo 'checked="checked"';
								} ?>/>
                            </div>

                        </li>

                        <li class="uwp-setting-name">

                            <label for="is_active" class="uwp-tooltip-wrap">
								<?php
								echo uwp_help_tip( __( 'Set if this sort option is active or not, if not it will not be shown to users.', 'userswp' ) );
								_e( 'Is active?', 'userswp' ); ?>
                            </label>
                            <div class="uwp-input-wrap">
								<?php $value = isset( $field->is_active ) && $field->is_active ? $field->is_active : 0; ?>
                                <input type="hidden" name="is_active" value="0"/>
                                <input type="checkbox" name="is_active"
                                       value="1" <?php checked( $value, 1, true ); ?> />
                            </div>

                        </li>

                        <input type="hidden" readonly="readonly" name="sort_order" id="sort_order"
                               value="<?php if ( isset( $field->sort_order ) ) {
							       echo esc_attr( $field->sort_order );
						       } ?>" size="50"/>

						<?php

						do_action( 'uwp_user_sorting_custom_fields', $field_id );

						?>

                        <li>
                            <div class="uwp-input-wrap uwp-tab-actions" data-setting="save_button">

                                <input type="button" class="button button-primary" name="save" id="save"
                                       value="<?php esc_attr_e( 'Save', 'userswp' ); ?>"
                                       onclick="save_field('<?php echo esc_attr( $field_id ); ?>', 'user_sorting')"/>
                                <a class="item-delete submitdelete deletion"
                                   id="delete-<?php echo esc_attr( $field_id ); ?>" href="javascript:void(0);"
                                   onclick="delete_field('<?php echo esc_attr( $field_id ); ?>', '<?php echo wp_create_nonce( 'uwp_sort_delete_nonce_' . $field_id ); ?>', '<?php echo esc_attr( $htmlvar_name ); ?>', 'user_sorting')"><?php _e( "Remove", "userswp" ); ?></a>

                            </div>
                        </li>
                    </ul>

                </div>
            </li>
			<?php
		}

		/**
		 * Handles tabs fields AJAX
		 *
		 * @since       2.0.0
		 * @package     userswp
		 *
		 * @return mixed|void
		 *
		 */
		public function ajax_handler() {

			if ( isset( $_REQUEST['create_field'] ) ) {

				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( - 1 );
				}

				$field_id     = isset( $_REQUEST['field_id'] ) ? trim( sanitize_text_field( $_REQUEST['field_id'] ), '_' ) : '';
				$field_action = isset( $_REQUEST['field_ins_upd'] ) ? sanitize_text_field( $_REQUEST['field_ins_upd'] ) : '';

				/* ------- check nonce field ------- */
				if ( isset( $_REQUEST['update'] ) && $_REQUEST['update'] == 'update' ) {

					if ( ! empty( $_REQUEST['tabs'] ) && is_array( $_REQUEST['tabs'] ) ) {

						$tabs = $_REQUEST['tabs'];
						$this->update_field_order( $tabs );

					}
				}

				/* ---- Show field form in admin ---- */
				if ( $field_action == 'new' ) {
					$htmlvar_name = isset( $_REQUEST['htmlvar_name'] ) ? sanitize_text_field( $_REQUEST['htmlvar_name'] ) : sanitize_text_field( $_REQUEST['tab_key'] );
					$this->field_adminhtml( $htmlvar_name, $field_action, $_REQUEST );
				}

				/* ---- Delete field ---- */
				if ( $field_id != '' && $field_action == 'delete' && isset( $_REQUEST['_wpnonce'] ) ) {
					if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'uwp_sort_delete_nonce_' . $field_id ) ) {
						return;
					}

					$this->field_delete( $field_id );
				}

				/* ---- Save field  ---- */
				if ( $field_action == 'submit' && isset( $_REQUEST['_wpnonce'] ) ) {
					if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'uwp_sort_extras_nonce_' . $field_id ) ) {
						return;
					}

					foreach ( $_REQUEST as $pkey => $pval ) {
						$tags = is_array( $_REQUEST[ $pkey ] ) ? 'skip_field' : '';

						if ( $tags != 'skip_field' ) {
							$_REQUEST[ $pkey ] = strip_tags( sanitize_text_field( $_REQUEST[ $pkey ] ), $tags );
						}
					}

					$lastid = $this->field_save( $_REQUEST );

					if ( is_int( $lastid ) && $lastid > 0 ) {
						$this->field_adminhtml( $lastid, 'submit' );
					} else {
						echo $lastid;
					}
				}
			}
			die();
		}

		/**
		 * Save tabs fields
		 *
		 * @since       2.0.0
		 * @package     userswp
		 *
		 * @param       array $request_field Request data
		 *
		 * @return string|int
		 *
		 */
		public function field_save( $request_field = array() ) {
			global $wpdb;
			$table_name = uwp_get_table_prefix() . 'uwp_user_sorting';

			$id           = isset( $request_field['field_id'] ) && $request_field['field_id'] ? absint( $request_field['field_id'] ) : '';
			$data_type    = isset( $request_field['data_type'] ) ? sanitize_text_field( $request_field['data_type'] ) : 'VARCHAR';
			$field_type   = isset( $request_field['field_type'] ) ? sanitize_text_field( $request_field['field_type'] ) : '';
			$site_title   = isset( $request_field['site_title'] ) ? sanitize_text_field( $request_field['site_title'] ) : '';
			$htmlvar_name = ! empty( $request_field['htmlvar_name'] ) ? sanitize_text_field( $request_field['htmlvar_name'] ) : str_replace( array(
				'-',
				' ',
				'"',
				"'"
			), array( '_', '', '', '' ), sanitize_title_with_dashes( $request_field['site_title'] ) );
			$tab_parent   = ! empty( $request_field['tab_parent'] ) ? sanitize_text_field( $request_field['tab_parent'] ) : 0;
			$tab_level    = ! empty( $request_field['tab_level'] ) ? sanitize_text_field( $request_field['tab_level'] ) : 0;
			$field_icon   = isset( $request_field['field_icon'] ) ? sanitize_text_field( $request_field['field_icon'] ) : '';
			$is_active    = isset( $request_field['is_active'] ) ? absint( $request_field['is_active'] ) : 0;
			$is_default   = isset( $request_field['is_default'] ) ? absint( $request_field['is_default'] ) : 0;
			$sort         = isset( $request_field['sort'] ) ? sanitize_text_field( $request_field['sort'] ) : 'asc';

			$total_tabs = $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name}" );

			if ( isset( $is_default ) && $is_default > 0 ) {
				$wpdb->query( "update " . $table_name . " set is_default='0' where is_default='1'" );
			}

			$data = array(
				'data_type'    => $data_type,
				'field_type'   => $field_type,
				'site_title'   => $site_title,
				'htmlvar_name' => $htmlvar_name,
				'sort_order'   => $total_tabs + 1,
				'tab_parent'   => $tab_parent,
				'tab_level'    => $tab_level,
				'field_icon'   => $field_icon,
				'is_active'    => $is_active,
				'is_default'   => $is_default,
				'sort'         => $sort,
			);

			$format = array_fill( 0, count( $data ), '%s' );

			if ( $id ) { // update

				$wpdb->update(
					$table_name,
					$data,
					array( 'id' => $id ),
					$format
				);

				$lastid = $id;

			} else { // insert
				$wpdb->insert(
					$table_name,
					$data,
					$format
				);

				$lastid = $wpdb->insert_id;
			}

			return (int) $lastid;
		}

		/**
		 * Save tabs fields
		 *
		 * @since       2.0.0
		 * @package     userswp
		 *
		 * @param       string $field_id Request data
		 *
		 * @return int
		 *
		 */
		public function field_delete( $field_id = '' ) {
			global $wpdb;
			$table_name = uwp_get_table_prefix() . 'uwp_user_sorting';

			if ( $field_id != '' ) {
				$cf = trim( $field_id, '_' );
				$wpdb->query( $wpdb->prepare( "delete from " . $table_name . " where id= %d ", array( $cf ) ) );

				return $field_id;
			} else {
				return 0;
			}
		}

		/**
		 * Updates user sorting sort order.
		 *
		 * @param       array $tabs Tabs array.
		 *
		 * @return      object|bool       Sorted tabs.
		 */
		public function update_field_order( $tabs = array() ) {
			global $wpdb;
			$table_name = uwp_get_table_prefix() . 'uwp_user_sorting';

			$count = 0;
			if ( ! empty( $tabs ) ) {
				$result = false;
				foreach ( $tabs as $index => $tab ) {
					$result = $wpdb->update(
						$table_name,
						array(
							'sort_order' => $index,
							'tab_level'  => (int) $tab['tab_level'],
							'tab_parent' => (int) $tab['tab_parent']
						),
						array( 'id' => absint( $tab['id'] ) ),
						array( '%d', '%d', '%d' )
					);
					$count ++;
				}
				if ( $result !== false ) {
					return true;
				} else {
					return new WP_Error( 'failed', __( "Failed to sort tab items.", "userswp" ) );
				}
			} else {
				return new WP_Error( 'failed', __( "Failed to sort tab items.", "userswp" ) );
			}
		}

		/**
		 * Show custom fields in the user sorting form builder.
		 *
		 * @param       array $fields Fields array.
		 *
		 * @return      array       Merged fields array.
		 */
		public function add_custom_sort_options( $fields ) {
			global $wpdb;
			$table_name = uwp_get_table_prefix() . 'uwp_form_fields';

			$custom_fields = $wpdb->get_results( "select data_type,field_type,site_title,htmlvar_name,field_icon from " . $table_name . " where is_active='1' and user_sort='1' AND field_type != 'fieldset' order by sort_order asc", 'ARRAY_A' );

			if ( ! empty( $custom_fields ) ) {

				foreach ( $custom_fields as $val ) {
					$fields[ $val['htmlvar_name'] ] = $val;
				}
			}

			return $fields;
		}

	}

}

new UsersWP_Settings_User_Sorting();