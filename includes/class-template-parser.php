<?php
/**
 * Template parser utility.
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Template_Parser' ) ) {
	class Onlive_WA_Order_Template_Parser {

		/**
		 * Replace placeholders in a template string with data.
		 *
		 * @param string $template   Template string.
		 * @param array  $data       Key/value data (without curly braces).
		 *
		 * @return string
		 */
		public static function parse( $template, $data = [] ) {
			if ( empty( $template ) ) {
				return '';
			}

			if ( empty( $data ) || ! is_array( $data ) ) {
				return $template;
			}

			$search  = [];
			$replace = [];

			foreach ( $data as $key => $value ) {
				$search[]  = '{{' . $key . '}}';
				$replace[] = is_scalar( $value ) ? $value : '';
			}

			return str_replace( $search, $replace, $template );
		}
	}
}
