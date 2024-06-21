<?php
if ( get_option( 'yespo_options' ) !== false ){
    $options = get_option('yespo_options', array());
    if(isset($options['yespo_username'])) $yespo_username = $options['yespo_username'];
    if(isset($options['yespo_api_key'])) $yespo_api_key = $options['yespo_api_key'];
}
?>
<div class="yespo-settings-page">
    <!--div id="yespo-notices"></div-->
    <section class="topPanel">
        <div class="contentPart panelBox">
            <img src="<?php echo Y_PLUGIN_URL;?>assets/images/yespologosmall.svg" width="33" height="33" alt="<?php echo Y_NAME;?>" title="<?php echo Y_NAME;?>">
            <div class="panelUser">
                <?php
                if(isset($yespo_username)) echo $yespo_username;
                ?>
            </div>
        </div>
    </section>
    <section class="userPanel">
        <div class="contentPart">
            <h1><?php echo __('Data synchronization',Y_TEXTDOMAIN) ?></h1>
            <p><?php echo __('Synchronize contacts and orders for subsequent analysis and efficient data utilization using Yespo marketing automation tools',Y_TEXTDOMAIN) ?></p>
            <div class="settingsSection">
            </div>
        </div>
    </section>
</div>
<style>
    #wpcontent{
        padding-left:0px;
    }
    .yespo-settings-page{
        background-color: #f0f0f1;
    }
    .yespo-settings-page .topPanel{
        width:100%;
        background-color: #fff;
        height: 52px;
        text-align:center;
    }
    .yespo-settings-page .contentPart{
        max-width: 990px;
    }
    .yespo-settings-page .topPanel .panelBox{
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
    }
    .yespo-settings-page .topPanel .panelBox img{
        /*margin-left: -16px;*/
    }

    .yespo-settings-page .userPanel .contentPart{
        margin:0 auto;
    }



    .yespo-settings-page .userPanel{
        margin: 20px 0;
    }
    .yespo-settings-page .settingsSection .sectionBody{
        padding: 12px 20px;
        background-color: #fff;
        overflow: auto;
    }
    .yespo-settings-page h1{
        font-weight: 700;
        font-size: 21px;
        line-height: 24.61px;
    }
    .yespo-settings-page p{
        color: #5f5f5f;
    }

    .yespo-settings-page .settingsSection .sectionBody .field-group{
        margin:15px 0;
    }

    .yespo-settings-page .settingsSection .sectionBody .formBlock{
        width:100%;
    }
    .yespo-settings-page .userPanel h4,
    .yespo-settings-page .userPanel .api-key-text{
        /*opacity:0.5;*/
        color: #9b9b9b;
    }
    .yespo-settings-page .userPanel h4 {
        font-size: 13px;
        line-height: 15.23px;
        font-weight: 400;
    }
    .yespo-settings-page #checkYespoAuthorization h4 {
        font-weight: 700;
        color:#525252;
    }

    .yespo-settings-page .settingsSection .sectionBody .formBlock #api_key,
    .yespo-settings-page .settingsSection .sectionBody .formBlock #api_key:focus {
        max-width: 600px;
        width:100%;
        height: 21px;
        box-shadow: none !important;
        border-top: 1px solid #fff !important;
        border-left: 1px solid #fff !important;
        border-right: 1px solid #fff !important;
        border-bottom: 1px solid #e3e3e3 !important;
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
    .yespo-settings-page .settingsSection .sectionBody .formBlock .field-group .informationText{
        font-size: 11px;
        line-height: 11px;
    }
    .yespo-settings-page .settingsSection .sectionBody .formBlock #sendYespoAuthData{
        min-width:117px;
        height: 34px;
        font-weight: 400;
    }
    .yespo-settings-page .settingsSection .sectionBody .formBlock #sendYespoAuthData{
        max-width: 200px;
        min-width:117px;
        height: 34px;
        font-weight: 400;
        font-size: 16px;
        line-height: 18px;
    }
    .yespo-settings-page .settingsSection .sectionBody .formBlock #api_key{
        padding:0px;
    }

