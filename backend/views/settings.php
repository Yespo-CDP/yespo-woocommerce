
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
            <span class="number">4</span>
            <h2><?php echo __('Product Feed Configuration',Y_TEXTDOMAIN) ?></h2>
            <img class="imageArrow" src="<?php echo Y_PLUGIN_URL;?>assets/images/arrow.svg" width="16" height="32">
        </div>
        <div class="sectionBody">
            <div class="formBlock">
                <div class="field-group">
                    <button id="importFeed" class="button button-primary"><?php echo __('Search Feed',Y_TEXTDOMAIN) ?></button>
                </div>
                <div class="field-group importFeedUrls" id="importFeedUrls">
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

    //get feeds urls
    class importFeedUrls {
        constructor(){
            this.importFeedButton = document.querySelector('#importFeed');
            this.tableArea = document.querySelector('#importFeedUrls');
            this.action = 'get_feed_urls';
            this.ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            this.listenImportClick();
        }

        listenImportClick(){
            this.importFeedButton.addEventListener('click', ()=> {
                this.getRequest();
                this.importFeedButton.disabled=true;
            });
        }

        createTable(urls){
            this.tableArea.innerHTML = '';
            const tableBody = document.createElement('table-body');

            urls.forEach(url => {
                const row = document.createElement('tr');
                //row.style.paddingTop = '20px';
                //row.style.paddingBottom = '20px';

                const urlColumn = document.createElement('td');
                urlColumn.textContent = url;

                const buttonColumn = document.createElement('td');

                const downloadButton = document.createElement('button');
                downloadButton.textContent = 'Download';

                downloadButton.classList.add('button', 'button-primary');

                downloadButton.dataset.url = url;

                downloadButton.addEventListener('click', function() {
                    const url = this.dataset.url;
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', '');
                    link.click();
                });
                buttonColumn.appendChild(downloadButton);

                row.appendChild(urlColumn);
                row.appendChild(buttonColumn);

                tableBody.appendChild(row);
            });
            this.tableArea.appendChild(tableBody);
            this.importFeedButton.remove();
        }

        getRequest() {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', this.ajaxUrl + '?action=' + this.action, true);
            xhr.onreadystatechange = () => {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    let response = xhr.responseText;
                    if (response) {
                        let data = JSON.parse( response.replace(/^[^\[]+/, '').replace(/[^\]]+$/, '') );
                        if(data.length > 0){
                            this.createTable(data);
                        }
                    }
                }
            };
            xhr.send();
        }
    }

    class UsersOrdersExportEsputnik {

        constructor() {
            this.progressBarUsers = document.querySelector('#exportProgressBar');
            this.progressBarOrders = document.querySelector('#exportOrdersProgressBar');
            this.exportUsersButton = document.querySelector('#export_users');
            this.exportOrdersButton = document.querySelector('#export_orders');
            this.users = null;
            this.usersExportStatus = false;
            this.total_users = null;
            this.orders = null;
            this.total_orders = null;
            this.exported = null;
            this.eventSource = null;
            this.ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            this.appendTotalNumber();
        }

        /** get data when loading the page **/
        appendTotalNumber() {
            this.getRequest('get_users_total', this.exportUsersButton, '#total-users', (response) => {
                this.users = response;
                if (parseInt(this.users) > 0){
                    if (this.users !== null && document.querySelector('#total-users') && this.users > 0) document.querySelector('#total-users').innerHTML = this.users;
                }
            }).then(() => {
                this.getRequest('get_users_total_export', this.exportUsersButton, '#total-users-export', (response) => {
                    this.users = response;
                    if (parseInt(this.users) > 0) {
                        this.exportUsersButton.disabled = false;
                        if (this.users !== null && document.querySelector('#total-users-export') && this.users > 0) document.querySelector('#total-users-export').innerHTML = this.users;
                        document.querySelector('#exportContactTotal').innerHTML = this.users;
                    } else {
                        document.querySelector('#progressContainerUsers').innerHTML = '<span class="notFound">No new contacts found</span>';
                    }
                });
            }).then(() => {
                this.getRequest('get_orders_total', this.exportOrdersButton, '#total-orders-export', (response) => {
                    this.orders = response;
                    if (parseInt(this.orders) > 0) {
                        if (this.orders !== null && document.querySelector('#total-orders') && this.orders > 0) document.querySelector('#total-orders').innerHTML = this.orders;
                    }
                });
            }).then(() => {
                this.getRequest('get_orders_total_export', this.exportOrdersButton, '#total-orders-export', (response) => {
                    this.orders = response;
                    if (parseInt(this.orders) > 0) {
                        this.exportOrdersButton.disabled = false;
                        if (this.orders !== null && document.querySelector('#total-orders-export') && this.orders > 0) document.querySelector('#total-orders-export').innerHTML = this.orders;
                        document.querySelector('#exportOrdersTotal').innerHTML = this.orders;
                    } else {
                        document.querySelector('#progressContainerOrders').innerHTML = '<span class="notFound">No new orders found</span>';
                    }
                });
            });
        }

        getRequest(action, button, target, callback) {
            return new Promise((resolve, reject) => {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', this.ajaxUrl + '?action=' + action, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        let response = xhr.responseText;
                        if (response) {
                            callback(response);
                        }
                        resolve();
                    }
                };
                xhr.send();
            });
        }

        /** start export **/
        startExportUsers() {
            this.startExport(
                'export_user_data_to_esputnik',
                'users'
            );
        }

        startExportOrders() {
            this.startExport(
                'export_order_data_to_esputnik',
                'orders'
            );
        }

        startExport(action, service){
            this.startExportChunk(action, service);
        }

        startExportChunk(action, service) {
            const formData = new FormData();
            formData.append('service', service);
            formData.append('action', action);

            fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (response) {
                        if(service === 'users'){
                            this.exportUsersButton.disabled = true;
                            this.processExportUsers();
                        }
                        if(service === 'orders'){
                            this.exportOrdersButton.disabled = true;
                            this.processExportOrders();
                        }
                    } else console.error('Send error:', response.statusText);
                })
                .catch(error => {
                    console.error('Send error:', error);
                });
        }

        /** check and update process **/

        processExportUsers() {
            this.checkExportStatus(
                'get_process_export_users_data_to_esputnik',
                'users',
                this.exportUsersButton,
                '#total-users-export',
                'export_user_data_to_esputnik',
                '#progressContainerUsers',
                '#exported-users'
            );
        }

        processExportOrders() {
            this.checkExportStatus(
                'get_process_export_orders_data_to_esputnik',
                'orders',
                this.exportOrdersButton,
                '#total-orders-export',
                'export_orders_data_to_esputnik',
                '#progressContainerOrders',
                '#exported-orders'
            );
        }

        checkExportStatus(action, way, button, totalUnits, totalExport, progressUnits, exportedUnits) {
            this.getProcessData(action, (response) => {
                response = JSON.parse(response);
                if (response && parseInt(response.display) !== 1){
                    if (response.exported !== null && document.querySelector(exportedUnits) && response.exported >= 0) {
                        this.updateProgress((response.exported / response.total) * 100, way);
                        document.querySelector(exportedUnits).innerHTML = response.exported;
                    }
                    if (response.exported !== response.total && response.status === 'active') {
                        button.disabled = true;
                        setTimeout(() => {
                            if(way === 'users') this.processExportUsers();
                            if (way === 'orders') this.processExportOrders();
                        }, 5000);
                    } else if(response.display === null){
                        console.log('inside response null');
                        if(way === 'users') this.userFinalExportChunk();
                        if(way === 'orders') this.orderFinalExportChunk();
                    } else {
                        this.usersExportStatus = false;
                    }
                }
            });
        }

        getProcessData(action, callback){
            return new Promise((resolve, reject) => {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', this.ajaxUrl + '?action=' + action, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        let response = xhr.responseText;
                        if (response) {
                            callback(response);
                        }
                        resolve();
                    }
                };
                xhr.send();
            });
        }

        updateProgress(progress, way) {
            if(way === 'users') this.progressBarUsers.style.width = `${progress}%`;
            else if(way === 'orders') this.progressBarOrders.style.width = `${progress}%`;
            //this.progressBar.innerHTML = `${Math.round(progress)}%`;

            if (progress >= 100) {
                if (this.eventSource) {
                    this.eventSource.close();
                }
                if(way === 'users') this.exportUsersButton.disabled=true;
                else if(way === 'orders') this.exportOrdersButton.disabled=true;
            }
        }

        userFinalExportChunk(){
            this.finalExportChunk('final_export_users_data_to_esputnik', 'users');
        }

        orderFinalExportChunk(){
            this.finalExportChunk('final_export_orders_data_to_esputnik', 'orders');
        }

        finalExportChunk(action, service) {
            const formData = new FormData();
            formData.append('service', service);
            formData.append('action', action);

            fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            });
        }
    }

    const usersOrdersExportEsputnik = new UsersOrdersExportEsputnik();
    document.querySelector('#export_users').addEventListener('click', function() {
        usersOrdersExportEsputnik.startExportUsers();
        this.disabled = true;
    });

    document.querySelector('#export_orders').addEventListener('click', function() {
        usersOrdersExportEsputnik.startExportOrders();
        this.disabled = true;
    });

    usersOrdersExportEsputnik.processExportUsers();
    usersOrdersExportEsputnik.processExportOrders();

    new importFeedUrls();


</script>