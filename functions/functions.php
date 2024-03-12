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
        $options['yespo_api_key'] = sanitize_text_field($_POST['yespo_api_key']);
        $result = (new \Yespo\Integrations\Esputnik\Esputnik_Account())->send_keys($options['yespo_api_key']);
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
add_action('wp_ajax_nopriv_check_api_key_esputnik', 'yespo_save_settings');

/** send user data to Esputnik **/
function register_woocommerce_user_esputnik($user_id){
    if(!empty($user_id)) {
        $user_data = get_userdata($user_id);
        if(isset($user_data->user_email)) return (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->create_on_yespo($user_data->user_email, $user_id);
    }
}
add_action('user_register', 'register_woocommerce_user_esputnik', 10, 1);

/** update user profile on esputnik service **/
function update_user_profile_esputnik($user_id, $old_user_data) {
    if(!empty($user_id)) {
        $user = get_user_by('id', $user_id);
        if(isset($user->data->user_email)) return (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->update_on_yespo($user);
    }
}
add_action('profile_update', 'update_user_profile_esputnik', 10, 2);

/*** Get total number for export ***/
function get_all_users_total() {
    $users = (new Yespo\Integrations\Esputnik\Esputnik_Export_Users)->get_users_count();
    echo json_encode($users);
    wp_die();
}
add_action('wp_ajax_get_users_total', 'get_all_users_total');
add_action('wp_ajax_nopriv_get_all_users_total', 'get_all_users_total');

/*** Export users to esputnik ***/
function export_user_data_to_esputnik(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'export_user_data_to_esputnik' ) {
        if(isset($_POST['startIndex'])){
            $response = (new Yespo\Integrations\Esputnik\Esputnik_Export_Users)->export_users_to_esputnik();
            echo json_encode(intval($response));
        }
    }
    wp_die();
}
add_action('wp_ajax_export_user_data_to_esputnik', 'export_user_data_to_esputnik');
add_action('wp_ajax_nopriv_export_user_data_to_esputnik', 'export_user_data_to_esputnik');

/** remove woocommerce user **/
function delete_woocommerce_user( $user_id ) {
    (new Yespo\Integrations\Esputnik\Esputnik_Contact())->delete_from_yespo($user_id);
}
add_action( 'delete_user', 'delete_woocommerce_user');
//add_action('save_post', 'delete_woocommerce_user', 10, 1);