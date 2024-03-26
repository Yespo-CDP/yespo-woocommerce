
<div class="wrap yespo-settings-page">
    <div id="yespo-notices"></div>
    <div class="esputnikLogo">
        <img src="<?php echo Y_PLUGIN_URL;?>assets/images/esputnik-logo.svg" width="176" height="41" alt="<?php echo Y_NAME;?>" title="<?php echo Y_NAME;?>">
    </div>
    <section class="settingsSection">
        <div class="sectionHeader">
            <span class="number">1</span>
            <h2><?php echo __('Authorization',Y_TEXTDOMAIN) ?></h2>
        </div>
        <?php
        if ( get_option( 'yespo_options' ) !== false ){
            $options = get_option('yespo_options', array());
            if(isset($options['yespo_username'])) $yespo_username = $options['yespo_username'];
            if(isset($options['yespo_api_key'])) $yespo_api_key = $options['yespo_api_key'];
        }
        ?>
        <div class="sectionBody">
            <div class="formBlock">
                <form id="check-authorization" method="post" action="">
                    <div class="field-group">
                        <input type="text" id="api_key" name="yespo_api_key" placeholder="API Key" value="<?php echo isset($yespo_api_key) ? $yespo_api_key : ''; ?>" />
                    </div>
                    <div class="field-group">
                        <span class="api-key-text"><?php echo __( 'Данный ключ вы можете получить по ссылке', Y_TEXTDOMAIN ); ?><a href="https://my.yespo.io/settings-ui/#/api-keys-list">https://my.yespo.io/settings-ui/#/api-keys-list</a></span>
                    </div>

                    <?php wp_nonce_field( 'yespo_plugin_settings_save', 'yespo_plugin_settings_nonce' ); ?>

                    <div class="field-group">
                        <input type="submit" id="send-auth-data" class="button button-primary" value="<?php echo __( 'Authorize', Y_TEXTDOMAIN ); ?>" />
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="settingsSection">
        <div class="sectionHeader">
            <span class="number">2</span>
            <h2><?php echo __('Export Contacts',Y_TEXTDOMAIN) ?></h2>
            <img class="imageArrow" src="<?php echo Y_PLUGIN_URL;?>assets/images/arrow.svg" width="16" height="32">
        </div>
        <div class="sectionBody">
            <div class="formBlock">
                <div class="field-group">
                    <span class="exportText"><?php echo __('Для отправки информации о заказах клиентов нажмите кнопку «Экспорт информации о заказах»',Y_TEXTDOMAIN) ?></span>
                </div>
                <div class="field-group">
                    <div class="progress-container" id="progressContainerUsers">
                        <div class="progress-bar" id="exportProgressBar">
                        </div>
                    </div>
                </div>
                <div class="field-group exportContactData">
                    <div class="dataElement"><?php echo __('Contacts',Y_TEXTDOMAIN) ?> <span id="total-users">0</span></div>
                    <div class="dataElement"><?php echo __('Export contacts',Y_TEXTDOMAIN) ?> <span id="total-users-export">0</span></div>
                    <div class="dataElement"><?php echo __('Finish contacts',Y_TEXTDOMAIN) ?> <span id="exported-users">0</span></div>
                </div>
                <div class="field-group">
                    <button id="export_users" class="button button-primary" disabled><?php echo __('Export information',Y_TEXTDOMAIN) ?></button>
                </div>
            </div>
        </div>
    </section>

    <section class="settingsSection">
        <div class="sectionHeader">
            <span class="number">3</span>
            <h2><?php echo __('Export Orders',Y_TEXTDOMAIN) ?></h2>
            <img class="imageArrow" src="<?php echo Y_PLUGIN_URL;?>assets/images/arrow.svg" width="16" height="32">
        </div>
        <div class="sectionBody">
            <div class="formBlock">
                <div class="field-group">
                    <span class="exportText"><?php echo __('Для отправки информации о заказах клиентов нажмите кнопку «Экспорт информации о заказах»',Y_TEXTDOMAIN) ?></span>
                </div>
                <div class="field-group">
                    <div class="progress-container" id="progressContainerOrders">
                        <div class="progress-bar" id="exportOrdersProgressBar">
                        </div>
                    </div>
                </div>
                <div class="field-group exportContactData">
                    <div class="dataElement"><?php echo __('Orders',Y_TEXTDOMAIN) ?> <span id="total-orders">0</span></div>
                    <div class="dataElement"><?php echo __('Export orders',Y_TEXTDOMAIN) ?> <span id="total-orders-export">0</span></div>
                    <div class="dataElement"><?php echo __('Finish orders',Y_TEXTDOMAIN) ?> <span id="exported-orders">0</span></div>
                </div>
                <div class="field-group">
                    <button id="export_orders" class="button button-primary" disabled><?php echo __('Export information',Y_TEXTDOMAIN) ?></button>
                </div>
            </div>
        </div>
    </section>

    <section class="settingsSection">
        <div class="sectionHeader">
            <span class="number number-total"><img src="<?php echo Y_PLUGIN_URL;?>assets/images/check.svg" width="7" height="7" alt="v" title="v"></span>
            <h2><?php echo __('Success page',Y_TEXTDOMAIN) ?></h2>
            <img class="imageArrow" src="<?php echo Y_PLUGIN_URL;?>assets/images/arrow.svg" width="16" height="32">
        </div>

        <div class="sectionBody">
            <div class="formBlock">
                <div class="field-group totalExportedRow">
                    <div class="leftSide"><?php echo __('Success page',Y_TEXTDOMAIN) ?></div><div class="rightSide" id="authorizationTotal"></div>
                </div>
                <div class="field-group totalExportedRow">
                    <div class="leftSide"><?php echo __('Export contacts',Y_TEXTDOMAIN) ?></div><div class="rightSide" id="exportContactTotal">0</div>
                </div>
                <div class="field-group totalExportedRow">
                    <div class="leftSide"><?php echo __('Export orders',Y_TEXTDOMAIN) ?></div><div class="rightSide" id="exportOrdersTotal">0</div>
                </div>
            </div>
        </div>
    </section>

