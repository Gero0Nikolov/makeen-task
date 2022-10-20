
class Admin {

    init() {

        this.baseSelector = 'makeen-task-metabox-field';

        this.selectors = {
            checkbox: `.${this.baseSelector}-checkbox`,
        };
        
        this.initEventHandlers();
    }

    initEventHandlers() {

        jQuery(document).on('click', this.selectors.checkbox, this.handleCheckboxChange);
    }

    handleCheckboxChange(event) {

        const target = jQuery(event.target);

        const targetValue = (
            target.prop('checked') ?
            'on' :
            'off'
        );

        target
            .val(targetValue)
            .trigger('change');
    }
};

window.Admin = new Admin;

jQuery(document).ready((event) => {

    window.Admin.init();
});