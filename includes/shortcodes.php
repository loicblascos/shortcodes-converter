<?php
/**
 * Shortcodes converter
 *
 * @package   Shortcodes converter
 * @author    Loïc Blascos
 * @copyright 2019 Loïc Blascos
 */

namespace Shortcodes_Converter\Shortcodes;

use Shortcodes_Converter\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convert embed shortcode
 *
 * @param array  $attr    Holds shortcode attrbiutes.
 * @param string $content Shortcode content.
 * @return string Converted shortcode.
 */
function embed( $attr = [], $content = '' ) {

	$attr = [
		'url'   => $content,
		'type'  => 'video',
		'align' => 'wide',
	];

	return Blocks\embed( $attr, $content );

}

/**
 * Convert vc_row shortcode
 *
 * @param array  $attr    Holds shortcode attrbiutes.
 * @param string $content Shortcode content.
 * @return string Converted shortcode.
 */
function vc_row( $attr = [], $content = '' ) {

	return $content;

}

/**
 * Convert vc_column shortcode
 *
 * @param array  $attr    Holds shortcode attrbiutes.
 * @param string $content Shortcode content.
 * @return string Converted shortcode.
 */
function vc_column( $attr = [], $content = '' ) {

	return $content;

}

/**
 * Convert vc_column_text shortcode
 *
 * @param array  $attr    Holds shortcode attrbiutes.
 * @param string $content Shortcode content.
 * @return string Converted shortcode.
 */
function vc_column_text( $attr = [], $content = '' ) {

	$output  = '';
	$content = trim( $content );
	$heading = array_fill_keys( [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], true );

	if ( empty( $content ) ) {
		return $output;
	}

	$tmp = '';
	$nodes = get_nodes( $content );

	foreach ( $nodes as $node ) {

		// Add in tmp string orphan or non valid tagname nodes content.
		if (
			empty( $node['tag'] ) ||
			( ! empty( $tmp ) && ! isset( $heading[ $node['tag'] ] ) && 'p' !== $node['tag'] )
		) {
			$tmp .= $node['txt'];
			continue;
		}

		// If this is a valide tagname and there is orphan in tmp.
		if ( ! empty( $tmp ) ) {
			$output .= Blocks\paragraph( [], $tmp );
		}

		// Clear tmp content.
		$tmp = '';

		if ( isset( $heading[ $node['tag'] ] ) ) {

			$level   = str_replace( 'h', '', $node['tag'] );
			$output .= Blocks\heading( [ 'level' => $level ], $node['txt'] );

		} else {
			$output .= Blocks\paragraph( [], $node['txt'] );
		}
	}

	// If latest node(s) are orphan.
	if ( ! empty( $tmp ) ) {
		$output .= Blocks\paragraph( [], $tmp );
	}

	$output = nl2br( $output );
	$output = str_replace( '&#xD;', '', $output );
	$output = str_replace( [ '<br/>', '<br />' ], '<br>', $output );

	return $output;

}

/**
 * Convert content to HTML nodes
 *
 * @param string $content Content to parse.
 * @return array
 */
function get_nodes( $content = '' ) {

	libxml_use_internal_errors( true );

	$doc = new \DOMDocument();
	$doc->loadHTML( '<?xml encoding="utf-8" ?><html><body>' . $content . '</body></html>' );
	$body = $doc->getElementsByTagName( 'body' )->item( 0 );
	$nodes = [];

	// phpcs:disable
	foreach ( $body->childNodes as $node ) {

		$content = ltrim( $node->c14n(), '&#xD;' );

		// Recurse div wrappers.
		if ( ! empty( $node->tagName ) && 'div' === $node->tagName ) {

			$content = strip_tags( $content, '<h1><h2><h3><h4><h5><h6><p><span><strong><em><b><i><u><s><a><abbr><code><pre><img>' );
			$content = trim( str_replace( '&#xD;', '',  $content ) );
			$nodes = array_merge( $nodes, get_nodes( $content ) );

		} else {

			$nodes[] = [
				'tag' => ! empty( $node->tagName ) ? $node->tagName : '',
				'txt' => $content,
			];

		}
	}
	// phpcs:enable

	libxml_clear_errors();

	return $nodes;

}

/**
 * Convert vc_video shortcode
 *
 * @param array  $attr    Holds shortcode attrbiutes.
 * @param string $content Shortcode content.
 * @return string Converted shortcode.
 */
function vc_video( $attr = [], $content = '' ) {

	if ( empty( $attr['link'] ) ) {
		return '';
	}

	$attr = [
		'url'   => $attr['link'],
		'type'  => 'video',
		'align' => 'wide',
	];

	return Blocks\embed( $attr, $content );

}

/**
 * Convert vc_single_image shortcode
 *
 * @param array  $attr    Holds shortcode attrbiutes.
 * @param string $content Shortcode content.
 * @return string Converted shortcode.
 */
function vc_single_image( $attr = [], $content = '' ) {

	if ( empty( $attr['image'] ) ) {
		return '';
	}

	$attr = [
		'id'       => ! empty( $attr['image'] ) ? $attr['image'] : '',
		'caption'  => ! empty( $attr['title'] ) ? $attr['title'] : '',
		'sizeSlug' => ! empty( $attr['img_size'] ) ? $attr['img_size'] : 'large',
	];

	return Blocks\image( $attr, $content );

}

/**
 * Convert vc_btn shortcode
 *
 * @param array  $attr    Holds shortcode attrbiutes.
 * @param string $content Shortcode content.
 * @return string Converted shortcode.
 */
function vc_btn( $attr = [], $content = '' ) {

	$link = ! empty( $attr['link'] ) ? $attr['link'] : '';

	if ( ! empty( $link ) ) {

		$pairs = explode( '|', $link );
		$link = [];

		foreach ( $pairs as $pair ) {

			$param = preg_split( '/\:/', $pair );

			if ( ! empty( $param[0] ) && isset( $param[1] ) ) {
				$link[ $param[0] ] = trim( rawurldecode( $param[1] ) );
			}
		}
	}

	$attr = [
		'href'   => ! empty( $link['url'] ) ? $link['url'] : '',
		'text'   => ! empty( $attr['title'] ) ? $attr['title'] : '',
		'target' => ! empty( $link['target'] ) ? $link['target'] : '',
	];

	return Blocks\button( $attr, $content );

}

/**
 * Convert vc_zigzag shortcode
 *
 * @param array  $attr    Holds shortcode attrbiutes.
 * @param string $content Shortcode content.
 * @return string Converted shortcode.
 */
function vc_zigzag( $attr = [], $content = '' ) {

	return Blocks\separator( $attr, $content );

}
