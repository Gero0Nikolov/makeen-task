class Validator {

  static validateData(validators, value) {

    const validatorState = {
      validator: null,
      value: value,
    };

    Object.keys(validators).forEach((validatorKey, index) => {

      const validatorObject = validators[validatorKey];
      const validation = validatorObject.validator(value);

      if (
        !validation &&
        !validatorState.validator
      ) {

        validatorState.validator = validatorKey;
        validatorState.value = validatorObject.default;
        return;
      }
    });

    return validatorState.value;
  }
}

export default Validator;
