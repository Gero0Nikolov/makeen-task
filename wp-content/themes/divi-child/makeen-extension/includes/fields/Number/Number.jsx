// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
import './style.css';

class Number extends Component {

  static slug = 'maex_number';

  /**
   * Handle input value change.
   *
   * @param {object} event
   */
  _onChange = (event) => {
    this.props._onChange(this.props.name, event.target.value);
  }

  render() {

    const minValue = (
      typeof this.props.fieldDefinition.min_value !== 'undefined' ?
      this.props.fieldDefinition.min_value :
      ''
    );
    
    const maxValue = (
      typeof this.props.fieldDefinition.max_value !== 'undefined' ?
      this.props.fieldDefinition.max_value :
      ''
    );

    return(
      <input
        id={`maex-number-${this.props.name}`}
        name={this.props.name}
        value={this.props.value}
        type='number'
        className='maex-number et-fb-settings-option-input et-fb-settings-option-input--block'
        onChange={this._onChange}
        min={minValue}
        max={maxValue}
      />
    );
  }
}

export default Number;
