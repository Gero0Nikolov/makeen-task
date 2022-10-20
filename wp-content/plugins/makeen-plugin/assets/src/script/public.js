class Public {

    init() {

        if (typeof window.mtpShortcodeDataObject === 'undefined') { return false; }

        this.baseSelector = 'makeen-task-shortcode';

        this.selectors = {
            button: `.${this.baseSelector}-button`,
            formContainer: `.${this.baseSelector}-form-container`,
        };

        this.logShortcodeData();
        this.initEventHandlers();
    }

    logShortcodeData() {

        const shortcodesMetaData = window.mtpShortcodeDataObject.shortcodes;

        if (Object.keys(shortcodesMetaData).length === 0) { return undefined; }

        Object.keys(shortcodesMetaData).forEach((id, index) => {

            const shortcodeMetaData = shortcodesMetaData[id];

            console.log(shortcodeMetaData.metaData);
        });
    }

    initEventHandlers() {

        jQuery(document).on('click', this.selectors.button, this.handleShortcodeButtonClick);
    }

    handleShortcodeButtonClick(event) {
        event.preventDefault();
        
        const target = jQuery(event.target);

        const shortcodeId = target.attr('data-shortcode-id');
        const shortcodeDataObject = window.mtpShortcodeDataObject.shortcodes[shortcodeId];

        if (
            target.attr('disabled') ||
            !shortcodeDataObject
        ) { return false; }

        target.attr('disabled', 'disabled');

        jQuery.ajax({
            type: 'POST',
            url: window.mtpShortcodeDataObject.securityData.ajaxUrl,
            data: {
                action: window.mtpShortcodeDataObject.securityData.action,
                nonce: shortcodeDataObject.securityData.nonce,
                formId: shortcodeDataObject.metaData.frm_id,
                shortcodeId: shortcodeDataObject.shortcode.id,
            },
            success: window.Public.handleShortcodeSuccess,
            error: window.Public.handleShortcodeError,
        });

        return undefined;
    }

    handleShortcodeSuccess(response) {

        const result = JSON.parse(response);
    
        const formId = result.data.form_id;
        const shortcodeId = result.data.shortcode_id;

        const button = jQuery(`${window.Public.selectors.button}[data-shortcode-id="${shortcodeId}"][data-form-id="${formId}"]`);
        if (button.length > 0) {
            button.remove();
        }
        
        if (!result.success) {

            result.messages.forEach((message, index) => {
                alert(message);
            });

            return false;
        }

        const formContainer = jQuery(`${window.Public.selectors.formContainer}[data-shortcode-id="${shortcodeId}"][data-form-id="${formId}"]`);
        if (formContainer.length > 0) {
            formContainer.html(result.data.markup);
        }
    }

    handleShortcodeError(response) {

        console.error(response);

        jQuery(window.Public.selectors.button).removeAttr('disabled');
    }
}

window.Public = new Public;

window.addEventListener('load', (event) => {

    window.Public.init();
});