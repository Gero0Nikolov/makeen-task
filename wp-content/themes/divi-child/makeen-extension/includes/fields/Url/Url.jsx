// External Dependencies
import React, { Component } from 'react';
import Validator from '../../helpers/Validator';

// Internal Dependencies
import './style.css';

class Url extends Component {

  static slug = 'maex_url';

  /**
   * Handle input value change.
   *
   * @param {object} event
   */
  _onChange = (event) => {
    const validators = {
      isValidUrl: {
        validator: (value) => {

          if (value.trim().length === 0) { return true; }

          let url;
  
          try {
            url = new URL(value);
          } catch (_) {
            return false;  
          }

          return url.protocol === "http:" || url.protocol === "https:";
        },
        default: event.target.value,
      },
    };

    const value = Validator.validateData(validators, event.target.value);
    this.props._onChange(this.props.name, value);
  }

  render() {

    return(
      <input
        id={`maex-url-${this.props.name}`}
        name={this.props.name}
        value={this.props.value}
        type='url'
        className='maex-url et-fb-settings-option-input et-fb-settings-option-input--block'
        onChange={this._onChange}
      />
    );
  }
}

export default Url;
