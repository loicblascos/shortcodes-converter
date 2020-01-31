<?php
/**
 * Autoloader
 *
 * @package   Shortcodes converter
 * @author    Loïc Blascos
 * @copyright 2019 Loïc Blascos
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	function ( $class ) {

		// Assume we're using namespaces (because that's how the plugin is structured).
		$namespace   = explode( '\\', $class );
		$class_trait = preg_match( '/Trait$/', $class ) ? 'trait-' : 'class-';

		// If we're not in the plugin's namespace then just return.
		if ( 'Shortcodes_Converter' !== array_shift( $namespace ) ) {
			return;
		}

		// Class name is the last part of the FQN.
		$class_name = array_pop( $namespace );

		// Remove "Trait" from the class name.
		if ( 'trait-' === $class_trait ) {
			$class_name = str_replace( 'Trait', '', $class_name );
		}

		$filename  = $class_trait . $class_name . '.php';
		$namespace = trim( implode( DIRECTORY_SEPARATOR, $namespace ) );
		$filename  = strtolower( str_replace( '_', '-', $filename ) );
		$namespace = strtolower( str_replace( '_', '-', $namespace ) );
		$directory = dirname( __FILE__ );

		if ( ! empty( $namespace ) ) {
			$directory .= DIRECTORY_SEPARATOR . $namespace;
		}

		$file = $directory . DIRECTORY_SEPARATOR . $filename;

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);
