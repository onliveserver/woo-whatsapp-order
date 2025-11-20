<?php

/**
 * Frontend rendering for WhatsApp Order buttons.
 *
 * @package Onlive_WA_Order
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! class_exists('Onlive_WA_Order_Pro_Frontend')) {
	class Onlive_WA_Order_Pro_Frontend
	{

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
		public function __construct(Onlive_WA_Order_Pro $plugin)
		{
			$this->plugin = $plugin;

			// Log frontend initialization
			$this->log_error('=== FRONTEND CLASS CONSTRUCTOR START ===');
			$this->log_error('Plugin enabled: ' . ($this->plugin->is_enabled() ? 'YES' : 'NO'));
			$this->log_error('WooCommerce active: ' . ($this->plugin->is_woocommerce_active() ? 'YES' : 'NO'));

			add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
			add_action('wp_head', [$this, 'output_custom_css']);

			// Register AJAX handlers with both logged-in and non-logged-in hooks
			// Use priority 0 to execute before other hooks
			add_action('wp_ajax_vaog2jucg3f2', [$this, 'handle_ajax_message'], 0);
			add_action('wp_ajax_nopriv_vaog2jucg3f2', [$this, 'handle_ajax_message'], 0);
			$this->log_error('AJAX handlers registered for action: vaog2jucg3f2');

			// Add a simple ping endpoint for testing
			add_action('wp_ajax_onlive_wa_ping', [$this, 'handle_ping'], 0);
			add_action('wp_ajax_nopriv_onlive_wa_ping', [$this, 'handle_ping'], 0);
			$this->log_error('Ping handlers registered for action: onlive_wa_ping');

			// Add early debug logging callback to track if our hooks are firing
			add_action('wp_ajax_vaog2jucg3f2', function() {
				$log_file = WP_CONTENT_DIR . '/plugins/onlive-whatsapp-order/error.log';
				$timestamp = date('Y-m-d H:i:s');
				$log_entry = "[$timestamp] === wp_ajax_vaog2jucg3f2 HOOK FIRED ===\n";
				file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
			}, -1000); // Very early priority

			add_action('wp_ajax_nopriv_vaog2jucg3f2', function() {
				$log_file = WP_CONTENT_DIR . '/plugins/onlive-whatsapp-order/error.log';
				$timestamp = date('Y-m-d H:i:s');
				$log_entry = "[$timestamp] === wp_ajax_nopriv_vaog2jucg3f2 HOOK FIRED ===\n";
				file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
			}, -1000); // Very early priority

			// Prevent redirects during AJAX requests - hook very early
			add_action('plugins_loaded', [$this, 'prevent_ajax_redirect'], -999);
			add_action('init', [$this, 'prevent_ajax_redirect'], 0);

			// Also register on admin_init to ensure it's loaded even if wp_enqueue_scripts isn't called
			add_action('admin_init', [$this, 'register_ajax_handlers'], 1);

			add_action('woocommerce_after_add_to_cart_button', [$this, 'render_single_button'], 21);
			add_action('woocommerce_proceed_to_checkout', [$this, 'render_cart_button'], 25);

			$this->log_error('=== FRONTEND CLASS CONSTRUCTOR COMPLETE ===');
		}

		/**
		 * Custom error logging to plugin directory.
		 *
		 * @param string $message Log message.
		 */
		private function log_error($message)
		{
			$log_file = WP_CONTENT_DIR . '/plugins/onlive-whatsapp-order/error.log';
			$timestamp = date('Y-m-d H:i:s');
			$log_entry = "[$timestamp] $message\n";
			file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
		}

		/**
		 * Prevent WordPress redirects during AJAX requests.
		 */
		public function prevent_ajax_redirect()
		{
			// Check if this is an AJAX request for our plugin
			if ($this->is_our_ajax_request()) {
				// Prevent all redirects and canonicalization
				remove_action('template_redirect', 'redirect_canonical');
				remove_action('template_redirect', 'wp_redirect_admin_locations');

				// Prevent any post type routing from happening
				if (! has_filter('status_header', [$this, 'filter_status_header'])) {
					add_filter('status_header', [$this, 'filter_status_header'], 10, 2);
				}

				// Also prevent 404 handling
				if (! has_filter('pre_handle_404', [$this, 'handle_404_override'])) {
					add_filter('pre_handle_404', [$this, 'handle_404_override'], 10, 1);
				}
			} else if ($this->is_our_ajax_request_early()) {
				// Early detection if DOING_AJAX hasn't been set yet
				remove_action('template_redirect', 'redirect_canonical');
				remove_action('template_redirect', 'wp_redirect_admin_locations');
			}
		}
		/**
		 * Override 404 handling for AJAX requests.
		 *
		 * @param bool $handled Whether the request was handled.
		 * @return bool
		 */
		public function handle_404_override($handled)
		{
			if ($this->is_our_ajax_request()) {
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
		public function filter_status_header($status, $code)
		{
			if ($this->is_our_ajax_request() && 404 === $code) {
				return 'HTTP/1.1 200 OK';
			}
			return $status;
		}

		/**
		 * Check if this is an AJAX request for our plugin.
		 *
		 * @return bool
		 */
		private function is_our_ajax_request()
		{
			if (! defined('DOING_AJAX') || ! DOING_AJAX) {
				return false;
			}

			$action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
			return in_array($action, ['vaog2jucg3f2'], true);
		}

		/**
		 * Check if this is our AJAX request even before DOING_AJAX is set.
		 * This is used during early hooks like plugins_loaded.
		 *
		 * @return bool
		 */
		private function is_our_ajax_request_early()
		{
			// Check for AJAX indicator in request
			$has_ajax_header = ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' === strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);

			if (! $has_ajax_header) {
				return false;
			}

			$action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
			return in_array($action, ['vaog2jucg3f2'], true);
		}
		/**
		 * Register AJAX handlers (backup).
		 */
		public function register_ajax_handlers()
		{
			$this->log_error('=== REGISTERING AJAX HANDLERS ===');
			$this->log_error('Checking if wp_ajax_vaog2jucg3f2 exists: ' . (has_action('wp_ajax_vaog2jucg3f2') ? 'YES' : 'NO'));
			$this->log_error('Checking if wp_ajax_nopriv_vaog2jucg3f2 exists: ' . (has_action('wp_ajax_nopriv_vaog2jucg3f2') ? 'YES' : 'NO'));

			// Ensure handlers are registered
			if (! has_action('wp_ajax_vaog2jucg3f2')) {
				add_action('wp_ajax_vaog2jucg3f2', [$this, 'handle_ajax_message']);
				$this->log_error('Registered wp_ajax_vaog2jucg3f2 handler');
			} else {
				$this->log_error('wp_ajax_vaog2jucg3f2 handler already exists');
			}

			if (! has_action('wp_ajax_nopriv_vaog2jucg3f2')) {
				add_action('wp_ajax_nopriv_vaog2jucg3f2', [$this, 'handle_ajax_message']);
				$this->log_error('Registered wp_ajax_nopriv_vaog2jucg3f2 handler');
			} else {
				$this->log_error('wp_ajax_nopriv_vaog2jucg3f2 handler already exists');
			}

			$this->log_error('=== AJAX HANDLERS REGISTRATION COMPLETE ===');
		}
		/**
		 * Enqueue frontend assets.
		 */
		public function enqueue_assets()
		{
			if (! $this->plugin->is_enabled() || ! $this->plugin->is_woocommerce_active()) {
				$this->log_error('Assets not enqueued - plugin disabled or WooCommerce inactive');
				return;
			}

			$this->log_error('=== ENQUEUEING ASSETS ===');
			$this->log_error('Current page is product: ' . (is_product() ? 'YES' : 'NO'));
			$this->log_error('Current page is cart: ' . (is_cart() ? 'YES' : 'NO'));

			$settings = $this->plugin->get_settings();

			if (! empty($settings['load_css'])) {
				wp_enqueue_style(
					'onlive-wa-order-style',
					ONLIVE_WA_ORDER_URL . 'assets/css/style.css',
					[],
					$this->plugin->version
				);
				$this->log_error('CSS enqueued');
			}

			wp_enqueue_script(
				'onlive-wa-order-frontend',
				ONLIVE_WA_ORDER_URL . 'assets/js/frontend.js',
				['jquery'],
				$this->plugin->version,
				true
			);
			$this->log_error('JavaScript enqueued');

			$localized_data = [
				'ajaxUrl'   => admin_url('admin-ajax.php'),
				'nonce'     => wp_create_nonce('onlive-wa-order'),
				'phone'     => $this->plugin->get_setting('phone', ''),
				'buttonSize' => $this->plugin->get_setting('button_size', 'medium'),
				'colors'    => [
					'background' => $this->plugin->get_setting('button_color', '#25D366'),
					'text'       => $this->plugin->get_setting('button_text_color', '#ffffff'),
				],
				'strings'   => [
					'phoneMissing' => __('Please add your WhatsApp number in the plugin settings.', 'onlive-wa-order'),
					'error'        => __('Unable to build the WhatsApp message. Please try again.', 'onlive-wa-order'),
				],
			];

			wp_localize_script(
				'onlive-wa-order-frontend',
				'onliveWAOrder',
				$localized_data
			);

			$this->log_error('JavaScript localized with data: ' . json_encode($localized_data));
			$this->log_error('=== ASSETS ENQUEUE COMPLETE ===');
		}

		/**
		 * Print custom CSS in head.
		 */
		public function output_custom_css()
		{
			if (! $this->plugin->is_enabled()) {
				return;
			}

			$custom_css = $this->plugin->get_setting('custom_css', '');
			if (empty($custom_css)) {
				return;
			}
?>
			<style id="onlive-wa-order-inline-css">
				<?php echo wp_strip_all_tags($custom_css); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				?>
			</style>
<?php
		}

		/**
		 * Render button on single product page.
		 */
		public function render_single_button()
		{
			$this->log_error('=== RENDER SINGLE BUTTON CHECK ===');
			$this->log_error('Plugin enabled: ' . ($this->plugin->is_enabled() ? 'YES' : 'NO'));
			$this->log_error('Should render single: ' . ($this->plugin->should_render_button('single') ? 'YES' : 'NO'));
			$this->log_error('Is product page: ' . (is_product() ? 'YES' : 'NO'));

			if (! $this->plugin->is_enabled() || ! $this->plugin->should_render_button('single')) {
				$this->log_error('Single button not rendered - plugin disabled or not configured for single pages');
				return;
			}

			global $product;
			$this->log_error('Global $product exists: ' . (isset($product) ? 'YES' : 'NO'));
			$this->log_error('Product type: ' . (isset($product) ? get_class($product) : 'N/A'));

			if (! $product instanceof WC_Product) {
				$this->log_error('Single button not rendered - invalid product object');
				return;
			}

			$disabled = get_post_meta($product->get_id(), '_onlive_wa_disable', true);
			$this->log_error('Product disabled for WhatsApp: ' . ($disabled === 'yes' ? 'YES' : 'NO'));

			if ('yes' === $disabled) {
				$this->log_error('Single button not rendered - disabled for this product');
				return;
			}

			$this->log_error('Rendering single button for product ID: ' . $product->get_id());
			echo wp_kses_post($this->get_button_markup($product, 'product'));
			$this->log_error('Single button rendered successfully');
		}

		/**
		 * Render cart button.
		 */
		public function render_cart_button()
		{
			$this->log_error('=== RENDER CART BUTTON CHECK ===');
			$this->log_error('Plugin enabled: ' . ($this->plugin->is_enabled() ? 'YES' : 'NO'));
			$this->log_error('Should render cart: ' . ($this->plugin->should_render_button('cart') ? 'YES' : 'NO'));
			$this->log_error('Is cart page: ' . (is_cart() ? 'YES' : 'NO'));

			if (! $this->plugin->is_enabled() || ! $this->plugin->should_render_button('cart')) {
				$this->log_error('Cart button not rendered - plugin disabled or not configured for cart');
				return;
			}

			if (! is_cart()) {
				$this->log_error('Cart button not rendered - not on cart page');
				return;
			}

			$this->log_error('WC function exists: ' . (function_exists('WC') ? 'YES' : 'NO'));
			$this->log_error('WC cart exists: ' . (function_exists('WC') && WC()->cart ? 'YES' : 'NO'));
			$this->log_error('Cart is empty: ' . (function_exists('WC') && WC()->cart && WC()->cart->is_empty() ? 'YES' : 'NO'));

			if (! function_exists('WC') || ! WC()->cart || WC()->cart->is_empty()) {
				$this->log_error('Cart button not rendered - cart not available or empty');
				return;
			}

			$this->log_error('Rendering cart button');
			echo wp_kses_post($this->get_button_markup(null, 'cart'));
			$this->log_error('Cart button rendered successfully');
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
		protected function get_button_markup($product, $context = 'product', $args = [])
		{
			$settings = $this->plugin->get_settings();

			$label = ! empty($args['label']) ? $args['label'] : ('cart' === $context ? $settings['button_label_cart'] : $settings['button_label_single']);

			$product_id = $product instanceof WC_Product ? $product->get_id() : 0;
			$classes    = ['onlive-wa-order-button', 'onlive-wa-size-' . $settings['button_size']];
			if (! empty($args['class'])) {
				$classes[] = sanitize_html_class($args['class']);
			}

			// Always include color, append size if plugin CSS is disabled.
			$style = sprintf('background-color:%1$s !important;color:%2$s !important;', $settings['button_color'], $settings['button_text_color']);
			if (empty($settings['load_css'])) {
				switch ($settings['button_size']) {
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
				'class'        => implode(' ', array_map('sanitize_html_class', $classes)),
				'data-context' => sanitize_key($context),
				'data-product' => absint($product_id),
				'data-sku'     => $product instanceof WC_Product ? esc_attr($product->get_sku()) : '',
				'aria-label'   => __('Order via WhatsApp', 'onlive-wa-order'),
				'style'        => $style,
			];

			$attribute_html = '';
			foreach ($attributes as $key => $value) {
				if ('' === $value && 'href' !== $key) {
					continue;
				}
				$attribute_html .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
			}

			$button_html = sprintf('<button%s>%s %s</button>', $attribute_html, esc_html($label), $this->get_button_icon());

			/**
			 * Filter the rendered WhatsApp button markup.
			 *
			 * @param string                    $button_html HTML markup.
			 * @param string                    $context     Button context.
			 * @param int                       $product_id  Related product ID.
			 * @param array                     $args        Additional args.
			 * @param Onlive_WA_Order_Pro_Frontend $frontend Frontend instance.
			 */
			return apply_filters('onlive_wa_order_button_html', $button_html, $context, $product_id, $args, $this);
		}

		/**
		 * Get the button icon SVG.
		 *
		 * @return string
		 */
		protected function get_button_icon()
		{
			return '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>';
		}

	/**
	 * AJAX handler to build WhatsApp URL.
	 */
	public function handle_ajax_message()
	{
		// Log the start of AJAX request
		$this->log_error('=== AJAX REQUEST START ===');
		$this->log_error('Timestamp: ' . date('Y-m-d H:i:s'));
		$this->log_error('User logged in: ' . (is_user_logged_in() ? 'YES' : 'NO'));
		$this->log_error('User ID: ' . (is_user_logged_in() ? get_current_user_id() : 'N/A'));
		$this->log_error('Request method: ' . $_SERVER['REQUEST_METHOD']);
		$this->log_error('Action: ' . ($_REQUEST['action'] ?? 'none'));
		$this->log_error('DOING_AJAX defined: ' . (defined('DOING_AJAX') ? 'YES' : 'NO'));
		$this->log_error('DOING_AJAX value: ' . (defined('DOING_AJAX') ? DOING_AJAX : 'N/A'));
		$this->log_error('POST data: ' . json_encode($_POST));
		$this->log_error('GET data: ' . json_encode($_GET));
		$this->log_error('REQUEST data: ' . json_encode($_REQUEST));
		$this->log_error('SERVER SCRIPT_NAME: ' . ($_SERVER['SCRIPT_NAME'] ?? 'none'));
		$this->log_error('SERVER REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'none'));

		// Check if this is actually our request
		$action = $_REQUEST['action'] ?? '';
		if ($action !== 'vaog2jucg3f2') {
			$this->log_error('ERROR: Wrong action received: ' . $action . ' (expected: vaog2jucg3f2)');
			$this->send_json_response(false, 'Invalid action', []);
			return; // Don't process wrong actions
		}

		$this->log_error('Action is correct, processing request...');

		// Clear output buffers
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		// Force JSON response headers
		header('Content-Type: application/json; charset=UTF-8', true);

		// Ensure WooCommerce is properly loaded for AJAX requests
		if (!defined('WC_ABSPATH')) {
			define('WC_ABSPATH', dirname(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php') . '/');
		}

		// Initialize WooCommerce if not already done
		if (!function_exists('WC') && !did_action('woocommerce_init')) {
			$this->log_error('Initializing WooCommerce...');
			// Load WooCommerce core
			require_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
			// Initialize WooCommerce
			WC();
			$this->log_error('WooCommerce initialized');
		}

		// Ensure session is started for non-logged-in users
		if (!is_user_logged_in() && function_exists('WC')) {
			$this->log_error('Initializing session/cart for non-logged-in user');
			if (!WC()->session) {
				WC()->initialize_session();
				$this->log_error('Session initialized');
			}
			if (!WC()->cart) {
				WC()->initialize_cart();
				$this->log_error('Cart initialized');
			}
		}

		$debug_info = [
			'timestamp' => time(),
			'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
			'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
			'is_ajax' => defined('DOING_AJAX') && DOING_AJAX,
			'action' => $_REQUEST['action'] ?? 'none',
		];

		try {
			$this->log_error('Checking if plugin is enabled...');
			// Check if plugin is enabled
			if (! $this->plugin->is_enabled()) {
				$this->log_error('Plugin is disabled');
				$debug_info['error'] = 'Plugin disabled';
				$this->send_json_response(false, 'Plugin is disabled', ['debug' => $debug_info]);
			}

			$this->log_error('Plugin is enabled, getting context...');
			// Get context
			$context = isset($_POST['context']) ? sanitize_key(wp_unslash($_POST['context'])) : 'product';
			$debug_info['context'] = $context;
			$this->log_error('Context: ' . $context);

		// Prepare data
		if ('cart' === $context) {
			$data = $this->prepare_cart_data();
			$debug_info['data_type'] = 'cart';
		} else {
			$product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;

			if (! $product_id) {
				$debug_info['error'] = 'Product ID required';
				$this->send_json_response(false, 'Product ID is required', ['debug' => $debug_info]);
			}

			$variation_id = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0;
			$quantity     = isset($_POST['quantity']) ? max(1, absint(wp_unslash($_POST['quantity']))) : 1;
			$variations   = isset($_POST['variations']) ? json_decode(wp_unslash($_POST['variations']), true) : [];

			$debug_info['product_id'] = $product_id;
			$debug_info['variation_id'] = $variation_id;
			$debug_info['quantity'] = $quantity;

			$data = $this->prepare_product_data($product_id, $variation_id, $quantity, $variations);
			$this->log_error('Product data prepared: ' . (is_wp_error($data) ? 'ERROR - ' . $data->get_error_message() : 'SUCCESS'));
		}

		// Check for errors
		if (is_wp_error($data)) {
			$this->log_error('Data preparation error: ' . $data->get_error_message());
			$debug_info['error'] = 'Data preparation error: ' . $data->get_error_message();
			$this->send_json_response(false, $data->get_error_message(), ['debug' => $debug_info]);
		}

		if (empty($data) || ! is_array($data)) {
			$this->log_error('Data is empty or not an array');
			$debug_info['error'] = 'Data empty or invalid';
			$this->send_json_response(false, 'Unable to retrieve product data', ['debug' => $debug_info]);
		}

		$this->log_error('Data validation passed, generating message...');

		// Generate message
		$message = $this->plugin->generate_message($context, $data);
		if (empty($message)) {
			$this->log_error('Message generation failed');
			$debug_info['error'] = 'Message generation failed';
			$this->send_json_response(false, 'Unable to generate message', ['debug' => $debug_info]);
		}

		$this->log_error('Message generated successfully, getting WhatsApp URL...');

		// Get WhatsApp URL
		$url = $this->plugin->get_whatsapp_url($message);

		// Fallback URL
		if (empty($url)) {
			$url = 'https://wa.me/?text=' . rawurlencode($message);
		}

		$this->log_error('WhatsApp URL generated: ' . $url);
		$this->log_error('=== AJAX REQUEST END - SUCCESS ===');

		$debug_info['success'] = true;
		$debug_info['url_length'] = strlen($url);
		$debug_info['message_length'] = strlen($message);

		// Return success
		$this->send_json_response(true, 'Success', ['url' => $url, 'message' => $message, 'debug' => $debug_info]);

		} catch (Exception $e) {
			$this->log_error('=== AJAX REQUEST END - EXCEPTION ===');
			$this->log_error('Exception: ' . $e->getMessage());
			$this->log_error('Stack trace: ' . $e->getTraceAsString());
			$debug_info['exception'] = $e->getMessage();
			$this->send_json_response(false, 'Exception: ' . $e->getMessage(), ['debug' => $debug_info]);
		}
	}		/**
		 * Send JSON response and exit.
		 *
		 * @param bool   $success Success status.
		 * @param string $message Response message.
		 * @param array  $data    Additional data.
		 */
		private function send_json_response($success, $message, $data = [])
		{
			$response = [
				'success' => (bool) $success,
				'message' => $message,
				'data'    => $data,
			];

			echo wp_json_encode($response);
			exit;
		}

		/**
		 * Simple ping handler for testing AJAX connectivity.
		 */
		public function handle_ping()
		{
			$this->log_error('=== PING REQUEST RECEIVED ===');
			$this->log_error('Timestamp: ' . date('Y-m-d H:i:s'));
			$this->log_error('User logged in: ' . (is_user_logged_in() ? 'YES' : 'NO'));
			$this->log_error('User ID: ' . (is_user_logged_in() ? get_current_user_id() : 'N/A'));

			$this->send_json_response(true, 'Ping successful', [
				'timestamp' => time(),
				'user_logged_in' => is_user_logged_in(),
				'plugin_enabled' => $this->plugin->is_enabled(),
				'woocommerce_active' => $this->plugin->is_woocommerce_active(),
			]);
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
		protected function prepare_product_data($product_id, $variation_id, $quantity, $attributes)
		{
			try {
				// Get product - use variation if provided
				$product = wc_get_product($variation_id ?: $product_id);
				if (! $product) {
					return new WP_Error('missing_product', __('Product not found.', 'onlive-wa-order'));
				}

				// Get base product for link
				$base_product = $variation_id ? wc_get_product($product_id) : $product;

				// Get price with fallback for guest users
				try {
					$price_raw = wc_get_price_to_display($product);
				} catch (Exception $e) {
					$price_raw = $product->get_price();
				}

				$price_label = strip_tags(html_entity_decode(wc_price($price_raw)));

				$variation_text = '';
				if (! empty($attributes)) {
					$variation_text = $this->format_variations($attributes);
				} elseif ($product->is_type('variation')) {
					$variation_text = $this->format_variations($product->get_variation_attributes());
				}

				// Get product link if enabled
				$product_link = '';
				if ($this->plugin->get_setting('include_product_link', 1)) {
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
			} catch (Exception $e) {
				// If an exception occurs, return a basic product data with fallbacks
				return new WP_Error('product_error', __('Error retrieving product information.', 'onlive-wa-order'));
			}
		}

		/**
		 * Prepare cart context data.
		 *
		 * @return array|WP_Error
		 */
		protected function prepare_cart_data()
		{
			$this->log_error('=== PREPARING CART DATA ===');
			$this->log_error('WC function exists: ' . (function_exists('WC') ? 'YES' : 'NO'));
			$this->log_error('WC()->cart exists: ' . (function_exists('WC') && WC()->cart ? 'YES' : 'NO'));

			if (! function_exists('WC') || ! WC()->cart) {
				$this->log_error('Cart not available - returning error');
				return new WP_Error('missing_cart', __('Cart is empty or not available.', 'onlive-wa-order'));
			}

			$cart = WC()->cart;
			$cart_contents = $cart->get_cart();
			$this->log_error('Cart contents count: ' . count($cart_contents));

			// Check if cart has items
			if (empty($cart_contents)) {
				$this->log_error('Cart is empty - returning error');
				return new WP_Error('empty_cart', __('Your cart is empty.', 'onlive-wa-order'));
			}

			$this->log_error('Cart has items, formatting cart data...');
			$items_text  = $this->format_cart_items($cart_contents);
			$total_raw   = strip_tags(html_entity_decode($cart->get_total()));
			$total_items = $cart->get_cart_contents_count();

			return [
				'product_name'     => __('Cart order', 'onlive-wa-order'),
				'product_price'    => '',
				'product_quantity' => $total_items,
				'product_variation' => '',
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
		protected function format_cart_items($cart_items)
		{
			$lines = [];
			foreach ($cart_items as $item) {
				$product = $item['data'];
				if (! $product) {
					continue;
				}

				$variation_text = '';
				if (! empty($item['variation'])) {
					$variation_text = $this->format_variations($item['variation']);
				}

				$line_total = strip_tags(html_entity_decode(wc_price($item['line_total'])));
				$line       = sprintf(
					'%1$dx %2$s%3$s - %4$s',
					$item['quantity'],
					$product->get_name(),
					$variation_text ? ' (' . $variation_text . ')' : '',
					$line_total
				);
				$lines[] = $line;
			}

			if (empty($lines)) {
				return '';
			}

			return __('Items:', 'onlive-wa-order') . "\n" . implode("\n", $lines);
		}

		/**
		 * Format variation attributes into string.
		 *
		 * @param array $attributes Variation data.
		 *
		 * @return string
		 */
		protected function format_variations($attributes)
		{
			if (empty($attributes)) {
				return '';
			}

			$parts = [];
			foreach ($attributes as $key => $value) {
				$key   = wc_attribute_label(str_replace('attribute_', '', $key));
				$value = sanitize_text_field($value);
				$parts[] = sprintf('%1$s: %2$s', $key, $value);
			}

			return implode(', ', $parts);
		}
	}
}
