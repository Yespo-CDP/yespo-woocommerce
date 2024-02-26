<?php

/**
 * Yespo
 *
 * @package   Yespo
 * @author    Yespo Omnichannel CDP <vadym.gmurya@asper.pro>
 * @copyright 2022 Yespo
 * @license   GPL 3.0+
 * @link      https://yespo.io/
 */

namespace Yespo\Backend;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Yespo\Engine\Base;

/**
 * This class contain the Enqueue stuff for the backend
 */
class Enqueue extends Base {

	/**
	 * Initialize the class.
	 *
	 * @return void|bool
	 */
	public function initialize() {
		if ( !parent::initialize() ) {
			return;
		}

		\add_action( AssetManager::ACTION_SETUP, array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue assets with Inpyside library https://inpsyde.github.io/assets
	 *
	 * @param \Inpsyde\Assets\AssetManager $asset_manager The class.
	 * @return void
	 */
	public function enqueue_assets( AssetManager $asset_manager ) {
		// Load admin style sheet and JavaScript.
		$assets = $this->enqueue_admin_styles();

		if ( !empty( $assets ) ) {
			foreach ( $assets as $asset ) {
				$asset_manager->register( $asset );
			}
		}

		$assets = $this->enqueue_admin_scripts();

		if ( !empty( $assets ) ) {
			foreach ( $assets as $asset ) {
				$asset_manager->register( $asset );
			}
		}

	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function enqueue_admin_styles() {
		$admin_page = \get_current_screen();
		$styles     = array();

		if ( !\is_null( $admin_page ) && 'toplevel_page_yespo' === $admin_page->id ) {
			$styles[0] = new Style( Y_TEXTDOMAIN . '-settings-style', \plugins_url( 'assets/build/plugin-settings.css', Y_PLUGIN_ABSOLUTE ) );
			$styles[0]->forLocation( Asset::BACKEND )->withVersion( Y_VERSION );
			$styles[0]->withDependencies( 'dashicons' );
		}

		$styles[1] = new Style( Y_TEXTDOMAIN . '-admin-style', \plugins_url( 'assets/build/plugin-admin.css', Y_PLUGIN_ABSOLUTE ) );
		$styles[1]->forLocation( Asset::BACKEND )->withVersion( Y_VERSION );
		$styles[1]->withDependencies( 'dashicons' );

		return $styles;
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function enqueue_admin_scripts() {
		$admin_page = \get_current_screen();
		$scripts    = array();

		if ( !\is_null( $admin_page ) && 'toplevel_page_yespo' === $admin_page->id ) {
			$scripts[0] = new Script( Y_TEXTDOMAIN . '-settings-script', \plugins_url( 'assets/build/plugin-settings.js', Y_PLUGIN_ABSOLUTE ) );
			$scripts[0]->forLocation( Asset::BACKEND )->withVersion( Y_VERSION );
			$scripts[0]->withDependencies( 'jquery-ui-tabs' );
			$scripts[0]->canEnqueue(
				function() {
					return \current_user_can( 'manage_options' );
				}
			);
		}

		$scripts[1] = new Script( Y_TEXTDOMAIN . '-settings-admin', \plugins_url( 'assets/build/plugin-admin.js', Y_PLUGIN_ABSOLUTE ) );
		$scripts[1]->forLocation( Asset::BACKEND )->withVersion( Y_VERSION );
		$scripts[1]->dependencies();

		return $scripts;
	}

}
