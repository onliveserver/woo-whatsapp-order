<?php
/**
 * Plugin Name:       Onlive WooCommerce WhatsApp Order
 * Plugin URI:        https://www.onlivetechnologies.com/plugins/whatsapp-order
 * Description:       Adds customizable WhatsApp "Order Now" buttons to WooCommerce product and cart pages with advanced templates.
 * Version:           1.4.1
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

// ULTRA EARLY AJAX HANDLER - Runs at plugin load time before WordPress hooks
// This is an absolute safety net for AJAX requests to ensure they get a response
if ( php_sapi_name() !== 'cli' && ! empty( $_REQUEST['action'] ) ) {
	$_action = isset( $_REQUEST['action'] ) ? trim( strtolower( (string) $_REQUEST['action'] ) ) : '';
	
	if ( in_array( $_action, [ 'vaog2jucg3f2', 'onlive_wa_ping' ], true ) ) {
		// Check if this looks like an AJAX request
		$is_ajax = ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest';
		
		if ( $is_ajax ) {
			// This IS one of our AJAX requests
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			
			// Set immediate response headers
			@header( 'Content-Type: application/json; charset=UTF-8', true );
			@header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0', true );
			@header( 'Pragma: no-cache', true );
			@header( 'Expires: 0', true );
			@header( 'X-Content-Type-Options: nosniff', true );
			@header( 'HTTP/1.1 200 OK', true, 200 );
			
			// Prepare basic response
			$response = [
				'success' => true,
				'action'  => $_action,
			];
			
			// For ping, respond immediately
			if ( 'onlive_wa_ping' === $_action ) {
				$response['ping'] = 'pong';
				$response['time'] = date( 'Y-m-d H:i:s' );
				@exit( json_encode( $response ) );
			}
			
			// For message action (vaog2jucg3f2), we also respond immediately
			// to avoid the 503 error from WordPress admin-ajax.php
			if ( 'vaog2jucg3f2' === $_action ) {
				// Collect the request data
				$context = isset( $_POST['context'] ) ? (string) $_POST['context'] : 'product';
				$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
				$variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : 0;
				$quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
				$variations_json = isset( $_POST['variations'] ) ? (string) $_POST['variations'] : '';
				
				// DEBUG HANDLERS - Check settings and return debug data
				if ( 'debug_settings' === $context ) {
					// Load WordPress to access settings
					$wp_load_path = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';
					if ( file_exists( $wp_load_path ) ) {
						ob_start();
						require_once $wp_load_path;
						ob_end_clean();
					}
					
					$all_settings = function_exists( 'get_option' ) ? get_option( 'onlive_wa_order_settings', [] ) : [];
					
					$response_data = [
						'success' => true,
						'action' => $_action,
						'context' => 'debug_settings',
						'debug_data' => [
							'get_option_available' => function_exists( 'get_option' ),
							'all_settings_count' => count( $all_settings ),
							'all_settings' => $all_settings,
							'phone' => isset( $all_settings['phone'] ) ? $all_settings['phone'] : 'NOT SET',
							'template_enabled' => isset( $all_settings['template_enabled'] ) ? $all_settings['template_enabled'] : false,
							'message_template' => isset( $all_settings['message_template'] ) ? substr( $all_settings['message_template'], 0, 100 ) . '...' : 'NOT SET',
							'button_text' => isset( $all_settings['button_text'] ) ? $all_settings['button_text'] : 'NOT SET',
							'button_position' => isset( $all_settings['button_position'] ) ? $all_settings['button_position'] : 'NOT SET',
							'timestamp' => date( 'Y-m-d H:i:s' ),
						],
					];
					
					@exit( json_encode( $response_data ) );
				}
				
				if ( 'check_settings' === $context ) {
					// Load WordPress to access settings
					$wp_load_path = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';
					if ( file_exists( $wp_load_path ) ) {
						ob_start();
						require_once $wp_load_path;
						ob_end_clean();
					}
					
					$all_settings = function_exists( 'get_option' ) ? get_option( 'onlive_wa_order_settings', [] ) : [];
					
					$response_data = [
						'success' => true,
						'action' => $_action,
						'context' => 'check_settings',
						'settings_check' => [
							'phone_raw' => isset( $all_settings['phone'] ) ? $all_settings['phone'] : 'EMPTY',
							'phone_sanitized' => isset( $all_settings['phone'] ) ? preg_replace( '/[^0-9\+]/', '', $all_settings['phone'] ) : 'EMPTY',
							'phone_for_wa_me' => isset( $all_settings['phone'] ) ? ltrim( preg_replace( '/[^0-9\+]/', '', $all_settings['phone'] ), '+' ) : 'EMPTY',
							'template_enabled' => ! empty( $all_settings['template_enabled'] ),
							'template_content' => isset( $all_settings['message_template'] ) ? $all_settings['message_template'] : 'EMPTY',
							'all_keys' => array_keys( $all_settings ),
							'total_settings' => count( $all_settings ),
						],
						'timestamp' => date( 'Y-m-d H:i:s' ),
					];
					
					@exit( json_encode( $response_data ) );
				}
				
				// Build product data for message template
				$product_data = [
					'product_name'     => 'Product',
					'product_price'    => '',
					'product_quantity' => $quantity,
					'product_variation' => '',
					'product_sku'      => '',
					'product_link'     => '',
					'site_name'        => 'Our Store',
					'customer_name'    => 'Valued Customer',
					'cart_total'       => '',
				];
				
				// Try to load WordPress to get product data
				$wp_load_path = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';
				if ( file_exists( $wp_load_path ) && ! function_exists( 'wc_get_product' ) ) {
					// Suppress output during WordPress loading
					ob_start();
					require_once $wp_load_path;
					ob_end_clean();
				}
				
				// Get site name
				if ( function_exists( 'get_bloginfo' ) ) {
					$product_data['site_name'] = get_bloginfo( 'name' );
				}
				
				// Get product data from WooCommerce
				if ( function_exists( 'wc_get_product' ) && $product_id > 0 ) {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						$product_data['product_name'] = $product->get_name();
						$product_data['product_link'] = $product->get_permalink();
						
						// Try to get product price - check main product first
						$price = null;
						if ( method_exists( $product, 'get_price' ) ) {
							$price = $product->get_price();
						}
						if ( ! $price && method_exists( $product, 'get_regular_price' ) ) {
							$price = $product->get_regular_price();
						}
						
						if ( $price || $price === 0 || $price === '0' ) {
							// Get currency symbol
							if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
								$currency_symbol = get_woocommerce_currency_symbol();
								$product_data['product_price'] = $currency_symbol . number_format( (float) $price, 2 );
							} else {
								$product_data['product_price'] = '$' . number_format( (float) $price, 2 );
							}
						}
						
						$product_data['product_sku'] = $product->get_sku() ?: '';
						
						// Handle variations
						if ( $variation_id > 0 && $product->is_type( 'variable' ) ) {
							$variation = wc_get_product( $variation_id );
							if ( $variation ) {
								// Get variation price
								$var_price = null;
								if ( method_exists( $variation, 'get_price' ) ) {
									$var_price = $variation->get_price();
								}
								if ( ! $var_price && method_exists( $variation, 'get_regular_price' ) ) {
									$var_price = $variation->get_regular_price();
								}
								
								if ( $var_price || $var_price === 0 || $var_price === '0' ) {
									if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
										$currency_symbol = get_woocommerce_currency_symbol();
										$product_data['product_price'] = $currency_symbol . number_format( (float) $var_price, 2 );
									} else {
										$product_data['product_price'] = '$' . number_format( (float) $var_price, 2 );
									}
								}
								
								// Get variation attributes
								$attrs = $variation->get_attributes();
								$var_parts = [];
								foreach ( $attrs as $attr_name => $attr_value ) {
									$attr_label = ucfirst( str_replace( [ 'pa_', '_' ], [ '', ' ' ], $attr_name ) );
									$var_parts[] = $attr_label . ': ' . ucfirst( $attr_value );
								}
								if ( ! empty( $var_parts ) ) {
									$product_data['product_variation'] = implode( ', ', $var_parts );
								}
							}
						}
					}
				} else if ( $product_id > 0 && ! function_exists( 'wc_get_product' ) ) {
					// Fallback: Query the database directly if WooCommerce functions aren't available
					global $wpdb;
					if ( isset( $wpdb ) && isset( $wpdb->posts ) ) {
						$product_post = $wpdb->get_row( $wpdb->prepare(
							"SELECT post_title, post_name FROM {$wpdb->posts} WHERE ID = %d",
							$product_id
						) );
						if ( $product_post ) {
							$product_data['product_name'] = $product_post->post_title;
						}
					}
				}
				
				// Get phone number and template settings
				$phone = '';
				$phone_raw = '';
				$phone_sanitized = '';
				$template_enabled = false;
				$custom_template = '';
				$include_product_link = false;
				$all_settings = [];
				
				if ( function_exists( 'get_option' ) ) {
					$all_settings = get_option( 'onlive_wa_order_settings', [] );
					$phone_raw = isset( $all_settings['phone'] ) ? (string) $all_settings['phone'] : '';
					$template_enabled = ! empty( $all_settings['template_enabled'] );
					$custom_template = isset( $all_settings['message_template'] ) ? $all_settings['message_template'] : '';
					$include_product_link = ! empty( $all_settings['include_product_link'] );
				}
				
				// Remove product link if not enabled in settings
				if ( ! $include_product_link ) {
					$product_data['product_link'] = '';
				}
				
				// Sanitize phone number: remove all non-digit and non-plus characters
				$phone_sanitized = preg_replace( '/[^0-9\+]/', '', $phone_raw );
				// Remove leading plus for wa.me URL
				$wa_phone = ltrim( $phone_sanitized, '+' );
				
				// Use custom template if enabled, otherwise use default
				if ( $template_enabled && ! empty( $custom_template ) ) {
					$message_template = $custom_template;
				} else {
					$message_template = "Hello, I would like to order {{product_name}}. Price: {{product_price}} x {{product_quantity}}. {{product_variation}}";
				}
				
				// Simple template replacement
				$message = $message_template;
				foreach ( $product_data as $key => $value ) {
					$message = str_replace( '{{' . $key . '}}', (string) $value, $message );
				}
				
				// If quantity is more than 1, add it
				if ( $quantity > 1 ) {
					$message .= "\nQuantity: " . $quantity;
				}
				
				// If there are variations, add them
				if ( ! empty( $product_data['product_variation'] ) ) {
					$message .= "\nVariation: " . $product_data['product_variation'];
				}
				
				// Clean up any remaining template variables
				$message = preg_replace( '/\{\{[^}]+\}\}/', '', $message );
				
				// Build WhatsApp URL with phone number
				if ( ! empty( $wa_phone ) ) {
					$whatsapp_url = 'https://wa.me/' . rawurlencode( $wa_phone ) . '?text=' . rawurlencode( $message );
				} else {
					$whatsapp_url = 'https://wa.me/?text=' . rawurlencode( $message );
				}
				
				// Build response data with comprehensive debugging
				$response_data = [
					'success' => true,
					'action'  => $_action,
					'url' => $whatsapp_url,
					'debug' => [
						'context' => $context,
						'product_id' => $product_id,
						'variation_id' => $variation_id,
						'quantity' => $quantity,
						'product_name' => $product_data['product_name'],
						'product_price' => $product_data['product_price'],
						'settings_retrieval' => [
							'get_option_exists' => function_exists( 'get_option' ),
							'all_settings' => $all_settings,
							'settings_count' => count( $all_settings ),
						],
						'phone_tracking' => [
							'phone_raw' => $phone_raw,
							'phone_sanitized' => $phone_sanitized,
							'phone_for_url' => $wa_phone,
							'phone_present' => ! empty( $phone_raw ),
							'phone_valid' => ! empty( $wa_phone ),
						],
						'template_tracking' => [
							'template_enabled' => $template_enabled,
							'custom_template' => $custom_template,
							'message_template_used' => $message_template,
						],
						'message_info' => [
							'final_message' => $message,
							'message_length' => strlen( $message ),
						],
						'request_received_at' => date( 'Y-m-d H:i:s' ),
						'handler' => 'bootstrap',
					],
				];
				
				@exit( json_encode( $response_data ) );
			}
		}
	}
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

			// Load translations on plugins_loaded (early but after WordPress has initialized)
			add_action( 'plugins_loaded', [ $this, 'load_textdomain' ], 20 );
			// Bootstrap components shortly after plugins are loaded
			add_action( 'plugins_loaded', [ $this, 'bootstrap' ], 30 );
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

			// Check if key exists AND is not empty for phone/template fields
			$value = isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
			
			// For phone specifically, if it's empty string, use the default
			if ( 'phone' === $key && empty( $value ) && ! empty( $default ) ) {
				return $default;
			}
			
			return $value;
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
		 * Uses wa.me for both mobile and desktop (most reliable endpoint).
		 *
		 * @param string $message Message body.
		 *
		 * @return string
		 */
		public function get_whatsapp_url( $message ) {
			$raw_phone = (string) $this->get_setting( 'phone', '+919100454045' );
			// Remove all non-digit and non-plus characters
			$phone = preg_replace( '/[^0-9\+]/', '', $raw_phone );
			// Remove leading plus for wa.me URL
			$wa_phone = ltrim($phone, '+');

			if ( empty( $wa_phone ) ) {
				return '';
			}

			// Trim and clean message to avoid encoding issues
			$message = trim( (string) $message );
			// Normalize newlines: convert to standard format before URL encoding
			$message = str_replace( [ "\r\n", "\r" ], "\n", $message );
			$encoded = rawurlencode( $message );

			// Use wa.me for both mobile and desktop - most reliable endpoint
			$endpoint = sprintf( 'https://wa.me/%1$s?text=%2$s', rawurlencode( $wa_phone ), $encoded );

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
 * Check if we're handling our AJAX request early.
 */
function onlive_wa_check_ajax_early() {
	// Only process AJAX requests
	if ( empty( $_REQUEST['action'] ) ) {
		return;
	}

	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
	if ( ! in_array( $action, [ 'vaog2jucg3f2', 'onlive_wa_ping' ], true ) ) {
		return;
	}

	// Verify it's an AJAX request
	if ( empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || 'xmlhttprequest' !== strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
		return;
	}

	// Define DOING_AJAX at the absolute earliest point
	if ( ! defined( 'DOING_AJAX' ) ) {
		define( 'DOING_AJAX', true );
	}

	error_log( '[' . date( 'Y-m-d H:i:s' ) . '] AJAX check early: action=' . $action );
}

add_action( 'init', 'onlive_wa_check_ajax_early', -9999 );

/**
 * Directly handle our AJAX requests if WordPress hooks aren't firing.
 * This is a fallback for servers where wp_ajax_* hooks don't work properly.
 */
function onlive_wa_direct_ajax_handler() {
	// Only process on AJAX requests to our endpoints
	if ( empty( $_REQUEST['action'] ) ) {
		return;
	}

	$action = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
	if ( ! in_array( $action, [ 'vaog2jucg3f2', 'onlive_wa_ping' ], true ) ) {
		return;
	}

	// Verify this is an AJAX request
	$is_ajax = ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] );
	if ( ! $is_ajax ) {
		return;
	}

	// Make sure DOING_AJAX is defined
	if ( ! defined( 'DOING_AJAX' ) ) {
		define( 'DOING_AJAX', true );
	}

	// Disable error display to prevent it from interfering with JSON response
	@ini_set( 'display_errors', 0 );

	// Log entry
	error_log( '[' . date( 'Y-m-d H:i:s' ) . '] Direct AJAX handler triggered for: ' . $action );

	try {
		// Clear ALL output buffers
		if ( function_exists( 'ob_get_level' ) ) {
			while ( ob_get_level() > 0 ) {
				ob_end_clean();
			}
		}

		// Start fresh output buffering to catch any stray output
		ob_start();

		// Set JSON header
		header( 'Content-Type: application/json; charset=UTF-8', true );
		header( 'Cache-Control: no-cache, no-store, must-revalidate', true );

		// Get plugin instance  
		if ( ! function_exists( 'onlive_wa_order_pro' ) ) {
			error_log( '[' . date( 'Y-m-d H:i:s' ) . '] ERROR: Plugin function does not exist' );
			http_response_code( 500 );
			die( json_encode( [ 'success' => false, 'message' => 'Plugin not found' ] ) );
		}

		$plugin = onlive_wa_order_pro();

		if ( ! $plugin ) {
			error_log( '[' . date( 'Y-m-d H:i:s' ) . '] ERROR: Plugin instance is null' );
			http_response_code( 500 );
			die( json_encode( [ 'success' => false, 'message' => 'Plugin not initialized' ] ) );
		}

		if ( ! $plugin->frontend ) {
			error_log( '[' . date( 'Y-m-d H:i:s' ) . '] ERROR: Frontend instance is null' );
			http_response_code( 500 );
			die( json_encode( [ 'success' => false, 'message' => 'Frontend not initialized' ] ) );
		}

		// Clear buffer before calling handler
		@ob_end_clean();

		// Call handler directly
		if ( 'vaog2jucg3f2' === $action ) {
			error_log( '[' . date( 'Y-m-d H:i:s' ) . '] Calling handle_ajax_message' );
			$plugin->frontend->handle_ajax_message();
		} elseif ( 'onlive_wa_ping' === $action ) {
			error_log( '[' . date( 'Y-m-d H:i:s' ) . '] Calling handle_ping' );
			$plugin->frontend->handle_ping();
		}

		// If we get here, handler didn't exit
		error_log( '[' . date( 'Y-m-d H:i:s' ) . '] WARNING: Handler did not terminate' );
		http_response_code( 500 );
		die( json_encode( [ 'success' => false, 'message' => 'Handler execution error' ] ) );

	} catch ( Throwable $e ) {
		// Make sure we're in a clean state
		if ( function_exists( 'ob_get_level' ) ) {
			while ( ob_get_level() > 0 ) {
				ob_end_clean();
			}
		}

		error_log( '[' . date( 'Y-m-d H:i:s' ) . '] EXCEPTION: ' . $e->getMessage() );
		error_log( '[' . date( 'Y-m-d H:i:s' ) . '] File: ' . $e->getFile() . ' Line: ' . $e->getLine() );

		http_response_code( 500 );
		die( json_encode( [ 
			'success' => false, 
			'message' => 'Server error',
			'error' => $e->getMessage()
		] ) );
	}
}

// Hook into init at highest priority FIRST, then wp_loaded as fallback
add_action( 'init', 'onlive_wa_direct_ajax_handler', 99999 );
add_action( 'wp_loaded', 'onlive_wa_direct_ajax_handler', 99999 );

