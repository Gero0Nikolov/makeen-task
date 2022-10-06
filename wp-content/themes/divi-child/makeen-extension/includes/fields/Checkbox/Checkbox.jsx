// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
import './style.css';

class Checkbox extends Component {

  static slug = 'maex_checkbox';

  /**
   * Handle input value change.
   *
   * @param {object} event
   */
  _onChange = (event) => {
    const value = (
      event.target.checked ?
      'on' :
      'off'
    );

    this.props._onChange(this.props.name, value);
  }

  render() {

    const id = `maex-checkbox-${this.props.name}`;
    const checked = this.props.value === 'on';

    return(
      <div className="et-fb-settings-options et-fb-option--multiple-checkboxes">
      
        <div className="et-fb-option-container">
          
          <div className="et-fb-multiple-checkboxes-wrap">
            
            <p className="et-fb-multiple-checkbox">
              
              <label htmlFor={id}>
            
                <input
                  id={id}
                  name={this.props.name}
                  type='checkbox'
                  className='maex-checkbox'
                  onChange={this._onChange}
                  checked={checked}
                />

                {this.props.fieldDefinition.checkboxConfig.label}
              </label>
            </p>
          </div>
        </div>
      </div>
    );
  }
}

export default Checkbox;
