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

function yespo_save_settings() {
    if ( ! isset( $_POST['yespo_plugin_settings_nonce'] ) || ! wp_verify_nonce( $_POST['yespo_plugin_settings_nonce'], 'yespo_plugin_settings_save' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options['yespo_username'] = sanitize_text_field( $_POST['yespo_username'] );
    $options['yespo_api_key'] = sanitize_text_field( $_POST['yespo_api_key'] );
    update_option( 'yespo_options', $options );
}

add_action( 'admin_init', 'yespo_save_settings' );


function forms_updated_option_callback(){
    if(isset($_REQUEST['settings-updated']) ) {
        if ($_REQUEST['page'] === 'yespo' ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __("Settings successfully saved!","yespo") . '</p></div>';
        }
    }
}
add_action('admin_notices', 'forms_updated_option_callback');