/*
    .yespo-settings-page .settingsSection .sectionBody .formBlock .inputApiLine{
        display:flex;
    }
 */
    .yespo-settings-page .settingsSection .sectionBody .formBlock .errorAPiKey p{
        color: red;
    }
    .yespo-settings-page .settingsSection .processTitles{
        display: flex;
        justify-content: space-between;
    }
    .yespo-settings-page .settingsSection .processTitles{
        display: flex;
        justify-content: space-between;
    }

    .yespo-settings-page .settingsSection .processTitle,
    .yespo-settings-page .settingsSection .processPercent{
        font-size: 16px;
        font-weight: 400;
        line-height: 18px;
        color:#000;
    }


    .yespo-settings-page .settingsSection .progress-container {
        width: 100%;
        height: 17px;
        background-color: #5989EA33;
        border-radius: 10px;
        margin: 10px auto;
    }
    .yespo-settings-page .settingsSection #progressContainerStopped{
        background-color: #e9e9e9;
    }

    .yespo-settings-page .settingsSection .progress-bar {
        /*width:47%;*/
        height: 11px;
        background-color: #5989EA;
        position: relative;
        top: 3px;
        border-radius: 10px;
        margin: auto 3px;
    }

    .yespo-settings-page .settingsSection .flexRow{
        display:flex;
    }
    .yespo-settings-page .settingsSection #exportProgressBarStopped {
        background-color:#9b9b9b;
    }
    .yespo-settings-page .settingsSection .synhronizationStarted{
        color:#5989EA;
        font-size: 18px;
        margin-top: 8px;
        margin-left: 20px;
    }
    .yespo-settings-page .settingsSection #stop-send-data{
        font-size: 16px;
        line-height: 18px;
        color: #000;
        background-color: #fff;
        min-width: 63px;
        max-width: 200px;
        height: 34px;
    }

    /* section resume */
    .yespo-settings-page .settingsSection .messageNonce{
        border-radius: 4px;
        border-left: 3px solid #31b3f4;
        background-color: #e9faff;
        box-shadow: 0px 2px 4px 0px #0000001F;
        display: flex;
        justify-content: space-evenly;
        padding:10px 0;
    }
    .yespo-settings-page .settingsSection .messageText{
        position: relative;
        max-width: 80%;
        font-size: 15px;
        font-weight: 400;
        line-height: 17.58px;
    }

    .yespo-settings-page .settingsSection .messageIcon > img{
        height: 100%;
    }
    .yespo-settings-page .settingsSection .messageIcon > img,
    .yespo-settings-page .settingsSection .messageButton{
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .yespo-settings-page .settingsSection .messageButton {
        position: relative;
        display: inline-block;
    }

    .yespo-settings-page .settingsSection .messageButton button,
    .yespo-settings-page .settingsSection .messageButtonError #resume-send-data{
        min-width: 107px;
        height: 34px;
    }
    .yespo-settings-page .settingsSection .messageButton button img,
    .yespo-settings-page .settingsSection .messageButtonError button img{
        margin-right: 10px;
    }
    .yespo-settings-page .settingsSection .messageButton button span,
    .yespo-settings-page .settingsSection .messageButtonError button span{
        font-size: 16px;
        font-weight: 400;
        line-height: 18px;
    }

    /* section error */
    .yespo-settings-page .settingsSection .percentRed{
        color: #ec514d;
    }
    .yespo-settings-page .settingsSection .messageNonceError{
        border-radius: 4px;
        border-left: 3px solid #ec514d;
        background-color: #fff5f4;
        box-shadow: 0px 2px 4px 0px #0000001F;
        display: flex;
        justify-content: space-evenly;
        padding:10px 0;
    }
    .yespo-settings-page .settingsSection .messageTextError{
        position: relative;
        width: 60%;
        font-size: 15px;
        font-weight: 400;
        line-height: 17.58px;
    }

    .yespo-settings-page .settingsSection .messageIconError{
        min-width: 36px;
        text-align: -webkit-center;
    }
    .yespo-settings-page .settingsSection .messageIconError > img{
        height: 100%;
    }
    .yespo-settings-page .settingsSection .messageIconError > img,
    .yespo-settings-page .settingsSection .messageButtonError{
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .yespo-settings-page .settingsSection .messageButtonError {
        position: relative;
        /*display: inline-block;*/
    }

    .yespo-settings-page .settingsSection .messageButtonError #contact-support {
        /*width: 166px;*/
        max-width:300px;
        height: 34px;
        box-shadow: 0px 2px 4px 0px #0000001F;
        border: 1px solid #e3e3e3;
        align-items: center;
        display: inline-flex;
        margin-right: 7px;
    }

    .yespo-settings-page .settingsSection .messageButtonError #contact-support span{
        font-size: 16px;
        font-weight: 400;
        line-height: 18px;
        color:#000;
        margin-left: 10px;
    }

    /* section success */
    .yespo-settings-page .sectionBodySuccess{
        padding: 0 !important;
        overflow: initial !important;
    }
    .yespo-settings-page .settingsSection .messageNonceSuccess{
        border-radius: 4px;
        border-left: 3px solid #3abc51;
        background-color: #e3ffe7;
        box-shadow: 0px 2px 4px 0px #0000001F;
        display: flex;
        justify-content: start;
        padding:10px 0;
        align-items: center;
    }
    .yespo-settings-page .settingsSection .messageNonceSuccess .messageIconSuccess{
        margin: auto 20px;
    }
    .yespo-settings-page .settingsSection .messageTextSuccess{
        position: relative;
        max-width: 90%;
        font-size: 15px;
    }

