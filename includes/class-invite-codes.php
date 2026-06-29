<?php
/**
 * UsersWP Invite Codes component.
 *
 * Handles invite code generation, validation, and registration gating.
 *
 * @since      1.2.66
 * @package    userswp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UsersWP_Invite_Codes.
 *
 * @since 1.2.66
 */
class UsersWP_Invite_Codes {

	/**
	 * Invite code ID validated during current request.
	 *
	 * Used to record usage after registration completes.
	 *
	 * @since 1.2.66
	 * @var int|null
	 */
	private $validated_code_id = null;

	/**
	 * Constructor.
	 *
	 * @since 1.2.66
	 */
	public function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 1.2.66
	 */
	private function setup_hooks() {
		// Registration gating.
		add_filter( 'uwp_validate_result', array( $this, 'validate_registration_code' ), 10, 3 );
		add_action( 'uwp_after_process_register', array( $this, 'record_code_usage' ), 10, 2 );

		// Add invite code field to registration form.
		add_filter( 'uwp_get_register_form_fields', array( $this, 'add_invite_code_field' ), 10, 2 );

		// Admin menu for code management — registered outside is_admin() so it always hooks.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 50 );

		if ( is_admin() ) {
			// Settings under the Addons tab.
			add_filter( 'uwp_get_sections_uwp-addons', array( $this, 'add_settings_section' ) );
			add_filter( 'uwp_get_settings_uwp-addons', array( $this, 'add_settings_fields' ), 10, 2 );
			// Add require-invite checkbox to per-form settings.
			add_action( 'uwp_user_type_form_after', array( $this, 'add_form_invite_option' ), 10, 1 );
			// Save per-form invite code option via the existing AJAX save filter.
			add_filter( 'uwp_multiple_registration_forms_update', array( $this, 'save_form_invite_option' ), 10, 2 );
			// Form Builder Register tab: invite code toggle.
			add_action( 'uwp_form_builder_tabs_register', array( $this, 'render_form_builder_invite_toggle' ) );
			// AJAX save for Form Builder invite code toggle.
			add_action( 'wp_ajax_uwp_save_invite_code_form_setting', array( $this, 'ajax_save_invite_code_form_setting' ) );
			// Process single-code deletion early (before page output) so wp_safe_redirect works.
			add_action( 'admin_init', array( $this, 'process_single_delete' ) );
		} else {
			// Frontend: profile tab for user code generation.
			add_filter( 'uwp_profile_tabs_predefined_fields', array( $this, 'add_profile_tab' ), 10, 2 );
			add_action( 'uwp_profile_invite_codes_tab_content', array( $this, 'render_profile_tab_content' ) );

			// Frontend code generation form handler.
			add_action( 'init', array( $this, 'handle_frontend_form' ) );

			// Register shortcode for invite codes section on account page.
			add_shortcode( 'uwp_invite_codes', array( $this, 'shortcode_invite_codes' ) );
		}

		/**
		 * Fires after invite codes hooks are set up.
		 *
		 * @since 1.2.66
		 * @param UsersWP_Invite_Codes $instance The invite codes instance.
		 */
		do_action( 'uwp_invite_codes_setup', $this );
	}

