export class Input {
  constructor(element) {
    this.element = element;
    this.DATA_ATTR_CUSTOM_VALIDITY = 'data-attr-custom-validity';
    this.inputElement = element.querySelector('.js-input');
  }

  init() {
    this.setCustomValidityMessage();
  }

  /**
   * Sets a custom error message if field doesn't match patter.
   */
  setCustomValidityMessage() {
    if (this.inputElement) {
      const customValidityMsg = this.inputElement.getAttribute(this.DATA_ATTR_CUSTOM_VALIDITY);

      if (customValidityMsg) {
        this.inputElement.setCustomValidity(customValidityMsg);
      }
    }
  }
}
