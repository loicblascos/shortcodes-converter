<?php
/**
 * Parser
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
 * Shortcode parser
 *
 * @class Shortcodes_Converter\Parser
 * @since 1.0.0
 */
final class Parser {

	/**
	 * Holds shortcodes tagnames and callbacks.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $shortcodes = [
		'embed'            => 'Shortcodes_Converter\Shortcodes\embed',
		'vc_row'           => 'Shortcodes_Converter\Shortcodes\vc_row',
		'vc_column'        => 'Shortcodes_Converter\Shortcodes\vc_column',
		'vc_column_text'   => 'Shortcodes_Converter\Shortcodes\vc_column_text',
		'vc_video'         => 'Shortcodes_Converter\Shortcodes\vc_video',
		'vc_single_image'  => 'Shortcodes_Converter\Shortcodes\vc_single_image',
		'vc_btn'           => 'Shortcodes_Converter\Shortcodes\vc_btn',
		'vc_zigzag'        => 'Shortcodes_Converter\Shortcodes\vc_zigzag',
	];

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		$this->shortcodes = apply_filters( 'sc_converter_shortcodes', $this->shortcodes );

	}

	/**
	 * Parse content
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  string $content Content to parse.
	 * @return string Content parsed.
	 */
	public function parse( $content ) {

		$content = $this->undo_shortcodes( $content );
		$content = preg_replace( '~(?:\[/?)[^/\]]+/?\]~s', '', $content );

		return $content;

	}

	/**
	 * Check if content contains shortcodes
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  string $content Content to parse.
	 * @return boolean
	 */
	public function has_shortcodes( $content ) {

		return false !== strpos( $content, '[' );

	}

	/**
	 * Get shortcodes tagnames from content
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  string $content Content to parse.
	 * @return array
	 */
	public function get_tagnames( $content ) {

		preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
		return array_intersect( array_keys( $this->shortcodes ), $matches[1] );

	}

	/**
	 * Convert shortcodes
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  string $content Content to parse.
	 * @return string
	 */
	public function undo_shortcodes( $content ) {

		if ( ! $this->has_shortcodes( $content ) ) {
			return $content;
		}

		$tagnames = $this->get_tagnames( $content );

		if ( empty( $tagnames ) ) {
			return $content;
		}

		$content = $this->undo_shortcodes_in_html_tags( $content, $tagnames );
		$pattern = get_shortcode_regex( $tagnames );
		$content = preg_replace_callback( "/$pattern/", [ $this, 'undo_shortcode_tag' ], $content );
		$content = unescape_invalid_shortcodes( $content );

		return $content;

	}

	/**
	 * Convert shortcodes in HTML tags
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  string $content  Content to parse.
	 * @param  string $tagnames Holds available shortcode tagnames.
	 * @return string
	 */
	public function undo_shortcodes_in_html_tags( $content, $tagnames ) {

		// Normalize entities in unfiltered HTML before adding placeholders.
		$trans = array(
			'&#91;' => '&#091;',
			'&#93;' => '&#093;',
		);

		$content = strtr( $content, $trans );

		$trans   = array(
			'[' => '&#91;',
			']' => '&#93;',
		);

		$pattern = get_shortcode_regex( $tagnames );
		$textarr = wp_html_split( $content );

		foreach ( $textarr as &$element ) {
			if ( '' == $element || '<' !== $element[0] ) {
				continue;
			}

			$noopen  = false === strpos( $element, '[' );
			$noclose = false === strpos( $element, ']' );

			if ( $noopen || $noclose ) {
				// This element does not contain shortcodes.
				if ( $noopen xor $noclose ) {
					// Need to encode stray [ or ] chars.
					$element = strtr( $element, $trans );
				}
				continue;
			}

			$attributes = wp_kses_attr_parse( $element );
			if ( false === $attributes ) {

				if ( 1 === preg_match( '%^<\s*\[\[?[^\[\]]+\]%', $element ) ) {
					$element = preg_replace_callback( "/$pattern/", [ $this, 'undo_shortcode_tag' ], $element );
				}

				$element = strtr( $element, $trans );
				continue;
			}

			$front = array_shift( $attributes );
			$back  = array_pop( $attributes );

			preg_match( '%[a-zA-Z0-9]+%', $front, $matches );

			foreach ( $attributes as &$attr ) {

				$open   = strpos( $attr, '[' );
				$close  = strpos( $attr, ']' );
				$double = strpos( $attr, '"' );
				$single = strpos( $attr, "'" );

				if ( false === $open || false === $close ) {
					continue;
				}

				if (
					( false === $single || $open < $single ) &&
					( false === $double || $open < $double )
				) {
					$attr = preg_replace_callback( "/$pattern/", [ $this, 'undo_shortcode_tag' ], $attr );
				} else {
					$count    = 0;
					$new_attr = preg_replace_callback( "/$pattern/", [ $this, 'undo_shortcode_tag' ], $attr, -1, $count );
					if ( $count > 0 ) {
						// Sanitize the shortcode output using KSES.
						$new_attr = wp_kses_one_attr( $new_attr, $matches[0] );
						if ( '' !== trim( $new_attr ) ) {
							$attr = $new_attr;
						}
					}
				}
			}

			$element = $front . implode( '', $attributes ) . $back;
			$element = strtr( $element, $trans );
		}

		$content = implode( '', $textarr );

		return $content;
	}

	/**
	 * Convert shortcodes tags
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  string $m Content to parse.
	 * @return string
	 */
	public function undo_shortcode_tag( $m ) {

		// Allow [[foo]] syntax for escaping a tag.
		if ( '[' === $m[1] && ']' === $m[6] ) {
			return substr( $m[0], 1, -1 );
		}

		$tag     = $m[2];
		$attr    = shortcode_parse_atts( $m[3] );
		$content = isset( $m[5] ) ? $m[5] : null;
		$output  = '';

		if ( isset( $this->shortcodes[ $tag ] ) && is_callable( $this->shortcodes[ $tag ] ) ) {
			$output = $m[1] . call_user_func( $this->shortcodes[ $tag ], $attr, $content );
		}

		return $this->undo_shortcodes( $output );

	}
}
