<?php
/**
 * Async
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
 * Handle asynchonous requests
 *
 * @class Shortcodes_Converter\Async
 * @since 1.0.0
 */
abstract class Async {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		add_action( 'wp_ajax_sc_converter_request', [ $this, 'maybe_handle' ] );

	}

	/**
	 * Send response
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param boolean $success Success state.
	 * @param string  $message Holds message for backend.
	 * @param array   $content Holds content for backend.
	 */
	protected function send_response( $success = true, $message = '', $content = '' ) {

		wp_send_json(
			[
				'success' => (bool) $success,
				'message' => wp_strip_all_tags( $message ),
				'content' => $content,
			]
		);

	}

	/**
	 * Handle unknown errors
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function unknown_error() {

		$this->send_response(
			false,
			__( 'Sorry, an unknown error occurred.', 'sc-converter' )
		);

	}

	/**
	 * Maybe handle request
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function maybe_handle() {

		$this->capability();
		$this->referer();
		$this->normalize();
		$this->sanitize();

		switch ( $this->request['method'] ) {
			case 'convert':
				$this->convert( $this->request );
				break;
			case 'restore':
				$this->restore( $this->request );
				break;
			case 'cleanup':
				$this->cleanup();
				break;
		}

		$this->send_response();
	}

	/**
	 * Check capability
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function capability() {

		if ( ! current_user_can( 'manage_options' ) ) {

			$this->send_response(
				false,
				__( 'You are not allowed to do this action.', 'sc-converter' )
			);

		}

	}

	/**
	 * Check referer
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function referer() {

		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'sc-converter' ) ) {

			$this->send_response(
				false,
				__( 'An error occurred. Please try to refresh the page or logout and login again.', 'sc-converter' )
			);

		}

		$this->request = wp_unslash( $_POST );

	}

	/**
	 * Normalise data
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function normalize() {

		$this->request = wp_parse_args(
			$this->request,
			[
				'posts_per_page' => 5,
				'post_type'      => [ 'post' ],
				'post__in'       => [],
				'offset'         => 0,
				'method'         => '',
			]
		);

	}

	/**
	 * Sanitize data
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function sanitize() {

		$this->marker = [
			'no_found_rows'  => true,
			'posts_per_page' => (int) $this->request['posts_per_page'],
			'post_type'      => array_map( 'sanitize_text_field', (array) $this->request['post_type'] ),
			'post__in'       => array_map( 'intval', (array) $this->request['post__in'] ),
			'offset'         => (int) $this->request['offset'],
		];

	}

	/**
	 * Handle convert request
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Holds query arguments.
	 */
	abstract protected function convert( $args = [] );

	/**
	 * Handle restore request
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Holds query arguments.
	 */
	abstract protected function restore( $args = [] );

	/**
	 * Handle cleanup request
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	abstract protected function cleanup();
}
