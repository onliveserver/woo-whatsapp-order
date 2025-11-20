<?php
/**
 * GitHub Plugin Updater for Onlive WhatsApp Order
 *
 * @package Onlive_WA_Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onlive_WA_Order_GitHub_Updater' ) ) {
	class Onlive_WA_Order_GitHub_Updater {

	/**
	 * GitHub repository owner (username).
	 *
	 * @var string
	 */
	private $github_owner = 'onliveserver';		/**
		 * GitHub repository name.
		 *
		 * @var string
		 */
		private $github_repo = 'onlive-whatsapp-order';

		/**
		 * GitHub API URL.
		 *
		 * @var string
		 */
		private $github_api_url = 'https://api.github.com/repos/';

		/**
		 * Plugin file path.
		 *
		 * @var string
		 */
		private $plugin_file;

		/**
		 * Plugin slug.
		 *
		 * @var string
		 */
		private $plugin_slug;

		/**
		 * Constructor.
		 *
		 * @param string $plugin_file Plugin main file path.
		 * @param string $github_owner GitHub repository owner.
		 * @param string $github_repo GitHub repository name.
		 */
		public function __construct( $plugin_file, $github_owner = '', $github_repo = '' ) {
			$this->plugin_file = $plugin_file;
			$this->plugin_slug = plugin_basename( $plugin_file );

			if ( ! empty( $github_owner ) ) {
				$this->github_owner = $github_owner;
			}
			if ( ! empty( $github_repo ) ) {
				$this->github_repo = $github_repo;
			}

			// Hook into WordPress update system.
			add_filter( 'transient_update_plugins', [ $this, 'check_update' ] );
			add_filter( 'transient_site_transient_update_plugins', [ $this, 'check_update' ] );
			add_filter( 'plugins_api', [ $this, 'plugin_info' ], 10, 3 );
		}

		/**
		 * Check for updates from GitHub.
		 *
		 * @param object $transient Transient data.
		 *
		 * @return object
		 */
		public function check_update( $transient ) {
			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			$remote_version = $this->get_remote_version();

			if ( is_wp_error( $remote_version ) ) {
				return $transient;
			}

			$local_version = $transient->checked[ $this->plugin_slug ];

			if ( version_compare( $remote_version, $local_version, '>' ) ) {
				$plugin_info = $this->get_plugin_info();

				if ( is_wp_error( $plugin_info ) ) {
					return $transient;
				}

				$transient->response[ $this->plugin_slug ] = (object) array_merge(
					$plugin_info,
					[
						'new_version' => $remote_version,
						'package'     => $this->get_download_url( $remote_version ),
						'url'         => $this->get_github_url(),
						'tested'      => '6.6',
					]
				);
			}

			return $transient;
		}

		/**
		 * Get plugin info for popup.
		 *
		 * @param false|object|array $result Plugin info.
		 * @param string             $action Action name.
		 * @param object             $args Arguments.
		 *
		 * @return false|object|array
		 */
		public function plugin_info( $result, $action, $args ) {
			if ( 'plugin_information' !== $action ) {
				return $result;
			}

			if ( $this->plugin_slug !== $args->slug ) {
				return $result;
			}

			$plugin_info = $this->get_plugin_info();

			if ( is_wp_error( $plugin_info ) ) {
				return $result;
			}

			$remote_version = $this->get_remote_version();

			if ( ! is_wp_error( $remote_version ) ) {
				$plugin_info['version'] = $remote_version;
				$plugin_info['package'] = $this->get_download_url( $remote_version );
			}

			return (object) $plugin_info;
		}

		/**
		 * Get remote version from GitHub.
		 *
		 * @return string|WP_Error
		 */
		private function get_remote_version() {
			$transient_key = 'onlive_wa_order_github_version';

			// Check cache first (1 hour).
			$cached = get_transient( $transient_key );
			if ( $cached ) {
				return $cached;
			}

			$release_data = $this->get_github_release();

			if ( is_wp_error( $release_data ) ) {
				return $release_data;
			}

			if ( empty( $release_data->tag_name ) ) {
				return new WP_Error( 'no_version', __( 'Could not determine remote version.', 'onlive-wa-order' ) );
			}

			// Remove 'v' prefix if present.
			$version = ltrim( $release_data->tag_name, 'v' );

			// Cache for 1 hour.
			set_transient( $transient_key, $version, HOUR_IN_SECONDS );

			return $version;
		}

		/**
		 * Get GitHub release data.
		 *
		 * @return object|WP_Error
		 */
		private function get_github_release() {
			$url = sprintf(
				'%s%s/%s/releases/latest',
				$this->github_api_url,
				$this->github_owner,
				$this->github_repo
			);

			$response = wp_remote_get(
				$url,
				[
					'headers'   => [
						'Accept'     => 'application/vnd.github.v3+json',
						'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
					],
					'sslverify' => true,
					'timeout'   => 10,
				]
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== $code ) {
				return new WP_Error(
					'github_error',
					sprintf(
						__( 'GitHub API error: %d', 'onlive-wa-order' ),
						$code
					)
				);
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );

			return $data;
		}

		/**
		 * Get download URL for specific version.
		 *
		 * @param string $version Version tag.
		 *
		 * @return string
		 */
		private function get_download_url( $version ) {
			return sprintf(
				'https://github.com/%s/%s/archive/refs/tags/v%s.zip',
				$this->github_owner,
				$this->github_repo,
				$version
			);
		}

		/**
		 * Get GitHub repository URL.
		 *
		 * @return string
		 */
		private function get_github_url() {
			return sprintf(
				'https://github.com/%s/%s',
				$this->github_owner,
				$this->github_repo
			);
		}

		/**
		 * Get basic plugin info.
		 *
		 * @return array
		 */
		private function get_plugin_info() {
			$release_data = $this->get_github_release();

			if ( is_wp_error( $release_data ) ) {
				return [];
			}

			$description = ! empty( $release_data->body ) ? $release_data->body : __( 'Onlive WooCommerce WhatsApp Order', 'onlive-wa-order' );

			return [
				'name'              => __( 'Onlive WooCommerce WhatsApp Order', 'onlive-wa-order' ),
				'slug'              => 'onlive-whatsapp-order',
				'plugin'            => $this->plugin_slug,
				'author'            => 'Onlive',
				'author_profile'    => $this->get_github_url(),
				'requires'          => '6.0',
				'requires_php'      => '7.4',
				'requires_plugins'  => [ 'woocommerce/woocommerce.php' ],
				'tested'            => '6.6',
				'rating'            => 100,
				'num_ratings'       => 1,
				'downloaded'        => 0,
				'active_installs'   => 0,
				'last_updated'      => gmdate( 'Y-m-d h:i A', strtotime( $release_data->published_at ?? 'now' ) ),
				'homepage'          => $this->get_github_url(),
				'description'       => $description,
				'sections'          => [
					'description'   => __( 'Adds WhatsApp Order buttons to WooCommerce with customizable templates and settings.', 'onlive-wa-order' ),
					'installation'  => __( '1. Upload to /wp-content/plugins/
2. Activate the plugin
3. Configure settings in WhatsApp Order Pro menu', 'onlive-wa-order' ),
					'faq'           => __( 'Q: How do I configure the plugin?
A: Go to WhatsApp Order Pro in the admin menu.', 'onlive-wa-order' ),
					'screenshots'   => [],
					'changelog'     => __( 'See GitHub releases for complete changelog.', 'onlive-wa-order' ),
				],
			];
		}
	}
}
