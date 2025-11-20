<?php
/**
 * Plugin Name:       Onlive WooCommerce WhatsApp Order
 * Plugin URI:        https://www.onlivetechnologies.com/plugins/whatsapp-order
 * Description:       Adds customizable WhatsApp "Order Now" buttons to WooCommerce product and cart pages with advanced templates.
 * Version:           1.4.0
 * Author:            Onlive Technologies
 * Author URI:        https://www.onlivetechnologies.com/
 * Support Email:     support@onliveinfotech.com
 * Text Domain:       onlive-wa-order
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Pro' ) ) {
	final class Onlive_WA_Order_Pro {

		/**
		 * Singleton instance.
		 *
		 * @var Onlive_WA_Order_Pro|null
		 */
		protected static $instance = null;

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		public $version = '1.4.0';

		/**
		 * Cached settings array.
		 *
		 * @var array
		 */
		protected $settings = [];

		/**
		 * Frontend handler instance.
		 *
		 * @var Onlive_WA_Order_Pro_Frontend|null
		 */
		public $frontend = null;

		/**
		 * Admin handler instance.
		 *
		 * @var Onlive_WA_Order_Pro_Admin|null
		 */
		public $admin = null;

		/**
		 * Initialize the plugin.
		 */
		private function __construct() {
			$this->define_constants();
			$this->includes();

			add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
			add_action( 'plugins_loaded', [ $this, 'bootstrap' ], 5 );
			add_action( 'admin_init', [ $this, 'maybe_display_woo_notice' ] );
		}

		/**
		 * Retrieve singleton instance.
		 *
		 * @return Onlive_WA_Order_Pro
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Define plugin constants.
		 */
		private function define_constants() {
			if ( ! defined( 'ONLIVE_WA_ORDER_FILE' ) ) {
				define( 'ONLIVE_WA_ORDER_FILE', __FILE__ );
			}

			if ( ! defined( 'ONLIVE_WA_ORDER_PATH' ) ) {
				define( 'ONLIVE_WA_ORDER_PATH', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'ONLIVE_WA_ORDER_URL' ) ) {
				define( 'ONLIVE_WA_ORDER_URL', plugin_dir_url( __FILE__ ) );
			}
		}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once ONLIVE_WA_ORDER_PATH . 'includes/class-template-parser.php';
		require_once ONLIVE_WA_ORDER_PATH . 'includes/class-github-updater.php';
		require_once ONLIVE_WA_ORDER_PATH . 'admin/settings-page.php';
		require_once ONLIVE_WA_ORDER_PATH . 'frontend/class-frontend.php';
	}		/**
		 * Load plugin text domain.
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'onlive-wa-order', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

	/**
	 * Bootstrap plugin components.
	 */
	public function bootstrap() {
		$this->settings = $this->get_settings();

		// Log bootstrap start
		error_log('=== PLUGIN BOOTSTRAP START ===');
		error_log('Is admin: ' . (is_admin() ? 'YES' : 'NO'));
		error_log('Is WooCommerce active: ' . ($this->is_woocommerce_active() ? 'YES' : 'NO'));
		error_log('Plugin enabled: ' . ($this->is_enabled() ? 'YES' : 'NO'));

		if ( is_admin() ) {
			$this->admin = new Onlive_WA_Order_Pro_Admin( $this );
			error_log('Admin class initialized');
		}

		$this->frontend = new Onlive_WA_Order_Pro_Frontend( $this );
		error_log('Frontend class initialized');

		// Initialize GitHub updater.
		new Onlive_WA_Order_GitHub_Updater( __FILE__ );
		error_log('GitHub updater initialized');
		error_log('=== PLUGIN BOOTSTRAP COMPLETE ===');
	}		/**
		 * Show notice if WooCommerce is inactive.
		 */
		public function maybe_display_woo_notice() {
			if ( $this->is_woocommerce_active() ) {
				return;
			}

			add_action(
				'admin_notices',
				function () {
					printf(
						'<div class="notice notice-error"><p>%s</p></div>',
						esc_html__( 'Onlive WooCommerce WhatsApp Order requires WooCommerce to be installed and active.', 'onlive-wa-order' )
					);
				}
			);
		}

		/**
		 * Determine if WooCommerce is active.
		 *
		 * @return bool
		 */
		public function is_woocommerce_active() {
			return class_exists( 'WooCommerce' );
		}

		/**
		 * Get plugin settings merged with defaults.
		 *
		 * @return array
		 */
		public function get_settings() {
			$stored = get_option( 'onlive_wa_order_settings', [] );

			return wp_parse_args( $stored, $this->get_default_settings() );
		}

		/**
		 * Get default settings array.
		 *
		 * @return array
		 */
	public function get_default_settings() {
		return [
			'enabled'            => 1,
			'phone'              => '',
			'positions'          => [
				'single' => 1,
				'cart'   => 1,
			],
			'button_label_single' => __( 'Order via WhatsApp', 'onlive-wa-order' ),
			'button_label_cart'   => __( 'Order Cart via WhatsApp', 'onlive-wa-order' ),
			'button_color'        => '#25D366',
			'button_text_color'   => '#ffffff',
			'button_size'         => 'medium',
			'template_enabled'    => 0,
			'message_template'    => "Hello, I would like to order {{product_name}}. Price: {{product_price}} x {{product_quantity}}. {{product_variation}}",
			'api_choice'          => 'wa',
			'custom_gateway'      => '',
			'custom_query_param'  => 'text',
			'load_css'            => 1,
			'custom_css'          => '',
			'include_product_link' => 1,
		];
	}		/**
		 * Refresh cached settings.
		 */
		public function refresh_settings() {
			$this->settings = $this->get_settings();
		}

		/**
		 * Retrieve a single setting value.
		 *
		 * @param string $key     Setting key.
		 * @param mixed  $default Default fallback.
		 *
		 * @return mixed
		 */
		public function get_setting( $key, $default = null ) {
			if ( empty( $this->settings ) ) {
				$this->settings = $this->get_settings();
			}

			return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
		}

		/**
		 * Determine if plugin features are enabled.
		 *
		 * @return bool
		 */
		public function is_enabled() {
			return (bool) $this->get_setting( 'enabled', true );
		}

		/**
		 * Determine if button should render for a context.
		 *
		 * @param string $context Context key (single|cart).
		 *
		 * @return bool
		 */
		public function should_render_button( $context ) {
			$positions = $this->get_setting( 'positions', [] );
			$context   = sanitize_key( $context );

			return ! empty( $positions[ $context ] );
		}

		/**
		 * Generate WhatsApp message by parsing template and data.
		 *
		 * @param string $context Context identifier.
		 * @param array  $data    Data set for replacements.
		 *
		 * @return string
		 */
		public function generate_message( $context, $data = [] ) {
			$template = '';
			if ( $this->get_setting( 'template_enabled', false ) ) {
				$template = (string) $this->get_setting( 'message_template', '' );
			}

			if ( empty( $template ) ) {
				$template = $this->get_default_template( $context );
			}

			$replacements = $this->prepare_replacements( $context, $data );
			$message      = Onlive_WA_Order_Template_Parser::parse( $template, $replacements );

			if ( 'cart' === $context && ! empty( $data['items_formatted'] ) ) {
				$message .= "\n\n" . $data['items_formatted'];
			}

			// Trim the final message
			$message = trim( $message );
			
			// Ensure message is not blank or only contains template placeholders
			if ( empty( $message ) || preg_match('/^\{\{[^}]+\}\}$/', $message ) ) {
				// Fallback to a basic message if blank
				if ( 'cart' === $context ) {
					$message = __( 'I would like to place an order from your store.', 'onlive-wa-order' );
				} else {
					$message = __( 'Hello, I am interested in this product.', 'onlive-wa-order' );
				}
			}

			/**
			 * Allow developers to filter the WhatsApp message before encoding.
			 *
			 * @param string                 $message    The generated message.
			 * @param string                 $context    Context identifier.
			 * @param array                  $data       Source data.
			 * @param Onlive_WA_Order_Pro    $plugin     Plugin singleton.
			 */
			return apply_filters( 'onlive_wa_order_message', $message, $context, $data, $this );
		}

		/**
		 * Build the replacement array for template parsing.
		 *
		 * @param string $context Context identifier.
		 * @param array  $data    Provided data.
		 *
		 * @return array
		 */
		private function prepare_replacements( $context, $data ) {
			$site_name     = get_bloginfo( 'name' );
			$current_user = wp_get_current_user();
			
			// For non-logged-in users, use "Guest" or their provided name
			$customer_name = '';
			if ( $current_user && $current_user->ID ) {
				$customer_name = $current_user->display_name;
			} elseif ( isset( $data['customer_name'] ) && ! empty( $data['customer_name'] ) ) {
				$customer_name = $data['customer_name'];
			} else {
				$customer_name = __( 'Guest Customer', 'onlive-wa-order' );
			}

			$replacements = [
				'product_name'     => isset( $data['product_name'] ) ? $data['product_name'] : '',
				'product_link'     => isset( $data['product_link'] ) ? $data['product_link'] : '',
				'product_price'    => isset( $data['product_price'] ) ? $data['product_price'] : '',
				'product_quantity' => isset( $data['product_quantity'] ) ? $data['product_quantity'] : '',
				'product_variation'=> isset( $data['product_variation'] ) ? $data['product_variation'] : '',
				'product_sku'      => isset( $data['product_sku'] ) ? $data['product_sku'] : '',
				'cart_total'       => isset( $data['cart_total'] ) ? $data['cart_total'] : '',
				'site_name'        => $site_name,
				'customer_name'    => $customer_name,
			];

			// Format variation with parentheses if not empty.
			if ( ! empty( $replacements['product_variation'] ) ) {
				$replacements['product_variation'] = ' (' . $replacements['product_variation'] . ')';
			} else {
				$replacements['product_variation'] = '';
			}

			return $replacements;
		}

		/**
		 * Default templates per context.
		 *
		 * @param string $context Context identifier.
		 *
		 * @return string
		 */
		private function get_default_template( $context ) {
			if ( 'cart' === $context ) {
				return __( "New order from {{site_name}}\nCustomer: {{customer_name}}\nCart total: {{cart_total}}", 'onlive-wa-order' );
			}

			return __( "Hello, I want to order {{product_name}}{{product_variation}} - {{product_price}} x {{product_quantity}}", 'onlive-wa-order' );
		}

		/**
		 * Build WhatsApp URL for a message.
		 *
		 * @param string $message Message body.
		 *
		 * @return string
		 */
		public function get_whatsapp_url( $message ) {
			$phone = preg_replace( '/[^0-9\+]/', '', (string) $this->get_setting( 'phone', '' ) );

			if ( empty( $phone ) ) {
				return '';
			}

			// Trim and clean message to avoid encoding issues
			$message = trim( (string) $message );
			
			// Normalize newlines: convert to standard format before URL encoding
			$message = str_replace( [ "\r\n", "\r" ], "\n", $message );
			
			$encoded  = rawurlencode( $message );
			$choice   = $this->get_setting( 'api_choice', 'wa' );
			$endpoint = '';

			// Detect if user is on mobile device
			$is_mobile = wp_is_mobile();

			switch ( $choice ) {
				case 'api':
					// Desktop: use web.whatsapp.com, Mobile: use api.whatsapp.com (opens app)
					if ( $is_mobile ) {
						$endpoint = sprintf( 'https://api.whatsapp.com/send?phone=%1$s&text=%2$s', rawurlencode( $phone ), $encoded );
					} else {
						$endpoint = sprintf( 'https://web.whatsapp.com/send?phone=%1$s&text=%2$s', rawurlencode( $phone ), $encoded );
					}
					break;
				case 'custom':
					$base          = $this->get_setting( 'custom_gateway', '' );
					$query_key     = $this->get_setting( 'custom_query_param', 'text' );
					$base          = esc_url_raw( $base );
					if ( empty( $base ) ) {
						$base = 'https://wa.me/' . rawurlencode( $phone );
					}
					$endpoint = add_query_arg(
						[ $query_key => $encoded ],
						$base
					);
					break;
				case 'wa':
				default:
					// wa.me works for both mobile and desktop automatically
					$endpoint = sprintf( 'https://wa.me/%1$s?text=%2$s', rawurlencode( $phone ), $encoded );
					break;
			}

			/**
			 * Filter the final WhatsApp endpoint URL.
			 *
			 * @param string $endpoint Full WhatsApp URL.
			 * @param string $message  Message body before encoding.
			 * @param Onlive_WA_Order_Pro $plugin Plugin instance.
			 */
			return apply_filters( 'onlive_wa_order_endpoint', $endpoint, $message, $this );
		}
	}
}

