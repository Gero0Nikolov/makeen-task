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

        console.log(window.mtpShortcodeDataObject.metaData);
    }

    initEventHandlers() {

        jQuery(document).on('click', this.selectors.button, this.handleShortcodeButtonClick);
    }

    handleShortcodeButtonClick(event) {
        event.preventDefault();
        
        const target = jQuery(event.target);

        if (target.attr('disabled')) { return false; }

        target.attr('disabled', 'disabled');

        jQuery.ajax({
            type: 'POST',
            url: window.mtpShortcodeDataObject.securityData.ajaxUrl,
            data: {
                action: window.mtpShortcodeDataObject.securityData.action,
                nonce: window.mtpShortcodeDataObject.securityData.nonce,
                formId: window.mtpShortcodeDataObject.metaData.frm_id,
            },
            success: window.Public.handleShortcodeSuccess,
            error: window.Public.handleShortcodeError,
        });

        return undefined;
    }

    handleShortcodeSuccess(response) {

        const result = JSON.parse(response);

        if (!result.success) {

            result.messages.forEach((message, index) => {
                alert(message);
            });

            return false;
        }

        jQuery(window.Public.selectors.formContainer).html(result.data.markup);

        jQuery(window.Public.selectors.button).removeAttr('disabled');
    }

    handleShortcodeError(response) {

        console.error(response);

        jQuery(window.Public.selectors.button).removeAttr('disabled');
    }
}

window.Public = new Public;

jQuery(document).ready((event) => {
    
    window.Public.init();
})