	/**
	 * Validate invite code during registration (uwp_validate_result filter).
	 *
	 * @since 1.2.66
	 * @param WP_Error|array $result Validation result or WP_Error.
	 * @param string         $type   Form type.
	 * @param array          $data   POST data.
	 * @return WP_Error|array
	 */
	public function validate_registration_code( $result, $type, $data ) {
		if ( 'register' !== $type ) {
			return $result;
		}

		if ( is_wp_error( $result ) ) {
			return $result; // Already failed prior validation.
		}

		$form_id = ! empty( $data['uwp_register_form_id'] ) ? absint( $data['uwp_register_form_id'] ) : 1;

		if ( ! uwp_is_invite_code_required( $form_id ) ) {
			return $result;
		}

		// Get code from POST or GET (auto-apply link). GET param is a public read, no nonce needed here.
		$code = '';
		if ( ! empty( $data['uwp_invite_code'] ) && is_string( $data['uwp_invite_code'] ) ) {
			$code = wp_unslash( $data['uwp_invite_code'] );
		} elseif ( ! empty( $_GET['uwp_invite_code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public auto-apply link.
			$raw_get = isset( $_GET['uwp_invite_code'] ) ? $_GET['uwp_invite_code'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( is_string( $raw_get ) ) {
				$code = sanitize_text_field( wp_unslash( $raw_get ) );
			}
		}

		if ( empty( $code ) ) {
			return new WP_Error(
				'uwp_invite_required',
				__( 'An invite code is required to register.', 'userswp' )
			);
		}

		$validated = uwp_validate_invite_code( $code, $form_id );

		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Stash for post-registration usage recording.
		$this->validated_code_id = (int) $validated['id'];

		return $result;
	}

	/**
	 * Record invite code usage after successful registration.
	 *
	 * @since 1.2.66
	 * @param array $result  Registration result data.
	 * @param int   $user_id Newly created user ID.
	 */
	public function record_code_usage( $result, $user_id ) {
		if ( null !== $this->validated_code_id && $user_id > 0 ) {
			$recorded = uwp_use_invite_code( $this->validated_code_id, $user_id );
			if ( ! $recorded ) {
				error_log( sprintf( 'UsersWP: Failed to record invite code usage. code_id=%d user_id=%d', $this->validated_code_id, $user_id ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- DB failure must be logged.
			}
			$this->validated_code_id = null;
		}
	}

	/**
	 * Add the invite code field to the registration form fields list.
	 *
	 * @since 1.2.66
	 * @param array $fields  Form fields from DB.
	 * @param int   $form_id Registration form ID.
	 * @return array
	 */
	public function add_invite_code_field( $fields, $form_id ) {
		if ( ! uwp_is_invite_code_required( $form_id ) ) {
			return $fields;
		}

		// Add a synthetic field object for the invite code input.
		$invite_field                    = new stdClass();
		$invite_field->id                = 0;
		$invite_field->form_id           = $form_id;
		$invite_field->htmlvar_name      = 'uwp_invite_code';
		$invite_field->site_title        = __( 'Invite Code', 'userswp' );
		$invite_field->form_label        = __( 'Invite Code', 'userswp' );
		$invite_field->help_text         = __( 'Enter your invite code to register.', 'userswp' );
		$invite_field->field_type        = 'invite_code';
		$invite_field->is_required       = '1';
		$invite_field->placeholder_value = '';
		$invite_field->is_active         = '1';
		$invite_field->css_class         = '';

		$fields = is_array( $fields ) ? $fields : array();
		$fields[] = $invite_field;

		return $fields;
	}

	/**
	 * Add the invite code input HTML (invoked by the form builder renderer).
	 *
	 * Uses the uwp_form_input_html_invite_code filter to inject the field markup.
	 *
	 * @since 1.2.66
	 */
	public function init_invite_code_field_renderer() {
		add_filter( 'uwp_form_input_html_invite_code', array( $this, 'render_invite_code_input' ), 10, 4 );
	}

	/**
	 * Render the invite code text input on the registration form.
	 *
	 * @since 1.2.66
	 * @param string $html    Existing HTML.
	 * @param object $field   Field definition.
	 * @param string $value   Current value.
	 * @param int    $form_id Form ID.
	 * @return string
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- filter requires 4 args.
	public function render_invite_code_input( $html, $field, $value, $form_id ) {
		$required = ! empty( $field->is_required ) ? ' <span class="text-danger">*</span>' : '';

		// Pre-fill from auto-apply link. Public GET param, no nonce needed.
		if ( empty( $value ) && ! empty( $_GET['uwp_invite_code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$raw_get = isset( $_GET['uwp_invite_code'] ) ? $_GET['uwp_invite_code'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( is_string( $raw_get ) ) {
				$value = sanitize_text_field( wp_unslash( $raw_get ) );
			}
		}

		$value = esc_attr( $value );

		$html  = '<div id="uwp_invite_code_row" class="required_field uwp_form_row clearfix uwp_clear form-group mb-3">';
		$html .= '<label for="uwp_invite_code">' . esc_html( $field->site_title ) . $required . '</label>';
		$html .= '<input type="text" name="uwp_invite_code" id="uwp_invite_code" class="form-control"'
			. ' value="' . $value . '"'
			. ' placeholder="' . esc_attr( __( 'Enter invite code', 'userswp' ) ) . '"'
			. '>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Add invite codes section to the Addons settings tab.
	 *
	 * @since 1.2.66
	 * @param array $sections Existing sections.
	 * @return array
	 */
	public function add_settings_section( $sections ) {
		$sections['invite-codes'] = __( 'Invite Codes', 'userswp' );
		return $sections;
	}

	/**
	 * Add invite codes settings fields to the Addons tab.
	 *
	 * @since 1.2.66
	 * @param array  $settings        Existing settings.
	 * @param string $current_section Current section key.
	 * @return array
	 */
	public function add_settings_fields( $settings, $current_section ) {
		if ( 'invite-codes' !== $current_section ) {
			return $settings;
		}

		return array(
			array(
				'title' => __( 'Invite Code Settings', 'userswp' ),
				'type'  => 'title',
				'id'    => 'uwp_invite_codes_settings_title',
			),
			array(
				'name'    => __( 'Max codes per user', 'userswp' ),
				'desc'    => __( 'Maximum active invite codes a user can create from the frontend.', 'userswp' ),
				'id'      => 'uwp_invite_max_per_user',
				'type'    => 'number',
				'default' => '10',
				'css'     => 'width:80px;',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'uwp_invite_codes_settings_title',
			),
		);
	}

	/**
	 * Add "Require Invite Code" checkbox to per-form settings page.
	 *
	 * @since 1.2.66
	 * @param int $form_id The form ID being edited.
	 */
	public function add_form_invite_option( $form_id ) {
		$required_forms = uwp_get_option( 'uwp_require_invite_code_forms', array() );
		if ( ! is_array( $required_forms ) ) {
			$required_forms = array();
		}
		$checked = in_array( (int) $form_id, $required_forms, true );
		?>
		<div class="uwp-invite-code-form-option" style="margin: 10px 0;">
			<label>
				<input type="checkbox" name="uwp_require_invite_code" value="1" <?php checked( $checked ); ?> />
				<?php esc_html_e( 'Require invite code for this registration form', 'userswp' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Save the per-form invite code requirement option.
	 *
	 * Hooks into uwp_multiple_registration_forms_update filter called during
	 * AJAX save of registration form settings. Nonce already verified by
	 * process_update_register_form() before this filter runs.
	 *
	 * @since 1.2.66
	 * @param array $register_forms Array of registration form settings.
	 * @param int   $form_id       The form ID being saved.
	 * @return array Modified register forms array.
	 */
	public function save_form_invite_option( $register_forms, $form_id ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $register_forms;
		}

		$required_forms = uwp_get_option( 'uwp_require_invite_code_forms', array() );
		if ( ! is_array( $required_forms ) ) {
			$required_forms = array();
		}

		// Nonce already verified by process_update_register_form() before this filter runs.
		if ( ! empty( $_POST['uwp_require_invite_code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! in_array( (int) $form_id, $required_forms, true ) ) {
				$required_forms[] = (int) $form_id;
			}
		} else {
			$required_forms = array_values( array_diff( $required_forms, array( (int) $form_id ) ) );
		}

		uwp_update_option( 'uwp_require_invite_code_forms', array_values( $required_forms ) );

		return $register_forms;
	}

	/**
	 * Render invite code toggle on the Form Builder Register tab.
	 *
	 * Fires via `uwp_form_builder_tabs_register` after the two-panel field layout.
	 *
	 * @since 1.2.66
	 * @param array $tabs Tab definitions.
	 */
	public function render_form_builder_invite_toggle( $tabs ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin display.
		$form_id         = ! empty( $_GET['form'] ) ? absint( $_GET['form'] ) : 1;
		$required_forms  = uwp_get_option( 'uwp_require_invite_code_forms', array() );
		if ( ! is_array( $required_forms ) ) {
			$required_forms = array();
		}
		$checked = in_array( $form_id, $required_forms, true );
		$nonce   = wp_create_nonce( 'uwp_save_invite_code_form_setting' );
		?>
		<div class="card" style="margin-top: 15px; padding: 15px;">
			<h3 style="margin-top: 0;"><?php esc_html_e( 'Invite Code', 'userswp' ); ?></h3>
			<label>
				<input type="checkbox"
					name="uwp_require_invite_code"
					value="1"
					data-form-id="<?php echo esc_attr( $form_id ); ?>"
					data-nonce="<?php echo esc_attr( $nonce ); ?>"
					<?php checked( $checked ); ?>
				/>
				<?php esc_html_e( 'Require invite code for this registration form', 'userswp' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'When enabled, visitors must enter a valid invite code to register.', 'userswp' ); ?></p>
		</div>
		<script>
		(function($) {
			$('input[name="uwp_require_invite_code"]').on('change', function() {
				var $cb = $(this);
				$.post(uwp_admin_ajax.url, {
					action: 'uwp_save_invite_code_form_setting',
					form_id: $cb.data('form-id'),
					enabled: $cb.is(':checked') ? 1 : 0,
					nonce: $cb.data('nonce')
				}, function(r) {
					if (!r.success) {
						$cb.prop('checked', !$cb.is(':checked'));
					}
				}).fail(function() {
					$cb.prop('checked', !$cb.is(':checked'));
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * AJAX handler: save invite code form setting from Form Builder.
	 *
	 * @since 1.2.66
	 */
	public function ajax_save_invite_code_form_setting() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'uwp_save_invite_code_form_setting' ) ) {
			wp_send_json_error();
		}

		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 1;
		$enabled = ! empty( $_POST['enabled'] );

		$required_forms = uwp_get_option( 'uwp_require_invite_code_forms', array() );
		if ( ! is_array( $required_forms ) ) {
			$required_forms = array();
		}

		if ( $enabled ) {
			if ( ! in_array( $form_id, $required_forms, true ) ) {
				$required_forms[] = $form_id;
			}
		} else {
			$required_forms = array_values( array_diff( $required_forms, array( $form_id ) ) );
		}

		uwp_update_option( 'uwp_require_invite_code_forms', array_values( $required_forms ) );
		wp_send_json_success();
	}

	/**
	 * Add invite codes admin menu page for managing codes.
	 *
	 * @since 1.2.66
	 * @param string $parent_slug Parent menu slug.
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'userswp',
			__( 'Invite Codes', 'userswp' ),
			__( 'Invite Codes', 'userswp' ),
			'manage_options',
			'uwp_invite_codes',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render the admin invite codes management page.
	 *
	 * @since 1.2.66
	 */
	public function render_admin_page() {
		// Handle admin form actions.
		$this->handle_admin_actions();

		require_once USERSWP_PATH . 'admin/tables/class-invite-codes-table.php';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice check after POST redirect.
		if ( isset( $_GET['uwp_deleted'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Invite code deleted.', 'userswp' ) . '</p></div>';
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice check after POST redirect.
		if ( isset( $_GET['uwp_delete_error'] ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Error deleting invite code.', 'userswp' ) . '</p></div>';
		}

		$table = new UsersWP_Invite_Codes_Table();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Invite Codes', 'userswp' ); ?></h1>
			<hr class="wp-header-end">

			<?php $table->views(); ?>

			<form method="post" action="" style="margin-bottom: 20px;">
				<?php wp_nonce_field( 'uwp_invite_codes_create', 'uwp_invite_codes_nonce' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="uwp_bulk_count"><?php esc_html_e( 'Generate Codes', 'userswp' ); ?></label></th>
						<td>
							<label for="uwp_bulk_count" style="margin-right: 3px;"><?php esc_html_e( 'Number of codes', 'userswp' ); ?></label>
							<input type="number" name="bulk_count" id="uwp_bulk_count" value="1" min="1" max="500" class="small-text" placeholder="<?php esc_attr_e( 'e.g. 10', 'userswp' ); ?>" />
							<label for="uwp_usage_limit" style="margin: 0 3px 0 8px;"><?php esc_html_e( 'Usage limit per code', 'userswp' ); ?></label>
							<input type="number" name="usage_limit" id="uwp_usage_limit" value="1" min="0" class="small-text" placeholder="<?php esc_attr_e( 'e.g. 1', 'userswp' ); ?>" title="<?php esc_attr_e( 'Max uses per code. 0 = unlimited.', 'userswp' ); ?>" />
							<span style="margin: 0 5px 0 8px;"><?php esc_html_e( 'Expiry', 'userswp' ); ?></span>
							<input type="date" name="expiry_date" class="" />
							<?php submit_button( __( 'Create', 'userswp' ), 'secondary', 'uwp_create_codes', false ); ?>
							<p class="description"><?php esc_html_e( 'Usage limit: 0 = unlimited. Leave expiry blank for no expiry.', 'userswp' ); ?></p>
						</td>
					</tr>
				</table>
			</form>

			<form method="post" action="">
				<?php
				$table->search_box( __( 'Search codes', 'userswp' ), 'uwp_invite_code_search' );
				$table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle admin form actions: create codes, delete codes.
	 *
	 * @since 1.2.66
	 */
	private function handle_admin_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Create codes (single or bulk).
		if ( isset( $_POST['uwp_create_codes'] ) && isset( $_POST['uwp_invite_codes_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['uwp_invite_codes_nonce'] ) ), 'uwp_invite_codes_create' ) ) {
				return;
			}

			$bulk_count  = isset( $_POST['bulk_count'] ) ? absint( $_POST['bulk_count'] ) : 1;
			$usage_limit = isset( $_POST['usage_limit'] ) ? absint( $_POST['usage_limit'] ) : 1;
			$expiry_date = ! empty( $_POST['expiry_date'] ) ? sanitize_text_field( wp_unslash( $_POST['expiry_date'] ) ) . ' 23:59:59' : '';

			$result = uwp_create_invite_code(
				array(
					'created_by'  => 0,
					'usage_limit' => $usage_limit,
					'expiry_date' => $expiry_date,
					'bulk_count'  => $bulk_count,
				)
			);

			if ( is_wp_error( $result ) ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
			} else {
				$count = count( $result );
				/* translators: %d: number of codes created */
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( _n( '%d code created.', '%d codes created.', $count, 'userswp' ), $count ) ) . '</p></div>';
			}
		}

		// Confirm-delete single code: show interstitial.
		if ( isset( $_GET['action'] ) && 'confirm_delete' === $_GET['action'] && ! empty( $_GET['code_id'] ) ) {
			$code_id = absint( $_GET['code_id'] );
			$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'uwp_confirm_delete_invite_code_' . $code_id ) ) {
				wp_nonce_ays( 'uwp_confirm_delete_invite_code_' . $code_id );
			}

			$code = uwp_get_invite_code( $code_id );
			if ( ! $code ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invite code not found.', 'userswp' ) . '</p></div>';
				return;
			}

			$delete_nonce = wp_create_nonce( 'uwp_delete_invite_code_' . $code_id );
			$cancel_url   = admin_url( 'admin.php?page=uwp_invite_codes' );
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Delete Invite Code', 'userswp' ); ?></h1>
				<div id="message" class="notice notice-warning">
					<p>
						<?php
						printf(
							/* translators: %s: invite code string */
							esc_html__( 'You are about to permanently delete invite code %s. This action cannot be undone.', 'userswp' ),
							'<strong>' . esc_html( $code->code ) . '</strong>'
						);
						?>
					</p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=uwp_invite_codes' ) ); ?>">
						<?php wp_nonce_field( 'uwp_delete_invite_code_' . $code_id, 'uwp_delete_nonce' ); ?>
						<input type="hidden" name="code_id" value="<?php echo esc_attr( $code_id ); ?>" />
						<p>
							<input type="submit" name="uwp_delete_code" class="button-primary" value="<?php esc_attr_e( 'Delete', 'userswp' ); ?>" />
							<a href="<?php echo esc_url( $cancel_url ); ?>" class="button"><?php esc_html_e( 'Cancel', 'userswp' ); ?></a>
						</p>
					</form>
				</div>
			</div>
			<?php
			exit; // Stop rendering the table list.
		}
	}

	/**
	 * Process single-code deletion from the confirmation form (POST).
	 *
	 * Runs on admin_init (before any page output) so wp_safe_redirect() can set
	 * headers cleanly. The confirm_delete interstitial posts here.
	 *
	 * @since 1.2.66
	 */
	public function process_single_delete() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_POST['uwp_delete_code'] ) || empty( $_POST['code_id'] ) ) {
			return;
		}

		$code_id = absint( $_POST['code_id'] );
		$nonce   = isset( $_POST['uwp_delete_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['uwp_delete_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'uwp_delete_invite_code_' . $code_id ) ) {
			wp_nonce_ays( 'uwp_delete_invite_code_' . $code_id );
		}

		global $wpdb;
		$table   = uwp_invite_code_table_name();
		$deleted = $wpdb->delete( $table, array( 'id' => $code_id ), array( '%d' ) );

		if ( $deleted ) {
			wp_safe_redirect( admin_url( 'admin.php?page=uwp_invite_codes&uwp_deleted=1' ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=uwp_invite_codes&uwp_delete_error=1' ) );
		}
		exit;
	}

	/**
	 * Add "Invite Codes" tab to the user's profile.
	 *
	 * @since 1.2.66
	 * @param array $fields  Predefined tab fields.
	 * @param int   $form_id Form ID.
	 * @return array
	 */
	public function add_profile_tab( $fields, $form_id ) {
		$fields[] = array(
			'tab_type'    => 'standard',
			'tab_level'   => 1,
			'tab_parent'  => 0,
			'tab_privacy' => 2, // Author and admin only.
			'tab_name'    => __( 'Invite Codes', 'userswp' ),
			'tab_icon'    => 'fas fa-ticket-alt',
			'tab_key'     => 'invite_codes',
			'sort_order'  => 100,
			'form_id'     => $form_id,
		);
		return $fields;
	}

	/**
	 * Render the profile invite codes tab content.
	 *
	 * @since 1.2.66
	 */
	public function render_profile_tab_content() {
		echo wp_kses_post( $this->get_invite_codes_frontend_html() );
	}

	/**
	 * Shortcode to display invite codes section on any page.
	 *
	 * @since 1.2.66
	 * @return string HTML output.
	 */
	public function shortcode_invite_codes() {
		if ( ! is_user_logged_in() ) {
			return '';
		}
		return $this->get_invite_codes_frontend_html();
	}

	/**
	 * Build the frontend invite codes HTML.
	 *
	 * @since 1.2.66
	 * @return string HTML.
	 */
	private function get_invite_codes_frontend_html() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return '';
		}

		$max_codes  = uwp_get_max_user_invite_codes();
		$user_count = uwp_count_user_invite_codes( $user_id );
		$can_create = $user_count < $max_codes;

		ob_start();

		uwp_get_template(
			'invite-codes.php',
			array(
				'user_id'    => $user_id,
				'max_codes'  => $max_codes,
				'user_count' => $user_count,
				'can_create' => $can_create,
				'codes'      => uwp_get_invite_codes(
					array(
						'user_id'  => $user_id,
						'per_page' => 50,
					)
				)->items,
			)
		);

		return ob_get_clean();
	}

	/**
	 * Handle frontend invite code form submissions (generate and delete).
	 *
	 * @since 1.2.66
	 */
	public function handle_frontend_form() {
		// Code generation.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in handle_frontend_generate().
		if ( isset( $_POST['uwp_invite_generate_submit'] ) ) {
			$this->handle_frontend_generate();
			return;
		}

		// Code deletion.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in handle_frontend_delete().
		if ( isset( $_POST['uwp_invite_delete_submit'] ) ) {
			$this->handle_frontend_delete();
			return;
		}
	}

	/**
	 * Handle frontend invite code generation.
	 *
	 * @since 1.2.66
	 */
	private function handle_frontend_generate() {
		if ( ! isset( $_POST['uwp_invite_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['uwp_invite_nonce'] ) ), 'uwp_invite_codes_frontend' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'userswp' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		// Rate limit: 30-second cooldown between code generations.
		$rate_key = 'uwp_invite_gen_' . $user_id;
		if ( get_transient( $rate_key ) ) {
			wp_die( esc_html__( 'Please wait before generating another code.', 'userswp' ) );
		}
		set_transient( $rate_key, 1, 30 );

		$max_codes  = uwp_get_max_user_invite_codes();
		$user_count = uwp_count_user_invite_codes( $user_id );

		if ( $user_count >= $max_codes ) {
			wp_die( esc_html__( 'You have reached the maximum number of invite codes.', 'userswp' ) );
		}

		$usage_limit = isset( $_POST['uwp_invite_usage_limit'] ) ? min( absint( $_POST['uwp_invite_usage_limit'] ), 100 ) : 1;
		$expiry_raw  = ! empty( $_POST['uwp_invite_expiry'] ) ? sanitize_text_field( wp_unslash( $_POST['uwp_invite_expiry'] ) ) : '';
		$expiry_date = $expiry_raw ? $expiry_raw . ' 23:59:59' : '';

		$result = uwp_create_invite_code(
			array(
				'created_by'  => $user_id,
				'usage_limit' => $usage_limit,
				'expiry_date' => $expiry_date,
				'bulk_count'  => 1,
			)
		);

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		// Redirect to avoid re-submission.
		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = home_url();
		}
		wp_safe_redirect( remove_query_arg( 'uwp_msg', $redirect ) );
		exit;
	}

	/**
	 * Handle frontend invite code deletion.
	 *
	 * @since 1.2.66
	 */
	private function handle_frontend_delete() {
		if ( ! isset( $_POST['uwp_invite_delete_nonce'] ) || ! isset( $_POST['code_id'] ) ) {
			return;
		}

		$code_id = absint( $_POST['code_id'] );

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['uwp_invite_delete_nonce'] ) ), 'uwp_invite_delete_' . $code_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'userswp' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		// Verify ownership — only the creator can delete their code.
		$code = uwp_get_invite_code( $code_id );
		if ( ! $code || (int) $code->created_by !== (int) $user_id ) {
			wp_die( esc_html__( 'You do not have permission to delete this code.', 'userswp' ) );
		}

		global $wpdb;
		$table = uwp_invite_code_table_name();
		$wpdb->delete( $table, array( 'id' => $code_id ), array( '%d' ) );

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = home_url();
		}
		wp_safe_redirect( remove_query_arg( 'uwp_msg', $redirect ) );
		exit;
	}
}
