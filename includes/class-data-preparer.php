<?php
/**
 * Data Preparation for WhatsApp Order messages.
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Data_Preparer' ) ) {
	class Onlive_WA_Order_Data_Preparer {

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
		public function prepare_product_data( $product_id, $variation_id, $quantity, $attributes ) {
			return Onlive_WA_Order_Product_Data::get_for_ajax( $product_id, $variation_id, $quantity, $attributes );
		}		/**
		 * Prepare cart context data.
		 *
		 * @return array|WP_Error
		 */
		public function prepare_cart_data() {
			if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
				return new WP_Error( 'missing_cart', __( 'Cart is empty or not available.', 'onlive-wa-order' ) );
			}

			$cart = WC()->cart;
			$cart_contents = $cart->get_cart();

			// Check if cart has items
			if ( empty( $cart_contents ) ) {
				return new WP_Error( 'empty_cart', __( 'Your cart is empty.', 'onlive-wa-order' ) );
			}

			$items_text  = $this->format_cart_items( $cart_contents );
			$total_raw   = strip_tags( html_entity_decode( $cart->get_total() ) );
			$total_items = $cart->get_cart_contents_count();

			return [
				'product_name'     => __( 'Cart order', 'onlive-wa-order' ),
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
		protected function format_cart_items( $cart_items ) {
			return Onlive_WA_Order_Formatter::format_cart_items( $cart_items );
		}

		/**
		 * Format variation attributes into string.
		 *
		 * @param array $attributes Variation data.
		 *
		 * @return string
		 */
		protected function format_variations( $attributes ) {
			return Onlive_WA_Order_Formatter::format_variations( $attributes );
		}
	}
}