<?php
/**
 * Frontend rendering for WhatsApp Order buttons.
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Pro_Frontend' ) ) {
	class Onlive_WA_Order_Pro_Frontend {

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

			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
			add_action( 'wp_head', [ $this, 'output_custom_css' ] );
			
			// Register AJAX handlers with both logged-in and non-logged-in hooks
			// Use priority 0 to execute before other hooks
			add_action( 'wp_ajax_onlive_wa_build_message', [ $this, 'handle_ajax_message' ], 0 );
			add_action( 'wp_ajax_nopriv_onlive_wa_build_message', [ $this, 'handle_ajax_message' ], 0 );
			
			// Prevent redirects during AJAX requests - hook very early
			add_action( 'plugins_loaded', [ $this, 'prevent_ajax_redirect' ], -999 );
			add_action( 'init', [ $this, 'prevent_ajax_redirect' ], 0 );
			
			// Also register on admin_init to ensure it's loaded even if wp_enqueue_scripts isn't called
			add_action( 'admin_init', [ $this, 'register_ajax_handlers' ], 1 );

			add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'render_single_button' ], 21 );
			add_action( 'woocommerce_proceed_to_checkout', [ $this, 'render_cart_button' ], 25 );
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
	}		/**
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
		return in_array( $action, [ 'onlive_wa_build_message' ], true );
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
		return in_array( $action, [ 'onlive_wa_build_message' ], true );
	}		/**
		 * Register AJAX handlers (backup).
		 */
		public function register_ajax_handlers() {
			// Ensure handlers are registered
			if ( ! has_action( 'wp_ajax_onlive_wa_build_message' ) ) {
				add_action( 'wp_ajax_onlive_wa_build_message', [ $this, 'handle_ajax_message' ] );
			}
			if ( ! has_action( 'wp_ajax_nopriv_onlive_wa_build_message' ) ) {
				add_action( 'wp_ajax_nopriv_onlive_wa_build_message', [ $this, 'handle_ajax_message' ] );
			}
		}

		/**
		 * Enqueue frontend assets.
		 */
		public function enqueue_assets() {
			if ( ! $this->plugin->is_enabled() || ! $this->plugin->is_woocommerce_active() ) {
				return;
			}

			$settings = $this->plugin->get_settings();

			if ( ! empty( $settings['load_css'] ) ) {
				wp_enqueue_style(
					'onlive-wa-order-style',
					ONLIVE_WA_ORDER_URL . 'assets/css/style.css',
					[],
					$this->plugin->version
				);
			}

			wp_enqueue_script(
				'onlive-wa-order-frontend',
				ONLIVE_WA_ORDER_URL . 'assets/js/frontend.js',
				[ 'jquery' ],
				$this->plugin->version,
				true
			);

			wp_localize_script(
				'onlive-wa-order-frontend',
				'onliveWAOrder',
				[
					'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
					'nonce'     => wp_create_nonce( 'onlive-wa-order' ),
					'phone'     => $this->plugin->get_setting( 'phone', '' ),
					'buttonSize'=> $this->plugin->get_setting( 'button_size', 'medium' ),
					'colors'    => [
						'background' => $this->plugin->get_setting( 'button_color', '#25D366' ),
						'text'       => $this->plugin->get_setting( 'button_text_color', '#ffffff' ),
					],
					'strings'   => [
						'phoneMissing' => __( 'Please add your WhatsApp number in the plugin settings.', 'onlive-wa-order' ),
						'error'        => __( 'Unable to build the WhatsApp message. Please try again.', 'onlive-wa-order' ),
					],
				]
			);
		}

		/**
		 * Print custom CSS in head.
		 */
		public function output_custom_css() {
			if ( ! $this->plugin->is_enabled() ) {
				return;
			}

			$custom_css = $this->plugin->get_setting( 'custom_css', '' );
			if ( empty( $custom_css ) ) {
				return;
			}
			?>
			<style id="onlive-wa-order-inline-css">
				<?php echo wp_strip_all_tags( $custom_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</style>
			<?php
		}

		/**
		 * Render button on single product page.
		 */
		public function render_single_button() {
			if ( ! $this->plugin->is_enabled() || ! $this->plugin->should_render_button( 'single' ) ) {
				return;
			}

			global $product;
			if ( ! $product instanceof WC_Product ) {
				return;
			}

			$disabled = get_post_meta( $product->get_id(), '_onlive_wa_disable', true );
			if ( 'yes' === $disabled ) {
				return;
			}

			echo wp_kses_post( $this->get_button_markup( $product, 'product' ) );
		}

		/**
		 * Render cart button.
		 */
		public function render_cart_button() {
			if ( ! $this->plugin->is_enabled() || ! $this->plugin->should_render_button( 'cart' ) ) {
				return;
			}

			if ( ! is_cart() ) {
				return;
			}

			if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
				return;
			}

			echo wp_kses_post( $this->get_button_markup( null, 'cart' ) );
		}

		/**
		 * Generate button markup.
		 *
		 * @param WC_Product|null $product Product instance.
		 * @param string          $context Button context.
		 * @param array           $args    Extra args.
		 *
		 * @return string
		 */
		protected function get_button_markup( $product, $context = 'product', $args = [] ) {
			$settings = $this->plugin->get_settings();

			$label = ! empty( $args['label'] ) ? $args['label'] : ( 'cart' === $context ? $settings['button_label_cart'] : $settings['button_label_single'] );

			$product_id = $product instanceof WC_Product ? $product->get_id() : 0;
			$classes    = [ 'onlive-wa-order-button', 'onlive-wa-size-' . $settings['button_size'] ];
			if ( ! empty( $args['class'] ) ) {
				$classes[] = sanitize_html_class( $args['class'] );
			}

			// Always include color, append size if plugin CSS is disabled.
			$style = sprintf( 'background-color:%1$s !important;color:%2$s !important;', $settings['button_color'], $settings['button_text_color'] );
			if ( empty( $settings['load_css'] ) ) {
				switch ( $settings['button_size'] ) {
					case 'small':
						$style .= 'font-size:0.85rem !important;padding:0.5rem 1.25rem !important;';
						break;
					case 'large':
						$style .= 'font-size:1.1rem !important;padding:0.85rem 2rem !important;';
						break;
					case 'medium':
					default:
						$style .= 'font-size:1rem !important;';
						break;
				}
			}

			$attributes = [
				'type'         => 'button',
				'class'        => implode( ' ', array_map( 'sanitize_html_class', $classes ) ),
				'data-context' => sanitize_key( $context ),
				'data-product' => absint( $product_id ),
				'data-sku'     => $product instanceof WC_Product ? esc_attr( $product->get_sku() ) : '',
				'aria-label'   => __( 'Order via WhatsApp', 'onlive-wa-order' ),
				'style'        => $style,
			];

			$attribute_html = '';
			foreach ( $attributes as $key => $value ) {
				if ( '' === $value && 'href' !== $key ) {
					continue;
				}
				$attribute_html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
			}

			$button_html = sprintf( '<button%s>%s %s</button>', $attribute_html, esc_html( $label ), $this->get_button_icon() );

			/**
			 * Filter the rendered WhatsApp button markup.
			 *
			 * @param string                    $button_html HTML markup.
			 * @param string                    $context     Button context.
			 * @param int                       $product_id  Related product ID.
			 * @param array                     $args        Additional args.
			 * @param Onlive_WA_Order_Pro_Frontend $frontend Frontend instance.
			 */
			return apply_filters( 'onlive_wa_order_button_html', $button_html, $context, $product_id, $args, $this );
		}

		/**
		 * Get the button icon SVG.
		 *
		 * @return string
		 */
		protected function get_button_icon() {
			return '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>';
		}

	/**
	 * AJAX handler to build WhatsApp URL.
	 */
	public function handle_ajax_message() {
		// Clear output buffers
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		// Force JSON response headers
		header( 'Content-Type: application/json; charset=UTF-8', true );

		// Check if plugin is enabled
		if ( ! $this->plugin->is_enabled() ) {
			$this->send_json_response( false, 'Plugin is disabled' );
		}

		// Get context
		$context = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : 'product';

		// Prepare data
		if ( 'cart' === $context ) {
			$data = $this->prepare_cart_data();
		} else {
			$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;

			if ( ! $product_id ) {
				$this->send_json_response( false, 'Product ID is required' );
			}

			$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
			$quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) : 1;
			$variations   = isset( $_POST['variations'] ) ? json_decode( wp_unslash( $_POST['variations'] ), true ) : [];

			$data = $this->prepare_product_data( $product_id, $variation_id, $quantity, $variations );
		}

		// Check for errors
		if ( is_wp_error( $data ) ) {
			$this->send_json_response( false, $data->get_error_message() );
		}

		if ( empty( $data ) || ! is_array( $data ) ) {
			$this->send_json_response( false, 'Unable to retrieve product data' );
		}

		// Generate message
		$message = $this->plugin->generate_message( $context, $data );
		if ( empty( $message ) ) {
			$this->send_json_response( false, 'Unable to generate message' );
		}

		// Get WhatsApp URL
		$url = $this->plugin->get_whatsapp_url( $message );

		// Fallback URL
		if ( empty( $url ) ) {
			$url = 'https://wa.me/?text=' . rawurlencode( $message );
		}

		// Return success
		$this->send_json_response( true, 'Success', [ 'url' => $url, 'message' => $message ] );
	}

	/**
	 * Send JSON response and exit.
	 *
	 * @param bool   $success Success status.
	 * @param string $message Response message.
	 * @param array  $data    Additional data.
	 */
	private function send_json_response( $success, $message, $data = [] ) {
		$response = [
			'success' => (bool) $success,
			'message' => $message,
			'data'    => $data,
		];

		echo wp_json_encode( $response );
		exit;
	}


	/**
	 * Prepare product context data.
		 *
		 * @param int   $product_id   Product ID.
		 * @param int   $variation_id Variation ID.
		 * @param int   $quantity     Quantity.
		 * @param array $attributes   Variation attributes.
		 *
		 * @return array|WP_Error
		 */
	protected function prepare_product_data( $product_id, $variation_id, $quantity, $attributes ) {
		try {
			// Get product - use variation if provided
			$product = wc_get_product( $variation_id ?: $product_id );
			if ( ! $product ) {
				return new WP_Error( 'missing_product', __( 'Product not found.', 'onlive-wa-order' ) );
			}

			// Get base product for link
			$base_product = $variation_id ? wc_get_product( $product_id ) : $product;
			
			// Get price with fallback for guest users
			try {
				$price_raw = wc_get_price_to_display( $product );
			} catch ( Exception $e ) {
				$price_raw = $product->get_price();
			}
			
			$price_label = strip_tags( html_entity_decode( wc_price( $price_raw ) ) );

			$variation_text = '';
			if ( ! empty( $attributes ) ) {
				$variation_text = $this->format_variations( $attributes );
			} elseif ( $product->is_type( 'variation' ) ) {
				$variation_text = $this->format_variations( $product->get_variation_attributes() );
			}

			// Get product link if enabled
			$product_link = '';
			if ( $this->plugin->get_setting( 'include_product_link', 1 ) ) {
				$product_link = $base_product->get_permalink();
			}

			return [
				'product_name'      => $product->get_name(),
				'product_link'      => $product_link,
				'product_price'     => $price_label,
				'product_quantity'  => $quantity,
				'product_variation' => $variation_text,
				'product_sku'       => $product->get_sku(),
				'cart_total'        => '',
			];
		} catch ( Exception $e ) {
			// If an exception occurs, return a basic product data with fallbacks
			return new WP_Error( 'product_error', __( 'Error retrieving product information.', 'onlive-wa-order' ) );
		}
	}

		/**
		 * Prepare cart context data.
		 *
		 * @return array|WP_Error
		 */
		protected function prepare_cart_data() {
			if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
				return new WP_Error( 'missing_cart', __( 'Cart is empty.', 'onlive-wa-order' ) );
			}

			$cart        = WC()->cart;
			$items_text  = $this->format_cart_items( $cart->get_cart() );
			$total_raw   = strip_tags( html_entity_decode( $cart->get_total() ) );
			$total_items = $cart->get_cart_contents_count();

			return [
				'product_name'     => __( 'Cart order', 'onlive-wa-order' ),
				'product_price'    => '',
				'product_quantity' => $total_items,
				'product_variation'=> '',
				'cart_total'       => $total_raw,
				'items_formatted'  => $items_text,
			];
		}

		/**
		 * Format cart items list.
		 *
		 * @param array $cart_items Cart items array.
		 *
		 * @return string
		 */
		protected function format_cart_items( $cart_items ) {
			$lines = [];
			foreach ( $cart_items as $item ) {
				$product = $item['data'];
				if ( ! $product ) {
					continue;
				}

				$variation_text = '';
				if ( ! empty( $item['variation'] ) ) {
					$variation_text = $this->format_variations( $item['variation'] );
				}

				$line_total = strip_tags( html_entity_decode( wc_price( $item['line_total'] ) ) );
				$line       = sprintf(
					'%1$dx %2$s%3$s - %4$s',
					$item['quantity'],
					$product->get_name(),
					$variation_text ? ' (' . $variation_text . ')' : '',
					$line_total
				);
				$lines[] = $line;
			}

			if ( empty( $lines ) ) {
				return '';
			}

			return __( 'Items:', 'onlive-wa-order' ) . "\n" . implode( "\n", $lines );
		}

		/**
		 * Format variation attributes into string.
		 *
		 * @param array $attributes Variation data.
		 *
		 * @return string
		 */
		protected function format_variations( $attributes ) {
			if ( empty( $attributes ) ) {
				return '';
			}

			$parts = [];
			foreach ( $attributes as $key => $value ) {
				$key   = wc_attribute_label( str_replace( 'attribute_', '', $key ) );
				$value = sanitize_text_field( $value );
				$parts[] = sprintf( '%1$s: %2$s', $key, $value );
			}

			return implode( ', ', $parts );
		}
	}
}
