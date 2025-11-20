<?php
/**
 * Admin settings for Onlive WhatsApp Order Pro.
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Pro_Admin' ) ) {
	class Onlive_WA_Order_Pro_Admin {

		/**
		 * Plugin instance.
		 *
		 * @var Onlive_WA_Order_Pro
		 */
		protected $plugin;

		/**
		 * Settings option name.
		 *
		 * @var string
		 */
		protected $option_name = 'onlive_wa_order_settings';

		/**
		 * Constructor.
		 *
		 * @param Onlive_WA_Order_Pro $plugin Plugin bootstrapper.
		 */
		public function __construct( Onlive_WA_Order_Pro $plugin ) {
			$this->plugin = $plugin;

			add_action( 'admin_menu', [ $this, 'register_menu' ] );
			add_action( 'admin_init', [ $this, 'register_settings' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
			add_action( 'add_meta_boxes', [ $this, 'register_product_metabox' ] );
			add_action( 'save_post_product', [ $this, 'save_product_metabox' ] );
			add_action( 'wp_ajax_onlive_wa_check_updates', [ $this, 'ajax_check_updates' ] );
			add_action( 'wp_ajax_onlive_wa_force_reinstall', [ $this, 'ajax_force_reinstall' ] );
			add_action( 'wp_ajax_onlive_wa_auto_install_update', [ $this, 'ajax_auto_install_update' ] );
		}

		/**
		 * Register admin menu and page.
		 */
		public function register_menu() {
			add_menu_page(
				__( 'WhatsApp Order Pro', 'onlive-wa-order' ),
				__( 'WhatsApp Order Pro', 'onlive-wa-order' ),
				'manage_options',
				'onlive-wa-order',
				[ $this, 'render_settings_page' ],
				'dashicons-whatsapp',
				56
			);
		}

		/**
		 * Register settings, sections, and fields.
		 */
		public function register_settings() {
			register_setting( 'onlive_wa_order_settings_group', $this->option_name, [ $this, 'sanitize_settings' ] );

			add_settings_section( 'onlive_wa_order_general', __( 'General Settings', 'onlive-wa-order' ), '__return_false', 'onlive-wa-order-general' );
			add_settings_field( 'enabled', __( 'Enable plugin', 'onlive-wa-order' ), [ $this, 'field_enable_plugin' ], 'onlive-wa-order-general', 'onlive_wa_order_general' );
			add_settings_field( 'phone', __( 'WhatsApp number', 'onlive-wa-order' ), [ $this, 'field_phone_number' ], 'onlive-wa-order-general', 'onlive_wa_order_general' );
			add_settings_field( 'positions', __( 'Button positions', 'onlive-wa-order' ), [ $this, 'field_positions' ], 'onlive-wa-order-general', 'onlive_wa_order_general' );

			add_settings_section( 'onlive_wa_order_button', __( 'Button Settings', 'onlive-wa-order' ), '__return_false', 'onlive-wa-order-button' );
			add_settings_field( 'button_labels', __( 'Button labels', 'onlive-wa-order' ), [ $this, 'field_button_labels' ], 'onlive-wa-order-button', 'onlive_wa_order_button' );
			add_settings_field( 'button_colors', __( 'Colors', 'onlive-wa-order' ), [ $this, 'field_button_colors' ], 'onlive-wa-order-button', 'onlive_wa_order_button' );
			add_settings_field( 'button_size', __( 'Button size', 'onlive-wa-order' ), [ $this, 'field_button_size' ], 'onlive-wa-order-button', 'onlive_wa_order_button' );

			add_settings_section( 'onlive_wa_order_template', __( 'Message Template Settings', 'onlive-wa-order' ), '__return_false', 'onlive-wa-order-template' );
			add_settings_field( 'include_product_link', __( 'Include product link', 'onlive-wa-order' ), [ $this, 'field_include_product_link' ], 'onlive-wa-order-template', 'onlive_wa_order_template' );
			add_settings_field( 'template_toggle', __( 'Custom template', 'onlive-wa-order' ), [ $this, 'field_template_toggle' ], 'onlive-wa-order-template', 'onlive_wa_order_template' );
			add_settings_field( 'message_template', __( 'Template builder', 'onlive-wa-order' ), [ $this, 'field_message_template' ], 'onlive-wa-order-template', 'onlive_wa_order_template' );
			add_settings_field( 'template_preview', __( 'Live preview', 'onlive-wa-order' ), [ $this, 'field_template_preview' ], 'onlive-wa-order-template', 'onlive_wa_order_template' );

			add_settings_section( 'onlive_wa_order_api', __( 'WhatsApp API Settings', 'onlive-wa-order' ), '__return_false', 'onlive-wa-order-api' );
			add_settings_field( 'api_choice', __( 'Sending system', 'onlive-wa-order' ), [ $this, 'field_api_choice' ], 'onlive-wa-order-api', 'onlive_wa_order_api' );
			add_settings_field( 'custom_gateway', __( 'Custom gateway URL', 'onlive-wa-order' ), [ $this, 'field_custom_gateway' ], 'onlive-wa-order-api', 'onlive_wa_order_api' );
			add_settings_field( 'custom_query', __( 'Custom query parameter', 'onlive-wa-order' ), [ $this, 'field_custom_query' ], 'onlive-wa-order-api', 'onlive_wa_order_api' );

			add_settings_section( 'onlive_wa_order_design', __( 'Design Settings', 'onlive-wa-order' ), '__return_false', 'onlive-wa-order-design' );
			add_settings_field( 'load_css', __( 'Plugin styles', 'onlive-wa-order' ), [ $this, 'field_load_css' ], 'onlive-wa-order-design', 'onlive_wa_order_design' );
			add_settings_field( 'custom_css', __( 'Custom CSS', 'onlive-wa-order' ), [ $this, 'field_custom_css' ], 'onlive-wa-order-design', 'onlive_wa_order_design' );
		}

		/**
		 * Enqueue admin assets.
		 */
		public function enqueue_assets( $hook = '' ) {
			$screen = get_current_screen();
			if ( ! $screen || 'toplevel_page_onlive-wa-order' !== $screen->id ) {
				return;
			}

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style(
				'onlive-wa-admin',
				ONLIVE_WA_ORDER_URL . 'assets/css/admin.css',
				[],
				$this->plugin->version
			);
			wp_enqueue_script( 'wp-color-picker' );

			wp_enqueue_script(
				'onlive-wa-admin',
				ONLIVE_WA_ORDER_URL . 'assets/js/admin.js',
				[ 'jquery', 'wp-color-picker' ],
				$this->plugin->version,
				true
			);

			wp_localize_script(
				'onlive-wa-admin',
				'onliveWAAdmin',
				[
					'placeholders' => [
						'{{product_name}}',
						'{{product_link}}',
						'{{product_price}}',
						'{{product_quantity}}',
						'{{product_variation}}',
						'{{product_sku}}',
						'{{cart_total}}',
						'{{site_name}}',
						'{{customer_name}}',
					],
					'nonce' => wp_create_nonce( 'onlive_wa_check_updates_nonce' ),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				]
			);
		}

		/**
		 * Register per-product meta box.
		 */
		public function register_product_metabox() {
			add_meta_box(
				'onlive-wa-order-product-box',
				__( 'WhatsApp Order Button', 'onlive-wa-order' ),
				[ $this, 'render_product_metabox' ],
				'product',
				'side'
			);
		}

		/**
		 * Render product meta box.
		 *
		 * @param WP_Post $post Current post.
		 */
		public function render_product_metabox( $post ) {
			$disabled = get_post_meta( $post->ID, '_onlive_wa_disable', true );
			wp_nonce_field( 'onlive_wa_product_meta', 'onlive_wa_product_meta_nonce' );
			?>
			<p>
				<label for="onlive-wa-disable">
					<input type="checkbox" id="onlive-wa-disable" name="onlive_wa_disable" value="1" <?php checked( $disabled, 'yes' ); ?> />
					<?php esc_html_e( 'Disable WhatsApp button for this product', 'onlive-wa-order' ); ?>
				</label>
			</p>
			<?php
		}

		/**
		 * Save product meta box value.
		 *
		 * @param int $post_id Product ID.
		 */
		public function save_product_metabox( $post_id ) {
			if ( ! isset( $_POST['onlive_wa_product_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['onlive_wa_product_meta_nonce'] ), 'onlive_wa_product_meta' ) ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! current_user_can( 'edit_product', $post_id ) ) {
				return;
			}

			$disabled = isset( $_POST['onlive_wa_disable'] ) ? 'yes' : 'no';
			update_post_meta( $post_id, '_onlive_wa_disable', $disabled );
		}

		/**
		 * Render settings page with tabs.
		 */
		public function render_settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$tabs        = $this->get_tabs();
			$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
			if ( ! isset( $tabs[ $current_tab ] ) ) {
				$current_tab = 'general';
			}

			$tab_page = sprintf( 'onlive-wa-order-%s', $current_tab );

			settings_errors( $this->option_name );
			?>
			<div class="wrap onlive-wa-settings">
				<div style="margin-bottom: 20px;">
					<h1><?php esc_html_e( 'WhatsApp Order Pro', 'onlive-wa-order' ); ?> <span style="font-size: 0.6em; color: #666; margin-left: 15px;"><?php echo 'v' . esc_html( $this->plugin->version ); ?></span></h1>
				</div>
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $tabs as $slug => $label ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'tab', $slug, admin_url( 'admin.php?page=onlive-wa-order' ) ) ); ?>" class="nav-tab <?php echo $slug === $current_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</h2>
				<?php if ( 'updates' === $current_tab ) : ?>
					<?php $this->render_updates_tab(); ?>
				<?php else : ?>
					<form method="post" action="options.php">
						<?php
						settings_fields( 'onlive_wa_order_settings_group' );
						do_settings_sections( $tab_page );
						submit_button();
						?>
					</form>
				<?php endif; ?>
				<?php $this->include_tab_notes( $current_tab ); ?>
			</div>
			<?php
		}

		/**
		 * Tabs list.
		 *
		 * @return array
		 */
		protected function get_tabs() {
			return [
				'general' => __( 'General Settings', 'onlive-wa-order' ),
				'button'  => __( 'Button Settings', 'onlive-wa-order' ),
				'template'=> __( 'Template Builder', 'onlive-wa-order' ),
				'api'     => __( 'WhatsApp API', 'onlive-wa-order' ),
				'design'  => __( 'Design Settings', 'onlive-wa-order' ),
				'updates' => __( 'Updates', 'onlive-wa-order' ),
			];
		}

		/**
		 * Include tab helper file.
		 *
		 * @param string $tab Current tab slug.
		 */
		protected function include_tab_notes( $tab ) {
			$tab_file = ONLIVE_WA_ORDER_PATH . 'admin/tabs/' . $tab . '.php';
			if ( file_exists( $tab_file ) ) {
				include $tab_file;
			}
		}

		/**
		 * Helper to get option.
		 *
		 * @param string $key     Key name.
		 * @param mixed  $default Default value.
		 *
		 * @return mixed
		 */
		protected function option( $key, $default = '' ) {
			$settings = $this->plugin->get_settings();
			return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
		}

		public function field_enable_plugin() {
			$value = (bool) $this->option( 'enabled', true );
			?>
			<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[enabled]" value="0" />
			<label for="onlive-wa-enabled">
				<input type="checkbox" id="onlive-wa-enabled" name="<?php echo esc_attr( $this->option_name ); ?>[enabled]" value="1" <?php checked( $value ); ?> />
				<?php esc_html_e( 'Activate WhatsApp buttons across the store', 'onlive-wa-order' ); ?>
			</label>
			<?php
		}

		public function field_phone_number() {
			$value = $this->option( 'phone', '' );
			?>
			<input type="text" class="regular-text" id="onlive-wa-phone" name="<?php echo esc_attr( $this->option_name ); ?>[phone]" value="<?php echo esc_attr( $value ); ?>" placeholder="+11234567890" />
			<p class="description"><?php esc_html_e( 'Enter your WhatsApp number including the international country code. Example: +447911123456', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function field_positions() {
			$positions = (array) $this->option( 'positions', [] );
			?>
			<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[positions][single]" value="0" />
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[positions][single]" value="1" <?php checked( ! empty( $positions['single'] ) ); ?> />
				<?php esc_html_e( 'Single product page', 'onlive-wa-order' ); ?>
			</label>
			<br />
			<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[positions][cart]" value="0" />
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[positions][cart]" value="1" <?php checked( ! empty( $positions['cart'] ) ); ?> />
				<?php esc_html_e( 'Cart page', 'onlive-wa-order' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Choose where to display the WhatsApp Order button.', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function field_button_labels() {
			$single = $this->option( 'button_label_single', __( 'Order via WhatsApp', 'onlive-wa-order' ) );
			$cart   = $this->option( 'button_label_cart', __( 'Order Cart via WhatsApp', 'onlive-wa-order' ) );
			?>
			<input type="text" id="onlive-wa-label-single" name="<?php echo esc_attr( $this->option_name ); ?>[button_label_single]" value="<?php echo esc_attr( $single ); ?>" class="regular-text" />
			<p class="description"><?php esc_html_e( 'Label for the button on single product pages.', 'onlive-wa-order' ); ?></p>
			<br />
			<input type="text" id="onlive-wa-label-cart" name="<?php echo esc_attr( $this->option_name ); ?>[button_label_cart]" value="<?php echo esc_attr( $cart ); ?>" class="regular-text" />
			<p class="description"><?php esc_html_e( 'Label for the button on the cart page.', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function field_button_colors() {
			$bg   = $this->option( 'button_color', '#25D366' );
			$text = $this->option( 'button_text_color', '#ffffff' );
			?>
			<label><?php esc_html_e( 'Background color', 'onlive-wa-order' ); ?></label><br />
			<input type="text" class="onlive-wa-color-field" name="<?php echo esc_attr( $this->option_name ); ?>[button_color]" value="<?php echo esc_attr( $bg ); ?>" data-default-color="#25D366" />
			<br /><br />
			<label><?php esc_html_e( 'Text color', 'onlive-wa-order' ); ?></label><br />
			<input type="text" class="onlive-wa-color-field" name="<?php echo esc_attr( $this->option_name ); ?>[button_text_color]" value="<?php echo esc_attr( $text ); ?>" data-default-color="#ffffff" />
			<?php
		}

		public function field_button_size() {
			$selected = $this->option( 'button_size', 'medium' );
			$options  = [
				'small'  => __( 'Small', 'onlive-wa-order' ),
				'medium' => __( 'Medium', 'onlive-wa-order' ),
				'large'  => __( 'Large', 'onlive-wa-order' ),
			];
			?>
			<select name="<?php echo esc_attr( $this->option_name ); ?>[button_size]" id="onlive-wa-button-size">
				<?php foreach ( $options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Control the minimum width and padding of the button.', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function field_include_product_link() {
			$enabled = (bool) $this->option( 'include_product_link', true );
			?>
			<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[include_product_link]" value="0" />
			<label for="onlive-wa-include-product-link">
				<input type="checkbox" id="onlive-wa-include-product-link" name="<?php echo esc_attr( $this->option_name ); ?>[include_product_link]" value="1" <?php checked( $enabled ); ?> />
				<?php esc_html_e( 'Include product link in messages', 'onlive-wa-order' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'When enabled, the product URL will be included in the WhatsApp message using {{product_link}} variable.', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function field_template_toggle() {
			$enabled = (bool) $this->option( 'template_enabled', false );
			?>
			<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[template_enabled]" value="0" />
			<label for="onlive-wa-template-enabled">
				<input type="checkbox" id="onlive-wa-template-enabled" name="<?php echo esc_attr( $this->option_name ); ?>[template_enabled]" value="1" <?php checked( $enabled ); ?> />
				<?php esc_html_e( 'Enable custom WhatsApp template', 'onlive-wa-order' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Use placeholders to personalize each message automatically.', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function field_message_template() {
			$value = $this->option( 'message_template', '' );
			?>
			<textarea class="large-text code" rows="6" id="onlive-wa-message-template" name="<?php echo esc_attr( $this->option_name ); ?>[message_template]" placeholder="<?php esc_attr_e( 'Hello, I want to order {{product_name}}', 'onlive-wa-order' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Available variables: {{product_name}}, {{product_price}}, {{product_quantity}}, {{product_variation}}, {{product_sku}}, {{cart_total}}, {{site_name}}, {{customer_name}}', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function field_template_preview() {
			?>
			<div id="onlive-wa-template-preview" class="onlive-wa-preview">
				<code><?php esc_html_e( 'Preview will appear here as you type.', 'onlive-wa-order' ); ?></code>
			</div>
			<?php
		}

		public function field_api_choice() {
			$current = $this->option( 'api_choice', 'wa' );
			$options = [
				'wa'     => __( 'wa.me short link', 'onlive-wa-order' ),
				'api'    => __( 'api.whatsapp.com', 'onlive-wa-order' ),
				'custom' => __( 'Custom gateway URL', 'onlive-wa-order' ),
			];
			foreach ( $options as $value => $label ) :
				?>
				<label>
					<input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[api_choice]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $current, $value ); ?> />
					<?php echo esc_html( $label ); ?>
				</label>
				<br />
				<?php
			endforeach;
			?><p class="description"><?php esc_html_e( 'Select which WhatsApp endpoint to use when opening messages.', 'onlive-wa-order' ); ?></p><?php
		}

		public function field_custom_gateway() {
			$value = $this->option( 'custom_gateway', '' );
			?>
			<input type="url" class="regular-text" name="<?php echo esc_attr( $this->option_name ); ?>[custom_gateway]" value="<?php echo esc_attr( $value ); ?>" placeholder="https://example.com/send" />
			<p class="description"><?php esc_html_e( 'Provide the base URL for your custom gateway if selected above.', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function field_custom_query() {
			$value = $this->option( 'custom_query_param', 'text' );
			?>
			<input type="text" class="regular-text" name="<?php echo esc_attr( $this->option_name ); ?>[custom_query_param]" value="<?php echo esc_attr( $value ); ?>" />
			<p class="description"><?php esc_html_e( 'Name of the query parameter used by the custom gateway for the message text.', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function field_load_css() {
			$enabled = (bool) $this->option( 'load_css', true );
			?>
			<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[load_css]" value="0" />
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[load_css]" value="1" <?php checked( $enabled ); ?> />
				<?php esc_html_e( 'Load default plugin CSS (disable to style via your theme)', 'onlive-wa-order' ); ?>
			</label>
			<?php
		}

		public function field_custom_css() {
			$value = $this->option( 'custom_css', '' );
			?>
			<textarea class="large-text code" rows="6" name="<?php echo esc_attr( $this->option_name ); ?>[custom_css]" placeholder=".onlive-wa-order-button { font-weight: 600; }"><?php echo esc_textarea( $value ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Custom CSS will be printed in the site head.', 'onlive-wa-order' ); ?></p>
			<?php
		}

		public function sanitize_settings( $input ) {
			$defaults = $this->plugin->get_default_settings();
			$clean    = wp_unslash( (array) $input );
			$output   = $this->plugin->get_settings();

			if ( isset( $clean['enabled'] ) ) {
				$output['enabled'] = ! empty( $clean['enabled'] ) ? 1 : 0;
			}

			if ( isset( $clean['phone'] ) ) {
				$phone = sanitize_text_field( $clean['phone'] );
				$phone = preg_replace( '/[^0-9\+]/', '', $phone );
				if ( ! empty( $phone ) && ! preg_match( '/^\+?[0-9]{6,15}$/', $phone ) ) {
					add_settings_error( $this->option_name, 'invalid_phone', __( 'Please enter a valid international WhatsApp number with country code.', 'onlive-wa-order' ) );
				} else {
					$output['phone'] = $phone;
				}
			}

			if ( isset( $clean['positions'] ) ) {
				$output['positions'] = [
					'single' => ! empty( $clean['positions']['single'] ) ? 1 : 0,
					'cart'   => ! empty( $clean['positions']['cart'] ) ? 1 : 0,
				];
			}

			if ( isset( $clean['button_label_single'] ) ) {
				$output['button_label_single'] = sanitize_text_field( $clean['button_label_single'] );
			}
			if ( isset( $clean['button_label_cart'] ) ) {
				$output['button_label_cart'] = sanitize_text_field( $clean['button_label_cart'] );
			}

			if ( isset( $clean['button_color'] ) ) {
				$output['button_color'] = $this->sanitize_hex( $clean['button_color'] );
			}
			if ( isset( $clean['button_text_color'] ) ) {
				$output['button_text_color'] = $this->sanitize_hex( $clean['button_text_color'] );
			}

			if ( isset( $clean['button_size'] ) ) {
				$sizes = [ 'small', 'medium', 'large' ];
				$output['button_size'] = in_array( $clean['button_size'], $sizes, true ) ? $clean['button_size'] : $defaults['button_size'];
			}

			if ( isset( $clean['template_enabled'] ) ) {
				$output['template_enabled'] = ! empty( $clean['template_enabled'] ) ? 1 : 0;
			}
			if ( isset( $clean['message_template'] ) ) {
				$output['message_template'] = wp_kses_post( $clean['message_template'] );
			}

			if ( isset( $clean['api_choice'] ) ) {
				$api_choices = [ 'wa', 'api', 'custom' ];
				$output['api_choice'] = in_array( $clean['api_choice'], $api_choices, true ) ? $clean['api_choice'] : $defaults['api_choice'];
			}
			if ( isset( $clean['custom_gateway'] ) ) {
				$output['custom_gateway'] = esc_url_raw( $clean['custom_gateway'] );
			}
			if ( isset( $clean['custom_query_param'] ) ) {
				$output['custom_query_param'] = sanitize_key( $clean['custom_query_param'] );
			}

			if ( isset( $clean['load_css'] ) ) {
				$output['load_css'] = ! empty( $clean['load_css'] ) ? 1 : 0;
			}
			if ( isset( $clean['custom_css'] ) ) {
				$output['custom_css'] = wp_strip_all_tags( $clean['custom_css'] );
			}

			$this->plugin->refresh_settings();

			return $output;
		}

		private function sanitize_hex( $color ) {
			$color = sanitize_hex_color( $color );
			return $color ? $color : '#000000';
		}

		/**
		 * AJAX handler to check for updates.
		 */
		public function ajax_check_updates() {
			// Verify nonce
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'onlive_wa_check_updates_nonce' ) ) {
				wp_send_json_error( __( 'Security check failed.', 'onlive-wa-order' ) );
			}

			// Check user capability
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You do not have permission to check for updates.', 'onlive-wa-order' ) );
			}

			try {
				// Clear plugin cache to force fresh check
				wp_clean_plugins_cache();
				delete_transient( 'onlive_wa_github_version' );
				delete_transient( 'onlive_wa_github_release' );

				// Force WordPress update check
				if ( function_exists( 'wp_update_plugins' ) ) {
					wp_update_plugins();
				}

				// Create and check updater
				if ( ! class_exists( 'Onlive_WA_Order_GitHub_Updater' ) ) {
					require_once ONLIVE_WA_ORDER_DIR . 'includes/class-github-updater.php';
				}

				$plugin_file = ONLIVE_WA_ORDER_FILE;
				$updater = new Onlive_WA_Order_GitHub_Updater( $plugin_file );

				// Use reflection to get remote version
				$remote_version = $this->get_github_remote_version();

				if ( is_wp_error( $remote_version ) ) {
					wp_send_json_error( $remote_version->get_error_message() );
				}

				// Get current version
				$current_version = $this->plugin->version;

				if ( version_compare( $remote_version, $current_version, '>' ) ) {
					$message = sprintf(
						__( '✓ Update available! Version %s is now available. Go to Plugins > Updates to install.', 'onlive-wa-order' ),
						esc_html( $remote_version )
					);
					wp_send_json_success( $message );
				} else {
					wp_send_json_success( __( '✓ You are running the latest version!', 'onlive-wa-order' ) );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( sprintf( __( 'Update check failed: %s', 'onlive-wa-order' ), $e->getMessage() ) );
			}
		}

		/**
		 * AJAX handler to force reinstall plugin data.
		 */
		public function ajax_force_reinstall() {
			// Verify nonce
			$nonce = isset( $_POST['nonce'] ) ? wp_unslash( $_POST['nonce'] ) : '';
			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'onlive_wa_check_updates_nonce' ) ) {
				wp_send_json_error( __( 'Security check failed. Please refresh the page and try again.', 'onlive-wa-order' ) );
			}

			// Check user capability
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You do not have permission to reinstall the plugin.', 'onlive-wa-order' ) );
			}

			try {
				// Delete all plugin data
				delete_option( $this->option_name );
				delete_transient( 'onlive_wa_github_version' );
				delete_transient( 'onlive_wa_github_release' );
				
				// Clear any cached data
				wp_cache_delete( 'onlive_wa_settings' );

				// Reinitialize default settings
				$default_settings = $this->get_default_settings();
				update_option( $this->option_name, $default_settings );

				// Refresh plugin settings
				if ( method_exists( $this->plugin, 'refresh_settings' ) ) {
					$this->plugin->refresh_settings();
				}

				// Provide feedback
				$message = __( '✓ Plugin has been reinstalled successfully. All default settings have been restored.', 'onlive-wa-order' );
				wp_send_json_success( $message );
			} catch ( Exception $e ) {
				wp_send_json_error( sprintf( __( 'Error during reinstallation: %s', 'onlive-wa-order' ), $e->getMessage() ) );
			}
		}

		/**
		 * AJAX handler to auto install updates from GitHub.
		 */
		public function ajax_auto_install_update() {
			// Verify nonce
			$nonce = isset( $_POST['nonce'] ) ? wp_unslash( $_POST['nonce'] ) : '';
			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'onlive_wa_check_updates_nonce' ) ) {
				wp_send_json_error( __( 'Security check failed. Please refresh the page and try again.', 'onlive-wa-order' ) );
			}

			// Check user capability
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You do not have permission to install updates.', 'onlive-wa-order' ) );
			}

			try {
				// Include WordPress plugin installer
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

				// Get latest release from GitHub
				$response = wp_remote_get(
					'https://api.github.com/repos/onlive-technologies/onlive-whatsapp-order-pro/releases/latest',
					[ 'timeout' => 10 ]
				);

				if ( is_wp_error( $response ) ) {
					wp_send_json_error( __( 'Could not reach GitHub. Please try again later.', 'onlive-wa-order' ) );
				}

				$body = wp_remote_retrieve_body( $response );
				$release = json_decode( $body );

				if ( ! $release || ! isset( $release->zipball_url ) ) {
					wp_send_json_error( __( 'Could not find release information. Please try again.', 'onlive-wa-order' ) );
				}

				// Download and install the plugin
				$download_url = $release->zipball_url;
				$file = download_url( $download_url );

				if ( is_wp_error( $file ) ) {
					wp_send_json_error( sprintf( __( 'Could not download plugin: %s', 'onlive-wa-order' ), $file->get_error_message() ) );
				}

				// Extract and install
				$result = unzip_file( $file, WP_PLUGIN_DIR );
				@unlink( $file );

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( sprintf( __( 'Could not extract plugin: %s', 'onlive-wa-order' ), $result->get_error_message() ) );
				}

				// Clean up old directory if needed
				delete_transient( 'onlive_wa_github_version' );
				delete_transient( 'onlive_wa_github_release' );
				wp_clean_plugins_cache();

				// Refresh plugin settings
				if ( method_exists( $this->plugin, 'refresh_settings' ) ) {
					$this->plugin->refresh_settings();
				}

				wp_send_json_success( __( '✓ Plugin updated successfully. Your settings have been preserved.', 'onlive-wa-order' ) );
			} catch ( Exception $e ) {
				wp_send_json_error( sprintf( __( 'Error installing update: %s', 'onlive-wa-order' ), $e->getMessage() ) );
			}
		}

		/**
		 * Get default plugin settings.
		 *
		 * @return array
		 */
		private function get_default_settings() {
			return [
				'enabled'               => '1',
				'phone'                 => '',
				'positions'             => [ 'single', 'archive', 'cart' ],
				'button_label_single'   => __( 'Order on WhatsApp', 'onlive-wa-order' ),
				'button_label_archive'  => __( 'Order on WhatsApp', 'onlive-wa-order' ),
				'button_label_cart'     => __( 'Order on WhatsApp', 'onlive-wa-order' ),
				'button_color'          => '#25D366',
				'button_text_color'     => '#FFFFFF',
				'button_size'           => 'medium',
				'include_product_link'  => '1',
				'custom_template'       => '0',
				'message_template'      => __( 'Hello, I am interested in {{product_name}} for {{product_price}}. Please let me know more details.', 'onlive-wa-order' ),
				'api_choice'            => 'api',
				'custom_gateway'        => '',
				'custom_query'          => 'text',
				'load_css'              => '1',
				'custom_css'            => '',
			];
		}

		/**
		 * Get remote version from GitHub API.
		 *
		 * @return string|WP_Error
		 */
		private function get_github_remote_version() {
			// Cache key
			$cache_key = 'onlive_wa_order_github_version';

			// Check cache first (1 hour)
			$cached = get_transient( $cache_key );
			if ( $cached ) {
				return $cached;
			}

			$url = 'https://api.github.com/repos/onliveserver/onlive-whatsapp-order/releases/latest';

			$response = wp_remote_get(
				$url,
				[
					'headers'   => [
						'Accept'     => 'application/vnd.github.v3+json',
						'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
					],
					'sslverify' => true,
					'timeout'   => 10,
				]
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== $code ) {
				return new WP_Error(
					'github_api_error',
					sprintf( __( 'GitHub API error: %d', 'onlive-wa-order' ), $code )
				);
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );

			if ( empty( $data->tag_name ) ) {
				return new WP_Error( 'no_version', __( 'Could not determine remote version.', 'onlive-wa-order' ) );
			}

			// Remove 'v' prefix if present
			$version = ltrim( $data->tag_name, 'v' );

			// Cache for 1 hour
			set_transient( $cache_key, $version, HOUR_IN_SECONDS );

			return $version;
		}

		/**
		 * Render updates tab content.
		 */
		public function render_updates_tab() {
			$current_version = $this->plugin->version;
			$latest_version  = $this->get_latest_github_version();
			$has_update      = ! is_wp_error( $latest_version ) && version_compare( $latest_version, $current_version, '>' );

			?>
			<div style="background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin-top: 20px;">
				<h3><?php esc_html_e( 'Plugin Information', 'onlive-wa-order' ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Current Version', 'onlive-wa-order' ); ?></th>
						<td><strong><?php echo esc_html( $current_version ); ?></strong></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Latest Version', 'onlive-wa-order' ); ?></th>
						<td>
							<?php if ( is_wp_error( $latest_version ) ) : ?>
								<span style="color: #d32f2f;"><?php esc_html_e( 'Could not check for updates', 'onlive-wa-order' ); ?></span>
							<?php else : ?>
								<strong><?php echo esc_html( $latest_version ); ?></strong>
								<?php if ( $has_update ) : ?>
									<span style="background: #2196F3; color: white; padding: 3px 8px; border-radius: 3px; margin-left: 10px;"><?php esc_html_e( 'Update Available', 'onlive-wa-order' ); ?></span>
								<?php else : ?>
									<span style="background: #4CAF50; color: white; padding: 3px 8px; border-radius: 3px; margin-left: 10px;"><?php esc_html_e( 'Up to Date', 'onlive-wa-order' ); ?></span>
								<?php endif; ?>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'GitHub Repository', 'onlive-wa-order' ); ?></th>
						<td>
							<a href="https://github.com/onlive-technologies/onlive-whatsapp-order-pro" target="_blank" style="color: #0073aa; text-decoration: none;">
								https://github.com/onlive-technologies/onlive-whatsapp-order-pro
								<span class="dashicons dashicons-external" style="width: 16px; height: 16px; margin-left: 5px;"></span>
							</a>
						</td>
					</tr>
				</table>
			</div>

			<div style="background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin-top: 20px;">
				<h3><?php esc_html_e( 'Update Actions', 'onlive-wa-order' ); ?></h3>
				<p><?php esc_html_e( 'Use the buttons below to manage plugin updates and reinstallation.', 'onlive-wa-order' ); ?></p>
				<div style="display: flex; gap: 10px;">
					<button type="button" id="onlive-wa-check-updates-btn" class="button button-primary">
						<span class="dashicons dashicons-update" style="margin-right: 5px; margin-top: 2px;"></span>
						<?php esc_html_e( 'Check for Updates', 'onlive-wa-order' ); ?>
					</button>
					<?php if ( $has_update ) : ?>
						<button type="button" id="onlive-wa-auto-install-btn" class="button button-success">
							<span class="dashicons dashicons-download" style="margin-right: 5px; margin-top: 2px;"></span>
							<?php esc_html_e( 'Install Update', 'onlive-wa-order' ); ?>
						</button>
					<?php endif; ?>
					<button type="button" id="onlive-wa-force-reinstall-btn" class="button button-secondary">
						<span class="dashicons dashicons-admin-tools" style="margin-right: 5px; margin-top: 2px;"></span>
						<?php esc_html_e( 'Force Reinstall', 'onlive-wa-order' ); ?>
					</button>
				</div>
			</div>

			<script>
				(function($) {
					$('#onlive-wa-check-updates-btn').on('click', function() {
						var btn = $(this);
						var originalText = btn.html();
						btn.prop('disabled', true).html('<span class="spinner" style="float: left; margin-right: 8px;"></span><?php esc_html_e( 'Checking...', 'onlive-wa-order' ); ?>');
						
						$.ajax({
							type: 'POST',
							url: onliveWAAdmin.ajax_url,
							data: {
								action: 'onlive_wa_check_updates',
								nonce: onliveWAAdmin.nonce
							},
							success: function(response) {
								alert('<?php esc_html_e( 'Update check completed. Please reload the page.', 'onlive-wa-order' ); ?>');
								setTimeout(function() {
									location.reload();
								}, 1500);
							},
							error: function() {
								alert('<?php esc_html_e( 'Error checking for updates. Please try again.', 'onlive-wa-order' ); ?>');
								btn.prop('disabled', false).html(originalText);
							}
						});
					});

					$('#onlive-wa-force-reinstall-btn').on('click', function() {
						if (!confirm('<?php esc_html_e( 'This will reinstall the plugin from GitHub. Continue?', 'onlive-wa-order' ); ?>')) {
							return;
						}

						var btn = $(this);
						var originalText = btn.html();
						btn.prop('disabled', true).html('<span class="spinner" style="float: left; margin-right: 8px;"></span><?php esc_html_e( 'Installing...', 'onlive-wa-order' ); ?>');
						
						$.ajax({
							type: 'POST',
							url: onliveWAAdmin.ajax_url,
							data: {
								action: 'onlive_wa_force_reinstall',
								nonce: onliveWAAdmin.nonce
							},
							success: function(response) {
								if (response.success) {
									alert('<?php esc_html_e( 'Plugin installed successfully. Reloading...', 'onlive-wa-order' ); ?>');
									setTimeout(function() {
										location.reload();
									}, 1500);
								} else {
									alert(response.data || '<?php esc_html_e( 'Installation failed. Please try again.', 'onlive-wa-order' ); ?>');
									btn.prop('disabled', false).html(originalText);
								}
							},
							error: function() {
								alert('<?php esc_html_e( 'Error during installation. Please try again.', 'onlive-wa-order' ); ?>');
								btn.prop('disabled', false).html(originalText);
							}
						});
					});

					$('#onlive-wa-auto-install-btn').on('click', function() {
						if (!confirm('<?php esc_html_e( 'Install the latest version? Your current settings will be preserved.', 'onlive-wa-order' ); ?>')) {
							return;
						}

						var btn = $(this);
						var originalText = btn.html();
						btn.prop('disabled', true).html('<span class="spinner" style="float: left; margin-right: 8px;"></span><?php esc_html_e( 'Installing...', 'onlive-wa-order' ); ?>');
						
						$.ajax({
							type: 'POST',
							url: onliveWAAdmin.ajax_url,
							data: {
								action: 'onlive_wa_auto_install_update',
								nonce: onliveWAAdmin.nonce
							},
							success: function(response) {
								if (response.success) {
									alert('<?php esc_html_e( 'Update installed successfully. Reloading...', 'onlive-wa-order' ); ?>');
									setTimeout(function() {
										location.reload();
									}, 1500);
								} else {
									alert(response.data || '<?php esc_html_e( 'Update failed. Please try again.', 'onlive-wa-order' ); ?>');
									btn.prop('disabled', false).html(originalText);
								}
							},
							error: function() {
								alert('<?php esc_html_e( 'Error during update. Please try again.', 'onlive-wa-order' ); ?>');
								btn.prop('disabled', false).html(originalText);
							}
						});
					});
				})(jQuery);
			</script>
			<?php
		}

		/**
		 * Get latest version from GitHub.
		 *
		 * @return string|WP_Error
		 */
		public function get_latest_github_version() {
			$cache_key = 'onlive_wa_latest_github_version';
			$cached    = get_transient( $cache_key );

			if ( false !== $cached ) {
				return $cached;
			}

			$response = wp_remote_get(
				'https://api.github.com/repos/onlive-technologies/onlive-whatsapp-order-pro/releases/latest',
				[ 'timeout' => 10 ]
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );

			if ( ! $data || ! isset( $data->tag_name ) ) {
				return new WP_Error( 'no_version', __( 'Could not determine remote version.', 'onlive-wa-order' ) );
			}

			$version = ltrim( $data->tag_name, 'v' );
			set_transient( $cache_key, $version, HOUR_IN_SECONDS );

			return $version;
		}
	}
}
