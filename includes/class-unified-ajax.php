<?php
/**
 * Unified AJAX handler for Onlive WooCommerce WhatsApp Order
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Onlive_WA_Order_Unified_Ajax' ) ) {
    class Onlive_WA_Order_Unified_Ajax {

        /**
         * Plugin instance
         * @var Onlive_WA_Order_Pro
         */
        private $plugin;

        /**
         * Supported actions
         * @var array
         */
        private $supported_actions = array( 'vaog2jucg3f2', 'onlive_wa_ping' );

        public function __construct( $plugin ) {
            $this->plugin = $plugin;
            add_action( 'wp_ajax_vaog2jucg3f2', array( $this, 'handle_ajax' ) );
            add_action( 'wp_ajax_nopriv_vaog2jucg3f2', array( $this, 'handle_ajax' ) );
            add_action( 'wp_ajax_onlive_wa_ping', array( $this, 'handle_ajax' ) );
            add_action( 'wp_ajax_nopriv_onlive_wa_ping', array( $this, 'handle_ajax' ) );

            // Provide fallback & debug - ensure we are earliest
            add_action( 'init', array( $this, 'maybe_define_ajax' ), -9999 );

            // JSON error handling
            add_action( 'wp_ajax_nopriv_onlive_wa_ping', array( $this, 'handle_ajax' ) );
        }

        /**
         * Ensure DOING_AJAX is present for requests with header
         */
        public function maybe_define_ajax() {
            if ( ! defined( 'DOING_AJAX' ) ) {
                if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' ) {
                    define( 'DOING_AJAX', true );
                }
            }
        }

        /**
         * Main handler invoked by wp_ajax_* hooks
         */
        public function handle_ajax() {
            // Use output buffering to guarantee clean JSON
            if ( function_exists( 'ob_get_level' ) ) {
                while ( ob_get_level() > 0 ) {
                    ob_end_clean();
                }
            }

            header( 'Content-Type: application/json; charset=UTF-8' );

            try {
                $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

                if ( 'onlive_wa_ping' === $action ) {
                    wp_send_json_success( array( 'timestamp' => time(), 'status' => 'alive' ) );
                }

                if ( 'vaog2jucg3f2' !== $action ) {
                    wp_send_json_error( 'Invalid action' );
                }

                // Product context
                $context = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : 'product';

                $product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
                if ( ! $product_id ) {
                    wp_send_json_error( 'Product ID is required' );
                }

                $variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
                $quantity = isset( $_POST['quantity'] ) ? max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) : 1;
                $variations = isset( $_POST['variations'] ) ? json_decode( wp_unslash( $_POST['variations'] ), true ) : array();

                $preparer = new Onlive_WA_Order_Data_Preparer();
                $data = $preparer->prepare_product_data( $product_id, $variation_id, $quantity, $variations );

                if ( is_wp_error( $data ) ) {
                    wp_send_json_error( $data->get_error_message() );
                }

                if ( empty( $data ) || ! is_array( $data ) ) {
                    wp_send_json_error( 'Unable to retrieve product data' );
                }

                $message = $this->plugin->generate_message( $context, $data );
                if ( empty( $message ) ) {
                    wp_send_json_error( 'Unable to generate message' );
                }

                $url = $this->plugin->get_whatsapp_url( $message );
                if ( empty( $url ) ) {
                    wp_send_json_error( 'WhatsApp number missing in plugin settings' );
                }

                wp_send_json_success( array( 'url' => $url, 'message' => $message ) );

            } catch ( Exception $e ) {
                wp_send_json_error( 'Server error: ' . $e->getMessage() );
            }
        }
    }
}