</style>

<script>

    class YespoExportData {
        constructor() {
            this.h1 = '<?php echo __('Synchronization progress', Y_TEXTDOMAIN)?>';
            //this.h1 = '<?php echo __('Data synchronization',Y_TEXTDOMAIN) ?>';
            this.outSideText = '<?php echo __('Synchronize contacts and orders for subsequent analysis and efficient data utilization using Yespo marketing automation tools', Y_TEXTDOMAIN)?>';
            this.h4 = '<?php echo __('The first data export will take some time; it will happen in the background, and it is not necessary to stay on the page', Y_TEXTDOMAIN)?>';
            this.resume = '<?php echo __( 'The synchronization process has been paused; you can resume it from the moment of pausing without losing the previous progress', Y_TEXTDOMAIN ); ?>';
            this.error = '<?php echo __('Some error have occurred. Try to resume synchronization. If it doesn’t help, contact Support', Y_TEXTDOMAIN)?>';
            this.error401 = '<?php echo __('Invalid API key. Please delete the plugin and start the configuration from scratch using a valid API key. No data will be lost.', Y_TEXTDOMAIN)?>';
            this.error555 = '<?php echo __('Outgoing activity on the server is blocked. Contact your provider to resolve the issue. Data synchronization can be resumed after this without data loss.', Y_TEXTDOMAIN)?>';
            this.success = '<?php echo __( 'Data is successfully synchronized', Y_TEXTDOMAIN ); ?>';
            this.synhStarted = '<?php echo __( 'Data synchronization has started', Y_TEXTDOMAIN ); ?>';
            //this.tableArea = document.querySelector('#importFeedUrls');
            this.pluginUrl = '<?php echo Y_PLUGIN_URL?>';
            this.pauseButton = '<?php echo __('Pause', Y_TEXTDOMAIN)?>';
            this.resumeButton = '<?php echo __('Resume', Y_TEXTDOMAIN)?>';
            this.contactSupportButton = '<?php echo __('Contact Support', Y_TEXTDOMAIN)?>';
            this.ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

            this.nonceApiKeyForm = '<?php echo wp_nonce_field( 'yespo_plugin_settings_save', 'yespo_plugin_settings_nonce', true, false ); ?>';
            this.apiKeyValue = '<?php echo isset($yespo_api_key) ? $yespo_api_key : ''; ?>';
            this.apiKeyText = '<?php echo __( 'The API key to connect account can be received by the', Y_TEXTDOMAIN ); ?> ';
            this.yespoLink = 'https://my.yespo.io/settings-ui/#/api-keys-list';
            this.yespoLinkText = '<?php echo __( 'link', Y_TEXTDOMAIN ); ?>';
            this.wpNonce = '<?php wp_nonce_field( 'yespo_plugin_settings_save', 'yespo_plugin_settings_nonce' ); ?>';

            this.eventSource = null;
            this.percentTransfered = 0;

            this.users = null;
            this.usersExportStatus = false;
            this.orders = null;

            //this.eventListeners();
            //if(document.querySelector('#checkYespoAuthorization')) this.checkSynchronization(document.querySelector('#checkYespoAuthorization'));

            this.getAccountYespoName();
            this.checkAuthorization()

            //this.showApiKeyForm();

        }

        // get top account name
        getAccountYespoName(){
            this.getRequest('get_account_yespo_name',  (response) => {
                response = JSON.parse(response);
                if(document.querySelector('.panelUser') && response.username !== undefined) document.querySelector('.panelUser').innerHTML=response.username;
                //console.log(response.username);
            });
        }

        /**
        * check authomatic authorization
        **/
        checkAuthorization(){
            this.getRequest('check_api_authorization_yespo',  (response) => {
                response = JSON.parse(response);
                if(response.auth && response.auth === 'success'){
                    this.getNumberDataExport();
                } else if(response.auth && response.auth === 'incorrect') {
                    let code = 401;
                    if(parseInt(response.code) === 0) code = 555;
                    this.showErrorPage('', code);
                } else {
                    this.showApiKeyForm();
                    this.startExportEventListener();
                }
            });
        }
        /*
        * Methods create html elements
        * */
        createElement(tag, options = {}, ...children) {
            const element = document.createElement(tag);
            for (const [key, value] of Object.entries(options)) {
                if (key.startsWith("data-")) {
                    element.setAttribute(key, value);
                } else {
                    element[key] = value;
                }
            }
            children.forEach(child => {
                if (typeof child === "string") {
                    element.appendChild(document.createTextNode(child));
                } else if (child instanceof Node) {
                    element.appendChild(child);
                }
            });
            return element;
        }

        createHeading(level, text, options = {}) {
            return this.createElement(`h${level}`, options, text);
        }

        createParagraph(text, options = {}) {
            return this.createElement("p", options, text);
        }

        createFieldGroup(additionClass = '') {
            const classes = ["field-group"];
            if (additionClass) {
                classes.push(additionClass);
            }
            return this.createElement("div", { className: classes.join(' ') });
        }

        createInputField() {
            return this.createElement("input", { type: 'text', name: "yespo_api_key", id: "api_key", value: this.apiKeyValue });
        }

        createButton(id, className, text, iconSrc, iconClass) {
            const icon = this.createElement("img", { src: iconSrc, alt: `${id}-icon`, className: iconClass });
            const span = this.createElement("span", {}, text);
            return this.createElement("button", { type: "submit", id, className }, icon, span);
        }

        createForm(id, method, action) {
            return this.createElement("form", { id, method, action });
        }

        createProcessTitles(progressText, percentText, percentClass) {
            const processTitle = this.createElement("div", { className: "processTitle" }, progressText);
            const processPercent = this.createElement("div", { className: `processPercent ${percentClass}` }, percentText);
            return this.createElement("div", { className: "processTitles" }, processTitle, processPercent);
        }

        createProgressBar(containerId, barId, progressPercent) {
            //const progressBar = this.createElement("div", { className: "progress-bar", id: barId });
            const progressBar = this.createElement("div", {
                className: "progress-bar",
                id: barId,
                style: `width: ${progressPercent};`
            });
            return this.createElement("div", { className: "progress-container", id: containerId }, progressBar);
        }

        createMessageNonceError(nonce, icon, mesText, mesButton,imgSrc, text, resumeIconSrc, resumeText, contactIconSrc = null, contactText = null) {
            const img = this.createElement("img", { src: imgSrc, width: 24, height: 24, alt: "", title: "" });
            const messageIcon = this.createElement("div", { className: icon }, img);
            const messageText = this.createElement("div", { className: mesText }, text);

            const resumeButton = this.createButton("resume-send-data", "button button-primary", resumeText, resumeIconSrc, "button-icon");

            let contactSupport = '';
            if(contactIconSrc && contactText) {
                contactSupport = this.createElement("a", { id: "contact-support", className: "button btn-light", href: "https://yespo.io/support", target: "_blank" },
                    this.createElement("img", { src: contactIconSrc, alt: "contact-icon", className: "contact-icon" }),
                    this.createElement("span", {}, contactText)
                );
            }

            let messageButton = this.createElement("div", { className: mesButton }, contactSupport);
            if(resumeIconSrc && resumeText) messageButton = this.createElement("div", { className: mesButton }, contactSupport, resumeButton)

            return this.createElement("div", { className: nonce }, messageIcon, messageText, messageButton);
        }

        appendUserPanel(
            sectionClass,
            progressContainer,
            exportProgressBar,
            progressText,
            progressPercent,
            percentClass,
            stopped,
            code = null
        ){
            const settingsSection = '.settingsSection';
            const sectionBody = this.createElement('div', { className: 'sectionBody sectionBodyAuth' });
            const formBlock = this.createElement('div', { className: 'formBlock' });

            let formId = 'stopExportData';
            if (stopped === 'stopped') formId = 'resumeExportData';

            const form = this.createForm(formId, 'post', '');
            const h4 = this.createHeading(4, this.h4);

            const fieldGroup1 = this.createFieldGroup();
            const processTitles = this.createProcessTitles(progressText, progressPercent, percentClass);
            const progressBar = this.createProgressBar(progressContainer, exportProgressBar, progressPercent);
            //progressBar.appendChild(mesSynhStarted);

            fieldGroup1.appendChild(processTitles);
            fieldGroup1.appendChild(progressBar);

            const fieldGroup2 = this.createFieldGroup();
            let sectionContent = '';

            if (stopped === 'stopped') {
                sectionContent = this.createMessageNonceError(
                    'messageNonce',
                    'messageIcon',
                    'messageText',
                    'messageButton',
                    this.pluginUrl + 'assets/images/esicon.svg',
                    this.resume,
                    this.pluginUrl + 'assets/images/union.svg',
                    this.resumeButton

                );
            } else if (stopped === 'error') {

                let messageText = this.error;
                let resumeIcon = this.pluginUrl + 'assets/images/union.svg';
                let resumeButton = this.resumeButton;
                if(code === 401 || code === 555) {
                    if(code === 401) messageText = this.error401;
                    else messageText = this.error555;
                    resumeIcon = '';
                    resumeButton = '';
                }

                sectionContent = this.createMessageNonceError(
                    'messageNonceError',
                    'messageIconError',
                    'messageTextError',
                    'messageButtonError',
                    this.pluginUrl + 'assets/images/erroricon.svg',
                    messageText,
                    resumeIcon,
                    resumeButton,
                    this.pluginUrl + 'assets/images/subtract.svg',
                    this.contactSupportButton
                );
            } else {
                fieldGroup2.classList.add('flexRow');
                sectionContent = this.createElement('input', { type: 'submit', id: 'stop-send-data', className: 'button btn-light', value: this.pauseButton });
            }
            const mesSynhStarted = this.createElement("div", { className: 'synhronizationStarted' });
            fieldGroup2.appendChild(sectionContent);
            fieldGroup2.appendChild(mesSynhStarted);


            form.append(h4, fieldGroup1, fieldGroup2);
            formBlock.appendChild(form);
            sectionBody.appendChild(formBlock);
            //userPanel.appendChild(contentPart);

            const mainContainer = document.querySelector(settingsSection);
            if (mainContainer) {
                mainContainer.innerHTML = '';
                //mainContainer.appendChild(userPanel);
                mainContainer.appendChild(sectionBody);
            } else {
                console.error(`Parent element with class "${settingsSection}" not found.`);
            }
        }

        addSuccessMessage() {
            const sectionBody = this.createElement('div', { className: 'sectionBody sectionBodySuccess' });
            const formBlock = this.createElement('div', { className: 'formBlock' });
            const fieldGroup = this.createElement('div', { className: 'field-group' });
            const messageNonceSuccess = this.createElement('div', { className: 'messageNonceSuccess' });
            const messageIconSuccess = this.createElement('div', { className: 'messageIconSuccess' });

            const img = this.createElement('img', {
                src: this.pluginUrl + 'assets/images/success.svg',
                width: 24,
                height: 24,
                alt: 'success',
                title: 'success'
            });

            messageIconSuccess.appendChild(img);

            const messageTextSuccess = this.createElement('div', { className: 'messageTextSuccess' }, this.success );

            messageNonceSuccess.appendChild(messageIconSuccess);
            messageNonceSuccess.appendChild(messageTextSuccess);

            fieldGroup.appendChild(messageNonceSuccess);
            formBlock.appendChild(fieldGroup);
            sectionBody.appendChild(formBlock);

            const messageContainer = document.querySelector('.settingsSection');
            if (messageContainer) {
                messageContainer.innerHTML = '';
                messageContainer.appendChild(sectionBody);
            } else {
                console.error('Parent element with class "sectionBodySuccess" not found.');
            }
        }

        /**
         * AUTHORIZATION FORM **/
        showApiKeyForm() {
            const sectionBody = this.createElement('div', { className: 'sectionBody sectionBodyAuth' });
            const formBlock = this.createElement('div', { className: 'formBlock' });

            let formId = 'checkYespoAuthorization';

            const form = this.createForm(formId, 'post', '');
            const h4 = this.createHeading(4, '<?php echo __( 'API Key', Y_TEXTDOMAIN ); ?>');

            const fieldGroup0 = this.createFieldGroup();
            const inputApiLine = this.createElement("div", { className: 'inputApiLine' });
            const inputField = this.createInputField();
            const errorAuth = this.createElement("div", { className: 'sendYespoAuthData' });

            inputApiLine.appendChild(inputField);
            inputApiLine.appendChild(errorAuth);
            fieldGroup0.appendChild(inputApiLine);

            const fieldGroup1 = this.createFieldGroup();
            const divEl = this.createElement("div", { className: 'informationText' });
            const spanEl = this.createElement("span", { className: 'api-key-text' }, this.apiKeyText);
            const aEl = this.createElement("a", { href: this.yespoLink }, this.yespoLinkText);
            divEl.appendChild(spanEl);
            divEl.appendChild(aEl);
            fieldGroup1.appendChild(divEl);

            const nonceField = this.createElement('div', { id: 'nonceField' });
            nonceField.innerHTML = this.nonceApiKeyForm;

            const fieldGroup2 = this.createFieldGroup();

            const submitButton = this.createElement('input', { type: 'submit', id: 'sendYespoAuthData', className: 'button button-primary', value: '<?php echo __( 'Synchronize', Y_TEXTDOMAIN ); ?>' });
            fieldGroup2.appendChild(submitButton);

            form.append(h4, fieldGroup0, fieldGroup1, nonceField, fieldGroup2);
            formBlock.appendChild(form);
            sectionBody.appendChild(formBlock);

            const mainContainer = document.querySelector('.settingsSection');
            if (mainContainer) {
                mainContainer.innerHTML = '';
                mainContainer.appendChild(sectionBody);
            }
        }
        /*
        * Methods dealing export data
        * */
        eventListeners(){
            document.addEventListener('DOMContentLoaded', () => {
                this.startExportEventListener();
            });
        }

        checkSynchronization(form){
            var spinner = document.getElementById('spinner');
            if (document.getElementById('sendYespoAuthData')) document.getElementById('sendYespoAuthData').disabled = true;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', this.ajaxUrl, true);
            var formData = new FormData(form);
            formData.append('action', 'check_api_key_esputnik');
            //spinner.style.display = 'block';
            document.querySelector('.sendYespoAuthData').innerHTML = '';
            xhr.send(formData);
            xhr.onreadystatechange = () => {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    //spinner.style.display = 'none';
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        console.log(response.status);
                        try {
                            if(response.status === 'success') {
                                //step 2. Start export
                                console.log('step2');
                                if (document.querySelector('.panelUser') && response.username !== '' && response.username !== undefined) document.querySelector('.panelUser').innerHTML = response.username;
                                this.getNumberDataExport();
                                //document.getElementById('authorization-response1').innerHTML = response.message;
                                //if (document.getElementById('sendYespoAuthData')) document.getElementById('sendYespoAuthData').disabled = true;
                            } else {
                                //console.log(response.message);
                                document.querySelector('.sendYespoAuthData').innerHTML = response.message;
                                if (document.getElementById('sendYespoAuthData')) document.getElementById('sendYespoAuthData').disabled = false;
                            }
                        } catch (error) {
                            console.error('Ошибка при парсинге JSON:', error);
                        }
                    } else {
                        console.error('Произошла ошибка при отправке данных на сервер');
                    }
                }
            };
        }

        getNumberDataExport(){
            Promise.all([
                this.getRequest('get_users_total_export', (response) => {
                    this.users = JSON.parse(response);
                }),
                this.getRequest('get_orders_total_export', (response) => {
                    this.orders = JSON.parse(response);
                })
            ]).then(() => {
                this.route(this.users, this.orders);
                console.log('display stop block');
                this.stopExportEventListener();
            });

        }

        getRequest(action, callback) {
            return new Promise((resolve, reject) => {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', this.ajaxUrl + '?action=' + action, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        let response = xhr.responseText;
                        if (response) {
                            console.log(response);
                            callback(response);
                        }
                        resolve();
                    }
                };
                xhr.send();
            });
        }

        route(users, orders){

            let total = parseInt(users.export) + parseInt(orders.export);
            let status = false;
            if(users.status || orders.status) status = true;
            if( parseInt(users.percent) < 100 ) this.percentTransfered = users.percent;
            else if( parseInt(orders.percent) < 100 ) this.percentTransfered = orders.percent;
            if(total > 0 && status) {
                this.stopExportData();
            } else if(total > 0) {
                this.startExportData();
                if(parseInt(users.export) > 0) this.startExportUsers();
                else if(parseInt(orders.export) > 0) this.startExportOrders();
            } else {
                this. addSuccessMessage();
            }
        }

        startExportData(){
            this.showExportProgress(this.percentTransfered);
            this.updateProgress(this.percentTransfered, 'export');

            console.log('export started inside method');
        }

        showExportProgress(percent){
            this.appendUserPanel(
                '.userPanel',
                'progressContainer',
                'exportProgressBar',
                this.h1,
                percent + '%',
                '',
                ''
            );
            if(document.querySelector('.synhronizationStarted')) document.querySelector('.synhronizationStarted').innerHTML=this.synhStarted;
        }
        startExportEventListener() {
            if(document.querySelector('#checkYespoAuthorization')) {
                let form = document.querySelector('#checkYespoAuthorization');
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    this.checkSynchronization(form);
                });
            }
        }

        //STOP EXPORT
        stopExportData(){
            Promise.all([
                this.getRequest('stop_export_data_to_yespo', (response) => {
                    this.percentTransfered = parseInt(response);
                })
            ]).then(() => {
                //console.log('got result');
                //console.log(this.percentTransfered);
                this.showResumeExportProgress(this.percentTransfered);
                this.resumeExportEventListener();
            });

        }
        stopExportEventListener(){
            if(document.querySelector('#stopExportData')) {
                document.querySelector('#stopExportData').addEventListener('submit', (event) => {
                    event.preventDefault();
                    document.querySelector('#stop-send-data').disabled = true;
                    this.stopExportData();
                });
            }
        }

        //RESUME EXPORT
        showResumeExportProgress(percent){
            this.appendUserPanel(
                '.userPanel',
                'progressContainerStopped',
                'exportProgressBarStopped',
                this.h1,
                percent + '%',
                '',
                'stopped'
            );
        }

        resumeExportEventListener(){
            console.log('resume one');
            if(document.querySelector('#resumeExportData')) {
                console.log('resume two');
                document.querySelector('#resumeExportData').addEventListener('submit', (event) => {
                    console.log('resume three');
                    event.preventDefault();
                    document.querySelector('#resume-send-data').disabled = true;
                    this.resumeExportData();
                });
            }
        }

        resumeExportData(){
            Promise.all([
                this.getRequest('resume_export_data_to_yespo', (response) => {
                    console.log('send to yespo');
                    this.percentTransfered = parseInt(response);
                })
            ]).then(() => {
                console.log('resume scenario');
                //this.startExportData();
                this.stopExportEventListener();
                this.getNumberDataExport();
                console.log('start progress bar');
                this.processExportUsers();
            });

        }

        //ERROR PAGE
        showErrorPage(percent, code) {
            this.appendUserPanel(
                '.userPanel',
                'progressContainerStopped',
                'exportProgressBarStopped',
                this.h1,
                percent,
                'percentRed',
                'error',
                parseInt(code)
            );
        }


        /**
         * start export
         * **/
        startExportUsers() {
            if(this.users.export > 0) {
                this.startExport(
                    'export_user_data_to_esputnik',
                    'users'
                );
            } /*else if(parseInt(this.orders.export) > 0){
                //this.orderExportMessage.style.display="block";
                this.startExportOrders();
            }*/
        }

        startExport(action, service){
            this.startExportChunk(action, service);
        }

        startExportOrders() {
            this.startExport(
                'export_order_data_to_esputnik',
                'orders'
            );
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
                            this.processExportUsers();
                        }
                        if(service === 'orders'){
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
                '#total-orders-export',
                'export_orders_data_to_esputnik',
                '#progressContainerOrders',
                '#exported-orders'
            );
        }

        checkExportStatus(action, way, totalUnits, totalExport, progressUnits, exportedUnits) {
            console.log('started checkExportStatus');
            this.getProcessData(action, (response) => {
                response = JSON.parse(response);
                console.log('got response');
                //if (response && parseInt(response.display) !== 1){
                    //if (response.exported !== null && document.querySelector(exportedUnits) && response.exported >= 0) {
                    if (response.exported !== null && response.exported >= 0) {
                        console.log('before update progress');


                        this.updateProgress(Math.floor( (response.exported / response.total) * 100), 'export');

                        console.log(Math.floor( (response.exported / response.total) * 100), way);
                        //document.querySelector(totalUnits).innerHTML = response.total - response.exported;
                        //document.querySelector(exportedUnits).innerHTML = response.exported;
                    }
                    console.log(response.percent);
                    if( response.percent === 100 && way === 'users' && response.status === 'completed'){
                        console.log('inside to start export orders');
                        this.startExportOrders();
                    } else if(response.percent === 100 && way === 'orders' && response.status === 'completed') this.updateProgress(100);
                    if (response.exported !== response.total && response.status === 'active') {
                        this.exportStatus = true;
                        //this.exportUsersButton.disabled = true;
                        setTimeout(() => {
                            if(way === 'users') this.processExportUsers();
                            if(way === 'orders') this.processExportOrders();
                        }, 5000);
                    } else if(response.status === 'error'){
                        console.log(response.status);
                        console.log(response.code);
                        if(document.querySelector('.processPercent')) response.percent = document.querySelector('.processPercent').innerText;
                        this.showErrorPage(response.percent, response.code);
                    } else if(response.code === null){
                        console.log('inside response null');
                        //if(way === 'users') this.userFinalExportChunk();
                        //if(way === 'orders') this.orderFinalExportChunk();
                    } else {
                        this.usersExportStatus = false;
                    }
                //}
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

            let progressBar = null;
            if(way === 'export' && document.querySelector('#exportProgressBar')) progressBar = document.querySelector('#exportProgressBar');
            else if(document.querySelector('#exportProgressBarStopped')) progressBar = document.querySelector('#exportProgressBarStopped');

            if(document.querySelector('.processPercent ')) document.querySelector('.processPercent ').textContent = `${progress}%`;

            if(progressBar){
                progressBar.style.width = `${progress}%`;
                if(progress > 0){
                    if(document.querySelector('.synhronizationStarted')) document.querySelector('.synhronizationStarted').innerHTML='';
                }
                if (progress >= 100) {

                    if (this.eventSource) {
                        this.eventSource.close();
                    }
                    if( document.querySelector('#stop-send-data') ) document.querySelector('#stop-send-data').disabled = true;
                    setTimeout(() => {
                        this. addSuccessMessage();
                    }, 5000);
                }
            }

        }


    }

    new YespoExportData();

</script>