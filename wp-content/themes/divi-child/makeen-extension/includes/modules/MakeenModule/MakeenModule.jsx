// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
import './style.css';


class MakeenModule extends Component {

  static slug = 'maex_makeen_module';

  render() {
    // const Content = this.props.content;
    // const StartingPoint = this.props.starting_point;

    console.log(this.props);

    return (
      <h1>
        {/* {this.props.starting_point} */}
      </h1>
    );
  }
}

export default MakeenModule;
