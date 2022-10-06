// External Dependencies
import React, { Component } from 'react';

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
    this.props._onChange(this.props.name, event.target.value);
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
