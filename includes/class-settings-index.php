<?php
/**
 * Settings Index File
 * Contains default settings and saved settings management
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Settings_Index' ) ) {
	class Onlive_WA_Order_Settings_Index {

		/**
		 * Option name for storing settings
		 */
		const OPTION_NAME = 'onlive_wa_order_settings';

		/**
		 * Get default settings array
		 *
		 * @return array
		 */
		public static function get_defaults() {
			return [
				'enabled'            => 1,
				'phone'              => '',
				'positions'          => [
					'single' => 1,
				],
				'button_label_single' => __( 'Order via WhatsApp', 'onlive-wa-order' ),
				'button_color'        => '#25D366',
				'button_text_color'   => '#ffffff',
				'button_size'         => 'medium',
				'template_enabled'    => 0,
				'message_template'    => "Hello, I would like to order {{product_name}}. Price: {{product_price}} x {{product_quantity}}. {{product_variation}}",
				'load_css'            => 1,
				'custom_css'          => '',
				'include_product_link' => 1,
			];
		}

		/**
		 * Get saved settings merged with defaults
		 *
		 * @return array
		 */
		public static function get_saved() {
			$stored = get_option( self::OPTION_NAME, [] );
			return wp_parse_args( $stored, self::get_defaults() );
		}

		/**
		 * Get a single setting value
		 *
		 * @param string $key     Setting key
		 * @param mixed  $default Default fallback
		 * @return mixed
		 */
		public static function get( $key, $default = null ) {
			$settings = self::get_saved();

			// Check if key exists AND is not empty for phone/template fields
			$value = isset( $settings[ $key ] ) ? $settings[ $key ] : $default;

			// For phone specifically, if it's empty string, use the default
			if ( 'phone' === $key && empty( $value ) && ! empty( $default ) ) {
				return $default;
			}

			return $value;
		}

		/**
		 * Save settings
		 *
		 * @param array $settings Settings array to save
		 * @return bool
		 */
		public static function save( $settings ) {
			return update_option( self::OPTION_NAME, $settings );
		}

		/**
		 * Reset to defaults
		 *
		 * @return bool
		 */
		public static function reset() {
			return update_option( self::OPTION_NAME, self::get_defaults() );
		}
	}
}