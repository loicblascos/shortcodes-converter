<?php
/**
 * Plugin
 *
 * @package   Shortcodes converter
 * @author    Loïc Blascos
 * @copyright 2019 Loïc Blascos
 */

namespace Shortcodes_Converter;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Instance of the plugin
 *
 * @class Shortcodes_Converter\Plugin
 * @since 1.0.0
 */
final class Plugin extends Async {

	use Converter;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		parent::__construct();

		add_action( 'plugins_loaded', [ $this, 'textdomain' ] );
		add_action( 'plugins_loaded', [ $this, 'register_helpers' ] );
		add_action( 'admin_menu', [ $this, 'add_tools_submenu' ] );

	}

	/**
	 * Set plugin instance properties
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Holds instance properties.
	 */
	public function set_instance( $args ) {

		foreach ( $args as $key => $val ) {
			$this->$key = $val;
		}
	}

	/**
	 * Register textdomain
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function textdomain() {

		load_plugin_textdomain(
			'sc-converter',
			false,
			basename( dirname( $this->file ) ) . '/languages'
		);

		// Translate Plugin Description.
		__( 'Convert shortcodes to Gutenberg blocks.', 'sc-converter' );

	}

	/**
	 * Register shortcodes and blocks helpers
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_helpers() {

		include $this->path . 'includes/blocks.php';
		include $this->path . 'includes/shortcodes.php';

	}

	/**
	 * Add submenu in Tools menu of WordPress
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_tools_submenu() {

		add_management_page(
			__( 'Shortcodes Converter', 'sc-converter' ),
			__( 'Shortcodes Converter', 'sc-converter' ),
			'manage_options',
			'sc_converter',
			[ $this, 'page_content' ]
		);

	}

	/**
	 * Handle page content
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function page_content() {

		include $this->path . 'includes/views/page.php';
		$this->enqueue();

	}

	/**
	 * Enqueue script
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue() {

		$data = [
			'ajaxUrl'  => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'nonce'    => current_user_can( 'manage_options' ) ? wp_create_nonce( 'sc-converter' ) : 666,
			/* translators: %d: progress in percent */
			'progress' => sprintf( __( 'Processing... %d%%', 'sc-converter' ), 0 ),
		];

		wp_enqueue_script( 'sc-converter', $this->url . 'assets/js/build.js', [], $this->version );
		wp_localize_script( 'sc-converter', 'sc_converter', $data );

	}
}
