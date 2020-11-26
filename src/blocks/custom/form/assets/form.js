import { sendForm } from '../../../helpers/forms';

export class Form {
  constructor(element, {
    DATA_ATTR_IS_FORM_COMPLEX,
    DATA_ATTR_FORM_TYPE,
    DATA_ATTR_FORM_TYPES_COMPLEX,
    DATA_ATTR_FORM_TYPES_COMPLEX_REDIRECT,
  }) {
    this.formWrapper = element;
    this.form = element.querySelector('.js-block-form-form');
    this.formId = element.getAttribute('id');
    this.spinner = element.querySelector('.js-spinner');
    this.submits = this.form.querySelectorAll('input[type="submit"]');
    this.formMessageSuccess = this.formWrapper.querySelector('.js-form-message--success');
    this.formErrorMessageWrapper = this.formWrapper.querySelector('.js-form-error-message-wrapper');
    this.overlay = this.formWrapper.querySelector('.js-form-overlay');
    this.basicCaptchaField = this.form.querySelector('.js-block-captcha');
    this.DATA_ATTR_IS_FORM_COMPLEX = DATA_ATTR_IS_FORM_COMPLEX;
    this.DATA_ATTR_FORM_TYPE = DATA_ATTR_FORM_TYPE;
    this.DATA_ATTR_FORM_TYPES_COMPLEX = DATA_ATTR_FORM_TYPES_COMPLEX;
    this.DATA_ATTR_FORM_TYPES_COMPLEX_REDIRECT = DATA_ATTR_FORM_TYPES_COMPLEX_REDIRECT;
    this.DATA_ATTR_BUCKAROO_SERVICE = 'data-buckaroo-service';
    this.DATA_ATTR_SUCCESSFULLY_SUBMITTED = 'data-form-successfully-submitted';
    this.DATA_ATTR_FIELD_DONT_SEND = 'data-do-not-send';
    this.DATA_ATTR_REDIRECT_URL_SUCCESS = 'data-redirect-on-success';
    this.EVENT_SUBMIT_SUCCESS = 'ef-simple-submit-success';
    this.EVENT_SUBMIT_ERROR = 'ef-simple-submit-error';
    this.EVENT_SUBMIT_COMPLEX_SUCCESS = 'ef-complex-submit-success';
    this.EVENT_SUBMIT_COMPLEX_ERROR = 'ef-complex-submit-error';
    this.STATE_IS_LOADING = false;
    this.CLASS_FORM_SUBMITTING = 'form-submitting';
    this.CLASS_HIDE_SPINNER = 'hide-spinner';
    this.CLASS_HIDE_MESSAGE = 'is-form-message-hidden';
    this.CLASS_HIDE_OVERLAY = 'hide-form-overlay';

    this.updateAllElements();

    this.siteUrl = window.eightshiftForms.siteUrl;
    this.internalServerErrorMessage = window.eightshiftForms.internalServerError;

    this.restRouteUrls = {};

    if (window.eightshiftForms.mailchimp) {
      this.restRouteUrls.mailchimpRestUri = `${this.siteUrl}${window.eightshiftForms.mailchimp.restUri}`;
    }

    if (window.eightshiftForms.dynamicsCrm) {
      this.restRouteUrls.dynamicsCrmRestUri = `${this.siteUrl}${window.eightshiftForms.dynamicsCrm.restUri}`;
    }

    if (window.eightshiftForms.sendEmail) {
      this.restRouteUrls.sendEmailRestUri = `${this.siteUrl}${window.eightshiftForms.sendEmail.restUri}`;
    }

    if (window.eightshiftForms.buckaroo) {
      this.restRouteUrls.buckarooIdealRestUri = `${this.siteUrl}${window.eightshiftForms.buckaroo.restUri.ideal}`;
      this.restRouteUrls.buckarooEmandateRestUri = `${this.siteUrl}${window.eightshiftForms.buckaroo.restUri.emandate}`;
      this.restRouteUrls.buckarooPayByEmailRestUri = `${this.siteUrl}${window.eightshiftForms.buckaroo.restUri.payByEmail}`;
    }

    this.formAccessibilityStatus = {
      loading: window.eightshiftForms.content.formLoading,
      success: window.eightshiftForms.content.formSuccess,
    };

    this.errorMessageClasses = [
      'form-message',
      'js-form-message',
      'js-form-message--error',
      'form-message__type--error',
    ];

    this.errors = [];
  }


