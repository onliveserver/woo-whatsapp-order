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