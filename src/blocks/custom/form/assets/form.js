import { sendForm } from '../../../helpers/forms';

export class Form {
  constructor(element, {
    DATA_ATTR_FORM_TYPE,
  }) {
    this.formWrapper = element;
    this.form = element.querySelector('.js-block-form-form');
    this.formId = element.getAttribute('id');
    this.spinner = element.querySelector('.js-spinner');
    this.submits = this.form.querySelectorAll('input[type="submit"]');
    this.formMessageSuccess = this.formWrapper.querySelector('.js-form-message--success');
    this.formMessageError = this.formWrapper.querySelector('.js-form-message--error');
    this.overlay = this.formWrapper.querySelector('.js-form-overlay');
    this.basicCaptchaField = this.form.querySelector('.js-block-captcha');
    this.DATA_ATTR_FORM_TYPE = DATA_ATTR_FORM_TYPE;
    this.DATA_ATTR_BUCKAROO_SERVICE = 'data-buckaroo-service';
    this.DATA_ATTR_SUCCESSFULLY_SUBMITTED = 'data-form-successfully-submitted';
    this.DATA_ATTR_FIELD_DONT_SEND = 'data-do-not-send';

    // Get form type from class.
    this.formType = this.form.getAttribute(this.DATA_ATTR_FORM_TYPE);

    this.siteUrl = window.eightshiftForms.siteUrl;
    this.internalServerErrorMessage = window.eightshiftForms.internalServerError;

    this.restRouteUrls = {
      buckarooIdealRestUri: `${this.siteUrl}${window.eightshiftForms.buckaroo.restUri.ideal}`,
      buckarooEmandateRestUri: `${this.siteUrl}${window.eightshiftForms.buckaroo.restUri.emandate}`,
      dynamicsCrmRestUri: `${this.siteUrl}${window.eightshiftForms.dynamicsCrm.restUri}`,
      sendEmailRestUri: `${this.siteUrl}${window.eightshiftForms.sendEmail.restUri}`,
    };

    this.formAccessibilityStatus = {
      loading: window.eightshiftForms.content.formLoading,
      success: window.eightshiftForms.content.formSuccess,
    };

    this.STATE_IS_LOADING = false;
    this.CLASS_FORM_SUBMITTING = 'form-submitting';
    this.CLASS_HIDE_SPINNER = 'hide-spinner';
    this.CLASS_HIDE_MESSAGE = 'hide-form-message';
    this.CLASS_HIDE_OVERLAY = 'hide-form-overlay';
  }


  init() {
    this.form.addEventListener('submit', async (e) => {

      if (this.formType !== 'custom') {
        e.preventDefault();
      }

      if (this.formType === 'dynamics-crm') {
        this.submitForm(this.restRouteUrls.dynamicsCrmRestUri, this.getFormData(this.form));
      }

      if (this.formType === 'buckaroo') {
        const buckarooService = this.form.getAttribute(this.DATA_ATTR_BUCKAROO_SERVICE);
        let restUrl = '';

        switch (buckarooService) {
          case 'ideal':
            restUrl = this.restRouteUrls.buckarooIdealRestUri;
            break;
          case 'emandate':
            restUrl = this.restRouteUrls.buckarooEmandateRestUri;
            break;
          default:
        }

        const response = await this.submitForm(restUrl, this.getFormData(this.form));

        if (response.code === 200 && response.data && response.data.redirectUrl) {
          window.location.href = response.data.redirectUrl;
        }
      }

      if (this.formType === 'email') {
        this.submitForm(this.restRouteUrls.sendEmailRestUri, this.getFormData(this.form));
      }
    });
  }

  async submitForm(url, data) {
    this.startLoading();

    const response = await sendForm(url, data);
    const isSuccess = response && response.code && response.code === 200;
    const is500Error = response && response.code && response.code === 'internal_server_error';

    if (is500Error) {
      response.message = this.internalServerErrorMessage;
    }

    this.endLoading(isSuccess, response);

    return response;
  }

  startLoading() {
    this.STATE_IS_LOADING = true;
    this.form.classList.add(this.CLASS_FORM_SUBMITTING);
    this.spinner.classList.remove(this.CLASS_HIDE_SPINNER);
    this.overlay.classList.remove(this.CLASS_HIDE_OVERLAY);
    this.spinner.innerHTML = `<p>${this.formAccessibilityStatus.loading}</p>`;
    [this.formMessageSuccess, this.formMessageError].forEach((msgElem) => msgElem.classList.add(this.CLASS_HIDE_MESSAGE));

    this.submits.forEach((submit) => {
      submit.disabled = true;
    });
  }

  endLoading(isSuccess, response) {
    const state = isSuccess ? 'success' : 'error';
    this.STATE_IS_LOADING = false;
    this.form.classList.remove(this.CLASS_FORM_SUBMITTING);
    this.spinner.classList.add(this.CLASS_HIDE_SPINNER);
    this.overlay.classList.add(this.CLASS_HIDE_OVERLAY);
    this.spinner.innerHTML = `<p>${this.formAccessibilityStatus[state]}</p>`;
    this.submits.forEach((submit) => {
      submit.disabled = false;
    });

    if (isSuccess) {
      this.formMessageSuccess.classList.remove(this.CLASS_HIDE_MESSAGE);
      this.form.setAttribute(this.DATA_ATTR_SUCCESSFULLY_SUBMITTED, 1);
    } else {
      this.formMessageError.textContent = response.message;
      this.formMessageError.classList.remove(this.CLASS_HIDE_MESSAGE);
      this.form.setAttribute(this.DATA_ATTR_SUCCESSFULLY_SUBMITTED, 0);
    }
  }

  /**
   * Returns all form fields as { key, value } objects.
   * Replaces placeholder values with values from a field.
   * Removes fields which shouldn't be sent.
   */
  getFormData(form) {
    return [...form.elements].filter((formElem) => {

      // Filter out unchecked radio buttons.
      if (formElem.getAttribute('type') === 'radio' && !formElem.checked) {
        return false;
      }

      return formElem.name && (!formElem.hasAttribute(this.DATA_ATTR_FIELD_DONT_SEND));
    }).map((formElem) => {
      return {
        key: formElem.name,
        value: this.replacePlaceholders(formElem.value, [...form.elements]),
      };
    });
  }

  /**
   * Replace all placeholders inside value of a form's field in format: [[form-field-name]] with values from
   * other form fields.
   *
   * Used when we need to have multiple form fields send concatenated inside a single field.
   *
   * @param {string} value Value of form field (haystack) in which we're looking for placeholders
   * @param {array} formElements All form's elements.
   * @return {string}
   */
  replacePlaceholders(value, formElements) {

    // Lets create a name: value map we're going to use for replacing stuff.
    const valueMap = formElements.filter((formElem) => formElem.name).reduce((obj, item) => (obj[item.name] = item.value, obj), {}); /* eslint-disable-line no-return-assign, no-sequences */
    const relevantKeys = Object.keys(valueMap).filter((newValueMap) => newValueMap.length);

    // If nothing in valueMap has keys then we don't need to do any replacing.
    if (!relevantKeys.length) {
      return value;
    }

    // Now let's create a regex that's going to replace all placeholders with actual values (only if
    // those fields exist in form ofc).
    return value.replace(new RegExp(relevantKeys.map((key) => `\\[\\[${key}\\]\\]`).join('|'), 'gi'), (matched) => {
      const matchedAsKey = matched.replaceAll('[', '').replaceAll(']', '');
      return valueMap[matchedAsKey] ?? matched;
    });
  }
}
