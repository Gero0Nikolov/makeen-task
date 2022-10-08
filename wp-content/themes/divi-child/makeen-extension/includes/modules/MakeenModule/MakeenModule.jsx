// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
import './style.css';

class MakeenModule extends Component {

  static slug = 'maex_makeen_module';

  render() {

    const formHtml = (
      this.state &&
      this.state.formHtml &&
      this.state.formidableFormId === this.props.formidable_forms ?
      this.state.formHtml :
      ''
    );

    const formidableFormsPluginIsActive = (
      !this.state ||
      (
        this.state &&
        this.state.formidableFormsPluginIsActive
      )
    );

    const fieldsKeyValue = {
			'starting_point': null,
			'trim_start': null,
			'trim_end': null,
			'start_img': null,
			'end_img': null,
			'src': null,
			'has_cc': null,
			'is_live': null,
			'formidable_forms': null,
    };

    Object.keys(fieldsKeyValue).forEach((key, index) => {

      if (typeof this.props[key] !== 'undefined') {

        fieldsKeyValue[key] = this.props[key];
      }
    });

    if (
      formidableFormsPluginIsActive &&
      formHtml.trim().length === 0 &&
      this.props.formidable_forms !== '0'
    ) {

      this.renderFormidableForm(this.props.formidable_forms).then((result) => {

        this.setState({ 
          formHtml: result.data, 
          formidableFormId: this.props.formidable_forms,
          formidableFormsPluginIsActive: result.formidable_forms_plugin_is_active,
        });
      });
    }

    console.log(fieldsKeyValue);

    return (
      <div className='maex_makeen_module' dangerouslySetInnerHTML={{ __html: formHtml, }}>
      </div>
    );
  }

  async renderFormidableForm(formidableFormId) {
    const response = await fetch(window.maexAjaxObject.url +'?action=render_formidable_form&formidable_form_id='+ formidableFormId);
    const json = await response.json().then((result) => {
      return result;
    });

    return json;
  }
}

export default MakeenModule;
