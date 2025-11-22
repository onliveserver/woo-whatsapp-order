<?php
/**
 * AJAX Handler for WhatsApp Order requests.
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Ajax_Handler' ) ) {
	class Onlive_WA_Order_Ajax_Handler {

		/**
		 * Plugin instance.
		 *
		 * @var Onlive_WA_Order_Pro
		 */
		protected $plugin;

		/**
		 * Constructor.
		 *
		 * @param Onlive_WA_Order_Pro $plugin Plugin bootstrapper.
		 */
		public function __construct( Onlive_WA_Order_Pro $plugin ) {
			$this->plugin = $plugin;

			// Register AJAX handlers with both logged-in and non-logged-in hooks
			add_action( 'wp_ajax_vaog2jucg3f2', [ $this, 'handle_ajax_message' ], 0 );
			add_action( 'wp_ajax_nopriv_vaog2jucg3f2', [ $this, 'handle_ajax_message' ], 0 );

			// Add a simple ping endpoint for testing
			add_action( 'wp_ajax_onlive_wa_ping', [ $this, 'handle_ping' ], 0 );
			add_action( 'wp_ajax_nopriv_onlive_wa_ping', [ $this, 'handle_ping' ], 0 );

			// Prevent redirects during AJAX requests
			add_action( 'plugins_loaded', [ $this, 'prevent_ajax_redirect' ], -999 );
			add_action( 'init', [ $this, 'prevent_ajax_redirect' ], 0 );
		}

		/**
		 * Prevent WordPress redirects during AJAX requests.
		 */
		public function prevent_ajax_redirect() {
			// Check if this is an AJAX request for our plugin
			if ( $this->is_our_ajax_request() ) {
				// Prevent all redirects and canonicalization
				remove_action( 'template_redirect', 'redirect_canonical' );
				remove_action( 'template_redirect', 'wp_redirect_admin_locations' );

				// Prevent any post type routing from happening
				if ( ! has_filter( 'status_header', [ $this, 'filter_status_header' ] ) ) {
					add_filter( 'status_header', [ $this, 'filter_status_header' ], 10, 2 );
				}

				// Also prevent 404 handling
				if ( ! has_filter( 'pre_handle_404', [ $this, 'handle_404_override' ] ) ) {
					add_filter( 'pre_handle_404', [ $this, 'handle_404_override' ], 10, 1 );
				}
			} else if ( $this->is_our_ajax_request_early() ) {
				// Early detection if DOING_AJAX hasn't been set yet
				remove_action( 'template_redirect', 'redirect_canonical' );
				remove_action( 'template_redirect', 'wp_redirect_admin_locations' );
			}
		}

		/**
		 * Override 404 handling for AJAX requests.
		 *
		 * @param bool $handled Whether the request was handled.
		 * @return bool
		 */
		public function handle_404_override( $handled ) {
			if ( $this->is_our_ajax_request() ) {
				return true; // Mark as handled to prevent 404
			}
			return $handled;
		}

		/**
		 * Filter HTTP status header to ensure 200 OK for AJAX.
		 *
		 * @param string $status The HTTP status string.
		 * @param int    $code   The HTTP status code.
		 * @return string
		 */
		public function filter_status_header( $status, $code ) {
			if ( $this->is_our_ajax_request() && 404 === $code ) {
				return 'HTTP/1.1 200 OK';
			}
			return $status;
		}

		/**
		 * Check if this is an AJAX request for our plugin.
		 *
		 * @return bool
		 */
		private function is_our_ajax_request() {
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				return false;
			}

			$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
			return in_array( $action, [ 'vaog2jucg3f2' ], true );
		}

		/**
		 * Check if this is our AJAX request even before DOING_AJAX is set.
		 * This is used during early hooks like plugins_loaded.
		 *
		 * @return bool
		 */
		private function is_our_ajax_request_early() {
			// Check for AJAX indicator in request
			$has_ajax_header = ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] );

			if ( ! $has_ajax_header ) {
				return false;
			}

			$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
			return in_array( $action, [ 'vaog2jucg3f2' ], true );
		}

		/**
		 * AJAX handler to build WhatsApp URL.
		 */
		public function handle_ajax_message() {
			// Check if this is actually our request
			$action = $_REQUEST['action'] ?? '';
			if ( $action !== 'vaog2jucg3f2' ) {
				$this->send_json_response( false, 'Invalid action', [] );
				return; // Don't process wrong actions
			}

			// Clear output buffers
			while ( ob_get_level() > 0 ) {
				ob_end_clean();
			}

			// Force JSON response headers
			header( 'Content-Type: application/json; charset=UTF-8', true );

			try {
				// Check if plugin is enabled
				if ( ! $this->plugin->is_enabled() ) {
					$this->send_json_response( false, 'Plugin is disabled', [] );
				}

				// Get context
				$context = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : 'product';

				// Prepare data using data preparer
				$data_preparer = new Onlive_WA_Order_Data_Preparer();

				if ( 'cart' === $context ) {
					$data = $data_preparer->prepare_cart_data();
				} else {
					$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;

					if ( ! $product_id ) {
						$this->send_json_response( false, 'Product ID is required', [] );
					}

					$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
					$quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) : 1;
					$variations   = isset( $_POST['variations'] ) ? json_decode( wp_unslash( $_POST['variations'] ), true ) : [];

					$data = $data_preparer->prepare_product_data( $product_id, $variation_id, $quantity, $variations );
				}

				// Check for errors
				if ( is_wp_error( $data ) ) {
					$this->send_json_response( false, $data->get_error_message(), [] );
				}

				if ( empty( $data ) || ! is_array( $data ) ) {
					$this->send_json_response( false, 'Unable to retrieve product data', [] );
				}

				// Generate message
				$message = $this->plugin->generate_message( $context, $data );
				if ( empty( $message ) ) {
					$this->send_json_response( false, 'Unable to generate message', [] );
				}

				// Get WhatsApp URL
				$url = $this->plugin->get_whatsapp_url( $message );

				// If URL is empty, phone is missing
				if ( empty( $url ) ) {
					$this->send_json_response( false, 'Please add your WhatsApp number in the plugin settings.', [] );
				}

				// Return success
				$this->send_json_response( true, 'Success', [ 'url' => $url, 'message' => $message ] );
			} catch ( Exception $e ) {
				$this->send_json_response( false, 'Exception: ' . $e->getMessage(), [] );
			}
		}

		/**
		 * Send JSON response and exit.
		 *
		 * @param bool   $success Success status.
		 * @param string $message Response message.
		 * @param array  $data    Additional data.
		 */
		public function send_json_response( $success, $message, $data = [] ) {
			// Clear all output buffers to ensure clean JSON response
			while ( ob_get_level() > 0 ) {
				ob_end_clean();
			}

			// Set proper headers
			header( 'Content-Type: application/json; charset=UTF-8', true );
			header( 'Cache-Control: no-cache, no-store, must-revalidate', true );
			header( 'Pragma: no-cache', true );
			header( 'Expires: 0', true );

			// Ensure success is boolean
			$response = [
				'success' => (bool) $success,
				'message' => $message,
				'data'    => $data,
			];

			echo wp_json_encode( $response );
			exit;
		}

		/**
		 * Simple ping handler for testing AJAX connectivity.
		 */
		public function handle_ping() {
			$this->send_json_response( true, 'Ping successful', [] );
		}
	}
}