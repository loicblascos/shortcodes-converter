<?php
/**
 * Shortcodes converter
 *
 * @package   Shortcodes converter
 * @author    Loïc Blascos
 * @copyright 2019 Loïc Blascos
 */

namespace Shortcodes_Converter\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convert block attribute to JSON literal string
 *
 * @param array $attr Holds attributes to encode.
 * @return string
 */
function convert_attr( $attr = [] ) {

	return json_encode( $attr );

}

/**
 * Heading block
 *
 * @param array  $attr    Holds block attrbiutes.
 * @param string $content Block content.
 * @return string Block content.
 */
function heading( $attr = [], $content = '' ) {

	$content = strip_tags( $content, '<strong><em><b><i><u><s><a><abbr><code><pre>' );
	$content = trim( $content );

	if ( empty( $content ) || empty( $attr['level'] ) ) {
		return '';
	}

	$level = max( 2, min( 6, (int) $attr['level'] ) );
	$attr  = convert_attr( [ 'level' => $level ] );
	$tag   = tag_escape( 'h' . $level );

	$block  = '<!-- wp:heading ' . $attr . ' -->';
	$block .= '<' . $tag . '>' . $content . '</' . $tag . '>';
	$block .= '<!-- /wp:heading -->';

	return $block;

}

/**
 * Paragraph block
 *
 * @param array  $attr    Holds block attrbiutes.
 * @param string $content Block content.
 * @return string Block content.
 */
function paragraph( $attr = [], $content = '' ) {

	$content = strip_tags( $content, '<strong><em><b><i><u><s><a><abbr><code><pre><img>' );
	$content = trim( $content );

	if ( empty( $content ) ) {
		return '';
	}

	$block  = '<!-- wp:paragraph -->';
	$block .= '<p>' . $content . '</p>';
	$block .= '<!-- /wp:paragraph -->';

	return $block;

}

/**
 * Button block
 *
 * @param array  $attr    Holds block attrbiutes.
 * @param string $content Block content.
 * @return string Block content.
 */
function button( $attr = [], $content = '' ) {

	if ( empty( $attr['href'] ) || empty( $attr['text'] ) ) {
		return '';
	}

	$target = ! empty( $attr['target'] ) && '_blank' === $attr['target'] ? ' target="_blank" rel="noreferrer noopener"' : '';

	$block  = '<!-- wp:button -->';
	$block .= '<div class="wp-block-button"><a class="wp-block-button__link" href="' . esc_url( $attr['href'] ) . '"' . $target . '>' . $attr['text'] . '</a></div>';
	$block .= '<!-- /wp:button -->';

	return $block;

}

/**
 * Spacer block
 *
 * @param array  $attr    Holds block attrbiutes.
 * @param string $content Block content.
 * @return string Block content.
 */
function spacer( $attr = [], $content = '' ) {

	if ( empty( $attr['height'] ) ) {
		return '';
	}

	$block  = '<!-- wp:spacer ' . convert_attr( [ 'height' => (int) $attr['height'] ] ) . ' -->';
	$block .= '<div style="height:' . esc_attr( $attr['height'] ) . '" aria-hidden="true" class="wp-block-spacer"></div>';
	$block .= '<!-- /wp:spacer -->';

	return $block;

}

/**
 * Separator block
 *
 * @param array  $attr    Holds block attrbiutes.
 * @param string $content Block content.
 * @return string Block content.
 */
function separator( $attr = [], $content = '' ) {

	$block  = '<!-- wp:separator -->';
	$block .= '<hr class="wp-block-separator"/>';
	$block .= '<!-- /wp:separator -->';

	return $block;

}

/**
 * Image block
 *
 * @param array  $attr    Holds block attrbiutes.
 * @param string $content Block content.
 * @return string Block content.
 */
function image( $attr = [], $content = '' ) {

	if ( empty( $attr['id'] ) ) {
		return '';
	}

	if ( empty( $attr['sizeSlug'] ) ) {
		$attr['sizeSlug'] = 'large';
	}

	$attr['id'] = (int) $attr['id'];
	$attr['className'] = 'size-' . $attr['sizeSlug'];

	$image = wp_get_attachment_image_src( $attr['id'], $attr['sizeSlug'] );

	if ( empty( $image[0] ) ) {
		return '';
	}

	if ( empty( $attr['alt'] ) ) {
		$attr['alt'] = '';
	}

	$block  = '<!-- wp:image ' . convert_attr( $attr ) . ' -->';
	$block .= '<figure class="wp-block-image ' . $attr['className'] . '">';
	$block .= '<img src="' . esc_url( $image[0] ) . '" alt="' . esc_attr( $attr['alt'] ) . '" class="wp-image-' . esc_attr( $attr['id'] ) . '"/>';
	$block .= ! empty( $attr['caption'] ) ? '<figcaption>' . $attr['caption'] . '</figcaption>' : '';
	$block .= '</figure>';
	$block .= '<!-- /wp:image -->';

	return $block;

}

/**
 * Audio block
 *
 * @param array  $attr    Holds block attrbiutes.
 * @param string $content Block content.
 * @return string Block content.
 */
function audio( $attr = [], $content = '' ) {

	if ( empty( $attr['src'] ) ) {
		return '';
	}

	$block  = '<!-- wp:audio -->';
	$block .= '<figure class="wp-block-audio">';
	$block .= '<audio controls src="' . esc_url( $attr['src'] ) . '"></audio>';
	$block .= ! empty( $attr['caption'] ) ? '<figcaption>' . $attr['caption'] . '</figcaption>' : '';
	$block .= '</figure>';
	$block .= '<!-- /wp:audio -->';

	return $block;

}

/**
 * Embed block
 *
 * @param array  $attr    Holds block attrbiutes.
 * @param string $content Block content.
 * @return string Block content.
 */
function embed( $attr = [], $content = '' ) {

	if ( empty( $attr['url'] ) || empty( $attr['type'] ) ) {
		return '';
	}

	$oembed = new \WP_oembed();
	$oembed = $oembed->get_data( $attr['url'] );

	if ( empty( $oembed ) ) {
		return '';
	}

	$provider = strtolower( $oembed->provider_name );
	$attr['providerNameSlug'] = $provider;
	$attr['className'] = ' wp-embed-aspect-16-9 wp-has-aspect-ratio';

	if ( ! empty( $attr['align'] ) ) {
		$attr['className'] .= ' align' . $attr['align'];
	}

	$block  = '<!-- wp:core-embed/' . $provider . ' ' . convert_attr( $attr ) . ' -->';
	$block .= '<figure class="wp-block-embed-' . $provider . ' wp-block-embed is-type-' . $attr['type'] . ' is-provider-' . $provider . $attr['className'] . '">';
	$block .= '<div class="wp-block-embed__wrapper">' . "\n" . $attr['url'] . "\n" . '</div>';
	$block .= '</figure>';
	$block .= '<!-- /wp:core-embed/' . $provider . ' -->';

	return $block;

}
