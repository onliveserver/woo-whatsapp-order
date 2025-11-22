<?php
/**
 * Common formatting utilities for WhatsApp Order messages.
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Formatter' ) ) {
	class Onlive_WA_Order_Formatter {

		/**
		 * Format cart items list.
		 *
		 * @param array $cart_items Cart items array.
		 *
		 * @return string
		 */
		public static function format_cart_items( $cart_items ) {
			$lines = [];
			foreach ( $cart_items as $item ) {
				$product = $item['data'];
				if ( ! $product ) {
					continue;
				}

				$variation_text = '';
				if ( ! empty( $item['variation'] ) ) {
					$variation_text = self::format_variations( $item['variation'] );
				}

				$currency_symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';
				$line_total = $currency_symbol . number_format( $item['line_total'], 2 );
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
		public static function format_variations( $attributes ) {
			if ( empty( $attributes ) ) {
				return '';
			}

			$parts = [];
			foreach ( $attributes as $key => $value ) {
				$key   = function_exists( 'wc_attribute_label' ) ? wc_attribute_label( str_replace( 'attribute_', '', $key ) ) : ucfirst( str_replace( [ 'attribute_', '_' ], [ '', ' ' ], $key ) );
				$value = function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $value ) : $value;
				$parts[] = sprintf( '%1$s: %2$s', $key, $value );
			}

			return implode( ', ', $parts );
		}

		/**
		 * Format price with currency symbol.
		 *
		 * @param float $price Price amount.
		 * @return string
		 */
		public static function format_price( $price ) {
		// Use WooCommerce currency code if possible, fallback to USD
		if ( function_exists( 'get_option' ) ) {
			$currency_code = get_option( 'woocommerce_currency', 'USD' );
		} else {
			$currency_code = 'USD';
		}
		return number_format( (float) $price, 2 ) . ' ' . $currency_code;
		}

		/**
		 * Get customer name with fallback.
		 *
		 * @return string
		 */
		public static function get_customer_name() {
			$current_user = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;

			if ( $current_user && $current_user->ID ) {
				return $current_user->display_name;
			}

			return __( 'Guest Customer', 'onlive-wa-order' );
		}

		/**
		 * Get site name.
		 *
		 * @return string
		 */
		public static function get_site_name() {
			return function_exists( 'get_bloginfo' ) ? get_bloginfo( 'name' ) : 'Our Store';
		}
	}
}