
<div class="wrap yespo-settings-page">
    <div id="yespo-notices"></div>
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <?php
    if ( get_option( 'yespo_options' ) !== false ){
        $options = get_option('yespo_options', array());
        if(isset($options['yespo_username'])) $yespo_username = $options['yespo_username'];
        if(isset($options['yespo_api_key'])) $yespo_api_key = $options['yespo_api_key'];
    }
    ?>
    <form method="post" action="">
        <div class="field-group">
            <label for="username"><?php echo __('Username',Y_TEXTDOMAIN) ?></label>
            <input type="text" id="username" name="yespo_username" placeholder="username" value="<?php echo isset($yespo_username) ? $yespo_username : ''; ?>" />
        </div>
        <div class="field-group">
            <label for="api_key"><?php echo __('Api Key (Password)',Y_TEXTDOMAIN) ?></label>
            <input type="text" id="api_key" name="yespo_api_key" placeholder="api-key" value="<?php echo isset($yespo_api_key) ? $yespo_api_key : ''; ?>" />
        </div>

        <?php wp_nonce_field( 'yespo_plugin_settings_save', 'yespo_plugin_settings_nonce' ); ?>

        <input type="submit" id="send-auth-data" class="button button-primary" value="<?php echo __( 'Authorize', Y_TEXTDOMAIN ); ?>" />
    </form>

</div>
<script>

    document.addEventListener('DOMContentLoaded', function() {

        if(document.querySelector('.wrap.yespo-settings-page form')) {
            var form = document.querySelector('.wrap.yespo-settings-page form');

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (document.getElementById('send-auth-data')) document.getElementById('send-auth-data').disabled = true;
                var xhr = new XMLHttpRequest();
                var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
                xhr.open('POST', ajaxUrl, true);
                var formData = new FormData(form);
                formData.append('action', 'check_api_key_esputnik');
                xhr.send(formData);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                document.getElementById('yespo-notices').innerHTML = response.message;
                                //console.log('Статус ответа:', response.status);
                                //console.log('Сообщение:', response.message);
                            } catch (error) {
                                console.error('Ошибка при парсинге JSON:', error);
                            }
                        } else {
                            console.error('Произошла ошибка при отправке данных на сервер');
                        }
                        if (document.getElementById('send-auth-data')) document.getElementById('send-auth-data').disabled = false;
                    }
                };
            });
        }
    });
</script>