/**
 * Activation hook: ensure WooCommerce exists.
 */
function onlive_wa_order_pro_activate() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( esc_html__( 'Onlive WooCommerce WhatsApp Order requires WooCommerce to be installed and active.', 'onlive-wa-order' ) );
	}
}
register_activation_hook( __FILE__, 'onlive_wa_order_pro_activate' );

/**
 * Helper accessor.
 *
 * @return Onlive_WA_Order_Pro
 */
function onlive_wa_order_pro() {
	return Onlive_WA_Order_Pro::instance();
}

onlive_wa_order_pro();

/**
 * Prevent WordPress redirects for our AJAX requests at the earliest possible stage.
 * This prevents 302 redirects that can break AJAX communication.
 */
if ( ! function_exists( 'onlive_wa_prevent_ajax_redirects' ) ) {
	function onlive_wa_prevent_ajax_redirects() {
		// Check if this is a request to our AJAX endpoint
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return;
		}
		
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( ! in_array( $action, [ 'vaog2jucg3f2', 'onlive_wa_ping' ], true ) ) {
			return;
		}
		
		// For our plugin AJAX requests, disable redirects at all levels
		if ( ! has_filter( 'wp_redirect', 'onlive_wa_block_redirect' ) ) {
			add_filter( 'wp_redirect', 'onlive_wa_block_redirect', -999, 2 );
		}
		
		// Remove redirect_canonical hook that might cause 302s
		remove_action( 'template_redirect', 'redirect_canonical' );
		remove_action( 'template_redirect', 'wp_redirect_admin_locations' );
	}
	
	add_action( 'init', 'onlive_wa_prevent_ajax_redirects', -999 );
}

