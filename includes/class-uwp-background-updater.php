<?php
/**
 * Background Updater
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once( dirname( __FILE__ ) . '/libraries/wp-async-request.php' );
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once( dirname( __FILE__ ) . '/libraries/wp-background-process.php' );
}

/**
 * UsersWP_Background_Updater Class.
 */
class UsersWP_Background_Updater extends WP_Background_Process {

    /**
     * Initiate new background process.
     */
    public function __construct() {
        // Uses unique prefix per blog so each blog has separate queue.
        $this->prefix = 'wp_' . get_current_blog_id();
        $this->action = 'uwp_updater';

        parent::__construct();
    }

	/**
	 * Dispatch updater.
	 *
	 * Updater will still run via cron job if this fails for any reason.
     *
     * @since 2.0.0
	 */
	public function dispatch() {
		$dispatched = parent::dispatch();

		if ( is_wp_error( $dispatched ) ) {
			uwp_error_log( sprintf( 'Unable to dispatch UsersWP updater: %s', $dispatched->get_error_message() ) );
		}
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
     *
     * @since 2.0.0
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			return;
		}

		$this->handle();
	}

	/**
	 * Schedule fallback event.
     *
     * @since 2.0.0
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Is the updater running?
     *
     * @since 2.0.0
     *
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Task.
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
     *
     * @since 2.0.0
	 *
	 * @param string $callback Update callback function.
	 * @return mixed
	 */
	protected function task( $callback ) {
		uwp_maybe_define( 'UWP_UPDATING', true );

		if ( is_callable( $callback ) ) {
			uwp_error_log( sprintf( 'Running %s callback', $callback ) );
			call_user_func( $callback );
			uwp_error_log( sprintf( 'Finished %s callback', $callback ) );
		} else {
			uwp_error_log( sprintf( 'Could not find %s callback', $callback ) );
		}

		return false;
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
     *
     * @since 2.0.0
	 */
	protected function complete() {
		uwp_error_log( 'UsersWP data update complete.' );
		parent::complete();
	}
}
