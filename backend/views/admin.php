<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Yespo
 * @author    Yespo Omnichannel CDP <vadym.gmurya@asper.pro>
 * @copyright 2022 Yespo
 * @license   GPL 3.0+
 * @link      https://yespo.io/
 */
?>

<div class="wrap yespo-settings-page">
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <?php
    if ( get_option( 'yespo_options' ) !== false ){
        $options = get_option('yespo_options', array());
        if(isset($options['yespo_username'])) $yespo_username = $options['yespo_username'];
        if(isset($options['yespo_api_key'])) $yespo_api_key = $options['yespo_api_key'];
    }
    ?>
    <form method="post" action="options.php">
        <div class="field-group">
            <label for="username"><?php echo __('Username','yespo') ?></label>
            <input type="text" id="username" name="yespo_username" value="<?php echo isset($yespo_username) ? $yespo_username : ''; ?>" />
        </div>
        <div class="field-group">
            <label for="api_key"><?php echo __('Api Key (Password)','yespo') ?></label>
            <input type="text" id="api_key" name="yespo_api_key" value="<?php echo isset($yespo_api_key) ? $yespo_api_key : ''; ?>" />
        </div>

        <?php wp_nonce_field( 'yespo_plugin_settings_save', 'yespo_plugin_settings_nonce' ); ?>

        <input type="submit" class="button button-primary" value="<?php echo __( 'Authorize', 'yespo' ); ?>" />
    </form>

</div>