/**
 * Block redirects for our AJAX requests.
 *
 * @param string|false $location The location to redirect to.
 * @param int          $status   The HTTP status code.
 * @return false|string
 */
function onlive_wa_block_redirect( $location, $status ) {
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
	if ( in_array( $action, [ 'vaog2jucg3f2', 'onlive_wa_ping' ], true ) ) {
		// Return false to prevent the redirect
		return false;
	}
	return $location;
}

/**
 * Debug all admin-ajax requests to see what's happening.
 */
function onlive_wa_debug_ajax_requests() {
	// Only log for our actions
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
	if ( ! in_array( $action, [ 'vaog2jucg3f2', 'onlive_wa_ping' ], true ) ) {
		return;
	}

	$log_file = WP_CONTENT_DIR . '/plugins/onlive-whatsapp-order/debug.log';
	$timestamp = date('Y-m-d H:i:s');
	
	$debug_info = [
		'timestamp' => $timestamp,
		'action' => $action,
		'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
		'doing_ajax' => defined('DOING_AJAX') ? DOING_AJAX : 'not_defined',
		'is_admin' => is_admin(),
		'user_logged_in' => is_user_logged_in(),
		'user_id' => is_user_logged_in() ? get_current_user_id() : 'guest',
		'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
		'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown',
		'post_data' => $_POST,
		'get_data' => $_GET,
		'headers' => [
			'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not_set',
			'accept' => $_SERVER['HTTP_ACCEPT'] ?? 'not_set',
			'x_requested_with' => $_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not_set',
		],
		'wp_debug' => [
			'wp_loaded' => did_action('wp_loaded'),
			'init' => did_action('init'),
			'wp' => did_action('wp'),
			'parse_request' => did_action('parse_request'),
			'send_headers' => did_action('send_headers'),
		],
	];

	$log_entry = "[{$timestamp}] AJAX DEBUG: " . json_encode($debug_info, JSON_PRETTY_PRINT) . "\n\n";
	file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
add_action( 'admin_init', 'onlive_wa_debug_ajax_requests', 1 );
add_action( 'wp_ajax_nopriv_vaog2jucg3f2', 'onlive_wa_debug_ajax_requests', -999 );
add_action( 'wp_ajax_vaog2jucg3f2', 'onlive_wa_debug_ajax_requests', -999 );
add_action( 'wp_ajax_nopriv_onlive_wa_ping', 'onlive_wa_debug_ajax_requests', -999 );
add_action( 'wp_ajax_onlive_wa_ping', 'onlive_wa_debug_ajax_requests', -999 );

