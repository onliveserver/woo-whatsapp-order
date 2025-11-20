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
						'{{product_price}}',
						'{{product_quantity}}',
						'{{product_variation}}',
						'{{product_sku}}',
						'{{cart_total}}',
						'{{site_name}}',
						'{{customer_name}}',
					],
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
				<h1><?php esc_html_e( 'WhatsApp Order Pro', 'onlive-wa-order' ); ?></h1>
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $tabs as $slug => $label ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'tab', $slug, admin_url( 'admin.php?page=onlive-wa-order' ) ) ); ?>" class="nav-tab <?php echo $slug === $current_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</h2>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'onlive_wa_order_settings_group' );
					do_settings_sections( $tab_page );
					submit_button();
					?>
				</form>
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
	}
}