  /**
   * Setup all form handling.
   */
  init() {
    this.form.addEventListener('submit', async (e) => {
      this.startLoading();
      this.updateAllElements();

      if (!this.isComplex) {
        const { isSuccess, response } = await this.submitFormSimple(e, this.formType);

        if (isSuccess) {
          this.submitEvent({ eventName: this.EVENT_SUBMIT_SUCCESS, formData: this.getFormData(this.form), response });
          this.showSuccessMessage();
        } else {
          this.submitEvent({ eventName: this.EVENT_SUBMIT_ERROR, formData: this.getFormData(this.form), response });
          this.errors.push(response.message || 'Unknown Error');
          this.showErrorMessages(this.errors);
        }
        this.endLoading(isSuccess);

        this.maybeRedirect(isSuccess);
      } else {

        // Submit to all regular routes in parallel.
        const submitPromises = [];
        for (const typeComplex of this.formTypesComplex) {
          submitPromises.push(this.submitFormSimple(e, typeComplex));
        }
        const submitStatuses = await Promise.all(submitPromises);
        for (const submitStatus of submitStatuses) {
          if (!submitStatus.isSuccess) {
            this.errors.push(submitStatus.response.message || 'Unknown Error');
          }
        }

        // Now let's submit to redirect route after (only if set)
        if (this.formTypesComplexRedirect.length) {
          const { isSuccess, response } = await this.submitFormSimple(e, this.formTypesComplexRedirect[0] || '');

          if (!isSuccess) {
            this.errors.push(response.message || 'Unknown Error');
          }
        }

        const isComplexSuccess = this.errors.length === 0;

        if (isComplexSuccess) {
          this.submitEvent({ eventName: this.EVENT_SUBMIT_COMPLEX_SUCCESS, formData: this.getFormData(this.form) });
          this.showSuccessMessage();
        } else {
          this.submitEvent({ eventName: this.EVENT_SUBMIT_COMPLEX_ERROR, formData: this.getFormData(this.form), errors: this.errors });
          this.showErrorMessages(this.errors);
        }

        this.endLoading(isComplexSuccess);

        this.maybeRedirect(isComplexSuccess);
      }
    });
  }

  /**
   * Updates form types and all it's configuration. We need to extract this and do it during initialization +
   * before submit because this configuration could be manipulated inside a project.
   */
  updateAllElements() {

    // Get form type from class.
    this.formType = this.form.getAttribute(this.DATA_ATTR_FORM_TYPE);
    this.formTypesComplex = this.form.getAttribute(this.DATA_ATTR_FORM_TYPES_COMPLEX) || null;
    this.formTypesComplex = this.formTypesComplex ? this.formTypesComplex.split(',') : [];
    this.formTypesComplexRedirect = this.form.getAttribute(this.DATA_ATTR_FORM_TYPES_COMPLEX_REDIRECT) || null;
    this.formTypesComplexRedirect = this.formTypesComplexRedirect ? this.formTypesComplexRedirect.split(',') : [];
    this.isComplex = this.form.hasAttribute(this.DATA_ATTR_IS_FORM_COMPLEX);

    // Redirection
    this.shouldRedirect = this.form.hasAttribute(this.DATA_ATTR_REDIRECT_URL_SUCCESS);
    if (this.shouldRedirect) {
      this.redirectUrlSuccess = this.form.getAttribute(this.DATA_ATTR_REDIRECT_URL_SUCCESS) || '';
    }
  }

  /**
   * Redirects user on success if needed.
   *
   * @param {boolean} success Is form successfully submitted
   */
  maybeRedirect(success) {
    if (!success || !this.shouldRedirect || !this.redirectUrlSuccess) {
      return;
    }

    window.location.href = this.redirectUrlSuccess;
  }

