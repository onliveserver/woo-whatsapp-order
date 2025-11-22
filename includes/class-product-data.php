<?php
/**
 * Common Product Data Handler
 * Provides unified product data retrieval and formatting
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Product_Data' ) ) {
	class Onlive_WA_Order_Product_Data {

		/**
		 * Get product data for WhatsApp link generation
		 *
		 * @param int $product_id   Product ID
		 * @param int $variation_id Variation ID (optional)
		 * @param int $quantity     Quantity
		 * @return array Product data or empty array on error
		 */
		public static function get_for_link( $product_id, $variation_id = 0, $quantity = 1 ) {
			$data = self::get_base_product_data( $product_id, $variation_id, $quantity );

			if ( empty( $data ) ) {
				return [];
			}

			// Add link-specific fields
			$data['site_name'] = Onlive_WA_Order_Formatter::get_site_name();
			$data['customer_name'] = Onlive_WA_Order_Formatter::get_customer_name();

			return $data;
		}

		/**
		 * Get product data for AJAX responses
		 *
		 * @param int   $product_id   Product ID
		 * @param int   $variation_id Variation ID (optional)
		 * @param int   $quantity     Quantity
		 * @param array $attributes   Variation attributes from request
		 * @return array|WP_Error Product data or error
		 */
		public static function get_for_ajax( $product_id, $variation_id = 0, $quantity = 1, $attributes = [] ) {
			try {
				$data = self::get_base_product_data( $product_id, $variation_id, $quantity, $attributes );

				if ( empty( $data ) ) {
					return new WP_Error( 'missing_product', __( 'Product not found.', 'onlive-wa-order' ) );
				}

				return $data;
			} catch ( Exception $e ) {
				return new WP_Error( 'product_error', __( 'Error retrieving product information.', 'onlive-wa-order' ) );
			}
		}

		/**
		 * Get base product data (shared logic)
		 *
		 * @param int   $product_id   Product ID
		 * @param int   $variation_id Variation ID
		 * @param int   $quantity     Quantity
		 * @param array $attributes   Variation attributes (optional)
		 * @return array Product data or empty array on error
		 */
		private static function get_base_product_data( $product_id, $variation_id = 0, $quantity = 1, $attributes = [] ) {
			if ( ! function_exists( 'wc_get_product' ) || $product_id <= 0 ) {
				return [];
			}

			// Get product - use variation if provided
			$product = wc_get_product( $variation_id ?: $product_id );
			if ( ! $product ) {
				return [];
			}

			// Get base product for link
			$base_product = $variation_id ? wc_get_product( $product_id ) : $product;

			// Get price
			$price_label = self::get_product_price( $product );

			// Get variation text
			$variation_text = self::get_variation_text( $product, $attributes );

			// Get product link if enabled
			$product_link = '';
			if ( Onlive_WA_Order_Settings_Index::get( 'include_product_link', 1 ) ) {
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
		}

		/**
		 * Get formatted product price
		 *
		 * @param WC_Product $product Product object
		 * @return string Formatted price
		 */
		private static function get_product_price( $product ) {
			$price_label = '';

			try {
				// Try wc_get_price_to_display first (respects customer prices)
				if ( function_exists( 'wc_get_price_to_display' ) ) {
					$price_raw = wc_get_price_to_display( $product );
					if ( $price_raw ) {
						return Onlive_WA_Order_Formatter::format_price( $price_raw );
					}
				}
			} catch ( Exception $e ) {
				// Continue to fallbacks
			}

			// Fallback 1: get_price()
			$price_raw = $product->get_price();
			if ( $price_raw ) {
				return Onlive_WA_Order_Formatter::format_price( $price_raw );
			}

			// Fallback 2: get_regular_price()
			$price_raw = $product->get_regular_price();
			if ( $price_raw ) {
				return Onlive_WA_Order_Formatter::format_price( $price_raw );
			}

			// Fallback 3: get_sale_price()
			$price_raw = $product->get_sale_price();
			if ( $price_raw ) {
				return Onlive_WA_Order_Formatter::format_price( $price_raw );
			}

			return $price_label;
		}

		/**
		 * Get variation text
		 *
		 * @param WC_Product $product    Product object
		 * @param array      $attributes Variation attributes from request
		 * @return string Formatted variation text
		 */
		private static function get_variation_text( $product, $attributes = [] ) {
			// If attributes provided directly (from AJAX), use them
			if ( ! empty( $attributes ) ) {
				return Onlive_WA_Order_Formatter::format_variations( $attributes );
			}

			// Otherwise get from product variation attributes
			if ( $product->is_type( 'variation' ) ) {
				$variation_attrs = $product->get_variation_attributes();
				return Onlive_WA_Order_Formatter::format_variations( $variation_attrs );
			}

			return '';
		}
	}
}