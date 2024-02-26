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

/**
 * Get the settings of the plugin in a filterable way
 *
 * @since 1.0.0
 * @return array
 */
function y_get_settings() {
	return apply_filters( 'y_get_settings', get_option( Y_TEXTDOMAIN . '-settings' ) );
}
