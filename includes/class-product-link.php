<?php
/**
 * Single Product Link Generator
 * Handles WhatsApp link generation for individual products
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Product_Link' ) ) {
	class Onlive_WA_Order_Product_Link {

		/**
		 * Generate WhatsApp URL for a single product
		 *
		 * @param int $product_id   Product ID
		 * @param int $variation_id Variation ID (optional)
		 * @param int $quantity     Quantity
		 * @return string WhatsApp URL or empty string on error
		 */
		public static function generate_url( $product_id, $variation_id = 0, $quantity = 1 ) {
			// Get product data
			$product_data = self::get_product_data( $product_id, $variation_id, $quantity );

			if ( empty( $product_data ) ) {
				return '';
			}

			// Get settings
			$settings = self::get_settings();

			// Generate message
			$message = self::generate_message( $product_data, $settings, $quantity );

			// Get phone number
			$phone = self::get_phone_number( $settings );

			if ( empty( $phone ) ) {
				return '';
			}

			// Build WhatsApp URL
			return self::build_whatsapp_url( $phone, $message );
		}

		/**
		 * Get product data array
		 *
		 * @param int $product_id   Product ID
		 * @param int $variation_id Variation ID
		 * @param int $quantity     Quantity
		 * @return array Product data or empty array on error
		 */
		private static function get_product_data( $product_id, $variation_id, $quantity ) {
			return Onlive_WA_Order_Product_Data::get_for_link( $product_id, $variation_id, $quantity );
		}

		/**
		 * Get plugin settings
		 *
		 * @return array Settings array
		 */
		private static function get_settings() {
			return Onlive_WA_Order_Settings_Index::get_saved();
		}

		/**
		 * Generate WhatsApp message
		 *
		 * @param array $product_data Product data
		 * @param array $settings     Plugin settings
		 * @param int   $quantity     Quantity
		 * @return string Message
		 */
		private static function generate_message( $product_data, $settings, $quantity ) {
			// Remove product link if not enabled
			if ( empty( $settings['include_product_link'] ) ) {
				$product_data['product_link'] = '';
			}

			// Use custom template if enabled, otherwise use default
			if ( ! empty( $settings['template_enabled'] ) && ! empty( $settings['message_template'] ) ) {
				// Replace bullet (•) with dash (-) for WhatsApp compatibility
				$message_template = str_replace(["\u2022", "•"], "-", $settings['message_template']);
			} else {
				$message_template = "Hello, I would like to order {{product_name}}. Price: {{product_price}} x {{product_quantity}}. {{product_variation}}";
				$message_template = str_replace(["\u2022", "•"], "-", $message_template);
			}

			// Template replacement
			$message = $message_template;
			foreach ( $product_data as $key => $value ) {
				$message = str_replace( '{{' . $key . '}}', (string) $value, $message );
			}

			// Add quantity if more than 1
			if ( $quantity > 1 ) {
				$message .= "\nQuantity: " . $quantity;
			}

			// Add variations if present
			if ( ! empty( $product_data['product_variation'] ) ) {
				$message .= "\nVariation: " . $product_data['product_variation'];
			}

			// Clean up remaining template variables
			$message = preg_replace( '/\{\{[^}]+\}\}/', '', $message );

			return trim( $message );
		}

		/**
		 * Get sanitized phone number
		 *
		 * @param array $settings Plugin settings
		 * @return string Phone number or empty string
		 */
		private static function get_phone_number( $settings ) {
			$phone_raw = $settings['phone'] ?? '';
			$phone = preg_replace( '/[^0-9\+]/', '', $phone_raw );
			return ltrim( $phone, '+' );
		}

		/**
		 * Build WhatsApp URL
		 *
		 * @param string $phone   Phone number
		 * @param string $message Message
		 * @return string WhatsApp URL
		 */
		private static function build_whatsapp_url( $phone, $message ) {
			if ( empty( $phone ) ) {
				return '';
			}

			$message = trim( $message );
			$message = str_replace( [ "\r\n", "\r" ], "\n", $message );

			return 'https://wa.me/' . rawurlencode( $phone ) . '?text=' . rawurlencode( $message );
		}
	}
}