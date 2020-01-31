<?php
/**
 * Shortcodes converter
 *
 * @package   Shortcodes converter
 * @author    Loïc Blascos
 * @copyright 2019 Loïc Blascos
 *
 * @wordpress-plugin
 * Plugin Name:  Shortcodes converter
 * Description:  Convert shortcodes to Gutenberg blocks.
 * Version:      1.0.0
 * Author:       Loïc Blascos
 * License:      GPL-3.0-or-later
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:  sc-converter
 * Domain Path:  /languages
 */

namespace Shortcodes_Converter;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Setup the plugin autoloader.
require_once 'includes/autoloader.php';

/**
 * Instanciate the plugin.
 *
 * @since 1.0.0
 * @return \Shortcodes_Converter\Plugin instance.
 */
function shortcodes_converter() {

	static $instance;

	if ( null === $instance ) {
		$instance = new Plugin();
	}

	return $instance;

}

shortcodes_converter()->set_instance(
	[
		'version' => '1.0.0',
		'file'    => __FILE__,
		'base'    => plugin_basename( __FILE__ ),
		'path'    => plugin_dir_path( __FILE__ ),
		'url'     => plugin_dir_url( __FILE__ ),
	]
);
