<?php

/**
 * @package   Yespo
 * @author    Yespo Omnichannel CDP <vadym.gmurya@asper.pro>
 * @copyright 2022 Yespo
 * @license   GPL 3.0+
 * @link      https://yespo.io/
 *
 * Plugin Name:     Yespo
 * Plugin URI:      https://yespo.io
 * Description:     Yespo Woocomerce Integration
 * Version:         1.0.0
 * Author:          Yespo Omnichannel CDP
 * Author URI:      https://yespo.io/
 * Text Domain:     yespo
 * License:         GPL 3.0+
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:     /languages
 * Requires PHP:    7.4
 * WordPress-Plugin-Boilerplate-Powered: v3.3.0
 */

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'Y_VERSION', '1.0.0' );
define( 'Y_TEXTDOMAIN', 'yespo' );
define( 'Y_NAME', 'Yespo' );
define( 'Y_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'Y_PLUGIN_ABSOLUTE', __FILE__ );
define( 'Y_MIN_PHP_VERSION', '7.4' );
define( 'Y_WP_VERSION', '5.3' );

add_action(
	'init',
	static function () {
		load_plugin_textdomain( Y_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	);

if ( version_compare( PHP_VERSION, Y_MIN_PHP_VERSION, '<=' ) ) {
	add_action(
		'admin_init',
		static function() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function() {
			echo wp_kses_post(
			sprintf(
				'<div class="notice notice-error"><p>%s</p></div>',
				__( '"Yespo" requires PHP 5.6 or newer.', Y_TEXTDOMAIN )
			)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

$yespo_libraries = require Y_PLUGIN_ROOT . 'vendor/autoload.php'; //phpcs:ignore

require_once Y_PLUGIN_ROOT . 'functions/functions.php';
require_once Y_PLUGIN_ROOT . 'functions/debug.php';

// Add your new plugin on the wiki: https://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered/wiki/Plugin-made-with-this-Boilerplate

$requirements = new \Micropackage\Requirements\Requirements(
	'Yespo',
	array(
		'php'            => Y_MIN_PHP_VERSION,
		'php_extensions' => array( 'mbstring' ),
		'wp'             => Y_WP_VERSION,
		// 'plugins'            => array(
		// array( 'file' => 'hello-dolly/hello.php', 'name' => 'Hello Dolly', 'version' => '1.5' )
		// ),
	)
);

if ( ! $requirements->satisfied() ) {
	$requirements->print_notice();

	return;
}


/**
 * Create a helper function for easy SDK access.
 *
 * @global type $y_fs
 * @return object
 */
function y_fs() {
	global $y_fs;

	if ( !isset( $y_fs ) ) {
		require_once Y_PLUGIN_ROOT . 'vendor/freemius/wordpress-sdk/start.php';
		$y_fs = fs_dynamic_init(
			array(
				'id'             => '',
				'slug'           => 'yespo',
				'public_key'     => '',
				'is_live'        => false,
				'is_premium'     => true,
				'has_addons'     => false,
				'has_paid_plans' => true,
				'menu'           => array(
					'slug' => 'yespo',
				),
			)
		);

		if ( $y_fs->is_premium() ) {
			$y_fs->add_filter(
				'support_forum_url',
				static function ( $wp_org_support_forum_url ) { //phpcs:ignore
					return 'https://your-url.test';
				}
			);
		}
	}

	return $y_fs;
}

// y_fs();

// Documentation to integrate GitHub, GitLab or BitBucket https://github.com/YahnisElsts/plugin-update-checker/blob/master/README.md
//Puc_v4_Factory::buildUpdateChecker( 'https://github.com/user-name/repo-name/', __FILE__, 'unique-plugin-or-theme-slug' ); 24022024

if ( ! wp_installing() ) {
	register_activation_hook( Y_TEXTDOMAIN . '/' . Y_TEXTDOMAIN . '.php', array( new \Yespo\Backend\ActDeact, 'activate' ) );
	register_deactivation_hook( Y_TEXTDOMAIN . '/' . Y_TEXTDOMAIN . '.php', array( new \Yespo\Backend\ActDeact, 'deactivate' ) );
	add_action(
		'plugins_loaded',
		static function () use ( $yespo_libraries ) {
			new \Yespo\Engine\Initialize( $yespo_libraries );
		}
	);
}
