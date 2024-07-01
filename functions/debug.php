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

//$y_debug = new WPBP_Debug( __( 'Yespo', YESPO_TEXTDOMAIN ) ); 24022024

/**
 * Log text inside the debugging plugins.
 *
 * @param string $text The text.
 * @return void
 */
function y_log( string $text ) {
	global $y_debug;
	$y_debug->log( $text );
}
