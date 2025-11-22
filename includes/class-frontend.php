<?php
/**
 * Frontend rendering for WhatsApp Order buttons.
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Frontend' ) ) {
	class Onlive_WA_Order_Frontend {

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
			add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'render_single_button' ], 21 );
		}

		/**
		 * Enqueue frontend assets.
		 */
		public function enqueue_assets() {
			if ( ! $this->plugin->is_enabled() || ! $this->plugin->is_woocommerce_active() ) {
				return;
			}

			// Don't load on checkout pages to prevent session issues
			if ( is_checkout() ) {
				return;
			}

			$settings = Onlive_WA_Order_Settings_Index::get_saved();

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

			$localized_data = [
				// Use query-based endpoint to increase compatibility with redirects
				// and coming-soon plugins that may block admin-ajax.php or rewrites.
				'ajaxUrl'   => home_url( '/?wa_ajax=1' ),
				'nonce'     => wp_create_nonce( 'onlive-wa-order' ),
				'phone'     => Onlive_WA_Order_Settings_Index::get( 'phone', '' ),
				'buttonSize' => Onlive_WA_Order_Settings_Index::get( 'button_size', 'medium' ),
				'colors'    => [
					'background' => Onlive_WA_Order_Settings_Index::get( 'button_color', '#25D366' ),
					'text'       => Onlive_WA_Order_Settings_Index::get( 'button_text_color', '#ffffff' ),
				],
				'template'  => [
					'enabled'   => (bool) Onlive_WA_Order_Settings_Index::get( 'template_enabled', false ),
					'custom'    => Onlive_WA_Order_Settings_Index::get( 'message_template', '' ),
					'includeLink' => (bool) Onlive_WA_Order_Settings_Index::get( 'include_product_link', true ),
				],
				'strings'   => [
					'phoneMissing' => __( 'Please add your WhatsApp number in the plugin settings.', 'onlive-wa-order' ),
					'error'        => __( 'Unable to build the WhatsApp message. Please try again.', 'onlive-wa-order' ),
				],
			];
			wp_localize_script(
				'onlive-wa-order-frontend',
				'onliveWAOrder',
				$localized_data
			);
		}

		/**
		 * Print custom CSS in head.
		 */
		public function output_custom_css() {
			if ( ! $this->plugin->is_enabled() ) {
				return;
			}

			$custom_css = Onlive_WA_Order_Settings_Index::get( 'custom_css', '' );
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
		 * Generate button markup.
		 *
		 * @param WC_Product|null $product Product instance.
		 * @param string          $context Button context.
		 * @param array           $args    Extra args.
		 *
		 * @return string
		 */
		protected function get_button_markup( $product, $context = 'product', $args = [] ) {
			$settings = Onlive_WA_Order_Settings_Index::get_saved();

			$label = ! empty( $args['label'] ) ? $args['label'] : $settings['button_label_single'];

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
			 * @param Onlive_WA_Order_Frontend $frontend    Frontend instance.
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
	}
}