</div>
<style>
    .yespo-settings-page{
        background-color: #f0f0f1;
        font-family: Inter;
    }
    .yespo-settings-page .esputnikLogo{
        text-align: center;
    }
    .yespo-settings-page .esputnikLogo img{
        margin: 40px 0;
    }
    .yespo-settings-page .settingsSection .sectionHeader{
        display:inline-flex;
        align-items: center;
    }
    .yespo-settings-page .settingsSection .sectionHeader .number{
        color:#f0f0f1;
        background-color: #2b64cd;
        width: 17px;
        height: 17px;
        border-radius: 50%;
        text-align: center;
        margin-right: 5px;
        font-weight: 500;
        font-size: 14px;
        line-height: 17px;
    }
    .yespo-settings-page .settingsSection .sectionHeader h2{
        font-size: 22px;
        font-weight: 500;
        line-height: 27px;
    }
    .yespo-settings-page .settingsSection .sectionBody{
        padding: 12px 20px;
        background-color: #fff;
        overflow: auto;
    }

    .yespo-settings-page .settingsSection .sectionBody .formBlock{
        max-width: 365px;
    }

    .yespo-settings-page .settingsSection .sectionBody .formBlock input,
    .yespo-settings-page .settingsSection .sectionBody .formBlock #export_users,
    .yespo-settings-page .settingsSection .sectionBody .formBlock #export_orders{
        width:100%;
        height: 43px;
        border-radius: 4px;
        border: 1px solid #e3e3e3;
    }
    .yespo-settings-page .settingsSection .sectionBody .formBlock input::placeholder{
        font-size: 12px;
        font-weight: 400;
        line-height: 15px;
        color: #000;
        opacity: 0.5;
    }

    .yespo-settings-page .settingsSection .sectionBody .formBlock #send-auth-data,
    .yespo-settings-page .settingsSection .sectionBody .formBlock #export_users,
    .yespo-settings-page .settingsSection .sectionBody .formBlock #export_orders{
        font-weight: 500;
        line-height: 15px;
    }

    .yespo-settings-page .settingsSection .sectionBody .formBlock .field-group{
        margin:8px 0;
    }
    .yespo-settings-page .settingsSection .sectionBody .formBlock .field-group .api-key-text{
        font-size: 8px;
        line-height: 10px;
    }

    /* progress container */
    .yespo-settings-page .settingsSection .imageArrow{
        position: absolute;
        margin: 0 50%;
    }
    .yespo-settings-page .settingsSection .sectionBody .formBlock .field-group .exportText{
        font-size: 12px;
        font-weight: 400;
        line-height: 15px;
    }
    .yespo-settings-page .settingsSection .progress-container {
        width: 100%;
        background: url("<?php echo Y_PLUGIN_URL;?>assets/images/progress-container.svg");
    }
    .yespo-settings-page .settingsSection .progress-bar {
        width: 0%;
        height: 12px;
        /*background-color: #2b64cd;*/
        background: url("<?php echo Y_PLUGIN_URL;?>assets/images/progress-container-hover.svg");
        transition: width 0.3s ease-in-out;
    }

    .yespo-settings-page .settingsSection:hover .progress-container::before {
        opacity: 0.5;
    }

    .yespo-settings-page .settingsSection .exportContactData{
        display: flex;
        justify-content: space-between;
    }
    .yespo-settings-page .settingsSection .exportContactData .dataElement{
        font-size: 12px;
        font-weight: 400;
        line-height: 15px;
    }
    .yespo-settings-page .settingsSection .exportContactData span{
        font-weight: 700;
        color: #2b64cd;
    }
    .yespo-settings-page .settingsSection .notFound,
    .yespo-settings-page .settingsSection .notAvailable{
        font-size: 10px;
        font-weight: 500;
        line-height: 12px;
    }
    .yespo-settings-page .settingsSection .notFound{
        color: #DF260D;
    }
    .yespo-settings-page .settingsSection .notAvailable{
        color: #fff;
    }
    .yespo-settings-page .settingsSection #progressContainerUsers{
        text-align: center;
    }


    .yespo-settings-page .settingsSection .sectionBody .formBlock .totalExportedRow{
        display: flex;
    }
    .yespo-settings-page .settingsSection .sectionBody .formBlock .totalExportedRow .leftSide{
        width: 40%;
        font-size: 18px;
        font-weight: 400;
        line-height: 22px;
    }
    .yespo-settings-page .settingsSection .sectionBody .formBlock .totalExportedRow .rightSide{
        width: 60%;
        font-size: 18px;
        font-weight: 500;
        line-height: 22px;
        color: #2b64cd;
    }
    .yespo-settings-page .settingsSection .sectionHeader .number-total{
        font-size: inherit !important;
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
                            var response = JSON.parse(xhr.responseText);
                            try {
                                document.getElementById('yespo-notices').innerHTML = response.message;
                            } catch (error) {
                                console.error('Ошибка при парсинге JSON:', error);
                            }
                            document.querySelector('#authorizationTotal').innerHTML = response.total;
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
                        if(parseInt(self.users) > 0) {
                            self.exportButton.disabled=false;
                            document.querySelector('#exportContactTotal').innerHTML=self.users;
                        }
                        else document.querySelector('#progressContainerUsers').innerHTML='<span class="notFound">No contacts found</span>';
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
                                //console.error('Error exporting chunk:', error);
                                document.querySelector('#progressContainerUsers').style.background = `url("<?php echo Y_PLUGIN_URL;?>assets/images/progress-container-warning.svg")`;
                                this.progressBar.style.width = '100%';
                                document.querySelector('#progressContainerUsers').innerHTML='<span class="notAvailable">Service not available now. Try again later</span>';
                                this.exportButton.disabled=false;
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
                            if (typeof response === 'number' && response > 0) {
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
            //this.progressBar.innerHTML = `${Math.round(progress)}%`;

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