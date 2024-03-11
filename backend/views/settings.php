
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
    <form id="check-authorization" method="post" action="">
        <div class="field-group">
            <label for="api_key"><?php echo __('Api Key (Password)',Y_TEXTDOMAIN) ?></label>
            <input type="text" id="api_key" name="yespo_api_key" placeholder="api-key" value="<?php echo isset($yespo_api_key) ? $yespo_api_key : ''; ?>" />
        </div>

        <?php wp_nonce_field( 'yespo_plugin_settings_save', 'yespo_plugin_settings_nonce' ); ?>

        <input type="submit" id="send-auth-data" class="button button-primary" value="<?php echo __( 'Authorize', Y_TEXTDOMAIN ); ?>" />
    </form>

    <?php

    ?>
    <h2><?php echo __('Export contacts',Y_TEXTDOMAIN) ?></h2>
    <div class="progress-container">
        <div class="progress-bar" id="exportProgressBar"></div>
    </div>
    <div class="export-status">Exporting <span id="exported-users">0</span> contact from <span id="total-users-export">0</span> items</div>
    <button id="export_users" class="button button-primary" disabled><?php echo __('Export',Y_TEXTDOMAIN) ?></button>

</div>
<style>
    .progress-container {
        width: 100%;
        background-color: #f0f0f0;
    }

    .progress-bar {
        width: 0%;
        height: 30px;
        background-color: #4caf50;
        transition: width 0.3s ease-in-out;
    }
</style>
<script>

    document.addEventListener('DOMContentLoaded', function() {

        if(document.querySelector('.wrap.yespo-settings-page #check-authorization')) {
            var form = document.querySelector('.wrap.yespo-settings-page #check-authorization');

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

    class UsersExportEsputnik {

        constructor(progressBarId) {
            this.progressBar = document.getElementById(progressBarId);
            this.exportButton = document.querySelector('#export_users');
            this.users = null;
            this.total_users = null;
            this.exported = null;
            this.eventSource = null;
            this.ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            this.appendTotalUsersNumber();
        }

        appendTotalUsersNumber(){
            this.getUsersXhrSetup().then(() => {
                if (this.users !== null && document.querySelector('#total-users-export') && this.users > 0) document.querySelector('#total-users-export').innerHTML = this.users;
            });
        }

        getUsersXhrSetup() {
            return new Promise((resolve, reject) => {
                let self = this;
                this.xhr = new XMLHttpRequest();
                this.xhr.open('GET', this.ajaxUrl + '?action=get_users_total', true);
                this.xhr.onreadystatechange = function() {
                    if (self.xhr.readyState === 4 && self.xhr.status === 200) {
                        self.users = self.xhr.responseText;
                        if(parseInt(self.users) > 0) self.exportButton.disabled=false;
                        resolve();
                    }
                };
                this.xhr.send();
            });
        }

        startExport() {
            this.getUsersXhrSetup().then(() => {
                if (this.users > 0) {
                    const batchSize = 1;
                    let currentIndex = 0;
                    this.exportButton.disabled=true;

                    const sendNextBatch = () => {
                        const usersToSend = this.users - currentIndex >= batchSize ?
                            batchSize :
                            this.users - currentIndex;

                        if (usersToSend > 0) {
                            this.exportUsersChunk(currentIndex, usersToSend).then(() => {
                                currentIndex++;
                                this.updateProgress((currentIndex / this.users) * 100);

                                if (currentIndex < this.users) {
                                    sendNextBatch();
                                }
                                if(document.querySelector('#exported-users')) document.querySelector('#exported-users').innerHTML=currentIndex;
                            }).catch(error => {
                                console.error('Error exporting chunk:', error);
                            });
                        }
                    };

                    sendNextBatch();
                }
            }).catch(error => {
                console.error('Error:', error);
            });
        }

        exportUsersChunk(startIndex, count) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.ajaxUrl, true);
                const formData = new FormData();
                formData.append('startIndex', startIndex);
                formData.append('count', count);
                formData.append('action', 'export_user_data_to_esputnik');

                xhr.onreadystatechange = () => {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (typeof response === 'number') {
                                resolve(response);
                            } else {
                                reject(response);
                            }
                        } else {
                            reject(xhr.statusText);
                        }
                    }
                };
                xhr.send(formData);
            });
        }

        updateProgress(progress) {
            this.progressBar.style.width = `${progress}%`;
            this.progressBar.innerHTML = `${Math.round(progress)}%`;

            if (progress >= 100) {
                if (this.eventSource) {
                    this.eventSource.close();
                }
                this.exportButton.disabled=false;
            }
        }
    }

    const usersExportEsputnik = new UsersExportEsputnik('exportProgressBar');
    document.querySelector('#export_users').addEventListener('click', function() {
        usersExportEsputnik.startExport();
        this.disabled = true;
    });
</script>