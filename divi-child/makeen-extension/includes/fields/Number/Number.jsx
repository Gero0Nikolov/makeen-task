// External Dependencies
import React, { Component } from 'react';
import Validator from '../../helpers/Validator';

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

    const validators = {
      min: {
        validator: (value) => {
        
          const minValue = (
            typeof this.props.fieldDefinition.min_value !== 'undefined' ?
            parseInt(this.props.fieldDefinition.min_value) :
            null
          );
          
          const valueInt = parseInt(value);

          return (
            !isNaN(valueInt) &&
            /^-?\d*$/.test(value) &&
            (
              minValue === null ||
              valueInt >= minValue
            )
          );
        },
        default: this.props.fieldDefinition.min_value,
      },
      max: {
        validator: (value) => {
        
          const maxValue = (
            typeof this.props.fieldDefinition.max_value !== 'undefined' ?
            parseInt(this.props.fieldDefinition.max_value) :
            null
          );
  
          const valueInt = parseInt(value);
  
          return (
            !isNaN(valueInt) &&
            /^-?\d*$/.test(value) &&
            (
              maxValue === null ||
              valueInt <= maxValue
            )
          );
        },
        default: this.props.fieldDefinition.max_value,
      },
      isNumber: {
        validator: (value) => {

          const valueInt = parseInt(value);

          return (
            !isNaN(valueInt) &&
            /^-?\d*$/.test(value)
          );
        },
        default: 0,
      },
    };

    const value = Validator.validateData(validators, event.target.value);
    this.props._onChange(this.props.name, value);
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