  /**
   * Submits the form for a single type.
   *
   * @param {EventObject} e Event object.
   * @param {string} formType Current form type.
   */
  async submitFormSimple(e, formType) {
    let submitStatus = {};

    if (formType !== 'custom') {
      e.preventDefault();
      submitStatus = { response: {}, isSuccess: true };
    }

    if (formType === 'dynamics-crm') {
      submitStatus = this.submitForm(this.restRouteUrls.dynamicsCrmRestUri, this.getFormData(this.form));
    }

    if (formType === 'buckaroo') {
      const buckarooService = this.form.getAttribute(this.DATA_ATTR_BUCKAROO_SERVICE);
      let restUrl = '';

      switch (buckarooService) {
        case 'ideal':
          restUrl = this.restRouteUrls.buckarooIdealRestUri;
          break;
        case 'emandate':
          restUrl = this.restRouteUrls.buckarooEmandateRestUri;
          break;
        case 'pay-by-email':
          restUrl = this.restRouteUrls.buckarooPayByEmailRestUri;
          break;
        default:
      }

      submitStatus = await this.submitForm(restUrl, this.getFormData(this.form));
      const { response } = submitStatus;

      if (response.code === 200 && response.data && response.data.redirectUrl) {
        window.location.href = response.data.redirectUrl;
      } else {
        submitStatus.isSuccess = false;
      }
    }

    if (formType === 'mailchimp') {
      submitStatus = this.submitForm(this.restRouteUrls.mailchimpRestUri, this.getFormData(this.form));
    }

    if (formType === 'email') {
      submitStatus = this.submitForm(this.restRouteUrls.sendEmailRestUri, this.getFormData(this.form));
    }

    if (formType === 'custom-event') {
      const customEvents = [...this.form.elements].filter((formElem) => formElem.getAttribute('name') === 'custom-events[]').map((formElem) => {
        return formElem.value;
      });

      customEvents.forEach((eventName) => {
        this.submitEvent({ eventName, formData: this.getFormData(this.form) });
      });

      submitStatus = { response: {}, isSuccess: true };
    }

    return submitStatus;
  }

  /**
   * Submits a custom JS event.
   *
   * @param {object} props Props.
   */
  submitEvent({ eventName, formData, response = {} }) {
    const submitEvent = new CustomEvent(eventName, {
      detail: {
        response,
        formData,
      },
    });
    this.form.dispatchEvent(submitEvent);
  }

  /**
   * Submits form.
   *
   * @param {string} url  Url to where to send request.
   * @param {object} data Data to send to endpoint.
   */
  async submitForm(url, data) {
    const response = await sendForm(url, data);
    const isSuccess = response && response.code && response.code === 200;
    const is500Error = response && response.code && response.code === 'internal_server_error';

    if (is500Error) {
      response.message = this.internalServerErrorMessage;
    }

    return {
      isSuccess,
      response,
    };
  }

  /**
   * Starts the form loading state.
   */
  startLoading() {
    this.STATE_IS_LOADING = true;
    this.form.classList.add(this.CLASS_FORM_SUBMITTING);
    this.spinner.classList.remove(this.CLASS_HIDE_SPINNER);
    this.overlay.classList.remove(this.CLASS_HIDE_OVERLAY);
    this.spinner.innerHTML = `<p>${this.formAccessibilityStatus.loading}</p>`;
    [this.formMessageSuccess, this.formErrorMessageWrapper].forEach((msgElem) => msgElem.classList.add(this.CLASS_HIDE_MESSAGE));
    this.errors = [];
    this.formErrorMessageWrapper.innerHTML = '';

    this.submits.forEach((submit) => {
      submit.disabled = true;
    });
  }

  /**
   * Ends the form loading state
   *
   * @param {bool} isSuccess State of the response.
   */
  endLoading(isSuccess) {
    const state = isSuccess ? 'success' : 'error';
    this.STATE_IS_LOADING = false;
    this.form.classList.remove(this.CLASS_FORM_SUBMITTING);
    this.spinner.classList.add(this.CLASS_HIDE_SPINNER);
    this.overlay.classList.add(this.CLASS_HIDE_OVERLAY);
    this.spinner.innerHTML = `<p>${this.formAccessibilityStatus[state]}</p>`;
    this.submits.forEach((submit) => {
      submit.disabled = false;
    });
  }

  /**
   * Un-hides success message.
   */
  showSuccessMessage() {
    this.formMessageSuccess.classList.remove(this.CLASS_HIDE_MESSAGE);
    this.form.setAttribute(this.DATA_ATTR_SUCCESSFULLY_SUBMITTED, 1);
  }

  /**
   * Un-hides all error messages.
   *
   * @param {array} errors Array of error string.
   */
  showErrorMessages(errors) {

    errors.forEach((error) => {
      this.appendErrorMessage(error);
    });
    this.formErrorMessageWrapper.classList.remove(this.CLASS_HIDE_MESSAGE);

    this.form.setAttribute(this.DATA_ATTR_SUCCESSFULLY_SUBMITTED, 0);
  }

  /**
   * Appends a new error message element at the end of error message wrapper.
   *
   * @param {string} error Error's contents
   */
  appendErrorMessage(error) {
    const errorMessageElem = document.createElement('div');
    errorMessageElem.classList.add(this.errorMessageClasses);
    errorMessageElem.innerHTML = error;
    this.formErrorMessageWrapper.appendChild(errorMessageElem);
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

      // Filter out unchecked checkboxes buttons.
      if (formElem.getAttribute('type') === 'checkbox' && !formElem.checked) {
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
