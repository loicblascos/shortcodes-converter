<?php
/**
 * Converter
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
 * Converter
 *
 * @class Shortcodes_Converter\Converter
 * @since 1.0.0
 */
trait Converter {

	/**
	 * Convert content
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $args Holds query arguments.
	 */
	protected function convert( $args = [] ) {

		global $wpdb;

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return;
		}

		while ( $query->have_posts() ) {

			$query->the_post();

			$post_id = get_the_ID();
			$content = get_the_content();

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE $wpdb->posts SET post_content = %s WHERE ID = %d",
					( new Parser() )->parse( $content ),
					$post_id
				)
			);

			update_post_meta( $post_id, '_sc_converter_post_content', $content );

		}

		wp_reset_postdata();

		$progress = $this->get_progress( $args );
		$this->send_response( false, $progress['message'], $progress['offset'] );

	}

	/**
	 * Restore original post content
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $args Holds query arguments.
	 */
	protected function restore( $args = [] ) {

		global $wpdb;

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return;
		}

		while ( $query->have_posts() ) {

			$query->the_post();

			$post_id = get_the_ID();
			$content = get_post_meta( $post_id, '_sc_converter_post_content', true );

			if ( ! empty( $content ) ) {

				$wpdb->query(
					$wpdb->prepare(
						"UPDATE $wpdb->posts SET post_content = %s WHERE ID = %d",
						$content,
						$post_id
					)
				);

				delete_post_meta( $post_id, '_sc_converter_post_content' );

			}
		}

		wp_reset_postdata();

		$progress = $this->get_progress( $args );
		$this->send_response( false, $progress['message'], $progress['offset'] );

	}

	/**
	 * Restore content
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function cleanup() {

		global $wpdb;

		$wpdb->query( "DELETE from $wpdb->postmeta WHERE meta_key = '_sc_converter_post_content'" );

		$this->send_response( false, __( 'Cleaned!', 'sc-converter' ) );

	}


	/**
	 * Get progression
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $args Holds query arguments.
	 * @return array Hold progression states.
	 */
	protected function get_progress( $args ) {

		$offset    = 0;
		$progress  = 100;
		$processed = $args['offset'] + $args['posts_per_page'];
		$tt_posts  = count(
			( new \WP_Query(
				wp_parse_args(
					[
						'fields'         => 'ids',
						'posts_per_page' => -1,
					],
					$args
				)
			) )->posts
		);

		wp_reset_postdata();

		if ( $processed < $tt_posts ) {

			$offset = $processed;
			$progress = min( 100, round( $offset / $tt_posts * 100 ) );

		}

		return [
			/* translators: %d: progress in percent */
			'message' => sprintf( __( 'Processing... %s%%', 'sc-converter' ), $progress ),
			'offset'  => $offset,
		];
	}
}
