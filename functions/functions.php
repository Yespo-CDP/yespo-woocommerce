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

    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'check_api_key_esputnik' ) {
        $options['yespo_username'] = sanitize_text_field($_POST['yespo_username']);
        $options['yespo_api_key'] = sanitize_text_field($_POST['yespo_api_key']);
        $result = (new \Yespo\Integrations\Esputnik\Account())->send_keys($options['yespo_username'], $options['yespo_api_key']);
        if ($result === 200) {
            $response_data = array(
                'status' => 'success',
                'message' => '<div class="notice notice-success is-dismissible"><p>' . __("Settings updated successfully!", Y_TEXTDOMAIN) . '</p></div>',
            );
        } else {
            $response_data = array(
                'status' => 'error',
                'message' => '<div class="notice notice-error is-dismissible"><p>' . __("Authorization failed, please check your credentials", Y_TEXTDOMAIN) . '</p></div>',
            );
        }
        update_option('yespo_options', $options);
        echo json_encode( $response_data );
        exit;
    }
}
add_action('wp_ajax_check_api_key_esputnik', 'yespo_save_settings');
add_action('wp_ajax_nopriv_gcheck_api_key_esputnik', 'yespo_save_settings');

/** send user data to Esputnik **/
function register_woocommerce_user_esputnik($user_id){
    if(!empty($user_id)) {
        $user_data = get_userdata($user_id);
        if(isset($user_data->user_email)) return (new \Yespo\Integrations\Esputnik\AddUpdateContact())->send_data($user_data->user_email, $user_id);
    }
}
add_action('user_register', 'register_woocommerce_user_esputnik', 10, 1);