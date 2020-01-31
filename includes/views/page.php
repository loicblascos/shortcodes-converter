<?php
/**
 * Shortcodes converter
 *
 * @package   Shortcodes converter
 * @author    Loïc Blascos
 * @copyright 2019 Loïc Blascos
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2><?php esc_html_e( 'Convert Shortcodes', 'sc-converter' ); ?></h2>
<button type="button" id="convert" class="button button-primary button-hero"><?php esc_html_e( 'Convert', 'sc-converter' ); ?></button>
<button type="button" class="button button-hero abort"><?php esc_html_e( 'Cancel', 'sc-converter' ); ?></button>

<h2><?php esc_html_e( 'Restore Shortcodes', 'sc-converter' ); ?></h2>
<button type="button" id="restore" class="button button-primary button-hero"><?php esc_html_e( 'Restore', 'sc-converter' ); ?></button>
<button type="button" class="button button-hero abort"><?php esc_html_e( 'Cancel', 'sc-converter' ); ?></button>

<h2><?php esc_html_e( 'Cleanup Content', 'sc-converter' ); ?></h2>
<button type="button" id="cleanup" class="button button-primary button-hero"><?php esc_html_e( 'Cleanup', 'sc-converter' ); ?></button>
<button type="button" class="button button-hero abort"><?php esc_html_e( 'Cancel', 'sc-converter' ); ?></button>
<?php
